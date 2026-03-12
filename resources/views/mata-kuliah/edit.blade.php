<x-layouts.app title="Edit Mata Kuliah">
    <x-slot:header>
        <x-layouts.page-header title="Edit Mata Kuliah" description="Edit data {{ $mataKuliah->nama }}">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('mata-kuliah.index')">
                    ← Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <x-ui.card class="">
        <form method="POST" action="{{ route('mata-kuliah.update', $mataKuliah->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="kode" label="Kode Mata Kuliah" placeholder="Contoh: IF101" :required="true"
                    :value="$mataKuliah->kode" />
                <x-ui.input name="nama" label="Nama Mata Kuliah" placeholder="Contoh: Kecerdasan Buatan"
                    :required="true" :value="$mataKuliah->nama" />
            </div>

            <x-ui.input name="dosen" label="Dosen Pengampu" placeholder="Nama dosen" :required="true"
                :value="$mataKuliah->dosen" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="ruangan" label="Ruangan" placeholder="Contoh: Lab 3" :required="true"
                    :value="$mataKuliah->ruangan" />
                <x-ui.select name="hari" label="Hari" :searchable="false" placeholder="Pilih hari" :required="true"
                    :options="[
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
                        'Sabtu' => 'Sabtu',
                    ]" :value="$mataKuliah->hari" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="jam_mulai" label="Jam Mulai" type="time" :required="true" :value="$mataKuliah->jam_mulai" />
                <x-ui.input name="jam_selesai" label="Jam Selesai" type="time" :required="true"
                    :value="$mataKuliah->jam_selesai" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="sks" label="SKS" type="number" min="1" max="4"
                    placeholder="Jumlah SKS" :value="$mataKuliah->sks" />
                <x-ui.input name="kelas" label="Kelas" placeholder="Contoh: A, B, C" :value="$mataKuliah->kelas" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="lms" label="LMS" placeholder="Contoh: Moodle, Google Classroom"
                    :value="$mataKuliah->lms" />
                <x-ui.input name="lms_link" label="LMS Link" placeholder="URL LMS" :value="$mataKuliah->lms_link" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="semester" label="Semester" type="number" min="1" max="8"
                    placeholder="Semester" :value="$mataKuliah->semester" />
                <x-ui.input name="tahun_ajaran" label="Tahun Ajaran" type="number" min="2020" max="2100"
                    placeholder="Tahun" :value="$mataKuliah->tahun_ajaran" />
            </div>

            <x-ui.input name="warna" label="Warna" placeholder="Contoh: biru, merah" :value="$mataKuliah->warna" />
            <x-ui.textarea name="catatan" label="Catatan" placeholder="Catatan tambahan" :value="$mataKuliah->catatan" />

            <x-ui.checkbox name="is_active" label="Aktif?" :checked="$mataKuliah->is_active" />

            <div class="flex justify-end gap-2 pt-4">
                <x-ui.button type="ghost" :href="route('mata-kuliah.index')" :isSubmit="false">Batal</x-ui.button>
                <x-ui.button type="primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update
                </x-ui.button>
            </div>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
