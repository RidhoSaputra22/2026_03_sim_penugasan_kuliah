@if ($items->count() > 0)
    <div class="hidden overflow-x-auto md:block" :class="{ 'select-none': isShiftPressed }" >

        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    @if ($selectable)
                        <th class="w-10">
                            <x-ui.checkbox :bare="true" class="checkbox-sm" x-bind:checked="isAllSelected"
                                aria-label="Pilih semua data" @mousedown.shift.prevent @click="toggleAll($event)" />
                        </th>
                    @endif

                    @foreach ($columns as $field)
                        <th>
                            @if ($isSortable($field))
                                <a href="{{ $sortUrl($field) }}" class="inline-flex items-center gap-1 hover:underline">
                                    <span>{{ $label($field) }}</span>

                                    @if ($sortDirection($field) === 'asc')
                                        <span>↑</span>
                                    @elseif ($sortDirection($field) === 'desc')
                                        <span>↓</span>
                                    @else
                                        <span class="opacity-30">↕</span>
                                    @endif
                                </a>
                            @else
                                {{ $label($field) }}
                            @endif
                        </th>
                    @endforeach

                    @if ($actions)
                        <th class="w-28 text-center">Aksi</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @foreach ($data as $row)
                    @php
                        $rowId = (string) data_get($row, $rowKey);
                    @endphp

                    <tr class="hover">
                        @if ($selectable)
                            <td>
                                <x-ui.checkbox :bare="true" class="checkbox-sm"
                                    x-bind:checked="selected.includes(@js($rowId))"
                                    x-bind:aria-label="'Pilih data ' + @js($rowId)" @mousedown.shift.prevent
                                    @click="toggleRow($event, @js($rowId))" />
                            </td>
                        @endif

                        @foreach ($columns as $field)
                            <td>
                                @isset(${'cell_' . $field})
                                    {{ ${'cell_' . $field}($row) }}
                                @else
                                    {!! $formatValue($row, $field) !!}
                                @endisset
                            </td>
                        @endforeach

                        @if ($actions)
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    @isset($rowActions)
                                        {{ $rowActions($row) }}
                                    @else
                                        @if ($showRoute)
                                            <a href="{{ $showRoute($row) }}" class="btn btn-ghost btn-xs" title="Fokus"
                                                aria-label="Fokus">
                                                <x-heroicon-o-eye class="h-4 w-4" />
                                            </a>
                                        @endif

                                        @if ($editRoute)
                                            <a href="{{ $editRoute($row) }}" class="btn btn-ghost btn-xs" title="Edit"
                                                aria-label="Edit">
                                                <x-heroicon-o-pencil-square class="h-4 w-4" />
                                            </a>
                                        @endif

                                        @if ($deleteRoute)
                                            <x-ui.button type="ghost" size="xs" :isSubmit="false" class="text-error"
                                                title="Hapus" aria-label="Hapus"
                                                @click="$dispatch('confirm-delete', { action: '{{ $deleteRoute($row) }}', message: 'Apakah Anda yakin ingin menghapus data ini?' })">
                                                <x-heroicon-o-trash class="h-4 w-4" />
                                            </x-ui.button>
                                        @endif
                                @endisset
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    </table>
</div>
@endif
