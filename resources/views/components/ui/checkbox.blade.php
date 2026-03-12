{{--
    Reusable Checkbox Component

    @param string $name - Input name (use array syntax for multiple: name="items[]")
    @param string $label - Label text for the group
    @param array $options - Array of options [['value' => 'val', 'label' => 'Label']]
    @param array $checked - Array of checked values
    @param bool $required - Required field
    @param string $layout - Layout direction: 'horizontal' or 'vertical'
    @param string $helpText - Helper text below checkbox group
    @param bool $single - If true, renders a single checkbox instead of group
--}}

@props([
    'name',
    'label' => null,
    'options' => [],
    'checked' => [],
    'required' => false,
    'layout' => 'horizontal',
    'helpText' => null,
    'single' => true,
])

<div class="form-control w-full">
    @if($label)
        <label class="label">
            <span class="label-text">
                {{ $label }}
                @if($required)
                    <span class="text-error">*</span>
                @endif
            </span>
        </label>
    @endif

    @if($single)
        <!-- Single checkbox -->
        <label class="flex items-center gap-2 cursor-pointer">
            <input
                type="checkbox"
                name="{{ $name }}"
                value="1"
                {{ old($name, in_array('1', (array)$checked)) ? 'checked' : '' }}
                {{ $required ? 'required' : '' }}
                {{ $attributes->merge(['class' => 'checkbox checkbox-primary']) }}
            />
            <span class="label-text">{{ $options[0]['label'] ?? '' }}</span>
        </label>
    @else
        <!-- Multiple checkboxes -->
        <div class="flex {{ $layout === 'vertical' ? 'flex-col' : 'flex-wrap' }} gap-4">
            @foreach($options as $option)
                @php
                    $isDisabled = isset($option['disabled']) && $option['disabled'];
                @endphp
                <label class="flex items-center gap-2 cursor-pointer {{ $isDisabled ? 'opacity-50' : '' }}">
                    <input
                        type="checkbox"
                        name="{{ $name }}"
                        value="{{ $option['value'] }}"
                        {{ in_array($option['value'], old($name, (array)$checked)) ? 'checked' : '' }}
                        {{ $required ? 'required' : '' }}
                        {{ $isDisabled ? 'disabled' : '' }}
                        {{ $attributes->merge(['class' => 'checkbox checkbox-primary']) }}
                    />
                    <span>{{ $option['label'] }}</span>
                </label>
            @endforeach
        </div>
    @endif

    @if($helpText)
        <label class="label">
            <span class="label-text-alt text-base-content/70">{{ $helpText }}</span>
        </label>
    @endif

    @error($name)
        <label class="label">
            <span class="label-text-alt text-error">{{ $message }}</span>
        </label>
    @enderror
</div>
