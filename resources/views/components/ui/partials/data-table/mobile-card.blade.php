@if ($mobileCard && $items->count() > 0)
    <div class="grid gap-3 md:hidden">
        @foreach ($data as $row)
            @php
                $rowId = (string) data_get($row, $rowKey);
            @endphp

            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body gap-3 p-4">
                    @if ($selectable)
                        <div class="flex justify-end">
                            <input
                                type="checkbox"
                                class="checkbox checkbox-sm"
                                :checked="selected.includes(@js($rowId))"
                                @mousedown.shift.prevent
                                @click="toggleRow($event, @js($rowId))"
                            >
                        </div>
                    @endif

                    <div class="space-y-2">
                        @foreach ($columns as $field)
                            <div class="flex items-start justify-between gap-4">
                                <div class="text-sm text-base-content/60">
                                    {{ $label($field) }}
                                </div>

                                <div class="text-right text-sm font-medium">
                                    @isset(${'cell_' . $field})
                                        {{ ${'cell_' . $field}($row) }}
                                    @else
                                        {!! $formatValue($row, $field) !!}
                                    @endisset
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($actions)
                        <div class="border-t border-base-200 pt-2">
                            <div class="flex justify-end gap-2">
                                @isset($rowActions)
                                    {{ $rowActions($row) }}
                                @else
                                    @if ($editRoute)
                                        <a href="{{ $editRoute($row) }}" class="btn btn-sm btn-ghost">
                                            Edit
                                        </a>
                                    @endif

                                    @if ($deleteRoute)
                                        <form
                                            method="POST"
                                            action="{{ $deleteRoute($row) }}"
                                            onsubmit="return confirm('Hapus data ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-error btn-outline">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                @endisset
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
