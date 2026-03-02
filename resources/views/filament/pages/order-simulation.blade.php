<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $this->getSimulationStats()['total_orders'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Total Orders
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-warning-600 dark:text-warning-400">
                        {{ $this->getSimulationStats()['pending_orders'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Pending Orders
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600 dark:text-success-400">
                        {{ $this->getSimulationStats()['completed_orders'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Paid Orders
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $this->getSimulationStats()['total_revenue'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Total Revenue (excl. voided)
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Simulation Form -->
        <x-filament::section>
            <x-slot name="heading">
                Simulasi Kasir
            </x-slot>
            <x-slot name="description">
                Buat order test dengan snapshot pricing, customer type detection, dan stock reduction.
            </x-slot>

            <form wire:submit="simulateOrder">
                {{ $this->form }}

                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button type="submit" color="primary">
                        Create Order
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <!-- Information -->
        <x-filament::section>
            <x-slot name="heading">
                How It Works
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <p><strong>Customer Rules:</strong></p>
                <ul>
                    <li><strong>Student selected:</strong> Uses student_price, auto-snapshots name, customer_type = 'student'</li>
                    <li><strong>Guest with name:</strong> Uses regular price, manual name, customer_type = 'guest'</li>
                    <li><strong>Anonymous:</strong> Uses regular price, no name, customer_type = 'guest'</li>
                </ul>

                <p class="mt-4"><strong>What happens on submit:</strong></p>
                <ul>
                    <li>Creates order with customer snapshot (immutable)</li>
                    <li>Creates order items with product_name, price, base_price snapshots</li>
                    <li>Calculates subtotal, discount, tax, grand_total</li>
                    <li>Reduces ingredient stock via FIFO/FEFO (only for menus with recipes)</li>
                </ul>

                <p class="mt-4 text-sm text-gray-500">
                    <strong>Note:</strong> Orders created here are real database entries. Use the Orders page to view or void them.
                </p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
