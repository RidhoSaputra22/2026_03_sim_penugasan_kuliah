{{--
    Reusable Floating Action Button Component

    @param string $variant - DaisyUI FAB variant, for example: flower
    @param string $triggerText - Fallback trigger content when no trigger slot is provided
    @param string $triggerAriaLabel - Accessible label for the fallback trigger
    @param string $triggerClass - Classes for the fallback trigger
    @param string $triggerTag - Supported: div, button
    @param string $triggerType - Button type when triggerTag is button
    @param string|null $mainActionText - Fallback main action content, defaults to triggerText
    @param string|null $mainActionAriaLabel - Accessible label for the fallback main action
    @param string $mainActionClass - Classes for the fallback main action
    @param string $mainActionType - Button type for the fallback main action
    @param bool $showMainAction - Whether the fallback main action should be rendered
    @slot trigger - Custom trigger content
    @slot mainAction - Custom main action content
    @slot $slot - FAB actions, ideally using <x-ui.fab.item>
--}}

@props([
    'variant' => 'flower',
    'triggerText' => '+',
    'triggerAriaLabel' => 'Buka menu aksi',
    'triggerClass' => 'btn btn-lg btn-circle btn-primary',
    'triggerTag' => 'div',
    'triggerType' => 'button',
    'mainActionText' => null,
    'mainActionAriaLabel' => null,
    'mainActionClass' => 'fab-main-action btn btn-lg btn-circle btn-secondary',
    'mainActionType' => 'button',
    'showMainAction' => true,
])

@php
    $triggerContent = isset($trigger) ? $trigger : $triggerText;
    $resolvedMainActionText = $mainActionText ?? $triggerText;
    $resolvedMainActionAriaLabel = $mainActionAriaLabel ?? $triggerAriaLabel;
    $resolvedMainActionContent = isset($mainAction) ? $mainAction : $resolvedMainActionText;
@endphp

<div {{ $attributes->class([
    'fab',
    "fab-{$variant}" => filled($variant),
]) }}>
    @if ($triggerTag === 'button')
        <button type="{{ $triggerType }}" aria-label="{{ $triggerAriaLabel }}" class="{{ $triggerClass }}">
            {{ $triggerContent }}
        </button>
    @else
        <div tabindex="0" role="button" aria-label="{{ $triggerAriaLabel }}" class="{{ $triggerClass }}">
            {{ $triggerContent }}
        </div>
    @endif

    @if ($showMainAction)
        <button type="{{ $mainActionType }}" aria-label="{{ $resolvedMainActionAriaLabel }}"
            class="{{ $mainActionClass }}">
            {{ $resolvedMainActionContent }}
        </button>
    @endif

    {{ $slot }}
</div>
