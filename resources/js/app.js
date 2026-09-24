import './bootstrap';

const desktopView = window.matchMedia('(min-width: 768px)');
const syncViewSwitch = () => document.querySelectorAll('[data-view-mode]').forEach(control => {
    const mode = control.dataset.viewMode === 'auto' ? (desktopView.matches ? 'table' : 'card') : control.dataset.viewMode;
    control.querySelectorAll('[data-view]').forEach(link => link.setAttribute('aria-current', link.dataset.view === mode ? 'true' : 'false'));
});
desktopView.addEventListener('change', syncViewSwitch);
syncViewSwitch();

const showProfileSection = (hash = window.location.hash) => {
    const profile = document.querySelector('.df-profile-page');
    let target;
    try { target = document.getElementById(decodeURIComponent(hash.replace(/^#/, ''))); } catch { return; }
    if (profile) {
        const section = target?.closest('[data-profile-panel]')?.dataset.profilePanel || 'ringkasan';
        profile.querySelectorAll('[data-profile-panel]').forEach(panel => { panel.hidden = panel.dataset.profilePanel !== section; });
        profile.querySelectorAll('.df-profile-nav a').forEach(link => { link.setAttribute('aria-current', link.hash === `#${section}` ? 'page' : 'false'); });
        profile.dataset.tabsReady = 'true';
    }
    if (target?.matches('details')) target.open = true;
};

window.addEventListener('hashchange', () => showProfileSection());
showProfileSection();

document.addEventListener('click', (event) => {
    const anchor = event.target.closest('.df-profile-page a[href^="#"]');

    if (anchor) {
        showProfileSection(anchor.hash);
    }
});

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');

    if (! toggle) {
        return;
    }

    const input = document.getElementById(toggle.dataset.target);

    if (! input) {
        return;
    }

    const shouldShow = input.type === 'password';

    input.type = shouldShow ? 'text' : 'password';
    toggle.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Tampilkan password');
    toggle.setAttribute('aria-pressed', String(shouldShow));
    toggle.title = shouldShow ? 'Sembunyikan password' : 'Tampilkan password';
    toggle.querySelector('[data-eye-open]')?.classList.toggle('hidden', shouldShow);
    toggle.querySelector('[data-eye-closed]')?.classList.toggle('hidden', ! shouldShow);

    const label = toggle.querySelector('[data-password-label]');

    if (label) {
        label.textContent = shouldShow ? 'Sembunyikan' : 'Lihat';
    }
});

document.addEventListener('click', event => {
    const opener = event.target.closest('[data-dialog-open]');
    if (opener) document.getElementById(opener.dataset.dialogOpen)?.showModal();
    if (event.target.closest('[data-dialog-close]')) event.target.closest('dialog')?.close();
});

document.querySelectorAll('dialog').forEach(dialog => {
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
    });
});

document.querySelectorAll('.df-action-menu').forEach(menu => {
    menu.addEventListener('toggle', event => {
        if (event.newState !== 'open') return;
        const trigger = document.querySelector(`[popovertarget="${menu.id}"]`);
        if (!trigger) return;
        const rect = trigger.getBoundingClientRect();
        menu.style.left = `${Math.max(8, Math.min(rect.right - menu.offsetWidth, innerWidth - menu.offsetWidth - 8))}px`;
        menu.style.top = `${rect.bottom + menu.offsetHeight + 8 < innerHeight ? rect.bottom + 4 : Math.max(8, rect.top - menu.offsetHeight - 4)}px`;
    });
});

let pendingDelete = null;
const confirmation = document.getElementById('delete-confirmation');
document.addEventListener('submit', event => {
    const form = event.target;
    const method = form.elements.namedItem('_method')?.value?.toLowerCase();
    if (method !== 'delete' || form.dataset.deleteConfirmed === 'true' || !confirmation) return;
    event.preventDefault();
    pendingDelete = form;
    confirmation.showModal();
});
document.querySelector('[data-confirm-delete]')?.addEventListener('click', () => {
    if (!pendingDelete) return;
    pendingDelete.dataset.deleteConfirmed = 'true';
    confirmation.close();
    pendingDelete.requestSubmit();
    pendingDelete = null;
});

const filterLabels = {
    category_id: 'Kategori', status: 'Status', academic_year: 'Tahun akademik', year: 'Tahun akademik',
    semester: 'Semester', source_type: 'Sumber data', source: 'Sumber data', visibility: 'Visibilitas',
    date_from: 'Dari tanggal', date_to: 'Sampai tanggal', role: 'Peran dosen', subcategory: 'Subkategori',
};
document.querySelectorAll('.df-filter-dialog input:not([type="hidden"]), .df-filter-dialog select').forEach(field => {
    const label = document.createElement('label');
    label.className = `df-label grid gap-2 ${field.classList.contains('sm:col-span-2') ? 'sm:col-span-2' : ''}`;
    label.textContent = filterLabels[field.name] || field.getAttribute('aria-label') || field.placeholder || field.name;
    field.before(label);
    label.append(field);
});

// Match the failed form, so identical field names in other profile sections stay unchanged.
const forms = [...document.querySelectorAll('form[method="post"]')];
forms.forEach(form => {
    const field = document.createElement('input');
    field.type = 'hidden';
    field.name = '_form_key';
    field.value = `${new URL(form.action).pathname}:${form.elements.namedItem('_method')?.value || 'post'}`;
    form.append(field);
});
const recoveryNode = document.getElementById('form-recovery');
if (recoveryNode) {
    const recovery = JSON.parse(recoveryNode.textContent);
    const form = forms.find(candidate => candidate.elements.namedItem('_form_key')?.value === recovery.key);
    if (form) {
        form.querySelectorAll('input[type="checkbox"]').forEach(field => {
            field.checked = String(recovery.values[field.name] ?? '') === field.value;
        });
        for (const [name, value] of Object.entries(recovery.values)) {
            const field = form.elements.namedItem(name);
            if (!field || typeof value === 'object' || field.type === 'file' || field.type === 'password') continue;
            if (field.type === 'checkbox' || field.type === 'radio') field.checked = String(value) === field.value;
            else field.value = value ?? '';
        }
        const section = form.closest('[data-profile-panel]');
        if (section) {
            history.replaceState(null, '', `#${section.dataset.profilePanel}`);
            showProfileSection();
        }
        let disclosure = form.closest('details');
        while (disclosure) { disclosure.open = true; disclosure = disclosure.parentElement.closest('details'); }
        for (const [name, errors] of Object.entries(recovery.errors)) {
            const field = form.elements.namedItem(name);
            if (!field) continue;
            field.setAttribute('aria-invalid', 'true');
            const message = document.createElement('span');
            message.className = 'df-field-error';
            message.id = `field-error-${name.replace(/[^a-z0-9]/gi, '-')}`;
            message.textContent = errors[0];
            field.setAttribute('aria-describedby', message.id);
            field.after(message);
        }
        const focusInvalidField = () => form.querySelector('[aria-invalid="true"]')?.focus();
        if (document.readyState === 'complete') focusInvalidField();
        else window.addEventListener('load', focusInvalidField, { once: true });
    }
}
