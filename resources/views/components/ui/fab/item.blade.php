{{--
    Reusable FAB Action Item

    @param string|null $tooltip - Tooltip text
    @param string|null $tooltipPosition - DaisyUI tooltip position, for example: left, right, bottom
    @param string $wrapperClass - Extra classes for the tooltip wrapper
    @param string|null $tag - Supported: button, a, div. Defaults to a when href exists, otherwise button
    @param string $type - Button type when tag is button
    @param string|null $href - Link target when tag is a
    @param string|null $ariaLabel - Accessible label, defaults to tooltip
    @param string $buttonClass - Base classes for the rendered action element
--}}

@props([
    'tooltip' => null,
    'tooltipPosition' => 'left',
    'wrapperClass' => '',
    'tag' => null,
    'type' => 'button',
    'href' => null,
    'ariaLabel' => null,
    'buttonClass' => 'btn btn-lg btn-circle',
])

@php
    $resolvedTag = $tag ?? ($href ? 'a' : 'button');
    $resolvedAriaLabel = $ariaLabel ?? $tooltip;
    $tooltipPositionClass = blank($tooltipPosition) || $tooltipPosition === 'tooltip'
        ? ''
        : (str_starts_with($tooltipPosition, 'tooltip-') ? $tooltipPosition : 'tooltip-' . $tooltipPosition);
    $wrapperClasses = trim(implode(' ', array_filter([
        $tooltip ? 'tooltip' : null,
        $tooltip ? $tooltipPositionClass : null,
        $wrapperClass,
    ])));
@endphp

@if ($tooltip)
    <div class="{{ $wrapperClasses }}" data-tip="{{ $tooltip }}">
@endif



@if ($resolvedTag === 'a')
    <a href="{{ $href ?? '#' }}"
        @if ($resolvedAriaLabel) aria-label="{{ $resolvedAriaLabel }}" @endif
        {{ $attributes->class([$buttonClass]) }}>
        {{ $slot }}
    </a>
@elseif ($resolvedTag === 'div')
    <div role="button" tabindex="0"
        @if ($resolvedAriaLabel) aria-label="{{ $resolvedAriaLabel }}" @endif
        {{ $attributes->class([$buttonClass]) }}>
        {{ $slot }}
    </div>
@else
    <button type="{{ $type }}"
        @if ($resolvedAriaLabel) aria-label="{{ $resolvedAriaLabel }}" @endif
        {{ $attributes->class([$buttonClass]) }}>
        {{ $slot }}
    </button>
@endif

@if ($tooltip)
    </div>
@endif
