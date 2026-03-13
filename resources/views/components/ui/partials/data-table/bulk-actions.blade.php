@if ($selectable && $bulkActionRoute && $items->count() > 0)
    <form
        method="POST"
        action="{{ $bulkActionRoute }}"
        class="flex flex-wrap items-center gap-2"
        @submit.prevent="submitBulkAction($event)"
        x-ref="bulkActionForm"
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

    {{-- Confirm Modal --}}
    <dialog id="bulk-action-confirm-modal" class="modal" x-ref="bulkActionModal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Konfirmasi Bulk Action</h3>

            <p class="py-3 text-sm text-base-content/70" x-text="confirmMessage"></p>

            <div class="rounded-box bg-base-200 p-3 text-sm">
                <div class="flex items-center justify-between">
                    <span>Aksi</span>
                    <span class="font-semibold" x-text="bulkActionLabel"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Jumlah data</span>
                    <span class="font-semibold" x-text="selected.length"></span>
                </div>
            </div>

            <div class="modal-action">
                <form method="dialog">
                    <button type="button" class="btn btn-ghost" @click="closeBulkActionModal()">
                        Batal
                    </button>
                </form>

                <button
                    type="button"
                    class="btn"
                    :class="bulkAction === 'delete' ? 'btn-error' : 'btn-primary'"
                    @click="confirmBulkAction()"
                >
                    Ya, Lanjutkan
                </button>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button @click="closeBulkActionModal()">close</button>
        </form>
    </dialog>
@endif
