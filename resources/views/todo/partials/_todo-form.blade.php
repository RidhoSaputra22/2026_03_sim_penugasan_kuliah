@php
    $currentAttachmentUrl = isset($todo) ? $todo->attachmentUrl() : null;
    $currentAttachmentName = isset($todo) ? $todo->attachmentName() : null;
    $currentAttachmentIsImage = isset($todo) ? $todo->attachmentIsImage() : false;
@endphp

<form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @if (isset($method) && $method === 'PUT')
        @method('PUT')
    @endif
    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">

    <x-ui.select name="tugas_id" label="Tugas" :required="true" placeholder="Pilih Tugas"
        :options="$tugasList->pluck('judul', 'id')->toArray()"
        :value="old('tugas_id', $tugasId ?? optional($todo)->tugas_id ?? '')" :error="$errors->first('tugas_id')" />

    <x-ui.input name="judul" label="Judul" :required="true"
        :value="old('judul', optional($todo)->judul)" :error="$errors->first('judul')" />

    <x-ui.textarea name="deskripsi" label="Deskripsi"
        :value="old('deskripsi', optional($todo)->deskripsi)" :error="$errors->first('deskripsi')" />

    <x-ui.select name="status" label="Status" :searchable="false" placeholder="Pilih status"
        :options="\App\Enums\Status::taskOptions()"
        :value="old('status', isset($todo) ? (optional($todo->status)->value ?? (string) $todo->status) : \App\Enums\Status::BELUM->value)"
        :error="$errors->first('status')" />

    <x-ui.input name="deadline" type="datetime-local" label="Deadline"
        :value="old('deadline', isset($todo) && $todo->deadline ? date('Y-m-d\\TH:i', strtotime($todo->deadline)) : '')"
        :error="$errors->first('deadline')" />

    <x-ui.input name="file" label="Foto Checklist" type="file" accept="image/*"
        helpText="Unggah foto bukti, referensi visual, atau progress checklist hingga 10 MB."
        :error="$errors->first('file')" />

    @if ($currentAttachmentUrl)
        <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-base-content/45">
                        Foto Saat Ini
                    </div>
                    <div class="mt-2 text-sm font-semibold text-base-content">
                        {{ $currentAttachmentName }}
                    </div>
                    <p class="mt-1 text-xs text-base-content/60">
                        Unggah foto baru hanya jika ingin mengganti gambar checklist ini.
                    </p>
                </div>

                <a href="{{ $currentAttachmentUrl }}" target="_blank" rel="noreferrer"
                    class="btn btn-ghost btn-sm w-full sm:w-auto">
                    <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                    Buka Foto
                </a>
            </div>

            @if ($currentAttachmentIsImage)
                <div class="mt-4 overflow-hidden rounded-md border border-base-300/70 bg-base-200/40">
                    <img src="{{ $currentAttachmentUrl }}" alt="Preview foto checklist {{ $currentAttachmentName }}"
                        class="h-56 w-full object-contain object-center">
                </div>
            @endif
        </div>
    @endif

    <div class="flex justify-end gap-2">
        <x-ui.button type="ghost" :href="$cancelUrl" :isSubmit="false">
            Batal
        </x-ui.button>
        <x-ui.button type="primary">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
