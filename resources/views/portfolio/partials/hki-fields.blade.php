<x-ui.card class="p-5 sm:p-6">
    <x-ui.section-header title="Data HKI" description="Isi data sesuai bukti permohonan atau sertifikat yang Anda miliki." />
    <div class="mt-5 grid gap-4 md:grid-cols-2">
        <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
            Jenis HKI
            <input name="hki_type" list="hki-types" value="{{ old('hki_type', $activity?->hki_type) }}" placeholder="Contoh: Hak Cipta" maxlength="100" class="df-field">
            <datalist id="hki-types">
                <option value="Hak Cipta">
                <option value="Paten">
                <option value="Paten Sederhana">
                <option value="Merek">
                <option value="Desain Industri">
            </datalist>
        </label>
        <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
            Status hukum HKI
            <select name="hki_status" class="df-field">
                <option value="">Belum dicatat</option>
                @foreach(['DIAJUKAN' => 'Diajukan', 'DIPROSES' => 'Diproses', 'TERCATAT' => 'Tercatat', 'TERBIT' => 'Terbit', 'LAINNYA' => 'Lainnya'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('hki_status', $activity?->hki_status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
            Nomor permohonan
            <input name="hki_application_number" value="{{ old('hki_application_number', $activity?->hki_application_number) }}" maxlength="100" class="df-field">
        </label>
        <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)]">
            Nomor pencatatan/sertifikat
            <input name="hki_registration_number" value="{{ old('hki_registration_number', $activity?->hki_registration_number) }}" maxlength="100" class="df-field">
        </label>
        <label class="grid gap-2 text-sm font-bold text-[var(--text-secondary)] md:col-span-2">
            Pemegang hak
            <input name="hki_rights_holder" value="{{ old('hki_rights_holder', $activity?->hki_rights_holder) }}" maxlength="255" placeholder="Nama pemegang hak sesuai dokumen" class="df-field">
        </label>
    </div>
</x-ui.card>
