<x-layouts.guest title="Login">
    <div class="w-full max-w-md mx-auto p-4">
        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 text-primary-content" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-base-content">SIM Penugasan Kuliah</h1>
            <p class="text-base-content/60 mt-1">Smart Student Task & Schedule Manager</p>
        </div>

        {{-- Login Card --}}
        <x-ui.card class="backdrop-blur-sm bg-base-100/80">
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- NIM --}}
                <x-ui.input name="nim" label="NIM" placeholder="Masukkan NIM Anda" :value="old('nim')"
                    :error="$errors->first('nim')" :required="true">
                </x-ui.input>

                {{-- Password --}}
                <x-ui.input name="password" label="Password" type="password" placeholder="Masukkan password"
                    :error="$errors->first('password')" :required="true">
                </x-ui.input>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between">
                    <x-ui.checkbox name="remember" class="checkbox-sm" :checked="old('remember')"
                        singleLabel="Ingat saya" />
                </div>

                {{-- Submit --}}
                <x-ui.button type="primary" class="w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Masuk
                </x-ui.button>
            </form>
        </x-ui.card>

        <p class="text-center text-base-content/50 text-sm mt-6">
            &copy; {{ date('Y') }} SIM Penugasan Kuliah
        </p>
    </div>
</x-layouts.guest>
