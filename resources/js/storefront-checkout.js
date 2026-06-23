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

const setPaymentLoading = (form, loading) => {
    form.dataset.processing = loading ? 'true' : 'false';
    form.querySelectorAll('button, input').forEach(control => {
        control.disabled = loading;
    });
};

const setOrderLoading = (form, loading) => {
    form.dataset.processing = loading ? 'true' : 'false';
    form.querySelectorAll('button').forEach(control => {
        control.disabled = loading;
    });
};

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

const showPaymentError = (form, message) => {
    const box = form.querySelector('[data-cod-payment-errors]');
    const success = form.querySelector('[data-cod-payment-success]');
    if (success) success.classList.add('hidden');
    if (!box) return;
    box.textContent = message;
    box.classList.remove('hidden');
};

const showPaymentSuccess = (form, message) => {
    const box = form.querySelector('[data-cod-payment-success]');
    const errors = form.querySelector('[data-cod-payment-errors]');
    if (errors) errors.classList.add('hidden');
    if (!box) return;
    box.textContent = message;
    box.classList.remove('hidden');
};

const showOrderError = (form, message) => {
    const box = form.querySelector('[data-place-order-errors]');
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
                if (payload.checkout_session?.payment_url) {
                    window.location.href = payload.checkout_session.payment_url;
                    return;
                }
                showCheckoutSuccess(form, `${payload.message} ${payload.checkout_session?.token || ''}`.trim());
            } catch (payload) {
                showCheckoutError(form, checkoutMessage(payload));
            } finally {
                if (label) label.textContent = originalLabel;
                setCheckoutLoading(form, false);
            }
        });
    });

    root.querySelectorAll('[data-cod-payment-form]').forEach(form => {
        if (form.dataset.codPaymentBound) return;
        form.dataset.codPaymentBound = 'true';
        const label = form.querySelector('[data-cod-payment-submit-label]');
        const originalLabel = label?.textContent;

        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (form.dataset.processing === 'true') return;
            setPaymentLoading(form, true);
            if (label) label.textContent = label.dataset.loadingLabel || 'Selecting...';
            try {
                const payload = await checkoutRequest(form.action, { method: 'POST', body: new FormData(form) });
                showPaymentSuccess(form, payload.message);
                if (payload.ready_to_order) {
                    document.querySelectorAll('[data-place-order-panel]').forEach(panel => panel.classList.remove('hidden'));
                }
            } catch (payload) {
                showPaymentError(form, checkoutMessage(payload));
            } finally {
                if (label) label.textContent = originalLabel;
                setPaymentLoading(form, false);
            }
        });
    });

    root.querySelectorAll('[data-place-order-form]').forEach(form => {
        if (form.dataset.placeOrderBound) return;
        form.dataset.placeOrderBound = 'true';
        const label = form.querySelector('[data-place-order-submit-label]');
        const originalLabel = label?.textContent;

        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (form.dataset.processing === 'true') return;
            setOrderLoading(form, true);
            if (label) label.textContent = label.dataset.loadingLabel || 'Placing...';
            try {
                const payload = await checkoutRequest(form.action, { method: 'POST', body: new FormData(form) });
                if (payload.redirect_url) {
                    window.location.href = payload.redirect_url;
                }
            } catch (payload) {
                showOrderError(form, checkoutMessage(payload));
            } finally {
                if (label) label.textContent = originalLabel;
                setOrderLoading(form, false);
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => bindCheckout());
