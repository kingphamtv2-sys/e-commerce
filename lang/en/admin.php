<?php

return [
    'common' => [
        'admin_workspace' => 'Admin workspace', 'management' => 'Management', 'system_status' => 'System status',
        'operational' => 'All services operational', 'administration_portal' => 'Administration portal', 'logout' => 'Logout',
        'home' => 'Home', 'active' => 'Active', 'inactive' => 'Inactive', 'default' => 'Default', 'actions' => 'Actions',
        'edit' => 'Edit', 'delete' => 'Delete', 'back' => 'Back', 'save' => 'Save', 'set_default' => 'Set Default', 'placeholder' => 'Placeholder',
    ],
    'menu' => [
        'dashboard' => 'Dashboard', 'settings' => 'System Settings', 'languages' => 'Languages', 'currencies' => 'Currencies',
        'tax_classes' => 'Tax Classes', 'tax_rates' => 'Tax Rates', 'categories' => 'Categories', 'products' => 'Products',
        'inventory' => 'Inventory', 'orders' => 'Orders', 'customers' => 'Customers', 'coupons' => 'Coupons',
        'banners' => 'Banners', 'reports' => 'Reports',
    ],
    'dashboard' => [
        'title' => 'Dashboard', 'total_orders' => 'Total Orders', 'total_revenue' => 'Total Revenue',
        'total_products' => 'Total Products', 'low_stock' => 'Low Stock Products', 'revenue_overview' => 'Revenue overview',
        'analytics_later' => 'Sales analytics will be available in a later task.', 'welcome_back' => 'Welcome back',
        'workspace_ready' => 'Your admin workspace is ready. Module data will appear here as each feature is implemented.',
        'current_role' => 'Current role', 'account_status' => 'Account status',
    ],
    'settings' => [
        'title' => 'System Settings', 'general' => 'General Settings',
        'general_desc' => 'Core identity and public contact information for your store.', 'localization' => 'Localization Settings',
        'localization_desc' => 'Choose the store defaults and enabled localization modes.', 'tax' => 'Tax Settings',
        'tax_desc' => 'Control whether tax is calculated and how product prices are interpreted.', 'order' => 'Order Settings',
        'order_desc' => 'Set baseline shipping thresholds and the order number prefix.', 'site_name' => 'Site Name',
        'site_email' => 'Site Email', 'site_phone' => 'Site Phone', 'site_address' => 'Site Address', 'logo_path' => 'Logo Path',
        'favicon_path' => 'Favicon Path', 'default_language' => 'Default Language', 'default_currency' => 'Default Currency',
        'multi_language' => 'Enable Multi Language', 'multi_currency' => 'Enable Multi Currency', 'enable_tax' => 'Enable Tax',
        'prices_include_tax' => 'Prices Include Tax', 'shipping_fee' => 'Default Shipping Fee',
        'free_shipping_min' => 'Free Shipping Minimum', 'order_prefix' => 'Order Code Prefix', 'save' => 'Save Settings',
        'validation_title' => 'Please review the highlighted settings.',
    ],
    'languages' => [
        'title' => 'Languages', 'add_title' => 'Add Language', 'edit_title' => 'Edit Language', 'add' => 'Add Language',
        'supported' => 'Supported Languages', 'supported_desc' => 'Manage availability, display order and the default language.',
        'count' => ':count languages', 'language' => 'Language', 'code' => 'Code', 'order' => 'Order', 'details' => 'Language Details',
        'details_desc' => 'Codes are normalized to lowercase and must be unique.', 'language_code' => 'Language Code',
        'sort_order' => 'Sort Order', 'english_name' => 'English Name', 'native_name' => 'Native Name',
        'active_desc' => 'Active languages can be used by future public language features.', 'default_language' => 'Default Language',
        'default_desc' => 'A default language must remain active. Use Set Default from the list to switch safely.',
        'create' => 'Create Language', 'save_changes' => 'Save Changes', 'empty' => 'No languages configured.',
        'review' => 'Please review the language details.', 'delete_confirm' => 'Delete this language?',
    ],
    'messages' => [
        'settings_updated' => 'Settings updated successfully.',
        'language_created' => 'Language created successfully.', 'language_updated' => 'Language updated successfully.',
        'language_deleted' => 'Language deleted successfully.', 'default_updated' => 'Default language updated successfully.',
        'cannot_delete_default' => 'Cannot delete default language.', 'inactive_cannot_default' => 'Inactive language cannot be set as default.',
    ],
];
