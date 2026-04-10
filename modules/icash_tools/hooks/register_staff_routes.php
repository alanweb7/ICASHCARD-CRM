<?php
function register_staff_routes()
{
    $CI = &get_instance();
    $CI->router->add_route('icash_tools/register_staff', 'icash_tools/register_staff/index');
    $CI->router->add_route('icash_tools/register_staff/submit', 'icash_tools/register_staff/submit');
    $CI->router->add_route('icash_tools/api/token', 'icash_tools_api/generate_token');
}
 