const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

const toast = (message, type = 'success') => {
    let element = document.getElementById('storefront-cart-toast');
    if (!element) {
        element = document.createElement('div');
        element.id = 'storefront-cart-toast';
        element.className = 'cart-toast';
        document.body.append(element);
    }

    const isError = type === 'error';
    element.replaceChildren();
    const shell = document.createElement('div');
    shell.className = `cart-toast-card ${isError ? 'is-error' : 'is-success'}`;
    const body = document.createElement('div');
    body.className = 'cart-toast-body';
    const icon = document.createElement('span');
    icon.className = `cart-toast-icon ${isError ? 'is-error' : 'is-success'}`;
    icon.innerHTML = isError
        ? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>'
        : '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>';
    const content = document.createElement('div');
    content.className = 'min-w-0 flex-1';
    const text = document.createElement('p');
    text.className = 'cart-toast-message';
    text.textContent = message;
    content.append(text);
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'cart-toast-close';
    closeButton.setAttribute('aria-label', 'Close');
    closeButton.textContent = '×';
    closeButton.addEventListener('click', () => hideToast(element));
    const progressTrack = document.createElement('div');
    progressTrack.className = `cart-toast-progress-track ${isError ? 'is-error' : 'is-success'}`;
    const progress = document.createElement('div');
    progress.className = `cart-toast-progress ${isError ? 'is-error' : 'is-success'}`;
    progress.style.width = '100%';
    progress.style.transitionDuration = '3000ms';
    progressTrack.append(progress);
    body.append(icon, content, closeButton);
    shell.append(body, progressTrack);
    element.append(shell);
    requestAnimationFrame(() => {
        progress.style.width = '0%';
    });

    element.classList.remove('hidden');
    element.classList.add('is-visible');
    window.clearTimeout(element.hideTimer);
    element.hideTimer = window.setTimeout(() => hideToast(element), 3000);
};

const hideToast = element => {
    element.classList.remove('is-visible');
    window.setTimeout(() => element.classList.add('hidden'), 260);
};

const updateHeaderCount = count => {
    document.querySelectorAll('[data-cart-count]').forEach(element => {
        element.textContent = count;
        element.classList.toggle('hidden', Number(count) < 1);
    });
};

const updateSummary = payload => {
    const summary = payload.summary || {};
    if (payload.cart_subtotal) {
        document.querySelectorAll('[data-cart-subtotal]').forEach(element => element.textContent = payload.cart_subtotal);
    }
    if (summary.formatted_estimated_total) {
        document.querySelectorAll('[data-cart-estimated-total]').forEach(element => element.textContent = summary.formatted_estimated_total);
    }
    if (summary.formatted_discount_amount) {
        document.querySelectorAll('[data-cart-discount]').forEach(element => element.textContent = summary.formatted_discount_amount);
    }
    document.querySelectorAll('[data-cart-discount-row]').forEach(element => {
        element.classList.toggle('hidden', Number(summary.discount_amount || 0) <= 0);
    });
    document.querySelectorAll('[data-applied-coupon]').forEach(element => {
        const applied = summary.applied_coupon;
        element.classList.toggle('hidden', !applied);
        if (applied) {
            element.querySelector('[data-applied-coupon-code]').textContent = applied.code;
        }
    });
    if (payload.cart_count !== undefined) {
        updateHeaderCount(payload.cart_count);
        document.querySelectorAll('[data-cart-total-items]').forEach(element => element.textContent = payload.cart_count);
    }
    const checkoutDisabled = Boolean(summary.is_empty || summary.has_unavailable);
    document.querySelectorAll('[data-cart-checkout-disabled]').forEach(element => element.classList.toggle('hidden', !checkoutDisabled));
    document.querySelectorAll('[data-cart-checkout-link]').forEach(element => element.classList.toggle('hidden', checkoutDisabled));
    if (payload.summary?.is_empty || payload.is_empty) {
        document.querySelector('[data-cart-items]')?.classList.add('hidden');
        document.querySelector('[data-cart-summary-box]')?.classList.add('hidden');
        document.querySelector('[data-cart-empty]')?.classList.remove('hidden');
    }
};

