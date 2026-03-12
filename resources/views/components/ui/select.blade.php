{{--
    Reusable Searchable Select Component

    @param string $name - Select name
    @param string $label - Label text
    @param array $options - Options array: [value => label] OR [['value' => x, 'label' => y]]
    @param string $value - Selected value (use instead of selected)
    @param string $selected - Selected value (deprecated, use value)
    @param string $placeholder - Placeholder option
    @param string $error - Error message
    @param bool $required - Required field
    @param bool $searchable - Enable search functionality (default: true)
    @param string $searchPlaceholder - Search input placeholder
--}}

@props([
'name',
'label' => null,
'options' => [],
'value' => null,
'selected' => null,
'placeholder' => 'Pilih...',
'error' => null,
'required' => false,
'searchable' => true,
'searchPlaceholder' => 'Cari...',
])

@php
// Support both 'value' and 'selected' prop for backwards compatibility
$selectedValue = old($name, $value ?? $selected);

// Normalize options to consistent format
$normalizedOptions = [];
foreach ($options as $key => $option) {
if (is_array($option) && isset($option['value']) && isset($option['label'])) {
$normalizedOptions[] = ['value' => $option['value'], 'label' => $option['label']];
} else {
$normalizedOptions[] = ['value' => $key, 'label' => $option];
}
}
@endphp

<div class="w-full">
    @if($label)
        <label class="label" for="{{ $name }}">
            <span class="label-text">
                {{ $label }}
                @if($required)
                    <span class="text-error">*</span>
                @endif
            </span>
        </label>
    @endif

    @if($searchable)
        {{-- Searchable Select with Alpine.js --}}
        <div x-data="{
                open: false,
                searchTerm: '',
                selectedValue: @js($selectedValue),
                selectedLabel: '',
                options: @js($normalizedOptions),
                get filteredOptions() {
                    if (!this.searchTerm) return this.options;
                    return this.options.filter(option =>
                        option.label.toLowerCase().includes(this.searchTerm.toLowerCase())
                    );
                },
                selectOption(value, label) {
                    this.selectedValue = value;
                    this.selectedLabel = label;
                    this.open = false;
                    this.searchTerm = '';
                    this.$dispatch('select-change', { name: '{{ $name }}', value: value });
                },
                init() {
                    // Set initial selected label
                    const selected = this.options.find(opt => opt.value == this.selectedValue);
                    if (selected) {
                        this.selectedLabel = selected.label;
                    }
                }
            }" @click.outside="open = false" class="relative">

            {{-- Hidden input for form submission --}}
            <input type="hidden" name="{{ $name }}" x-model="selectedValue" {{ $required ? 'required' : '' }}>

            {{-- Display/Trigger Button --}}
            <button type="button" @click="open = !open"
                {{ $attributes->merge(['class' => 'select select-bordered w-full flex items-center justify-between' . ($error ? ' select-error' : '')]) }}>
                <span x-text="selectedLabel || @js($placeholder)"
                    :class="!selectedLabel ? 'text-base-content/40' : ''"></span>
                <svg class="w-5 h-5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {{-- Dropdown --}}
            <div x-cloak x-show="open" x-transition
                class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg">

                {{-- Search Input --}}
                <div class="p-2 border-b border-base-300">
                    <div class="relative">
                        <input type="text" x-model="searchTerm" placeholder="{{ $searchPlaceholder }}"
                            class="input input-sm input-bordered w-full pr-8" @click.stop>
                        <svg class="w-4 h-4 absolute right-2.5 top-2.5 text-base-content/40" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Options List --}}
                <div class="max-h-60 overflow-y-auto">
                    @if($placeholder && !$required)
                        <button type="button" @click="selectOption('', @js($placeholder))"
                            class="w-full text-left px-4 py-2 hover:bg-base-200 text-base-content/40"
                            :class="selectedValue === '' ? 'bg-primary/10' : ''">
                            {{ $placeholder }}
                        </button>
                    @endif

                    <template x-for="option in filteredOptions" :key="option.value">
                        <button type="button" @click="selectOption(option.value, option.label)"
                            class="w-full text-left px-4 py-2 hover:bg-base-200 transition-colors"
                            :class="selectedValue == option.value ? 'bg-primary/10 font-medium' : ''">
                            <span x-text="option.label"></span>
                        </button>
                    </template>

                    <div x-show="filteredOptions.length === 0" class="px-4 py-3 text-center text-base-content/60 text-sm">
                        Tidak ada hasil
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Standard HTML Select (non-searchable) --}}
        <select id="{{ $name }}" name="{{ $name }}"
            {{ $attributes->merge(['class' => 'select select-bordered w-full' . ($error ? ' select-error' : '')]) }}
            {{ $required ? 'required' : '' }}>
            @if($placeholder)
                <option value="" disabled {{ !$selectedValue ? 'selected' : '' }}>{{ $placeholder }}</option>
            @endif

            @foreach($normalizedOptions as $option)
                <option value="{{ $option['value'] }}" {{ $selectedValue == $option['value'] ? 'selected' : '' }}>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
    @endif

    @if($error)
        <label class="label">
            <span class="label-text-alt text-error">{{ $error }}</span>
        </label>
    @endif

    @error($name)
        <label class="label">
            <span class="label-text-alt text-error">{{ $message }}</span>
        </label>
    @enderror
</div>
