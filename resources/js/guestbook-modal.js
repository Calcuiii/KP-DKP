import DOMPurify from 'dompurify';

export const initializeGuestbookModal = (app, request, onVerified) => {
    const dialog = app.querySelector('[data-guestbook-dialog]');
    const content = dialog?.querySelector('[data-guestbook-content]');
    if (!dialog || !content) return null;

    let busy = false;
    let returnFocus;
    const focusForm = () => content.querySelector('input[type="tel"], button[type="submit"]')?.focus();
    const render = (html) => {
        content.innerHTML = DOMPurify.sanitize(html);
    };
    const showError = (message) => {
        let alert = content.querySelector('[role="alert"]');
        if (!alert) {
            alert = document.createElement('p');
            alert.role = 'alert';
            alert.className = 'mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm leading-relaxed text-red-800';
            const header = content.querySelector('header');
            if (header) header.after(alert);
            else content.prepend(alert);
        }
        alert.textContent = message;
        alert.tabIndex = -1;
        alert.focus();
    };
    const close = () => dialog.close();
    dialog.querySelector('[data-guestbook-close]').addEventListener('click', close);
    dialog.addEventListener('click', (event) => {
        const bounds = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right
            || event.clientY < bounds.top || event.clientY > bounds.bottom)) close();
    });
    dialog.addEventListener('close', () => returnFocus?.focus());

    content.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        event.preventDefault();
        if (busy) return;
        busy = true;
        content.setAttribute('aria-busy', 'true');
        const buttons = [...content.querySelectorAll('button[type="submit"]')];
        buttons.forEach((button) => { button.disabled = true; });
        try {
            const data = Object.fromEntries(new FormData(form));
            const payload = await request(form.action, { method: 'POST', body: JSON.stringify(data) });
            if (payload.allowed) {
                close();
                await onVerified();
            } else if (payload.html) {
                render(payload.html);
                focusForm();
            }
        } catch (error) {
            if (error.payload?.html) render(error.payload.html);
            const validation = Object.values(error.payload?.errors ?? {}).flat()[0];
            const message = error.status === 429 ? 'Terlalu banyak percobaan. Tunggu satu menit lalu coba lagi.'
                : error.status === 419 ? 'Sesi halaman berakhir. Muat ulang halaman untuk mencoba lagi.'
                    : validation ?? error.message;
            showError(message);
        } finally {
            busy = false;
            content.removeAttribute('aria-busy');
            buttons.forEach((button) => { if (button.isConnected) button.disabled = false; });
        }
    });

    return {
        isOpen: () => dialog.open,
        open: async () => {
            if (dialog.open || busy) return;
            returnFocus = document.activeElement;
            const loading = document.createElement('p');
            loading.className = 'p-8 text-sm text-muted-foreground';
            loading.role = 'status';
            loading.textContent = 'Menyiapkan verifikasi…';
            content.replaceChildren(loading);
            dialog.showModal();
            busy = true;
            try {
                const payload = await request(app.dataset.guestbookUrl);
                render(payload.html);
                if (dialog.open) focusForm();
            } catch {
                showError('Verifikasi belum dapat dimuat. Tutup pop-up dan coba lagi.');
            } finally {
                busy = false;
            }
        },
    };
};
