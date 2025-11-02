<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Association Rules (FP-Growth) -->
        <x-filament::section>
            <x-slot name="heading">
                Association Rules (FP-Growth Algorithm)
            </x-slot>
            <x-slot name="description">
                Frequent itemset patterns showing which products are commonly purchased together
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Association Rule</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Support</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Confidence</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lift</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->getMockAssociationData() as $rule)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rule['rule'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $rule['support'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $rule['confidence'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $rule['lift'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <!-- Customer Clustering -->
        <x-filament::section>
            <x-slot name="heading">
                Customer Clustering (K-Means)
            </x-slot>
            <x-slot name="description">
                Customer segments based on spending behavior and visit frequency
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cluster</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer Count</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg. Spend</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Frequency</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->getMockClusterData() as $cluster)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $cluster['cluster'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $cluster['count'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $cluster['avg_spend'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $cluster['frequency'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Demand Prediction -->
            <x-filament::section>
                <x-slot name="heading">
                    Demand Prediction (Random Forest)
                </x-slot>
                <x-slot name="description">
                    12-month sales forecast based on historical patterns
                </x-slot>

                <div id="demandChart"></div>
            </x-filament::section>

            <!-- Stock Level Analysis -->
            <x-filament::section>
                <x-slot name="heading">
                    Stock Level Analysis
                </x-slot>
                <x-slot name="description">
                    Current inventory status distribution
                </x-slot>

                <div id="stockChart"></div>
            </x-filament::section>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Demand Prediction Chart
            const predictionData = @js($this->getMockPredictionData());
            const demandChart = new ApexCharts(document.querySelector('#demandChart'), {
                series: predictionData.series,
                chart: {
                    type: 'line',
                    height: 300,
                    toolbar: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: predictionData.categories
                },
                colors: ['#3b82f6', '#f59e0b'],
                dataLabels: {
                    enabled: false
                },
                legend: {
                    position: 'top'
                }
            });
            demandChart.render();

            // Stock Level Chart
            const stockData = @js($this->getMockStockData());
            const stockChart = new ApexCharts(document.querySelector('#stockChart'), {
                series: stockData.series,
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: stockData.labels,
                colors: ['#10b981', '#f59e0b', '#ef4444'],
                legend: {
                    position: 'bottom'
                }
            });
            stockChart.render();
        });
    </script>
    @endpush
</x-filament-panels::page>
