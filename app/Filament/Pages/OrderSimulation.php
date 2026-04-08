<?php

namespace App\Filament\Pages;

use App\Models\AppliedPromotion;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Services\OrderCalculationService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class OrderSimulation extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-beaker';

    protected static string | \UnitEnum | null $navigationGroup = 'Tools';

    protected static ?string $navigationLabel = 'Order Simulation';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Simulasi Kasir (Dev Tool)';

    protected string $view = 'filament.pages.order-simulation';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'payment_method' => 'cash',
            'promotion_strategy' => 'single_best',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('customer_id')
                ->label('Customer (Student)')
                ->options(
                    User::whereHas('role', fn ($q) => $q->where('name', 'student'))
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->nullable()
                ->placeholder('Guest (no account)')
                ->helperText('Select a student for student pricing, or leave empty for guest'),

            TextInput::make('customer_name')
                ->label('Guest Name (optional)')
                ->maxLength(255)
                ->placeholder('Leave empty for anonymous guest')
                ->helperText('Only used when no student is selected'),

            Select::make('payment_method')
                ->label('Payment Method')
                ->options([
                    'cash' => 'Cash',
                    'debit' => 'Debit Card',
                    'credit' => 'Credit Card',
                    'qris' => 'QRIS',
                    'e-wallet' => 'E-Wallet',
                ])
                ->required()
                ->native(false),

            Select::make('promotion_strategy')
                ->label('Promotion Mode')
                ->options([
                    'single_best' => 'Pilih Satu Promo Terbaik',
                    'stacking' => 'Tumpuk Promo Terpilih',
                ])
                ->default('single_best')
                ->required()
                ->native(false)
                ->helperText('Kasir bisa pilih satu promo terbaik atau kombinasikan promo terpilih'),

            Select::make('promotion_ids')
                ->label('Promosi yang Dipakai')
                ->options(
                    Promotion::query()
                        ->where('status', 'active')
                        ->whereDate('start_date', '<=', now())
                        ->whereDate('end_date', '>=', now())
                        ->pluck('name', 'id')
                )
                ->multiple()
                ->searchable()
                ->native(false)
                ->helperText('Kosongkan untuk auto-select berdasarkan menu dan rule promo'),

            Repeater::make('items')
                ->label('Order Items')
                ->schema([
                    Select::make('menu_id')
                        ->label('Menu Item')
                        ->options(Menu::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->native(false),
                    TextInput::make('quantity')
                        ->label('Quantity')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required(),
                ])
                ->columns(2)
                ->defaultItems(1)
                ->addActionLabel('Add Item')
                ->required()
                ->minItems(1)
                ->columnSpanFull(),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function simulateOrder(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            $calcService = app(OrderCalculationService::class);

            // Resolve customer
            $customer = !empty($data['customer_id'])
                ? User::with('role', 'studentProfile')->find($data['customer_id'])
                : null;

            // Build customer snapshot
            $guestName = empty($data['customer_id']) ? ($data['customer_name'] ?? null) : null;
            $customerSnapshot = $calcService->snapshotCustomer($customer, $guestName);

            // Create order (observer auto-fills customer_type if not set)
            $order = Order::create(array_merge($customerSnapshot, [
                'cashier_id' => auth()->id(),
                'payment_method' => $data['payment_method'],
                'payment_status' => 'paid',
                'total_price' => 0, // will be recalculated
            ]));

            // Create order items with snapshots
            $aggregatedPromotions = [];
            foreach ($data['items'] as $item) {
                $menu = Menu::findOrFail($item['menu_id']);
                $itemData = $calcService->calculateItem(
                    $menu,
                    (int) $item['quantity'],
                    $customer,
                    $data['promotion_ids'] ?? [],
                    $data['promotion_strategy'] ?? 'single_best',
                    true,
                );

                $itemPromotions = $itemData['applied_promotions'] ?? [];
                unset($itemData['applied_promotions']);

                OrderItem::create(array_merge($itemData, [
                    'order_id' => $order->id,
                    'handled_by' => auth()->id(),
                ]));

                foreach ($itemPromotions as $promo) {
                    $promoId = (int) $promo['promotion_id'];
                    if (!isset($aggregatedPromotions[$promoId])) {
                        $aggregatedPromotions[$promoId] = [
                            'order_id' => $order->id,
                            'promotion_id' => $promoId,
                            'discount_type' => $promo['discount_type'],
                            'discount_value' => $promo['discount_value'],
                            'discount_amount' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $aggregatedPromotions[$promoId]['discount_amount'] += $promo['discount_amount'];
                }
            }

            if (!empty($aggregatedPromotions)) {
                AppliedPromotion::insert(array_values($aggregatedPromotions));

                Promotion::whereIn('id', array_keys($aggregatedPromotions))
                    ->increment('usage_count');
            }

            // Recalculate totals and reduce stock
            OrderObserver::recalculateTotals($order);
            OrderObserver::reduceStockForOrder($order);

            DB::commit();

            $order->refresh();

            Notification::make()
                ->title('Order Created Successfully')
                ->success()
                ->body(sprintf(
                    "Order #%s | %s (%s) | Total: Rp %s",
                    substr($order->id, 0, 8),
                    $order->customer_display_name,
                    $order->customer_type,
                    number_format($order->grand_total, 0, ',', '.')
                ))
                ->send();

            // Reset form
            $this->form->fill([
                'payment_method' => 'cash',
                'promotion_strategy' => 'single_best',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Simulation Failed')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getSimulationStats(): array
    {
        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('payment_status', 'pending')->count(),
            'completed_orders' => Order::where('payment_status', 'paid')->count(),
            'total_revenue' => 'Rp ' . number_format(
                Order::where('payment_status', 'paid')->whereNull('voided_at')->sum('grand_total'),
                0, ',', '.'
            ),
        ];
    }
}
