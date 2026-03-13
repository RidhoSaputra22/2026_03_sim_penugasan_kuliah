@once
    @push('scripts')
        <script>
            function dataTableComponent({ rowIds = [] }) {
                return {
                    rowIds,
                    selected: [],
                    bulkAction: '',
                    lastCheckedIndex: null,
                    isShiftPressed: false,

                    get isAllSelected() {
                        return this.rowIds.length > 0 && this.selected.length === this.rowIds.length;
                    },

                    get canSubmitBulk() {
                        return this.bulkAction !== '' && this.selected.length > 0;
                    },

                    toggleAll(event) {
                        const checked = event.target.checked;
                        this.selected = checked ? [...this.rowIds] : [];
                        this.lastCheckedIndex = null;
                    },

                    clearSelection() {
                        this.selected = [];
                        this.lastCheckedIndex = null;
                    },

                    toggleRow(event, rowId) {
                        rowId = String(rowId);

                        const currentIndex = this.rowIds.indexOf(rowId);
                        const wasSelected = this.selected.includes(rowId);
                        const shouldSelect = !wasSelected;
                        const isShiftPressed = event.shiftKey;

                        if (
                            isShiftPressed &&
                            this.lastCheckedIndex !== null &&
                            currentIndex !== -1
                        ) {
                            const start = Math.min(this.lastCheckedIndex, currentIndex);
                            const end = Math.max(this.lastCheckedIndex, currentIndex);
                            const rangeIds = this.rowIds.slice(start, end + 1);

                            if (shouldSelect) {
                                this.selected = [...new Set([...this.selected, ...rangeIds])];
                            } else {
                                this.selected = this.selected.filter(id => !rangeIds.includes(id));
                            }
                        } else {
                            if (shouldSelect) {
                                this.selected = [...new Set([...this.selected, rowId])];
                            } else {
                                this.selected = this.selected.filter(id => id !== rowId);
                            }
                        }

                        this.lastCheckedIndex = currentIndex;
                    },

                    submitBulkAction(event) {
                        if (!this.bulkAction) {
                            alert('Pilih bulk action terlebih dahulu.');
                            return;
                        }

                        if (this.selected.length === 0) {
                            alert('Pilih minimal satu data.');
                            return;
                        }

                        if (this.bulkAction === 'delete') {
                            if (!confirm('Yakin ingin menghapus data terpilih?')) {
                                return;
                            }
                        }

                        event.target.submit();
                    }
                };
            }
        </script>
    @endpush
@endonce
