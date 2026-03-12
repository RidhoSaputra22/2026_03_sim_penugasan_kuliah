<x-layouts.app title="Tambah Mata Kuliah">
    <x-slot:header>
        <x-layouts.page-header title="Tambah Mata Kuliah" description="Tambahkan jadwal mata kuliah baru">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('mata-kuliah.index')">
                    ← Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <x-ui.card class="">
        <form method="POST" action="{{ route('mata-kuliah.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="kode" label="Kode Mata Kuliah" placeholder="Contoh: IF101" :required="true" />
                <x-ui.input name="nama" label="Nama Mata Kuliah" placeholder="Contoh: Kecerdasan Buatan" :required="true" />
            </div>

            <x-ui.input name="dosen" label="Dosen Pengampu" placeholder="Nama dosen" :required="true" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="ruangan" label="Ruangan" placeholder="Contoh: Lab 3" :required="true" />
                <x-ui.select name="hari" label="Hari" :searchable="false" placeholder="Pilih hari" :required="true"
                    :options="[
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
                        'Sabtu' => 'Sabtu',
                    ]" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="jam_mulai" label="Jam Mulai" type="time" :required="true" />
                <x-ui.input name="jam_selesai" label="Jam Selesai" type="time" :required="true" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="sks" label="SKS" type="number" min="1" max="4" placeholder="Jumlah SKS" />
                <x-ui.input name="kelas" label="Kelas" placeholder="Contoh: A, B, C" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="lms" label="LMS" placeholder="Contoh: Moodle, Google Classroom" />
                <x-ui.input name="lms_link" label="LMS Link" placeholder="URL LMS" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input name="semester" label="Semester" type="number" min="1" max="8" placeholder="Semester" />
                <x-ui.input name="tahun_ajaran" label="Tahun Ajaran" type="number" min="2020" max="2100" placeholder="Tahun" />
            </div>

            <x-ui.input name="warna" label="Warna" placeholder="Contoh: biru, merah" />
            <x-ui.textarea name="catatan" label="Catatan" placeholder="Catatan tambahan" />

            <x-ui.checkbox name="is_active" label="Aktif?"  checked />

            <div class="flex justify-end gap-2 pt-4">
                <x-ui.button type="ghost" :href="route('mata-kuliah.index')" :isSubmit="false">Batal</x-ui.button>
                <x-ui.button type="primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan
                </x-ui.button>
            </div>
            </div>

        </form>
    </x-ui.card>
</x-layouts.app>
