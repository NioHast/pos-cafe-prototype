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
                        Completed Orders
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ $this->getSimulationStats()['total_revenue'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Total Revenue
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- Simulation Form -->
        <x-filament::section>
            <x-slot name="heading">
                Create Test Order
            </x-slot>
            <x-slot name="description">
                Simulate orders to test FIFO/FEFO inventory logic and system behavior
            </x-slot>

            <form wire:submit="simulateOrder">
                {{ $this->form }}

                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button type="submit" color="primary">
                        Simulate Order
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <!-- Information -->
        <x-filament::section>
            <x-slot name="heading">
                Simulation Information
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <p><strong>What this simulation does:</strong></p>
                <ul>
                    <li>Creates test orders with specified items and quantities</li>
                    <li>Tests FIFO/FEFO ingredient consumption logic</li>
                    <li>Validates inventory deduction accuracy</li>
                    <li>Checks order processing workflow</li>
                </ul>

                <p class="mt-4"><strong>Note:</strong> Simulated orders are real database entries. Use the Orders page to manage or delete test data.</p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
