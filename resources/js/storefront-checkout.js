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

const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));

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

const addressFormData = form => {
    const formData = new FormData();
    ['shipping[country_code]', 'shipping[province]', 'shipping[district]'].forEach(name => {
        const field = form.querySelector(`[name="${name}"]`);
        if (field) formData.append(name, field.value);
    });

    return formData;
};

const setShippingLoading = (form, loading) => {
    form.querySelectorAll('[data-shipping-loading]').forEach(element => element.classList.toggle('hidden', !loading));
};

const showShippingError = (form, message) => {
    const box = form.querySelector('[data-shipping-errors]');
    if (!box) return;
    box.textContent = message;
    box.classList.remove('hidden');
};

const hideShippingError = form => {
    form.querySelectorAll('[data-shipping-errors]').forEach(element => element.classList.add('hidden'));
};

const updateCheckoutAvailability = (form, payload) => {
    const submit = form.querySelector('[data-checkout-submit]');
    const selected = payload.selected_shipping_method || payload.summary?.selected_shipping_method;
    const hasMethods = payload.has_available_shipping_methods ?? payload.summary?.has_available_shipping_methods;
    if (submit) {
        submit.disabled = !selected || !hasMethods;
    }
};

const renderShippingMethods = (form, payload) => {
    const container = form.querySelector('[data-shipping-methods]');
    const hidden = form.querySelector('[data-selected-shipping-method]');
    if (!container) return;

    const methods = payload.available_shipping_methods || payload.summary?.available_shipping_methods || [];
    const selected = payload.selected_shipping_method || payload.summary?.selected_shipping_method || null;
    if (hidden) hidden.value = selected?.id || '';

    if (!methods.length) {
        container.innerHTML = `<p class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">${container.dataset.emptyMessage || 'No shipping methods are available for this address.'}</p>`;
        showShippingError(form, container.dataset.emptyMessage || 'No shipping methods are available for this address.');
        updateCheckoutAvailability(form, payload);
        return;
    }

    hideShippingError(form);
    container.innerHTML = methods.map(method => {
        const checked = selected?.id === method.id;
        const labelClass = checked ? 'border-indigo-300 bg-indigo-50/50' : 'border-slate-200 bg-slate-50 hover:border-indigo-200';
        const description = method.description ? `<span class="mt-1 block text-sm font-semibold leading-6 text-slate-600">${escapeHtml(method.description)}</span>` : '';
        const estimate = method.estimated_delivery ? `<span class="mt-2 block text-xs font-bold text-slate-500">${escapeHtml(method.estimated_delivery)}</span>` : '';

        return `
            <label class="block cursor-pointer rounded-2xl border p-5 transition ${labelClass}">
                <span class="flex items-start gap-4">
                    <input type="radio" name="_shipping_method_radio" value="${method.id}" ${checked ? 'checked' : ''} data-shipping-method-option class="mt-1 h-5 w-5 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-base font-extrabold text-slate-950">${escapeHtml(method.name)}</span>
                            <span class="font-extrabold text-slate-950">${escapeHtml(method.formatted_shipping_amount)}</span>
                        </span>
                        ${description}
                        ${estimate}
                    </span>
                </span>
            </label>
        `;
    }).join('');
    updateCheckoutAvailability(form, payload);
};

const refreshShippingMethods = async form => {
    if (!form.dataset.shippingMethodsUrl) return null;
    setShippingLoading(form, true);
    try {
        const query = new URLSearchParams(addressFormData(form)).toString();
        const payload = await checkoutRequest(`${form.dataset.shippingMethodsUrl}?${query}`, { method: 'GET' });
        renderShippingMethods(form, payload);
        updateCheckoutSummary({ summary: payload.summary || payload });
        return payload;
    } catch (payload) {
        showShippingError(form, checkoutMessage(payload));
        updateCheckoutAvailability(form, { selected_shipping_method: null, has_available_shipping_methods: false });
        return null;
    } finally {
        setShippingLoading(form, false);
    }
};

const selectShippingMethod = async (form, methodId) => {
    if (!form.dataset.shippingSelectUrl || !methodId) return;
    const data = addressFormData(form);
    data.append('shipping_method_id', methodId);
    setShippingLoading(form, true);
    try {
        const payload = await checkoutRequest(form.dataset.shippingSelectUrl, { method: 'POST', body: data });
        renderShippingMethods(form, payload);
        updateCheckoutSummary(payload);
    } catch (payload) {
        showShippingError(form, checkoutMessage(payload));
        await refreshShippingMethods(form);
    } finally {
        setShippingLoading(form, false);
    }
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
                    renderShippingMethods(form, payload.summary || payload);
                } catch (payload) {
                    showCheckoutError(form, checkoutMessage(payload));
                }
            }, 250);
        };

        form.querySelectorAll('[data-checkout-summary-input]').forEach(input => {
            input.addEventListener('change', () => {
                const hidden = form.querySelector('[data-selected-shipping-method]');
                if (hidden) hidden.value = '';
                refreshSummary();
                refreshShippingMethods(form);
            });
            input.addEventListener('blur', refreshSummary);
        });

        form.addEventListener('change', event => {
            if (!event.target.matches('[data-shipping-method-option]')) return;
            selectShippingMethod(form, event.target.value);
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
                updateCheckoutAvailability(form, {
                    selected_shipping_method: form.querySelector('[data-selected-shipping-method]')?.value ? { id: form.querySelector('[data-selected-shipping-method]').value } : null,
                    has_available_shipping_methods: true,
                });
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
                    document.querySelectorAll('[data-place-order-form]').forEach(orderForm => {
                        if (payload.order_url) orderForm.action = payload.order_url;
                        const orderLabel = orderForm.querySelector('[data-place-order-submit-label]');
                        if (orderLabel && payload.order_label) {
                            orderLabel.textContent = payload.order_label;
                            orderLabel.dataset.defaultLabel = payload.order_label;
                        }
                        if (orderLabel && payload.order_loading_label) orderLabel.dataset.loadingLabel = payload.order_loading_label;
                    });
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
                if (label) label.textContent = label.dataset.defaultLabel || label.textContent;
                setOrderLoading(form, false);
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', () => bindCheckout());
