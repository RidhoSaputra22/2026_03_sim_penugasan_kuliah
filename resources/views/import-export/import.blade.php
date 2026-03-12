<x-layouts.app :title="'Import ' . $title">
    <x-slot:header>
        <x-layouts.page-header title="Import {{ $title }}" description="Import data dari file Excel (.xlsx)">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" href="{{ route($backRoute) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    @if(session('success'))
        <x-ui.alert type="success" class="mb-4">{{ session('success') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert type="error" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    {{-- Error detail dari import --}}
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <x-ui.card class="mb-4 border-error/30">
            <div x-data="{ showAll: false }">
                <h4 class="font-semibold text-error mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                    Detail Error Import ({{ count(session('import_errors')) }} baris)
                </h4>
                <ul class="list-disc list-inside text-sm text-error/80 space-y-1">
                    @foreach(array_slice(session('import_errors'), 0, 5) as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                @if(count(session('import_errors')) > 5)
                    <div x-show="showAll" class="mt-2">
                        <ul class="list-disc list-inside text-sm text-error/80 space-y-1">
                            @foreach(array_slice(session('import_errors'), 5) as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button @click="showAll = !showAll" class="btn btn-xs btn-ghost mt-2" x-text="showAll ? 'Sembunyikan' : 'Lihat semua ({{ count(session('import_errors')) }})'">
                    </button>
                @endif
            </div>
        </x-ui.card>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Form Import --}}
        <div class="lg:col-span-2">
            <x-ui.card>
                <div class="py-4">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-success/10 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Upload File Import</h3>
                            <p class="text-sm text-base-content/60">Upload file Excel (.xlsx) sesuai template yang disediakan</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('import-export.import.process', $module) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- File Upload --}}
                        <div class="space-y-2">
                            <label class="label">
                                <span class="label-text font-medium">File Excel (.xlsx) <span class="text-error">*</span></span>
                            </label>
                            <div x-data="{ fileName: '' }" class="relative">
                                <input type="file" name="file" accept=".xlsx"
                                    class="file-input file-input-bordered w-full"
                                    @change="fileName = $event.target.files[0]?.name || ''"
                                    required />
                            </div>
                            <p class="text-xs text-base-content/50">Format: Excel (.xlsx). Maksimal 10MB.</p>
                            @error('file')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Opsi --}}
                        <div class="bg-base-200/50 rounded-lg p-4 space-y-3">
                            <h4 class="font-medium text-sm uppercase tracking-wider text-base-content/70">Opsi Import</h4>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="skip_errors" value="1" class="checkbox checkbox-sm checkbox-primary" />
                                <div>
                                    <span class="label-text font-medium">Lewati baris yang error</span>
                                    <p class="text-xs text-base-content/50">Jika dicentang, baris yang gagal akan dilewati dan sisanya tetap diimport</p>
                                </div>
                            </label>
                        </div>

                        {{-- Tombol --}}
                        <div class="flex justify-end gap-3 pt-2">
                            <x-ui.button type="ghost" size="md" href="{{ route($backRoute) }}">Batal</x-ui.button>
                            <x-ui.button type="success" size="md" :isSubmit="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                Proses Import
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.card>
        </div>

        {{-- Sidebar: Panduan & Template --}}
        <div class="space-y-6">
            {{-- Download Template --}}
            <x-ui.card>
                <h4 class="font-semibold mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Template Import
                </h4>
                <p class="text-sm text-base-content/60 mb-3">Download template Excel untuk memastikan format data sesuai.</p>
                <x-ui.button type="info" size="sm" :outline="true" href="{{ route('import-export.template', $module) }}" class="w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Download Template Excel
                </x-ui.button>
            </x-ui.card>

            {{-- Panduan Format --}}
            <x-ui.card>
                <h4 class="font-semibold mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Panduan Format
                </h4>
                <ul class="text-sm space-y-2 text-base-content/70">
                    <li class="flex items-start gap-2">
                        <span class="badge badge-xs badge-primary mt-1">1</span>
                        <span>Gunakan format file <strong>Excel (.xlsx)</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="badge badge-xs badge-primary mt-1">2</span>
                        <span>Baris pertama harus berisi header kolom</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="badge badge-xs badge-primary mt-1">3</span>
                        <span>Format tanggal: <strong>YYYY-MM-DD</strong> (contoh: 2026-03-04)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="badge badge-xs badge-primary mt-1">4</span>
                        <span>Ukuran file maksimal <strong>10MB</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="badge badge-xs badge-primary mt-1">5</span>
                        <span>Data yang sudah ada di database akan <strong>otomatis dilewati</strong></span>
                    </li>
                </ul>
            </x-ui.card>

            {{-- Kolom yang Tersedia --}}
            <x-ui.card>
                <h4 class="font-semibold mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                    Kolom Import
                </h4>
                <div class="overflow-x-auto">
                    <table class="table table-xs">
                        <thead>
                            <tr>
                                <th>Header</th>
                                <th>Kolom</th>
                                <th>Wajib</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($importHeaders as $i => $header)
                                <tr>
                                    <td class="font-medium">{{ $header }}</td>
                                    <td class="text-xs font-mono text-base-content/60">{{ $importColumns[$i] }}</td>
                                    <td>
                                        @if(in_array($importColumns[$i], $required))
                                            <span class="badge badge-error badge-xs">Wajib</span>
                                        @else
                                            <span class="text-xs text-base-content/40">Opsional</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
