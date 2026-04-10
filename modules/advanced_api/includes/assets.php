<?php

hooks()->add_action('pre_deactivate_module', ADVANCED_API_MODULE.'_deregister');
function advanced_api_deregister($module_name)
{
    if (ADVANCED_API_MODULE == $module_name['system_name']) {
        delete_option(ADVANCED_API_MODULE.'_verification_id');
        delete_option(ADVANCED_API_MODULE.'_last_verification');
        delete_option(ADVANCED_API_MODULE.'_product_token');
        delete_option(ADVANCED_API_MODULE.'_heartbeat');
    }
}
