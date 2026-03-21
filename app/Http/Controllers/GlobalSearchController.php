<?php

namespace App\Http\Controllers;

use BackedEnum;
use App\Enums\Status;
use App\Models\Event;
use App\Models\MataKuliah;
use App\Models\Tugas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    private const MIN_QUERY_LENGTH = 2;

    private const MAX_RESULTS = 8;

    private const ENTITY_LIMIT = 5;

    private const FETCH_LIMIT = 15;

    public function __invoke(Request $request): JsonResponse
    {
        $query = $this->normalize((string) $request->query('q', ''));

        if (mb_strlen($query) < self::MIN_QUERY_LENGTH) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();

        $results = $this->buildPageResults($query)
            ->merge($this->buildMataKuliahResults($query))
            ->merge($this->buildTaskResults($query, (int) $user->id))
            ->merge($this->buildEventResults($query, (int) $user->id))
            ->sortByDesc('score')
            ->take(self::MAX_RESULTS)
            ->values()
            ->map(fn (array $result) => collect($result)->except('score')->all());

        return response()->json([
            'results' => $results,
        ]);
    }

    private function buildPageResults(string $query): Collection
    {
        return collect([
            [
                'title' => 'Dashboard',
                'subtitle' => 'Ringkasan jadwal, tugas, dan deadline',
                'category' => 'Menu',
                'icon' => 'home',
                'url' => route('dashboard'),
                'keywords' => 'beranda home utama ringkasan',
            ],
            [
                'title' => 'Jadwal Kuliah',
                'subtitle' => 'Kelola mata kuliah, dosen, dan ruangan',
                'category' => 'Menu',
                'icon' => 'academic-cap',
                'url' => route('mata-kuliah.index'),
                'keywords' => 'mata kuliah jadwal kelas akademik',
            ],
            [
                'title' => 'Tugas',
                'subtitle' => 'Pantau deadline, progress, dan prioritas tugas',
                'category' => 'Menu',
                'icon' => 'document-text',
                'url' => route('tugas.index'),
                'keywords' => 'assignment deadline todo pekerjaan',
            ],
            [
                'title' => 'Kalender',
                'subtitle' => 'Lihat jadwal kuliah, deadline, dan event',
                'category' => 'Menu',
                'icon' => 'calendar',
                'url' => route('kalender.index'),
                'keywords' => 'calendar agenda event tanggal',
            ],
            [
                'title' => 'Statistik',
                'subtitle' => 'Analisis produktivitas dan perkembangan tugas',
                'category' => 'Menu',
                'icon' => 'chart-bar',
                'url' => route('statistik.index'),
                'keywords' => 'stat analitik laporan progress',
            ],
            [
                'title' => 'Profil Saya',
                'subtitle' => 'Kelola informasi akun dan kredensial',
                'category' => 'Menu',
                'icon' => 'user-circle',
                'url' => route('profile.show'),
                'keywords' => 'profil akun pengguna user setting',
            ],
            [
                'title' => 'Tentang Aplikasi',
                'subtitle' => 'Informasi fitur utama dan teknologi aplikasi',
                'category' => 'Menu',
                'icon' => 'sparkles',
                'url' => route('about.index'),
                'keywords' => 'about bantuan informasi aplikasi',
            ],
        ])->map(function (array $page) use ($query) {
            $score = $this->scoreMatch($query, [
                $page['title'],
                $page['subtitle'],
                $page['keywords'],
                implode(' ', [$page['title'], $page['subtitle'], $page['keywords']]),
            ]);

            return [
                ...$page,
                'score' => $score,
            ];
        })->filter(fn (array $page) => $page['score'] > 0);
    }

    private function buildMataKuliahResults(string $query): Collection
    {
        $like = '%' . $query . '%';

        return MataKuliah::query()
            ->where(function ($mataKuliahQuery) use ($like) {
                $mataKuliahQuery->where('nama', 'like', $like)
                    ->orWhere('kode', 'like', $like)
                    ->orWhere('dosen', 'like', $like)
                    ->orWhere('ruangan', 'like', $like)
                    ->orWhere('hari', 'like', $like);
            })
            ->orderBy('nama')
            ->limit(self::FETCH_LIMIT)
            ->get()
            ->map(function (MataKuliah $mataKuliah) use ($query) {
                $hari = $this->stringifyValue($mataKuliah->hari);

                $subtitle = sprintf(
                    '%s • %s • %s, %s-%s',
                    $mataKuliah->kode,
                    $mataKuliah->dosen,
                    $hari,
                    $mataKuliah->jam_mulai,
                    $mataKuliah->jam_selesai
                );

                return [
                    'title' => $mataKuliah->nama,
                    'subtitle' => $subtitle,
                    'category' => 'Mata Kuliah',
                    'icon' => 'academic-cap',
                    'url' => route('mata-kuliah.show', $mataKuliah),
                    'score' => $this->scoreMatch($query, [
                        $mataKuliah->nama,
                        $subtitle,
                        implode(' ', [
                            $mataKuliah->kode,
                            $mataKuliah->dosen,
                            $mataKuliah->ruangan,
                            $hari,
                        ]),
                    ]),
                ];
            })
            ->filter(fn (array $result) => $result['score'] > 0)
            ->sortByDesc('score')
            ->take(self::ENTITY_LIMIT)
            ->values();
    }

    private function buildTaskResults(string $query, int $userId): Collection
    {
        $like = '%' . $query . '%';

        return Tugas::query()
            ->where('user_id', $userId)
            ->with('mataKuliah:id,nama,kode')
            ->where(function ($taskQuery) use ($like) {
                $taskQuery->where('judul', 'like', $like)
                    ->orWhere('deskripsi', 'like', $like)
                    ->orWhere('catatan', 'like', $like)
                    ->orWhereHas('mataKuliah', function ($mataKuliahQuery) use ($like) {
                        $mataKuliahQuery->where('nama', 'like', $like)
                            ->orWhere('kode', 'like', $like);
                    });
            })
            ->orderBy('deadline')
            ->limit(self::FETCH_LIMIT)
            ->get()
            ->map(function (Tugas $tugas) use ($query) {
                $status = $tugas->status instanceof Status
                    ? $tugas->status->label()
                    : Str::headline((string) $tugas->status);

                $subtitle = sprintf(
                    '%s • Deadline %s • %s',
                    $tugas->mataKuliah->nama ?? 'Tanpa mata kuliah',
                    $this->formatDateTime($tugas->deadline),
                    $status
                );

                return [
                    'title' => $tugas->judul,
                    'subtitle' => $subtitle,
                    'category' => 'Tugas',
                    'icon' => 'document-text',
                    'url' => route('tugas.show', $tugas),
                    'score' => $this->scoreMatch($query, [
                        $tugas->judul,
                        $subtitle,
                        implode(' ', [
                            $tugas->deskripsi ?? '',
                            $tugas->catatan ?? '',
                            $tugas->mataKuliah->nama ?? '',
                            $tugas->mataKuliah->kode ?? '',
                        ]),
                    ]),
                ];
            })
            ->filter(fn (array $result) => $result['score'] > 0)
            ->sortByDesc('score')
            ->take(self::ENTITY_LIMIT)
            ->values();
    }

    private function buildEventResults(string $query, int $userId): Collection
    {
        $like = '%' . $query . '%';

        return Event::query()
            ->where('user_id', $userId)
            ->where(function ($eventQuery) use ($like) {
                $eventQuery->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('location', 'like', $like);
            })
            ->orderBy('start')
            ->limit(self::FETCH_LIMIT)
            ->get()
            ->map(function (Event $event) use ($query) {
                $subtitle = trim(implode(' • ', array_filter([
                    $this->formatDateTime($event->start),
                    $event->location,
                ])));

                return [
                    'title' => $event->title,
                    'subtitle' => $subtitle !== '' ? $subtitle : 'Event kalender',
                    'category' => 'Event',
                    'icon' => 'calendar',
                    'url' => route('kalender.index'),
                    'score' => $this->scoreMatch($query, [
                        $event->title,
                        $subtitle,
                        implode(' ', [
                            $event->description ?? '',
                            $event->location ?? '',
                        ]),
                    ]),
                ];
            })
            ->filter(fn (array $result) => $result['score'] > 0)
            ->sortByDesc('score')
            ->take(self::ENTITY_LIMIT)
            ->values();
    }

    private function scoreMatch(string $query, array $fields): int
    {
        $query = $this->normalize($query);

        if ($query === '') {
            return 0;
        }

        $terms = array_values(array_filter(explode(' ', $query)));
        $score = 0;

        foreach ($fields as $index => $field) {
            $text = $this->normalize((string) $field);

            if ($text === '') {
                continue;
            }

            $baseScore = match ($index) {
                0 => 120,
                1 => 90,
                default => 60,
            };

            if ($text === $query) {
                $score = max($score, $baseScore + 180);
                continue;
            }

            if (str_starts_with($text, $query)) {
                $score = max($score, $baseScore + 130);
            }

            foreach (preg_split('/\s+/', $text) ?: [] as $word) {
                if ($word !== '' && str_starts_with($word, $query)) {
                    $score = max($score, $baseScore + 110);
                    break;
                }
            }

            if ($this->containsAllTerms($terms, $text)) {
                $score = max($score, $baseScore + 90);
            }

            if (str_contains($text, $query)) {
                $score = max($score, $baseScore + 70);
            }
        }

        return $score;
    }

    private function containsAllTerms(array $terms, string $text): bool
    {
        if ($terms === []) {
            return false;
        }

        foreach ($terms as $term) {
            if (! str_contains($text, $term)) {
                return false;
            }
        }

        return true;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->squish()
            ->value();
    }

    private function formatDateTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return Carbon::parse($value)->format('d M Y H:i');
    }

    private function stringifyValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}
