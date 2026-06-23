const overlay = () => document.getElementById('admin-loading-overlay');
let activeRequests = 0;
let overlayShowTimer = null;
let overlayHideTimer = null;
let overlayShownAt = 0;
const OVERLAY_DELAY = 140;
const MINIMUM_VISIBLE_TIME = 240;

const showOverlay = () => {
    activeRequests += 1;
    window.clearTimeout(overlayHideTimer);
    if (overlayShownAt || overlayShowTimer) return;

    overlayShowTimer = window.setTimeout(() => {
        overlayShowTimer = null;
        if (activeRequests === 0 || !overlay()) return;
        overlayShownAt = Date.now();
        overlay().classList.add('opacity-100', 'pointer-events-auto');
        overlay().classList.remove('opacity-0', 'pointer-events-none');
        document.body.classList.add('overflow-hidden');
    }, OVERLAY_DELAY);
};

const hideOverlay = () => {
    activeRequests = Math.max(0, activeRequests - 1);
    if (activeRequests > 0) return;
    window.clearTimeout(overlayShowTimer);
    overlayShowTimer = null;
    if (!overlayShownAt || !overlay()) return;

    const remaining = Math.max(0, MINIMUM_VISIBLE_TIME - (Date.now() - overlayShownAt));
    overlayHideTimer = window.setTimeout(() => {
        if (activeRequests > 0 || !overlay()) return;
        overlay().classList.remove('opacity-100', 'pointer-events-auto');
        overlay().classList.add('opacity-0', 'pointer-events-none');
        window.setTimeout(() => {
            if (activeRequests === 0) document.body.classList.remove('overflow-hidden');
        }, 200);
        overlayShownAt = 0;
    }, remaining);
};

window.adminLoading = { show: showOverlay, hide: hideOverlay };

const showErrors = (form, payload) => {
    const box = form.querySelector('[data-async-errors]');
    if (!box) return;
    const errors = Object.values(payload.errors || {}).flat();
    box.textContent = errors[0] || payload.message || document.documentElement.dataset.asyncError || 'Request failed.';
    box.classList.remove('hidden');
    const tab = form.closest('[data-product-tab]')?.dataset.productTab;
    if (tab) window.dispatchEvent(new CustomEvent('admin:tab-error', { detail: { tab } }));
};

const clearErrors = (form) => {
    const box = form.querySelector('[data-async-errors]');
    if (box) {
        box.textContent = '';
        box.classList.add('hidden');
    }
};

const notify = (message, type = 'success') => {
    let toast = document.getElementById('admin-async-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'admin-async-toast';
        toast.className = 'fixed right-5 top-24 z-[90] rounded-xl px-5 py-3 text-sm font-bold text-white opacity-0 shadow-xl transition duration-200 ease-out';
        document.body.append(toast);
    }
    toast.textContent = message;
    toast.classList.remove('hidden', 'bg-emerald-600', 'bg-rose-600', 'opacity-0', 'translate-y-2');
    toast.classList.add(type === 'error' ? 'bg-rose-600' : 'bg-emerald-600', 'opacity-100', 'translate-y-0');
    window.clearTimeout(toast.hideTimer);
    toast.hideTimer = window.setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-2');
        window.setTimeout(() => toast.classList.add('hidden'), 200);
    }, 3000);
};

window.adminToast = notify;

const updateOptionSelectors = (form, payload) => {
    if (!form.dataset.optionId || !payload.value?.status) return;
    document.querySelectorAll(`[data-option-select="${form.dataset.optionId}"]`).forEach(select => {
        if ([...select.options].some(option => Number(option.value) === Number(payload.value.id))) return;
        select.add(new Option(payload.value.display_value || payload.value.value, payload.value.id));
    });
};

const addVariantSelector = payload => {
    if (!payload.variant_selector_html || !payload.option?.id) return;
    const target = document.getElementById('variant-option-selectors');
    if (!target || target.querySelector(`[data-variant-selector="${payload.option.id}"]`)) return;
    target.insertAdjacentHTML('beforeend', payload.variant_selector_html);
    document.querySelectorAll('[data-create-variant-trigger]').forEach(button => button.disabled = false);
    document.getElementById('variant-setup-hint')?.classList.add('hidden');
};

