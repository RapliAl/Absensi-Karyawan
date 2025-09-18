<x-filament::modal icon="heroicon-o-information-circle">
    <x-slot name="heading">
        Modal heading
    </x-slot>

    {{-- Modal content --}}
    <div>
    <div>
    @if (isset($karyawan) && $karyawan)
        <div id="hadir-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" 
             style="display: none;" 
             x-data="{ show: false }" 
             x-show="show" 
             x-on:open-modal.window="show = ($event.detail.id === 'hadir-modal')" 
             x-on:close-modal.window="show = false" 
             x-on:keydown.escape.window="show = false">
            <div class="p-8 bg-white rounded-lg shadow-lg" @click.away="show = false">
                <h2 class="text-2xl font-bold text-center mb-4">Data Hadir: {{ $karyawan->nama ?? 'Unknown' }}</h2>
                {{-- <div class="flex justify-center">
                    <!-- Use route to generate QR code -->
                    <img src="{{ route('qr.show', $karyawan->id) }}" 
                         alt="QR Code" 
                         style="width: 250px; height: 250px; border: 1px solid #ccc;">
                </div> --}}
                <p class="text-center mt-4">Nama : {{ $karyawan->{'name'} ?? 'N/A' }}</p>
                <button @click="show = false" class="mt-4 px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">Submit</button>
            </div>
        </div>
    @else
        <!-- Hidden debug info -->
        <div style="display: none;">
            <p>HadirController: No karyawan data available</p>
        </div>
    @endif
</div>
</div>
</x-filament::modal>