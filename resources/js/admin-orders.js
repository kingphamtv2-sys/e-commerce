const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
const modal = document.getElementById('order-cancel-modal');
const markPaidModal = document.getElementById('mark-paid-modal');

const setModal = (target, open) => {
    if (!target || (!open && target.querySelector('form')?.dataset.processing === 'true')) return;
    target.classList.toggle('pointer-events-none', !open);
    target.classList.toggle('opacity-0', !open);
    target.classList.toggle('pointer-events-auto', open);
    target.classList.toggle('opacity-100', open);
    target.setAttribute('aria-hidden', open ? 'false' : 'true');
};

const replace = (selector, html) => {
    const current = document.querySelector(selector);
    if (!current || !html) return;
    current.outerHTML = html;
};

document.addEventListener('click', event => {
    if (event.target.closest('[data-open-order-cancel]')) setModal(modal, true);
    if (event.target.closest('[data-close-order-cancel], [data-cancel-backdrop]')) setModal(modal, false);
    if (event.target.closest('[data-open-mark-paid]')) setModal(markPaidModal, true);
    if (event.target.closest('[data-close-mark-paid], [data-mark-paid-backdrop]')) setModal(markPaidModal, false);
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
        setModal(modal, false);
        setModal(markPaidModal, false);
    }
});

document.addEventListener('submit', async event => {
    const form = event.target.closest('form[data-order-action]');
    if (!form) return;
    event.preventDefault();
    if (form.dataset.processing === 'true' || !form.reportValidity()) return;

    form.dataset.processing = 'true';
    const button = event.submitter;
    if (button) {
        button.disabled = true;
        button.dataset.originalLabel = button.textContent;
        button.textContent = document.documentElement.dataset.processingLabel || 'Processing…';
    }
    const errorBox = form.querySelector('[data-order-errors]');
    errorBox?.classList.add('hidden');
    window.adminLoading?.show();

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = Object.values(payload.errors || {}).flat()[0] || payload.message || 'Request failed.';
            if (errorBox) {
                errorBox.textContent = message;
                errorBox.classList.remove('hidden');
            }
            window.adminToast?.(message, 'error');
            return;
        }

        replace('#order-status-summary', payload.status_html);
        replace('#order-management', payload.management_html);
        replace('#order-timeline', payload.timeline_html);
        if (form.hasAttribute('data-cancel-form')) setModal(modal, false);
        if (form.hasAttribute('data-mark-paid-form')) setModal(markPaidModal, false);
        if (form.action.includes('/notes')) form.reset();
        window.adminToast?.(payload.message);
    } catch {
        const message = 'Connection error. Please try again.';
        if (errorBox) {
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        }
        window.adminToast?.(message, 'error');
    } finally {
        form.dataset.processing = 'false';
        if (button) {
            button.disabled = false;
            button.textContent = button.dataset.originalLabel || button.textContent;
            delete button.dataset.originalLabel;
        }
        window.adminLoading?.hide();
    }
});
