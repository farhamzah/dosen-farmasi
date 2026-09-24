@props([
    'identifier',
])

@php
    $statusLabels = [
        'DRAFT' => 'Tercatat',
        'ADMIN_VERIFIED' => 'Tercatat',
        'SYSTEM_VERIFIED' => 'Tercatat otomatis',
        'VERIFIED' => 'Tercatat',
        'REVISION_REQUIRED' => 'Perlu Revisi',
        'REJECTED' => 'Ditolak',
    ];
    $visibilityLabels = [
        'PRIVATE' => 'Private',
        'INTERNAL' => 'Internal',
        'PUBLIC' => 'Public',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-[var(--radius-md)] border border-[var(--border)] bg-white p-4']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-black text-[var(--text-primary)]">{{ $identifier->identifier_type }}</p>
            <p class="mt-1 truncate text-sm font-semibold text-[var(--text-secondary)]">{{ $identifier->identifier_value }}</p>
        </div>
        <x-ui.badge :tone="str_contains((string) $identifier->verification_status, 'VERIFIED') ? 'success' : 'info'">{{ $statusLabels[$identifier->verification_status] ?? str($identifier->verification_status)->replace('_', ' ')->title() }}</x-ui.badge>
    </div>
    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <x-ui.badge tone="neutral">{{ $visibilityLabels[$identifier->visibility] ?? $identifier->visibility }}</x-ui.badge>
        @if($identifier->profile_url)
            <a href="{{ $identifier->profile_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-extrabold text-[var(--brand-700)] hover:text-[var(--brand-900)]">Buka profil</a>
        @endif
    </div>
    <details class="mt-4 rounded-[var(--radius-sm)] border border-[var(--border)] bg-[var(--surface-muted)]/70 p-3">
        <summary class="cursor-pointer text-sm font-black text-[var(--brand-800)]">Edit visibilitas dan data</summary>
        <form method="post" action="{{ route('profile.identifiers.update', $identifier) }}" class="mt-4 space-y-3">
            @csrf
            @method('put')
            <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                Jenis identitas
                <select name="identifier_type" class="df-field mt-2" required>
                    @foreach(\App\Models\LecturerExternalIdentifier::TYPES as $type)
                        <option value="{{ $type }}" @selected($identifier->identifier_type === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                ID atau username
                <input name="identifier_value" value="{{ $identifier->identifier_value }}" class="df-field mt-2" required>
            </label>
            <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                URL profil
                <input name="profile_url" value="{{ $identifier->profile_url }}" class="df-field mt-2" placeholder="https://...">
            </label>
            <label class="block text-xs font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                Visibilitas
                <select name="visibility" class="df-field mt-2" required>
                    @foreach($visibilityLabels as $value => $label)
                        <option value="{{ $value }}" @selected($identifier->visibility === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="grid gap-2 sm:grid-cols-[1fr_auto]">
                <button class="df-button df-button-primary">Simpan Perubahan</button>
                <button type="submit" form="delete-identifier-{{ $identifier->id }}" class="df-button df-button-secondary text-rose-700">Hapus</button>
            </div>
        </form>
        <form id="delete-identifier-{{ $identifier->id }}" method="post" action="{{ route('profile.identifiers.destroy', $identifier) }}" class="hidden">
            @csrf
            @method('delete')
        </form>
    </details>
</div>
