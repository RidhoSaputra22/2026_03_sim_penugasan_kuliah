{{--
    Reusable Card Component

    @param string $title - Card title (optional)
    @param string $class - Additional CSS classes (optional)
    @param bool $compact - Use compact padding (optional)
    @param string $href - If provided, the entire card becomes a link (optional)
    @slot actions - Optional slot for action buttons/links
--}}

@props([
    'title' => null,
    'class' => '',
    'compact' => false,
    'href' => null,
])

@php
    $hoverClass = $href
        ? ' cursor-pointer transition-shadow duration-200 hover:shadow-2xl hover:-translate-y-0.5 active:scale-[0.99]'
        : '';
@endphp

@if ($href)

    <div {{ $attributes->merge(['class' => 'card bg-base-100 shadow-xl ' . $class . $hoverClass]) }}>
        <a href="{{ $href }}">
            <div class="card-body {{ $compact ? 'p-4' : '' }}">
                @if ($title)
                    <h2 class="card-title">{{ $title }}</h2>
                @endif
                {{ $slot }}
                @if (isset($actions))
                    <div class="card-actions justify-end mt-4">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </a>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'card bg-base-100 shadow-xl ' . $class]) }}>
        <div class="card-body {{ $compact ? 'p-4' : '' }}">
            @if ($title)
                <h2 class="card-title">{{ $title }}</h2>
            @endif
            {{ $slot }}
            @if (isset($actions))
                <div class="card-actions justify-end mt-4">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
@endif
