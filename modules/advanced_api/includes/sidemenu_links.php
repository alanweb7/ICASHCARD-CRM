<?php

if (is_admin()) {

    hooks()->add_action('admin_init', function () {
        get_instance()->app_tabs->add_settings_tab('advanced_rest_api', [
            'name'     => _l('advanced_rest_api'),
            'view'     => 'advanced_api/rest_api_settings',
            'icon'     => 'fab fa-app-store',
            'position' => 5,
        ]);
        get_instance()->app_menu->add_sidebar_menu_item('advanced_api', [
            'slug'     => 'advanced_api',
            'name'     => _l('advanced_api'),
            'icon'     => 'fa-brands fa-app-store',
            'href'     => admin_url('advanced_api/v1/advanced_api/view'),
            'position' => 31,
        ]);

        get_instance()->app_menu->add_sidebar_children_item('advanced_api', [
            'slug'     => 'advanced_api',
            'name'     => _l('api_settings'),
            'href'     => admin_url('settings?group=advanced_rest_api'),
            'position' => 31,
        ]);
    });
}
