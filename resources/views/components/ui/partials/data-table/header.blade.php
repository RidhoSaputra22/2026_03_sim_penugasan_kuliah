{{-- Header + Filter selalu tampil --}}
<div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div class="space-y-1">
        <h2 class="card-title">{{ $title }}</h2>

        <div class="text-sm text-base-content/70">
            Total:
            <span class="font-semibold">
                {{ $isPaginated ? $totalItems : $items->count() }}
            </span>

            @if ($selectable)
                <span class="mx-2">•</span>
                <span>
                    Dipilih:
                    <span class="font-semibold" x-text="selected.length"></span>
                </span>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route(Route::currentRouteName()) }}#{{ Str::slug($title) }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
        <label class="input input-bordered input-sm flex w-full sm:min-w-64 items-center gap-2">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 opacity-70"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"
                />
            </svg>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="grow"
                placeholder="Cari data..."
            />
        </label>

        @isset($filters)
            <div class="flex  items-center gap-2">
                {{ $filters }}
            </div>
        @endisset

        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <a href="{{ url()->current() }}" class="btn btn-sm btn-ghost">Reset</a>
    </form>
</div>
