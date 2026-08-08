import './bootstrap';

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
    toggle.querySelector('[data-eye-open]')?.classList.toggle('hidden', shouldShow);
    toggle.querySelector('[data-eye-closed]')?.classList.toggle('hidden', ! shouldShow);

    const label = toggle.querySelector('[data-password-label]');

    if (label) {
        label.textContent = shouldShow ? 'Sembunyikan' : 'Lihat';
    }
});
