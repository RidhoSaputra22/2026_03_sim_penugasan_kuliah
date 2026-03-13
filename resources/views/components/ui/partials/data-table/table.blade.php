@if ($items->count() > 0)
    <div class="hidden overflow-x-auto md:block" :class="{ 'select-none': isShiftPressed }" >

        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    @if ($selectable)
                        <th class="w-10">
                            <input type="checkbox" class="checkbox checkbox-sm" :checked="isAllSelected"
                                @mousedown.shift.prevent @click="toggleAll($event)">
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
                                <input type="checkbox" class="checkbox checkbox-sm"
                                    :checked="selected.includes(@js($rowId))" @mousedown.shift.prevent
                                    @click="toggleRow($event, @js($rowId))">
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
                                        @if ($editRoute)
                                            <a href="{{ $editRoute($row) }}" class="btn btn-ghost btn-xs" title="Edit"
                                                aria-label="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        @endif

                                        @if ($deleteRoute)
                                            <button type="button" class="btn btn-ghost btn-xs text-error"
                                                title="Hapus" aria-label="Hapus"
                                                @click="$dispatch('confirm-delete', { action: '{{ $deleteRoute($row) }}', message: 'Apakah Anda yakin ingin menghapus data ini?' })">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
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
