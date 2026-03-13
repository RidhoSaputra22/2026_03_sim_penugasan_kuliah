{{-- Bulk Actions --}}
@if ($selectable && $bulkActionRoute && $items->count() > 0)
    <form
        method="POST"
        action="{{ $bulkActionRoute }}"
        class="flex flex-wrap items-center gap-2"
        @submit.prevent="submitBulkAction($event)"
    >
        @csrf

        <select
            name="bulk_action"
            class="select select-bordered select-sm"
            x-model="bulkAction"
        >
            <option value="">Bulk Action</option>
            <option value="delete">Hapus</option>

            @isset($bulkActions)
                {{ $bulkActions }}
            @endisset
        </select>

        <template x-for="id in selected" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>

        <button
            type="submit"
            class="btn btn-sm btn-error"
            :disabled="!canSubmitBulk"
            :class="{ 'btn-disabled': !canSubmitBulk }"
        >
            Terapkan
        </button>

        <button
            type="button"
            class="btn btn-sm btn-ghost"
            x-show="selected.length > 0"
            @click="clearSelection()"
        >
            Reset Pilihan
        </button>
    </form>
@endif
