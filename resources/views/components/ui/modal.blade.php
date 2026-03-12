{{--
    Reusable Modal Component

    @param string $id - Modal ID for triggering
    @param string $title - Modal title
    @param string $size - Modal size: sm, md, lg
    @param bool $closeButton - Show close button
--}}


@props([
    'id',
    'title' => null,
    'size' => 'md',
    'closeButton' => true,
    'centered' => true,
])

@php
$sizeClass = match($size) {
    'sm' => 'max-w-sm',
    'md' => 'max-w-lg',
    'lg' => 'max-w-3xl',
    'xl' => 'max-w-5xl',
    default => 'max-w-lg',
};
$modalPositionClass = $centered ? 'modal-middle' : 'modal-bottom sm:modal-middle';
@endphp

@php
    // Allow dynamic title via slot if provided
    $hasTitleSlot = isset($title) && $title === null && $attributes->has('title');
@endphp

@php
$sizeClass = match($size) {
'sm' => 'max-w-sm',
'md' => 'max-w-lg',
'lg' => 'max-w-3xl',
'xl' => 'max-w-5xl',
default => 'max-w-lg',
};
@endphp

<dialog id="{{ $id }}" class="modal {{ $modalPositionClass }}">
    <div class="modal-box {{ $sizeClass }}">
        @if($title || $closeButton || (isset($title) && trim($title) === '' && isset($titleSlot)))
        <div class="flex justify-between items-center mb-4">
            @if(isset($titleSlot))
                {{ $titleSlot }}
            @elseif($title)
                <h3 class="font-bold text-lg">{{ $title }}</h3>
            @endif
            @if($closeButton)
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost">✕</button>
            </form>
            @endif
        </div>
        @endif

        {{ $slot }}

        @if(isset($actions))
        <div class="modal-action">
            {{ $actions }}
        </div>
        @endif
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
