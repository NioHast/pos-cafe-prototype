<?php

namespace App\Filament\Pages;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class OrderSimulation extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-beaker';

    protected static string | \UnitEnum | null $navigationGroup = 'Transactions';

    protected static ?string $navigationLabel = 'Order Simulation';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Order Simulation & Testing';

    protected string $view = 'filament.pages.order-simulation';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Simulation Configuration')
                    ->schema([
                        Components\TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->default('Test Customer')
                            ->required(),
                        Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'debit' => 'Debit Card',
                                'credit' => 'Credit Card',
                                'e-wallet' => 'E-Wallet',
                            ])
                            ->default('cash')
                            ->required()
                            ->native(false),
                        Components\Repeater::make('items')
                            ->label('Order Items')
                            ->schema([
                                Components\Select::make('menu_id')
                                    ->label('Menu Item')
                                    ->options(Menu::pluck('name', 'id'))
                                    ->required()
                                    ->reactive()
                                    ->searchable()
                                    ->native(false),
                                Components\TextInput::make('quantity')
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
                            ->minItems(1),
                    ])->columns(2),
            ])
            ->statePath('data');
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
                'customer_name' => $data['customer_name'],
                'total_amount' => $total,
                'payment_method' => $data['payment_method'],
                'status' => 'pending',
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

            $this->form->fill();
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
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_revenue' => 'Rp ' . number_format(Order::where('status', 'completed')->sum('total_amount'), 0, ',', '.'),
        ];
    }
}
