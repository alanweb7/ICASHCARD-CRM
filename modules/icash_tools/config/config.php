<?php defined('BASEPATH') or exit('No direct script access allowed');

$hook['admin_init'][] = [
    'function' => 'register_staff_routes',
    'filename' => 'register_staff_routes.php',
    'filepath' => 'modules/icash_tools/hooks',
];