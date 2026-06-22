window.productTabs = (initialTab = 'general', errorTabs = []) => ({
    activeTab: initialTab,
    errors: Object.fromEntries(errorTabs.map(tab => [tab, true])),
    dirty: {},
    select(tab, disabled = false) {
        if (!disabled) this.activeTab = tab;
    },
    markDirty(event) {
        const panel = event.target.closest('[data-product-tab]');
        if (panel) this.dirty[panel.dataset.productTab] = true;
    },
    markSaved(tab) {
        if (tab) {
            this.dirty[tab] = false;
            this.errors[tab] = false;
        }
    },
    markError(tab) {
        if (tab) this.errors[tab] = true;
    },
});