const syncVariantManagement = payload => {
    if (!payload || payload.variant_selectors_html === undefined) return;

    const selectorTarget = document.getElementById('variant-option-selectors');
    if (selectorTarget) {
        selectorTarget.innerHTML = payload.variant_selectors_html || '';
        document.dispatchEvent(new CustomEvent('admin:content-updated', { detail: { root: selectorTarget } }));
    }

    const listTarget = document.getElementById('variant-list');
    if (listTarget && payload.variant_list_html !== undefined) {
        listTarget.innerHTML = payload.variant_list_html || '';
        document.dispatchEvent(new CustomEvent('admin:content-updated', { detail: { root: listTarget } }));
    }

    const canCreate = Boolean(payload.can_create_variants);
    document.querySelectorAll('[data-create-variant-trigger]').forEach(button => button.disabled = !canCreate);
    document.getElementById('variant-setup-hint')?.classList.toggle('hidden', canCreate);
};

window.syncVariantManagement = syncVariantManagement;

const bindForm = (form) => {
    if (form.dataset.asyncBound) return;
    form.dataset.asyncBound = 'true';
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (form.dataset.processing === 'true' || !form.reportValidity()) return;

        form.dataset.processing = 'true';
        clearErrors(form);
        const submitter = event.submitter;
        if (submitter) submitter.disabled = true;
        showOverlay();

        try {
            const response = await fetch(form.action, {
                method: 'POST', body: new FormData(form), credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                showErrors(form, payload);
                return;
            }

            form.closest('[data-modal-panel]')?.querySelector('[data-modal-close]')?.click();
            const target = document.querySelector(form.dataset.appendTarget);
            target?.querySelector('[data-empty-state]')?.remove();
            if (target && payload.main_image_id) target.querySelectorAll('[data-main-badge]').forEach(badge => badge.remove());
            if (target && payload.html) {
                target.insertAdjacentHTML('beforeend', payload.html);
                document.dispatchEvent(new CustomEvent('admin:content-updated', { detail: { root: target } }));
            }
            const replaceTarget = document.querySelector(form.dataset.replaceTarget);
            if (replaceTarget && payload.html) {
                replaceTarget.outerHTML = payload.html;
                const newTarget = document.querySelector(form.dataset.replaceTarget);
                if (newTarget) document.dispatchEvent(new CustomEvent('admin:content-updated', { detail: { root: newTarget } }));
            }
            const logTarget = document.querySelector(form.dataset.logTarget);
            if (logTarget && payload.log_html) logTarget.insertAdjacentHTML('afterbegin', payload.log_html);
            updateOptionSelectors(form, payload);
            addVariantSelector(payload);
            syncVariantManagement(payload);
            if (form.dataset.optionId && payload.value) document.querySelectorAll(`[data-option-value-count="${form.dataset.optionId}"]`).forEach(element => element.textContent = String(Number(element.textContent || 0) + 1));
            if (payload.variant_id && payload.image_count !== undefined) document.querySelectorAll(`[data-variant-image-count="${payload.variant_id}"]`).forEach(element => element.textContent = payload.image_count);
            if (!payload.variant_id && payload.image_count !== undefined) document.querySelectorAll('[data-product-image-count]').forEach(element => element.textContent = payload.image_count);
            if (payload.variant_image_panel_html && payload.variant) {
                const select = document.querySelector('[data-variant-image-select]');
                if (select && ![...select.options].some(option => Number(option.value) === Number(payload.variant.id))) select.add(new Option(`${payload.variant.name} — ${payload.variant.sku}`, payload.variant.id));
                const panels = document.getElementById('variant-image-panels');
                panels?.insertAdjacentHTML('beforeend', payload.variant_image_panel_html);
                document.querySelector('[data-variant-images-empty]')?.classList.add('hidden');
                document.querySelector('[data-variant-images-manager]')?.classList.remove('hidden');
                if (select) { select.value = String(payload.variant.id); select.dispatchEvent(new Event('change', { bubbles: true })); }
                if (panels) document.dispatchEvent(new CustomEvent('admin:content-updated', { detail: { root: panels } }));
            }
            if (payload.inventory_html) {
                const inventoryList = document.getElementById('inventory-stock-list');
                if (payload.product_stock_id) document.getElementById(`inventory-row-${payload.product_stock_id}`)?.remove();
                inventoryList?.insertAdjacentHTML('beforeend', payload.inventory_html);
                if (inventoryList) document.dispatchEvent(new CustomEvent('admin:content-updated', { detail: { root: inventoryList } }));
            }
            form.reset();
            const tab = form.closest('[data-product-tab]')?.dataset.productTab;
            if (tab) window.dispatchEvent(new CustomEvent('admin:tab-saved', { detail: { tab } }));
            notify(payload.message || 'Saved.');
        } catch (error) {
            showErrors(form, { message: document.documentElement.dataset.asyncError || 'Connection error.' });
        } finally {
            form.dataset.processing = 'false';
            if (submitter) submitter.disabled = false;
            hideOverlay();
        }
    });
};

