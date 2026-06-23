const checkoutCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

const checkoutRequest = async (url, options = {}) => {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': checkoutCsrfToken(),
            ...(options.headers || {}),
        },
        ...options,
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw payload;
    }

    return payload;
};

const setCheckoutLoading = (form, loading) => {
    form.dataset.processing = loading ? 'true' : 'false';
    form.querySelectorAll('button, input, textarea').forEach(control => {
        control.disabled = loading;
    });
};

const checkoutMessage = payload => Object.values(payload.errors || {}).flat()[0] || payload.message || 'Checkout failed.';

const showCheckoutError = (form, message) => {
    const box = form.querySelector('[data-checkout-errors]');
    const success = form.querySelector('[data-checkout-success]');
    if (success) success.classList.add('hidden');
    if (!box) return;
    box.textContent = message;
    box.classList.remove('hidden');
};

const showCheckoutSuccess = (form, message) => {
    const box = form.querySelector('[data-checkout-success]');
    const errors = form.querySelector('[data-checkout-errors]');
    if (errors) errors.classList.add('hidden');
    if (!box) return;
    box.textContent = message;
    box.classList.remove('hidden');
};

const updateCheckoutSummary = payload => {
    const formatted = payload.summary?.formatted || {};
    const map = {
        '[data-checkout-subtotal]': formatted.subtotal,
        '[data-checkout-discount]': formatted.discount_amount,
        '[data-checkout-tax]': formatted.tax_amount,
        '[data-checkout-shipping]': formatted.shipping_amount,
        '[data-checkout-grand-total]': formatted.grand_total,
    };

    Object.entries(map).forEach(([selector, value]) => {
        if (value === undefined) return;
        document.querySelectorAll(selector).forEach(element => {
            element.textContent = value;
        });
    });
};

const bindCheckout = (root = document) => {
    root.querySelectorAll('[data-checkout-form]').forEach(form => {
        if (form.dataset.checkoutBound) return;
        form.dataset.checkoutBound = 'true';
        const label = form.querySelector('[data-checkout-submit-label]');
        const originalLabel = label?.textContent;
        let summaryTimer = null;

        const refreshSummary = () => {
            window.clearTimeout(summaryTimer);
            summaryTimer = window.setTimeout(async () => {
                try {
                    const query = new URLSearchParams(new FormData(form)).toString();
                    const payload = await checkoutRequest(`${form.dataset.checkoutSummaryUrl}?${query}`, { method: 'GET' });
                    updateCheckoutSummary(payload);
                } catch (payload) {
                    showCheckoutError(form, checkoutMessage(payload));
                }
            }, 250);
        };

        form.querySelectorAll('[data-checkout-summary-input]').forEach(input => {
            input.addEventListener('change', refreshSummary);
            input.addEventListener('blur', refreshSummary);
        });

        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (form.dataset.processing === 'true' || !form.reportValidity()) return;
            const data = new FormData(form);
            setCheckoutLoading(form, true);
            if (label) label.textContent = label.dataset.loadingLabel || 'Creating...';
            try {
                const payload = await checkoutRequest(form.action, { method: 'POST', body: data });
                updateCheckoutSummary(payload);
                showCheckoutSuccess(form, `${payload.message} ${payload.checkout_session?.token || ''}`.trim());
            } catch (payload) {
                showCheckoutError(form, checkoutMessage(payload));
            } finally {
                if (label) label.textContent = originalLabel;
                setCheckoutLoading(form, false);
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => bindCheckout());
