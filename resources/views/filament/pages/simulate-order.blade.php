<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Simulasi Pengurangan Stok</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Halaman ini untuk mensimulasikan pengurangan stok bahan baku berdasarkan pesanan menu. 
                    Stok akan berkurang sesuai resep dan menggunakan logika FIFO/FEFO.
                </p>
            </div>

            <form wire:submit="simulateOrder">
                {{ $this->form }}

                <div class="mt-6 flex gap-3">
                    @foreach ($this->getFormActions() as $action)
                        {{ $action }}
                    @endforeach
                </div>
            </form>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Informasi</h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Halaman ini HANYA mensimulasikan pengurangan stok, tidak membuat pesanan sungguhan.</li>
                            <li>Stok akan dikurangi dari batch dengan tanggal kadaluarsa terdekat (FEFO) atau tanggal masuk terlama (FIFO).</li>
                            <li>Jika stok tidak mencukupi, sistem akan menampilkan pesan error dan tidak akan mengurangi stok apapun.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
