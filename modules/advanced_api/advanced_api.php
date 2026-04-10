<?php

defined('BASEPATH') || exit('No direct script access allowed');

/*
    Module Name: Advanced REST API
    Module URI: https://advanced.com.br
    Description: Endpoints REST API for Perfex CRM
    Version: 1.0.1
    Requires at least': 3.0.*
*/

/*
 * Define module name
 * Module Name Must be in CAPITAL LETTERS
*/
define('ADVANCED_API_MODULE', 'advanced_api');

require_once __DIR__.'/vendor/autoload.php';

// Register activation module hook
register_activation_hook(ADVANCED_API_MODULE, 'advanced_api_module_activate_hook');
function advanced_api_module_activate_hook()
{
    require_once __DIR__.'/install.php';
}

// Register deactivation module hook
register_deactivation_hook(ADVANCED_API_MODULE, 'advanced_api_module_deactivate_hook');
function advanced_api_module_deactivate_hook()
{
    update_option('advanced_api_enabled', 0);
}

// Register language files, must be registered if the module is using languages
register_language_files(ADVANCED_API_MODULE, [ADVANCED_API_MODULE]);

// Load module helper file
get_instance()->load->helper(ADVANCED_API_MODULE.'/advanced_api');

require_once __DIR__.'/includes/assets.php';
require_once __DIR__.'/includes/sidemenu_links.php';

hooks()->add_action('client_status_changed', function($clientData) {
    if ($clientData['status'] == 0) {
        get_instance()->db->update(db_prefix() . 'contacts', ['customer_api_key' => NULL], ['userid' => $clientData['id']]);
    }
});

hooks()->add_action('contact_updated', function($contactID) {
    get_instance()->db->update(db_prefix() . 'contacts', ['customer_api_key' => NULL], ['id' => $contactID]);   
});

hooks()->add_action('after_user_reset_password', function($data) {
    get_instance()->db->update(db_prefix() . 'contacts', ['customer_api_key' => NULL], ['id' => $data['userid']]);    
});