const bindAsyncForms = (root = document) => root.querySelectorAll('form[data-async-create]').forEach(bindForm);

document.addEventListener('DOMContentLoaded', () => bindAsyncForms());
document.addEventListener('admin:content-updated', event => bindAsyncForms(event.detail.root));

const fadeAndRemove = (element) => {
    if (!element) return;
    element.classList.add('transition', 'duration-200', 'ease-out', 'opacity-0', '-translate-y-2');
    window.setTimeout(() => element.remove(), 220);
};

const updateDeleteRelatedUi = (payload, button) => {
    if (payload.option_value_id || button.dataset.optionValueId) {
        const optionValueId = payload.option_value_id || button.dataset.optionValueId;
        const optionId = payload.option_id || button.dataset.optionId;
        document.querySelectorAll(`[data-option-select="${optionId}"] option[value="${optionValueId}"]`).forEach(element => element.remove());
        document.querySelectorAll(`[data-option-value-count="${optionId}"]`).forEach(element => element.textContent = String(Math.max(0, Number(element.textContent || 0) - 1)));
    }

    if (payload.option_id || button.dataset.optionId) {
        const optionId = payload.option_id || button.dataset.optionId;
        if (!payload.option_value_id && button.dataset.deleteType !== 'option-value') {
            document.querySelectorAll(`[data-variant-selector="${optionId}"]`).forEach(element => element.remove());
            const hasSelectors = Boolean(document.querySelector('#variant-option-selectors [data-variant-selector]'));
            document.querySelectorAll('[data-create-variant-trigger]').forEach(trigger => trigger.disabled = !hasSelectors);
            document.getElementById('variant-setup-hint')?.classList.toggle('hidden', hasSelectors);
        }
    }

    if (payload.variant_id || button.dataset.variantId) {
        const variantId = payload.variant_id || button.dataset.variantId;
        document.querySelector(`[data-variant-image-select] option[value="${variantId}"]`)?.remove();
        document.querySelector(`[data-variant-image-panel="${variantId}"]`)?.remove();
        if (payload.inventory_stock_id) document.getElementById(`inventory-row-${payload.inventory_stock_id}`)?.remove();

        const select = document.querySelector('[data-variant-image-select]');
        if (select && select.options.length > 0) {
            select.value = select.options[0].value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
            document.querySelector('[data-variant-images-manager]')?.classList.add('hidden');
            document.querySelector('[data-variant-images-empty]')?.classList.remove('hidden');
        }
    }
};

const deleteModal = () => document.querySelector('[data-admin-delete-modal]');