const request = async (url, options = {}) => {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
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

const setLoading = (element, loading) => {
    element.dataset.processing = loading ? 'true' : 'false';
    element.querySelectorAll('button, input').forEach(control => {
        if (control.matches('[data-keep-enabled]')) return;
        control.disabled = loading;
    });
};

const showErrors = (root, payload) => {
    const message = Object.values(payload.errors || {}).flat()[0] || payload.message || document.documentElement.dataset.cartError || 'Cart action failed.';
    const box = root.querySelector('[data-cart-errors]');
    if (box) {
        box.textContent = message;
        box.classList.remove('hidden');
        return;
    }

    toast(message, 'error');
};

const clearErrors = root => {
    const box = root.querySelector('[data-cart-errors]');
    if (box) {
        box.textContent = '';
        box.classList.add('hidden');
    }
};

const bindAddForms = (root = document) => root.querySelectorAll('[data-cart-add]').forEach(form => {
    if (form.dataset.cartBound) return;
    form.dataset.cartBound = 'true';
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (form.dataset.processing === 'true' || !form.reportValidity()) return;
        clearErrors(form);
        const data = new FormData(form);
        const submitter = event.submitter;
        const label = submitter?.querySelector('[data-cart-button-label]');
        const original = label?.textContent;
        setLoading(form, true);
        if (label) label.textContent = submitter.dataset.loadingLabel || original;
        try {
            const payload = await request(form.action, { method: 'POST', body: data });
            updateSummary(payload);
            toast(payload.message);
        } catch (payload) {
            showErrors(form, payload);
        } finally {
            if (label) label.textContent = original;
            setLoading(form, false);
        }
    });
});

const bindCartRows = (root = document) => {
    root.querySelectorAll('[data-cart-update]').forEach(form => {
        if (form.dataset.cartBound) return;
        form.dataset.cartBound = 'true';
        const input = form.querySelector('[data-cart-quantity]');
        form.querySelector('[data-cart-decrease]')?.addEventListener('click', () => {
            input.value = Math.max(1, Number(input.value || 1) - 1);
            form.requestSubmit();
        });
        form.querySelector('[data-cart-increase]')?.addEventListener('click', () => {
            input.value = Number(input.value || 1) + 1;
            form.requestSubmit();
        });
        input?.addEventListener('change', () => form.requestSubmit());
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (form.dataset.processing === 'true' || !form.reportValidity()) return;
            clearErrors(form.closest('[data-cart-row]') || form);
            const data = new FormData(form);
            setLoading(form, true);
            try {
                const payload = await request(form.action, { method: 'POST', body: data });
                const row = form.closest('[data-cart-row]');
                if (row && payload.cart_row_html) {
                    row.outerHTML = payload.cart_row_html;
                    const newRow = document.querySelector(`[data-cart-row="${payload.item.id}"]`);
                    if (newRow) bindCartRows(newRow);
                }
                updateSummary(payload);
                toast(payload.message);
            } catch (payload) {
                showErrors(form.closest('[data-cart-row]') || form, payload);
            } finally {
                setLoading(form, false);
            }
        });
    });

    root.querySelectorAll('[data-cart-remove]').forEach(button => {
        if (button.dataset.cartBound) return;
        button.dataset.cartBound = 'true';
        button.addEventListener('click', async () => {
            if (button.dataset.processing === 'true') return;
            button.dataset.processing = 'true';
            button.disabled = true;
            const row = button.closest('[data-cart-row]');
            clearErrors(row || document);
            try {
                const payload = await request(button.dataset.cartRemove, { method: 'DELETE' });
                row?.classList.add('opacity-0', '-translate-y-2');
                window.setTimeout(() => row?.remove(), 180);
                updateSummary(payload);
                toast(payload.message);
            } catch (payload) {
                showErrors(row || document, payload);
            } finally {
                button.dataset.processing = 'false';
                button.disabled = false;
            }
        });
    });
};

const bindClearCart = (root = document) => root.querySelectorAll('[data-cart-clear]').forEach(button => {
    if (button.dataset.cartBound) return;
    button.dataset.cartBound = 'true';
    button.addEventListener('click', async () => {
        if (button.dataset.processing === 'true') return;
        if (!await confirmClear(button.dataset.confirmMessage || 'Clear cart?')) return;
        button.dataset.processing = 'true';
        button.disabled = true;
        try {
            const payload = await request(button.dataset.cartClear, { method: 'DELETE' });
            document.querySelectorAll('[data-cart-row]').forEach(row => row.remove());
            updateSummary({ ...payload, is_empty: true });
            toast(payload.message);
        } catch (payload) {
            toast(payload.message || document.documentElement.dataset.cartError || 'Cart action failed.', 'error');
        } finally {
            button.dataset.processing = 'false';
            button.disabled = false;
        }
    });
});

