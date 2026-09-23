import './bootstrap';

const openHashDetails = () => {
    const target = document.getElementById(decodeURIComponent(window.location.hash.slice(1)));

    if (target?.matches('details')) {
        target.open = true;
    }
};

window.addEventListener('hashchange', openHashDetails);
openHashDetails();

document.addEventListener('click', (event) => {
    const anchor = event.target.closest('a[href^="#tambah-"]');

    if (anchor) {
        document.getElementById(anchor.hash.slice(1))?.setAttribute('open', '');
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
    toggle.querySelector('[data-eye-open]')?.classList.toggle('hidden', shouldShow);
    toggle.querySelector('[data-eye-closed]')?.classList.toggle('hidden', ! shouldShow);

    const label = toggle.querySelector('[data-password-label]');

    if (label) {
        label.textContent = shouldShow ? 'Sembunyikan' : 'Lihat';
    }
});
