{{-- Empty state, tapi filter tetap sudah tampil di atas --}}
@if ($items->count() == 0)
    <div class="card border border-base-200 bg-base-100">
        <div class="card-body py-16 text-center text-base-content/60">
            <div class="text-lg font-semibold">
                {{ $hasActiveFilter ? 'Data tidak ditemukan' : 'Tidak ada data' }}
            </div>
            <div class="text-sm">
                {{ $hasActiveFilter ? 'Coba ubah atau reset filter Anda.' : $emptyText }}
            </div>

            @if ($hasActiveFilter)
                <div class="mt-4">
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-ghost">
                        Reset Filter
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif
