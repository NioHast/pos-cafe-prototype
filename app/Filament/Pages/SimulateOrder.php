<?php

namespace App\Filament\Pages;

use App\Models\Menu;
use App\Services\InventoryService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;

class SimulateOrder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-beaker';

    protected string $view = 'filament.pages.simulate-order';

    protected static ?string $title = 'Simulasi Pesanan';

    protected static ?string $navigationLabel = 'Simulasi Pesanan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'items' => [
                [
                    'menu_id' => null,
                    'quantity' => 1,
                ],
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Repeater::make('items')
                    ->label('Item Pesanan')
                    ->schema([
                        Select::make('menu_id')
                            ->label('Menu')
                            ->options(Menu::where('status', 'available')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $menu = Menu::find($state);
                                    if ($menu) {
                                        $set('price', $menu->price);
                                    }
                                }
                            }),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->reactive(),
                        TextInput::make('price')
                            ->label('Harga')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->addActionLabel('Tambah Item')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => 
                        $state['menu_id'] 
                            ? Menu::find($state['menu_id'])?->name . ' (x' . ($state['quantity'] ?? 1) . ')'
                            : 'Item Baru'
                    ),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('simulate')
                ->label('Simulasikan Pesanan')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action('simulateOrder'),
            Action::make('reset')
                ->label('Reset Form')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->form->fill([
                        'items' => [
                            [
                                'menu_id' => null,
                                'quantity' => 1,
                            ],
                        ],
                    ]);
                }),
        ];
    }

    public function simulateOrder(): void
    {
        $data = $this->form->getState();

        if (empty($data['items'])) {
            Notification::make()
                ->title('Error')
                ->body('Tidak ada item pesanan.')
                ->danger()
                ->send();
            return;
        }

        // Filter out empty items
        $items = collect($data['items'])
            ->filter(fn ($item) => !empty($item['menu_id']) && !empty($item['quantity']))
            ->toArray();

        if (empty($items)) {
            Notification::make()
                ->title('Error')
                ->body('Harap pilih menu dan masukkan jumlah.')
                ->danger()
                ->send();
            return;
        }

        try {
            $inventoryService = app(InventoryService::class);
            $result = $inventoryService->decreaseStockForOrder($items);

            // Build success message with details
            $message = $result['message'] . "\n\n";
            $message .= "Detail Pengurangan Stok:\n";
            
            foreach ($result['changes'] as $change) {
                $message .= "- {$change['ingredient_name']}: -{$change['total_deducted']} {$change['unit']}\n";
            }

            Notification::make()
                ->title('Berhasil!')
                ->body($message)
                ->success()
                ->duration(10000)
                ->send();

            // Reset form after successful simulation
            $this->form->fill([
                'items' => [
                    [
                        'menu_id' => null,
                        'quantity' => 1,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal!')
                ->body($e->getMessage())
                ->danger()
                ->duration(10000)
                ->send();
        }
    }
}
