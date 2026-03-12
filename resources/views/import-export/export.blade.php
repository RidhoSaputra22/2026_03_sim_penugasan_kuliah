<x-layouts.app :title="'Export ' . $title">
    <x-slot:header>
        <x-layouts.page-header title="Export {{ $title }}" description="Export data ke file Excel (.xlsx)">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" href="{{ route($backRoute) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    @if(session('error'))
        <x-ui.alert type="error" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    <x-ui.card>
        <div class=" py-4">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-primary/10 p-3 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Export {{ $title }}</h3>
                    <p class="text-sm text-base-content/60">Pilih rentang tanggal dan format file untuk mengexport data</p>
                </div>
            </div>

            <form method="POST" action="{{ route('import-export.export.process', $module) }}" class="space-y-6">
                @csrf

                {{-- Rentang Tanggal --}}
                <div class="bg-base-200/50 rounded-lg p-4 space-y-4">
                    <h4 class="font-medium text-sm uppercase tracking-wider text-base-content/70">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Filter Rentang Tanggal
                    </h4>
                    <p class="text-xs text-base-content/50">
                        Berdasarkan kolom: <strong>{{ $dateColumn }}</strong>.
                        Kosongkan untuk mengexport semua data.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input type="date" name="tanggal_dari" label="Tanggal Dari" value="{{ old('tanggal_dari') }}" />
                        <x-ui.input type="date" name="tanggal_sampai" label="Tanggal Sampai" value="{{ old('tanggal_sampai') }}" />
                    </div>
                </div>

                {{-- Format Export --}}
                <div class="bg-base-200/50 rounded-lg p-4 space-y-4">
                    <h4 class="font-medium text-sm uppercase tracking-wider text-base-content/70">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Format File
                    </h4>
                    <div class="flex items-center gap-2 p-3 bg-base-100 rounded-lg border border-base-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <div>
                            <span class="font-medium text-sm">Excel (.xlsx)</span>
                            <p class="text-xs text-base-content/50">Microsoft Excel / OpenDocument Spreadsheet</p>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/50">File Excel dapat dibuka menggunakan Microsoft Excel, Google Sheets, atau LibreOffice Calc.</p>
                </div>

                {{-- Kolom yang akan di-export --}}
                <div class="bg-base-200/50 rounded-lg p-4 space-y-3">
                    <h4 class="font-medium text-sm uppercase tracking-wider text-base-content/70">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        Kolom yang Di-export
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($config['headers'] as $header)
                            <span class="badge badge-outline badge-sm">{{ $header }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button type="ghost" size="md" href="{{ route($backRoute) }}">Batal</x-ui.button>
                    <x-ui.button type="primary" size="md" :isSubmit="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Download Export
                    </x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.card>
</x-layouts.app>
