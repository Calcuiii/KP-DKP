document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.querySelector('[data-reply-letter-dialog]');
    if (!dialog) return;

    const frame = dialog.querySelector('[data-reply-letter-frame]');
    const newTab = dialog.querySelector('[data-reply-letter-new-tab]');
    const participant = dialog.querySelector('[data-reply-letter-participant]');
    let trigger;

    document.querySelectorAll('[data-reply-letter-preview]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            trigger = link;
            participant.textContent = link.dataset.participantName ?? '';
            frame.src = link.href;
            newTab.href = link.href;
            dialog.showModal();
        });
    });

    dialog.querySelector('[data-reply-letter-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        const bounds = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right
            || event.clientY < bounds.top || event.clientY > bounds.bottom)) dialog.close();
    });
    dialog.addEventListener('close', () => {
        frame.removeAttribute('src');
        newTab.removeAttribute('href');
        participant.textContent = '';
        trigger?.focus();
    });
});
