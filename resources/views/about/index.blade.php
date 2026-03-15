<x-layouts.app title="Tentang Aplikasi">
    <x-slot:header>
        <x-layouts.page-header title="Tentang Aplikasi" />
    </x-slot:header>

    <div class="max-w-3xl mx-auto">
        <x-ui.card>
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-primary rounded-md flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-11 w-11 text-primary-content" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-base-content">SIM Penugasan Kuliah</h1>
                <p class="text-base-content/60 mt-2">Smart Student Task & Schedule Manager</p>
                <x-ui.badge type="primary" class="mt-2">v1.0.0</x-ui.badge>
            </div>

            <div class="prose prose-sm max-w-none">
                <p>
                    Aplikasi ini dirancang untuk membantu mahasiswa <strong>mengelola tugas, jadwal kuliah,
                    deadline, dan aktivitas akademik</strong> dalam satu dashboard yang terintegrasi.
                </p>

                <h3>✨ Fitur Utama</h3>
                <ul>
                    <li><strong>Dashboard</strong> — Ringkasan jadwal, tugas, dan deadline</li>
                    <li><strong>Jadwal Kuliah</strong> — Kelola jadwal mata kuliah</li>
                    <li><strong>Manajemen Tugas</strong> — Tracking tugas dengan progress bar</li>
                    <li><strong>Kalender Akademik</strong> — Visualisasi jadwal dan deadline</li>
                    <li><strong>Statistik</strong> — Analisis produktivitas</li>
                    <li><strong>Reminder</strong> — Pengingat deadline otomatis</li>
                </ul>

                <h3>🛠 Teknologi</h3>
                <ul>
                    <li>Laravel 12</li>
                    <li>Tailwind CSS + DaisyUI</li>
                    <li>Alpine.js</li>
                    <li>Chart.js</li>
                </ul>
            </div>

            <div class="divider"></div>

            <div class="text-center text-sm text-base-content/50">
                <p>&copy; {{ date('Y') }} SIM Penugasan Kuliah. All rights reserved.</p>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
