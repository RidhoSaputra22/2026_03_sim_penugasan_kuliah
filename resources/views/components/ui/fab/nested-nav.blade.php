{{--
    Nested FAB Navigation

    @param array $items - Navigation items: label, href, icon, active?, buttonClass?
    @param string $label - Accessible label and tooltip for the trigger
    @param string $buttonClass - Classes for the nested FAB trigger
    @param string $panelClass - Classes for the navigation panel
    @param string $itemButtonClass - Classes for default navigation buttons
    @param string $activeItemButtonClass - Classes for active navigation buttons
--}}

@props([
    'items' => [],
    'label' => 'Buka navigasi cepat',
    'buttonClass' => 'btn btn-circle btn-lg btn-neutral shadow-lg',
    'panelClass' => 'rounded-2xl border border-base-300/70 bg-base-100/95 p-2 shadow-2xl backdrop-blur',
    'itemButtonClass' => 'btn btn-circle btn-sm btn-base-100 shadow-md',
    'activeItemButtonClass' => 'btn btn-circle btn-sm btn-primary shadow-md',
])

@if (!empty($items))
    <div x-data="{ navOpen: false }" class="relative" @click.outside="navOpen = false"
        @keydown.escape.stop="navOpen = false">
        <div class="absolute right-[calc(100%+0.75rem)] top-1/2 -translate-y-1/2">
            <div x-show="navOpen" x-cloak x-transition.origin.right.duration.200ms
                class="pointer-events-auto flex flex-col items-end gap-2 {{ $panelClass }}">
                @foreach ($items as $item)
                    @php
                        $resolvedItemButtonClass = $item['buttonClass']
                            ?? (($item['active'] ?? false) ? $activeItemButtonClass : $itemButtonClass);
                    @endphp

                    <div class="tooltip tooltip-left" data-tip="{{ $item['label'] }}">
                        <a href="{{ $item['href'] }}" aria-label="{{ $item['label'] }}" class="{{ $resolvedItemButtonClass }}">
                            <x-ui.fab.icon :name="$item['icon']" class="h-4 w-4" />
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="tooltip tooltip-left" data-tip="{{ $label }}">
            <button type="button" @click.stop="navOpen = !navOpen" :aria-expanded="navOpen.toString()"
                aria-label="{{ $label }}" class="{{ $buttonClass }}">
                <span x-show="!navOpen" x-cloak>
                    <x-ui.fab.icon name="dashboard" class="h-5 w-5" />
                </span>
                <span x-show="navOpen" x-cloak>
                    <x-ui.fab.icon name="close" class="h-5 w-5" />
                </span>
            </button>
        </div>
    </div>
@endif
