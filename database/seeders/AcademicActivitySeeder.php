<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\DayOfWeek;
use App\Enums\Status;
use App\Models\Absensi;
use App\Models\Event;
use App\Models\MataKuliah;
use App\Models\Reminder;
use App\Models\Todo;
use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class AcademicActivitySeeder extends Seeder
{
    public function run(): void
    {
        $mataKuliahs = MataKuliah::query()
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $users = User::query()->orderBy('id')->get();

        if ($mataKuliahs->isEmpty() || $users->isEmpty()) {
            return;
        }

        $attendanceMap = $mataKuliahs
            ->mapWithKeys(fn (MataKuliah $mataKuliah) => [
                $mataKuliah->id => $this->seedAttendanceForCourse($mataKuliah),
            ]);

        foreach ($users as $index => $user) {
            $this->seedTasksForUser($user, $mataKuliahs, $attendanceMap, $index);
            $this->seedEventsForUser($user, $index);
        }
    }

    private function seedAttendanceForCourse(MataKuliah $mataKuliah): Collection
    {
        $topics = collect($this->topicsForCourse($mataKuliah))
            ->pad(6, 'Diskusi materi inti dan latihan terarah.')
            ->take(6)
            ->values();

        $statuses = [
            AttendanceStatus::HADIR->value,
            AttendanceStatus::HADIR->value,
            AttendanceStatus::IZIN->value,
            AttendanceStatus::HADIR->value,
            AttendanceStatus::SAKIT->value,
            AttendanceStatus::HADIR->value,
        ];

        $records = collect();

        foreach ($topics as $meetingIndex => $topic) {
            $meetingNumber = $meetingIndex + 1;
            $tanggal = $this->meetingDate($mataKuliah, $meetingNumber);

            $absensi = Absensi::updateOrCreate(
                [
                    'mata_kuliah_id' => $mataKuliah->id,
                    'tanggal' => $tanggal->toDateString(),
                ],
                [
                    'pertemuan_ke' => $meetingNumber,
                    'status' => $statuses[$meetingIndex] ?? AttendanceStatus::HADIR->value,
                    'topik' => $topic,
                    'catatan' => [
                        [
                            'judul' => 'Ringkasan kelas',
                            'isi' => 'Mahasiswa membahas topik ' . mb_strtolower($topic) . '.',
                        ],
                        [
                            'judul' => 'Tindak lanjut',
                            'isi' => 'Siapkan bahan latihan mandiri untuk pertemuan berikutnya.',
                        ],
                    ],
                ]
            );

            $records->push($absensi->fresh());
        }

        return $records->sortBy('tanggal')->values();
    }

    private function seedTasksForUser(
        User $user,
        Collection $mataKuliahs,
        Collection $attendanceMap,
        int $userIndex
    ): void {
        $taskTemplates = $this->taskTemplates();
        $assignedCourses = $mataKuliahs->values();
        $courseCount = $assignedCourses->count();

        foreach ($taskTemplates as $templateIndex => $template) {
            $mataKuliah = $assignedCourses[($templateIndex + $userIndex) % $courseCount];
            $courseAttendances = $attendanceMap->get($mataKuliah->id, collect());
            $linkedAttendance = $courseAttendances->isNotEmpty()
                ? $courseAttendances->last()
                : null;

            $deadline = Carbon::now()
                ->startOfDay()
                ->addDays($template['deadline_offset'] + $userIndex)
                ->setTime($template['deadline_hour'], 0);

            $title = $this->buildTaskTitle($template['type'], $mataKuliah, $linkedAttendance);

            $tugas = Tugas::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'mata_kuliah_id' => $mataKuliah->id,
                    'judul' => $title,
                ],
                [
                    'absensi_id' => $template['attach_attendance'] ? $linkedAttendance?->id : null,
                    'deskripsi' => $this->buildTaskDescription($template['type'], $mataKuliah, $linkedAttendance),
                    'deadline' => $deadline,
                    'status' => Status::BELUM->value,
                    'progress' => 0,
                    'prioritas' => $template['prioritas'],
                    'file' => null,
                    'catatan' => $template['catatan'],
                ]
            );

            $this->seedTodosForTask($tugas, $template['todos'], $deadline);
            $this->syncTaskProgressFromTodos($tugas);
            $this->seedRemindersForTask($tugas, $deadline);
        }
    }

    private function seedTodosForTask(Tugas $tugas, array $todos, Carbon $deadline): void
    {
        foreach ($todos as $index => $todo) {
            Todo::updateOrCreate(
                [
                    'tugas_id' => $tugas->id,
                    'judul' => $todo['judul'],
                ],
                [
                    'deskripsi' => $todo['deskripsi'],
                    'status' => $todo['done'] ? Status::SELESAI->value : Status::BELUM->value,
                    'deadline' => $deadline->copy()->subHours(max(2, ($index + 1) * 6)),
                ]
            );
        }
    }

    private function syncTaskProgressFromTodos(Tugas $tugas): void
    {
        $totalTodos = $tugas->todos()->count();
        $doneTodos = $tugas->todos()->where('status', Status::SELESAI->value)->count();

        $progress = $totalTodos > 0 ? (int) round(($doneTodos / $totalTodos) * 100) : 0;
        $status = $progress >= 100
            ? Status::SELESAI->value
            : ($progress > 0 ? Status::PROGRESS->value : Status::BELUM->value);

        $tugas->update([
            'progress' => $progress,
            'status' => $status,
        ]);
    }

    private function seedRemindersForTask(Tugas $tugas, Carbon $deadline): void
    {
        $reminders = [
            $deadline->copy()->subDay()->setTime(8, 0),
            $deadline->copy()->subHours(3),
        ];

        foreach ($reminders as $tanggalNotifikasi) {
            $alreadyTriggered = $tanggalNotifikasi->lessThanOrEqualTo(Carbon::now());

            Reminder::updateOrCreate(
                [
                    'tugas_id' => $tugas->id,
                    'tanggal_notifikasi' => $tanggalNotifikasi,
                ],
                [
                    'status' => $alreadyTriggered ? Status::SELESAI->value : Status::BELUM->value,
                    'terkirim' => $alreadyTriggered,
                ]
            );
        }
    }

    private function seedEventsForUser(User $user, int $userIndex): void
    {
        $eventTemplates = [
            [
                'title' => 'Study Group Mingguan',
                'description' => 'Diskusi progres tugas dan pembagian fokus pengerjaan minggu ini.',
                'location' => 'Perpustakaan Kampus',
                'start_offset' => 1,
                'start_hour' => 19,
                'duration_hours' => 2,
            ],
            [
                'title' => 'Review Sprint Akademik',
                'description' => 'Cek deadline terdekat, reminder aktif, dan update prioritas tugas.',
                'location' => 'Ruang Diskusi 2',
                'start_offset' => 3,
                'start_hour' => 16,
                'duration_hours' => 1,
            ],
            [
                'title' => 'Seminar Teknologi Kampus',
                'description' => 'Sesi berbagi praktik pengembangan aplikasi akademik berbasis web.',
                'location' => 'Aula Fakultas',
                'start_offset' => 5,
                'start_hour' => 13,
                'duration_hours' => 3,
            ],
            [
                'title' => 'Konsultasi Proyek',
                'description' => 'Bawa daftar blocker dan progres implementasi terakhir.',
                'location' => 'Ruang Dosen Pembimbing',
                'start_offset' => 7,
                'start_hour' => 10,
                'duration_hours' => 1,
            ],
        ];

        foreach ($eventTemplates as $event) {
            $start = Carbon::now()
                ->startOfDay()
                ->addDays($event['start_offset'] + $userIndex)
                ->setTime($event['start_hour'], 0);

            Event::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'title' => $event['title'],
                    'start' => $start,
                ],
                [
                    'description' => $event['description'],
                    'end' => $start->copy()->addHours($event['duration_hours']),
                    'location' => $event['location'],
                    'color' => null,
                ]
            );
        }
    }

    private function meetingDate(MataKuliah $mataKuliah, int $meetingNumber): Carbon
    {
        $weeksAgo = max(1, 7 - $meetingNumber);

        return Carbon::now()
            ->startOfWeek(Carbon::MONDAY)
            ->subWeeks($weeksAgo)
            ->addDays($this->isoDayNumber($mataKuliah->hari) - 1)
            ->startOfDay();
    }

    private function isoDayNumber(DayOfWeek|string|null $day): int
    {
        $value = $day instanceof DayOfWeek ? $day->value : (string) $day;

        return match ($value) {
            DayOfWeek::MONDAY->value => 1,
            DayOfWeek::TUESDAY->value => 2,
            DayOfWeek::WEDNESDAY->value => 3,
            DayOfWeek::THURSDAY->value => 4,
            DayOfWeek::FRIDAY->value => 5,
            DayOfWeek::SATURDAY->value => 6,
            default => 7,
        };
    }

    private function topicsForCourse(MataKuliah $mataKuliah): array
    {
        return match ($mataKuliah->kode) {
            'IF301' => [
                'Pengenalan kompleksitas algoritma',
                'Array, linked list, dan operasi dasar',
                'Stack dan queue untuk pemrosesan data',
                'Tree traversal dan representasi graf',
                'Sorting dan searching',
                'Analisis performa algoritma',
            ],
            'SI302' => [
                'Konsep basis data relasional',
                'Entity relationship diagram',
                'Normalisasi data tahap 1-3',
                'Query select dan join',
                'Agregasi, grouping, dan subquery',
                'Desain skema untuk sistem akademik',
            ],
            'IF303' => [
                'Struktur project Laravel',
                'Routing, controller, dan blade',
                'Validasi form dan request lifecycle',
                'Relasi model Eloquent',
                'Seeder, factory, dan migration',
                'Optimasi alur dashboard akademik',
            ],
            'IF304' => [
                'Kebutuhan fungsional dan non-fungsional',
                'Use case dan activity diagram',
                'Penyusunan backlog sprint',
                'Analisis risiko proyek',
                'Review arsitektur aplikasi',
                'Evaluasi iterasi dan retrospektif',
            ],
            'IF305' => [
                'Prinsip dasar usability',
                'User flow dan information architecture',
                'Wireframe low fidelity',
                'Prototyping interaktif',
                'Heuristic evaluation',
                'Aksesibilitas antarmuka',
            ],
            'SI306' => [
                'Identifikasi aktor dan kebutuhan sistem',
                'Dokumen analisis proses bisnis',
                'Spesifikasi requirement prioritas',
                'Perancangan modul inti aplikasi',
                'Integrasi diagram UML',
                'Review dokumen sistem berjalan',
            ],
            'IF307' => [
                'Model OSI dan TCP/IP',
                'Subnetting dan alamat IP',
                'Switching dan routing dasar',
                'Konfigurasi VLAN sederhana',
                'Troubleshooting konektivitas',
                'Keamanan dasar jaringan kampus',
            ],
            default => [
                'Pengantar materi dan kontrak kuliah',
                'Diskusi konsep inti',
                'Latihan studi kasus',
                'Review tugas pekanan',
                'Pendalaman materi',
                'Evaluasi capaian pembelajaran',
            ],
        };
    }

    private function taskTemplates(): array
    {
        return [
            [
                'type' => 'resume',
                'deadline_offset' => -2,
                'deadline_hour' => 21,
                'prioritas' => 'tinggi',
                'attach_attendance' => true,
                'catatan' => 'Kerjakan poin paling berat lebih dulu lalu sisakan waktu untuk revisi.',
                'todos' => [
                    [
                        'judul' => 'Baca ulang catatan kuliah',
                        'deskripsi' => 'Ambil tiga poin inti yang paling sering dibahas dosen.',
                        'done' => true,
                    ],
                    [
                        'judul' => 'Tulis rangkuman satu halaman',
                        'deskripsi' => 'Susun ringkasan yang siap diunggah ke LMS.',
                        'done' => false,
                    ],
                    [
                        'judul' => 'Upload hasil akhir',
                        'deskripsi' => 'Pastikan format file dan nama dokumen sesuai ketentuan.',
                        'done' => false,
                    ],
                ],
            ],
            [
                'type' => 'kuis',
                'deadline_offset' => 1,
                'deadline_hour' => 20,
                'prioritas' => 'sedang',
                'attach_attendance' => false,
                'catatan' => 'Sisihkan waktu 30 menit untuk latihan soal tambahan.',
                'todos' => [
                    [
                        'judul' => 'Kerjakan latihan mandiri',
                        'deskripsi' => 'Fokus pada contoh soal yang mirip pembahasan kelas.',
                        'done' => true,
                    ],
                    [
                        'judul' => 'Catat bagian yang belum paham',
                        'deskripsi' => 'Siapkan pertanyaan untuk dibahas di grup belajar.',
                        'done' => true,
                    ],
                    [
                        'judul' => 'Review jawaban akhir',
                        'deskripsi' => 'Periksa kembali istilah dan langkah penyelesaian.',
                        'done' => false,
                    ],
                ],
            ],
            [
                'type' => 'laporan',
                'deadline_offset' => 4,
                'deadline_hour' => 18,
                'prioritas' => 'tinggi',
                'attach_attendance' => true,
                'catatan' => 'Pastikan narasi laporan selaras dengan hasil implementasi terbaru.',
                'todos' => [
                    [
                        'judul' => 'Rapikan struktur dokumen',
                        'deskripsi' => 'Cek heading, penomoran, dan susunan subbab.',
                        'done' => true,
                    ],
                    [
                        'judul' => 'Lengkapi bagian analisis',
                        'deskripsi' => 'Tambahkan penjelasan alasan pemilihan solusi.',
                        'done' => true,
                    ],
                    [
                        'judul' => 'Finalisasi lampiran',
                        'deskripsi' => 'Masukkan tangkapan layar atau diagram pendukung.',
                        'done' => true,
                    ],
                ],
            ],
            [
                'type' => 'presentasi',
                'deadline_offset' => 2,
                'deadline_hour' => 17,
                'prioritas' => 'sedang',
                'attach_attendance' => false,
                'catatan' => 'Gunakan slide singkat dan fokus pada demo utama.',
                'todos' => [
                    [
                        'judul' => 'Susun outline presentasi',
                        'deskripsi' => 'Tentukan alur pembukaan, inti, dan penutup.',
                        'done' => true,
                    ],
                    [
                        'judul' => 'Siapkan visual pendukung',
                        'deskripsi' => 'Gunakan contoh kasus yang relevan dengan materi minggu ini.',
                        'done' => false,
                    ],
                ],
            ],
            [
                'type' => 'praktikum',
                'deadline_offset' => 6,
                'deadline_hour' => 22,
                'prioritas' => 'rendah',
                'attach_attendance' => true,
                'catatan' => 'Simpan konfigurasi akhir dan dokumentasikan langkah penting.',
                'todos' => [
                    [
                        'judul' => 'Jalankan ulang skenario uji',
                        'deskripsi' => 'Pastikan hasil praktikum konsisten.',
                        'done' => false,
                    ],
                    [
                        'judul' => 'Ambil screenshot hasil',
                        'deskripsi' => 'Simpan bukti output yang akan disertakan pada laporan.',
                        'done' => false,
                    ],
                ],
            ],
            [
                'type' => 'refleksi',
                'deadline_offset' => 8,
                'deadline_hour' => 19,
                'prioritas' => 'sedang',
                'attach_attendance' => false,
                'catatan' => 'Tuliskan pembelajaran utama dan rencana perbaikan minggu depan.',
                'todos' => [
                    [
                        'judul' => 'Tuliskan insight utama',
                        'deskripsi' => 'Pilih dua hal yang paling berpengaruh pada pemahaman materi.',
                        'done' => true,
                    ],
                    [
                        'judul' => 'Susun aksi lanjut',
                        'deskripsi' => 'Buat daftar langkah kecil untuk pekan berikutnya.',
                        'done' => false,
                    ],
                ],
            ],
        ];
    }

    private function buildTaskTitle(string $type, MataKuliah $mataKuliah, ?Absensi $absensi): string
    {
        $meetingLabel = $absensi?->pertemuan_ke
            ? 'Pertemuan ' . $absensi->pertemuan_ke
            : 'minggu ini';

        return match ($type) {
            'resume' => 'Resume materi ' . $meetingLabel . ' - ' . $mataKuliah->kode,
            'kuis' => 'Persiapan kuis ' . $mataKuliah->kode,
            'laporan' => 'Finalisasi laporan proyek ' . $mataKuliah->kode,
            'presentasi' => 'Draft presentasi ' . $mataKuliah->nama,
            'praktikum' => 'Checklist praktikum ' . $mataKuliah->kode,
            default => 'Refleksi belajar ' . $mataKuliah->nama,
        };
    }

    private function buildTaskDescription(string $type, MataKuliah $mataKuliah, ?Absensi $absensi): string
    {
        $topic = $absensi?->topik ?? 'materi perkuliahan terbaru';

        return match ($type) {
            'resume' => 'Susun ringkasan singkat dari ' . $topic . ' untuk ' . $mataKuliah->nama . '.',
            'kuis' => 'Siapkan catatan belajar dan latihan inti untuk kuis pada mata kuliah ' . $mataKuliah->nama . '.',
            'laporan' => 'Lengkapi laporan berbasis pembahasan ' . $topic . ' dan sinkronkan dengan revisi terakhir.',
            'presentasi' => 'Rancang materi presentasi yang padat dan mudah dipahami untuk sesi ' . $mataKuliah->nama . '.',
            'praktikum' => 'Dokumentasikan hasil praktikum dan rapikan langkah pengerjaan agar mudah direview.',
            default => 'Tuliskan evaluasi singkat tentang proses belajar pada mata kuliah ' . $mataKuliah->nama . '.',
        };
    }
}
