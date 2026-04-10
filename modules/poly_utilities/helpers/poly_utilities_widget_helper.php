<?php

defined('BASEPATH') or exit('No direct script access allowed');

class poly_utilities_widget_helper
{
    /**
     * Display permission information for widgets.
     */
    public static $roles = array(
        array('name' => 'active', 'type' => 'checkbox', 'label' => 'Active', 'value' => false),
        array('name' => 'active_admin', 'type' => 'checkbox', 'label' => 'Active Admin', 'value' => false),
        array('name' => 'active_staff', 'type' => 'checkbox', 'label' => 'Active Staff', 'value' => false),
        array('name' => 'active_client', 'type' => 'checkbox', 'label' => 'Active Client', 'value' => false)
    );
    public static $widget_blocks;
    public static $avaible_widgets;
    public static $dynamic_widgets;

    /**
     * Set up default widget information.
     */
    public static function init()
    {
        self::$dynamic_widgets = json_decode(clear_textarea_breaks(get_option(POLY_WIDGETS)));
        // Widget active
        $filteredRoles = array_filter(self::$roles, function ($role) {
            return $role['name'] == 'active';
        });
        // Reset keys
        self::$roles = array_values($filteredRoles);
        self::$widget_blocks = array(
            array(
                'id' => 'poly-area-after-main-menu',
                'name' => _l('poly_utilities_widget_after_topbar_menu_header'),
                'description' => _l('poly_utilities_widget_after_topbar_menu_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-right-avatar',
                'name' => _l('poly_utilities_widget_right_avatar_header'),
                'description' => _l('poly_utilities_widget_right_avatar_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-after-avatar',
                'name' => _l('poly_utilities_widget_after_avatar_header'),
                'description' => _l('poly_utilities_widget_after_avatar_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-before-sidebar-logo',
                'name' => _l('poly_utilities_widget_before_sidebar_logo_header'),
                'description' => _l('poly_utilities_widget_before_sidebar_logo_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-before-sidebar-menu',
                'name' =>  _l('poly_utilities_widget_before_sidebar_menu_header'),
                'description' => _l('poly_utilities_widget_before_sidebar_menu_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-after-sidebar-menu',
                'name' =>  _l('poly_utilities_widget_after_sidebar_menu_header'),
                'description' => _l('poly_utilities_widget_after_sidebar_menu_description'),
                'default' => 'true'
            ),

            array(
                'id' => 'poly-area-before-setup-menu',
                'name' =>  _l('poly_utilities_widget_before_setup_menu_header'),
                'description' => _l('poly_utilities_widget_before_setup_menu_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-after-setup-menu',
                'name' =>  _l('poly_utilities_widget_after_setup_menu_header'),
                'description' => _l('poly_utilities_widget_after_setup_menu_description'),
                'default' => 'true'
            ),

            array(
                'id' => 'poly-area-before-dashboard',
                'name' =>  _l('poly_utilities_widget_before_dashboard_header'),
                'description' => _l('poly_utilities_widget_before_dashboard_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-before-article-details',
                'name' =>  _l('poly_utilities_widget_before_article_details_header'),
                'description' => _l('poly_utilities_widget_before_article_details_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-between-article-details',
                'name' =>  _l('poly_utilities_widget_between_article_details_header'),
                'description' => _l('poly_utilities_widget_between_article_details_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-after-article-details',
                'name' =>  _l('poly_utilities_widget_after_article_details_header'),
                'description' => _l('poly_utilities_widget_after_article_details_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-footer',
                'name' =>  _l('poly_utilities_widget_admin_footer_header'),
                'description' => _l('poly_utilities_widget_admin_footer_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-customers-toppage',
                'name' =>  _l('poly_utilities_widget_customer_toppage_header'),
                'description' => _l('poly_utilities_widget_customer_toppage_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-customers-footer',
                'name' =>  _l('poly_utilities_widget_customer_footer_header'),
                'description' => _l('poly_utilities_widget_customer_footer_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-customers-before-login',
                'name' =>  _l('poly_utilities_widget_customers_before_login_header'),
                'description' => _l('poly_utilities_widget_customers_before_login_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-customers-after-login',
                'name' =>  _l('poly_utilities_widget_customers_after_login_header'),
                'description' => _l('poly_utilities_widget_customers_after_login_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-login-top-page',
                'name' =>  _l('poly_utilities_widget_login_top_page_header'),
                'description' => _l('poly_utilities_widget_login_top_page_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-login-bottom-page',
                'name' =>  _l('poly_utilities_widget_login_bottom_page_header'),
                'description' => _l('poly_utilities_widget_login_bottom_page_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-admin-before-login',
                'name' =>  _l('poly_utilities_widget_admin_before_login_header'),
                'description' => _l('poly_utilities_widget_admin_before_login_description'),
                'default' => 'true'
            ),
            array(
                'id' => 'poly-area-admin-after-login',
                'name' =>  _l('poly_utilities_widget_admin_after_login_header'),
                'description' => _l('poly_utilities_widget_admin_after_login_description'),
                'default' => 'true'
            )
        );

        self::$avaible_widgets = array(
            array(
                'name' => 'Text', 'type' => 'text', 'fields' => array(
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'value' => ''],
                    ['name' => 'description', 'type' => 'textarea', 'label' => 'Description', 'value' => '']
                ),
                'roles' => self::$roles,
                'active' => true
            ),

            array(
                'name' => 'HTML', 'type' => 'html',
                'fields' => array(
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'value' => ''],
                    ['name' => 'description', 'type' => 'textarea', 'label' => 'HTML', 'value' => '']
                ),
                'roles' => self::$roles,
                'active' => true
            ),
            array(
                'name' => 'Image', 'type' => 'image',
                'fields' => array(
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'value' => ''],
                    ['name' => 'image', 'type' => 'image', 'label' => 'Add Image', 'value' => '']
                ),
                'roles' => self::$roles,
                'active' => false
            ), array(
                'name' => 'Pinned Project', 'type' => 'pinned_project',
                'fields' => array(
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'value' => ''],
                    ['name' => 'template', 'type' => 'textarea', 'label' => 'Template display', 'value' => '']
                ),
                'roles' => self::$roles,
                'active' => false
            ),
            array(
                'name' => 'Human Resources\' birthday', 'type' => 'birthday',
                'fields' => array(
                    ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'value' => ''],
                    ['name' => 'description', 'type' => 'textarea', 'label' => 'Description', 'value' => '']
                ),
                'roles' => self::$roles,
                'active' => false
            )
        );
    }

    /**
     * Checks if a $widget_area has widgets to display.
     * @param string $widget_area The registered widget ID.
     * @return bool true if the widget_area has widgets to display, otherwise false.
     */
    public static function is_active_widget($widget_area)
    {
        foreach (self::$dynamic_widgets as $obj) {
            if (isset($obj->id) && $obj->id === $widget_area && !empty($obj->widgets)) {
                return count($obj->widgets) > 0;
            }
        }
        return false;
    }

    /**
     * Set the content display position for the widget based on $widget_area
     * @param string $widget_area The registered widget position ID.
     * @return string HTML content code for the widget_area content display position
     */
    public static function dynamic_widget($widget_area)
    {
        echo '<span id="widget_' . $widget_area . '"></span>';
    }
}
