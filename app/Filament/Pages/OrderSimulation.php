<?php

namespace App\Filament\Pages;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OrderSimulation extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-beaker';

    protected static string | \UnitEnum | null $navigationGroup = 'Transactions';

    protected static ?string $navigationLabel = 'Order Simulation';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Order Simulation & Testing';

    protected string $view = 'filament.pages.order-simulation';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'customer_name' => 'Test Customer',
            'payment_method' => 'cash',
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('customer_name')
                ->label('Customer Name')
                ->required()
                ->maxLength(255),
            Select::make('payment_method')
                ->label('Payment Method')
                ->options([
                    'cash' => 'Cash',
                    'debit' => 'Debit Card',
                    'credit' => 'Credit Card',
                    'e-wallet' => 'E-Wallet',
                ])
                ->required()
                ->native(false),
            Repeater::make('items')
                ->label('Order Items')
                ->schema([
                    Select::make('menu_id')
                        ->label('Menu Item')
                        ->options(Menu::pluck('name', 'id'))
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
            // Calculate total
            $total = 0;
            foreach ($data['items'] as $item) {
                $menu = Menu::find($item['menu_id']);
                $total += $menu->price * $item['quantity'];
            }

            // Create order
            $order = Order::create([
                'customer_id' => null, // Simulation doesn't need real customer
                'cashier_id' => auth()->id(),
                'total_price' => $total,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
            ]);

            // Create order items
            foreach ($data['items'] as $item) {
                $menu = Menu::find($item['menu_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $item['menu_id'],
                    'quantity' => $item['quantity'],
                    'price' => $menu->price,
                    'subtotal' => $menu->price * $item['quantity'],
                ]);
            }

            Notification::make()
                ->title('Order Simulated Successfully')
                ->success()
                ->body("Order #{$order->id} created with total Rp " . number_format($total, 0, ',', '.'))
                ->send();

            // Reset form
            $this->form->fill([
                'customer_name' => 'Test Customer',
                'payment_method' => 'cash',
                'items' => [],
            ]);
        } catch (\Exception $e) {
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
            'total_revenue' => 'Rp ' . number_format(Order::where('payment_status', 'paid')->sum('total_price'), 0, ',', '.'),
        ];
    }
}