const bindDeleteModal = () => {
    const modal = deleteModal();
    if (!modal || modal.dataset.bound) return;
    modal.dataset.bound = 'true';
    const panel = modal.querySelector('[data-delete-panel]');
    const errorBox = modal.querySelector('[data-delete-errors]');
    const confirmButton = modal.querySelector('[data-delete-confirm]');
    const spinner = modal.querySelector('[data-delete-spinner]');
    const label = modal.querySelector('[data-delete-button-label]');
    let activeButton = null;

    const close = () => {
        if (modal.dataset.processing === 'true') return;
        modal.classList.add('pointer-events-none', 'opacity-0');
        modal.classList.remove('pointer-events-auto', 'opacity-100');
        panel?.classList.add('translate-y-3', 'scale-95', 'opacity-0');
        panel?.classList.remove('translate-y-0', 'scale-100', 'opacity-100');
        modal.setAttribute('aria-hidden', 'true');
        activeButton = null;
    };

    const setError = (message) => {
        if (!errorBox) return;
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    const setProcessing = (processing) => {
        modal.dataset.processing = processing ? 'true' : 'false';
        if (confirmButton) confirmButton.disabled = processing;
        spinner?.classList.toggle('hidden', !processing);
        if (label) label.textContent = processing ? document.documentElement.dataset.processingLabel || 'Processing…' : document.documentElement.dataset.deleteLabel || 'Delete';
    };

    modal.querySelector('[data-delete-cancel]')?.addEventListener('click', close);
    modal.querySelector('[data-delete-backdrop]')?.addEventListener('click', close);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') close();
    });

    document.addEventListener('admin:confirm-delete', event => {
        activeButton = event.detail.button;
        errorBox?.classList.add('hidden');
        modal.querySelector('[data-delete-title]').textContent = activeButton.dataset.deleteTitle || document.documentElement.dataset.deleteLabel || 'Delete';
        modal.querySelector('[data-delete-message]').textContent = activeButton.dataset.deleteMessage || '';
        modal.querySelector('[data-delete-warning]').textContent = activeButton.dataset.deleteWarning || '';
        setProcessing(false);
        modal.classList.remove('pointer-events-none', 'opacity-0');
        modal.classList.add('pointer-events-auto', 'opacity-100');
        panel?.classList.remove('translate-y-3', 'scale-95', 'opacity-0');
        panel?.classList.add('translate-y-0', 'scale-100', 'opacity-100');
        modal.setAttribute('aria-hidden', 'false');
        confirmButton?.focus();
    });

    confirmButton?.addEventListener('click', async () => {
        if (!activeButton || modal.dataset.processing === 'true') return;
        setProcessing(true);
        showOverlay();

        try {
            const response = await fetch(activeButton.dataset.deleteUrl, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = Object.values(payload.errors || {}).flat()[0] || payload.message || document.documentElement.dataset.asyncError || 'Delete failed.';
                setError(message);
                notify(message, 'error');
                return;
            }

            const target = document.querySelector(activeButton.dataset.deleteTarget);
            updateDeleteRelatedUi(payload, activeButton);
            syncVariantManagement(payload);
            fadeAndRemove(target);
            notify(payload.message || document.documentElement.dataset.deletedLabel || 'Deleted.');
            setProcessing(false);
            close();
        } catch (error) {
            const message = document.documentElement.dataset.asyncError || 'Connection error.';
            setError(message);
            notify(message, 'error');
        } finally {
            setProcessing(false);
            hideOverlay();
        }
    });
};

const bindDeleteButtons = (root = document) => root.querySelectorAll('[data-async-delete]').forEach(button => {
    if (button.dataset.deleteBound) return;
    button.dataset.deleteBound = 'true';
    button.addEventListener('click', () => {
        button.closest('details')?.removeAttribute('open');
        document.dispatchEvent(new CustomEvent('admin:confirm-delete', { detail: { button } }));
    });
});

document.addEventListener('DOMContentLoaded', () => {
    bindDeleteModal();
    bindDeleteButtons();
});
document.addEventListener('admin:content-updated', event => bindDeleteButtons(event.detail.root));