const bindCouponForms = (root = document) => {
    root.querySelectorAll('[data-coupon-apply]').forEach(form => {
        if (form.dataset.cartBound) return;
        form.dataset.cartBound = 'true';
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (form.dataset.processing === 'true' || !form.reportValidity()) return;
            clearErrors(form.closest('[data-cart-summary-box]') || form);
            const data = new FormData(form);
            setLoading(form, true);
            try {
                const payload = await request(form.action, { method: 'POST', body: data });
                form.reset();
                updateSummary(payload);
                toast(payload.message);
            } catch (payload) {
                showErrors(form.closest('[data-cart-summary-box]') || form, payload);
            } finally {
                setLoading(form, false);
            }
        });
    });

    root.querySelectorAll('[data-coupon-remove]').forEach(button => {
        if (button.dataset.cartBound) return;
        button.dataset.cartBound = 'true';
        button.addEventListener('click', async () => {
            if (button.dataset.processing === 'true') return;
            button.dataset.processing = 'true';
            button.disabled = true;
            clearErrors(button.closest('[data-cart-summary-box]') || document);
            try {
                const payload = await request(button.dataset.couponRemove, { method: 'DELETE' });
                updateSummary(payload);
                toast(payload.message);
            } catch (payload) {
                showErrors(button.closest('[data-cart-summary-box]') || document, payload);
            } finally {
                button.dataset.processing = 'false';
                button.disabled = false;
            }
        });
    });
};

const confirmClear = message => new Promise(resolve => {
    let modal = document.getElementById('cart-clear-confirm-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'cart-clear-confirm-modal';
        modal.className = 'pointer-events-none fixed inset-0 z-[95] grid place-items-center bg-slate-950/60 p-4 opacity-0 backdrop-blur-sm transition duration-200';
        modal.innerHTML = `
            <div data-confirm-panel class="w-full max-w-md translate-y-3 scale-95 rounded-3xl bg-white p-6 opacity-0 shadow-2xl transition duration-200">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-rose-100 text-rose-700">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
                </div>
                <h2 class="mt-4 text-xl font-extrabold text-slate-950">${document.documentElement.dataset.clearCartTitle || 'Clear cart'}</h2>
                <p data-confirm-message class="mt-2 text-sm font-semibold leading-6 text-slate-600"></p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-confirm-cancel class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-extrabold text-slate-700 hover:bg-slate-50">${document.documentElement.dataset.cancelLabel || 'Cancel'}</button>
                    <button type="button" data-confirm-ok class="rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-rose-700">${document.documentElement.dataset.clearCartLabel || 'Clear cart'}</button>
                </div>
            </div>`;
        document.body.append(modal);
    }
    modal.querySelector('[data-confirm-message]').textContent = message;
    const panel = modal.querySelector('[data-confirm-panel]');
    const close = value => {
        modal.classList.add('pointer-events-none', 'opacity-0');
        modal.classList.remove('pointer-events-auto', 'opacity-100');
        panel.classList.add('translate-y-3', 'scale-95', 'opacity-0');
        panel.classList.remove('translate-y-0', 'scale-100', 'opacity-100');
        resolve(value);
    };
    modal.querySelector('[data-confirm-cancel]').onclick = () => close(false);
    modal.querySelector('[data-confirm-ok]').onclick = () => close(true);
    modal.classList.remove('pointer-events-none', 'opacity-0');
    modal.classList.add('pointer-events-auto', 'opacity-100');
    panel.classList.remove('translate-y-3', 'scale-95', 'opacity-0');
    panel.classList.add('translate-y-0', 'scale-100', 'opacity-100');
    modal.querySelector('[data-confirm-cancel]').focus();
});

document.addEventListener('DOMContentLoaded', () => {
    bindAddForms();
    bindCartRows();
    bindClearCart();
    bindCouponForms();
});
