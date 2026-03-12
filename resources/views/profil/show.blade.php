<x-layouts.app title="Profil Saya">
    <x-slot:header>
        <x-layouts.page-header title="Profil Saya" description="Kelola informasi akun Anda" />
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Profile Card --}}
        <x-ui.card class="text-center">
            <x-ui.avatar :name="$user->name" size="lg" class="mx-auto" />
            <h3 class="text-xl font-bold mt-4">{{ $user->name }}</h3>
            <p class="text-base-content/60">{{ $user->nim }}</p>
            <p class="text-sm text-base-content/50 mt-1">{{ $user->email }}</p>
            <div class="divider"></div>
            <div class="text-xs text-base-content/50">
                Bergabung sejak {{ $user->created_at->format('d F Y') }}
            </div>
        </x-ui.card>

        {{-- Update Form --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Info Form --}}
            <x-ui.card title=" Informasi Pribadi">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-ui.input name="name" label="Nama Lengkap" :value="$user->name" :required="true" />
                    <x-ui.input name="nim" label="NIM" :value="$user->nim" :required="true" />
                    <x-ui.input name="email" label="Email" type="email" :value="$user->email" :required="true" />

                    <div class="divider">Ubah Password (Opsional)</div>

                    <x-ui.input name="current_password" label="Password Lama" type="password"
                        placeholder="Masukkan password lama" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.input name="password" label="Password Baru" type="password"
                            placeholder="Password baru" />
                        <x-ui.input name="password_confirmation" label="Konfirmasi Password" type="password"
                            placeholder="Ulangi password baru" />
                    </div>

                    <div class="flex justify-end pt-4">
                        <x-ui.button type="primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Perubahan
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
