<?php

use Braintree\Instance;

defined('BASEPATH') or exit('No direct script access allowed');

use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class Poly_utilities extends AdminController
{
    private $CI;
    private $current_user_id;
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->current_user_id = get_staff_user_id();

        staff_can_poly_utilities();
    }

    /**
     * Scripts
     * @return view
     */
    public function scripts()
    {
        $data['title'] = _l('poly_utilities_scripts_extend');
        $data['current_user_id'] = $this->current_user_id;
        $this->load->view('scripts/manage', $data);
    }

    /**
     * Add Scripts
     * @return view
     */ //
    public function scripts_add()
    {
        $data['title'] = (isset($_GET['id'])) ? _l('poly_utilities_scripts_update_extend') : _l('poly_utilities_scripts_add_extend');
        $data['current_user_id'] = $this->current_user_id;
        $this->load->view('scripts/create', $data);
    }

    /**
     * Styles
     * @return view
     */
    public function styles()
    {
        $data['title'] = _l('poly_utilities_styles_extend');
        $data['current_user_id'] = $this->current_user_id;
        $this->load->view('styles/manage', $data);
    }

    /**
     * Add Styles
     * @return view
     */
    public function styles_add()
    {
        $data['title'] = (isset($_GET['id'])) ? _l('poly_utilities_styles_update_extend') : _l('poly_utilities_styles_add_extend');
        $data['current_user_id'] = $this->current_user_id;
        $this->load->view('styles/create', $data);
    }

    /**
     * Quick Access Menu
     * @return view
     */
    public function quick_access()
    {
        //staff_can_poly_utilities();

        $data['title'] = _l('poly_utilities_shortcut_menu_extend');
        $this->load->view('quick_access/manage', $data);
    }

    /**
     * Retrieve the list of default settings
     */
    public function ajax_default_settings()
    {
        $defaultSettings = [
            'rels' => poly_utilities_common_helper::$rels,
            'target' => poly_utilities_common_helper::$targets,
            'type' => poly_utilities_common_helper::get_link_type()
        ];
        header('Content-Type: application/json');
        echo json_encode($defaultSettings);
        exit();
    }

    /**
     * Custom Menu
     * @return view
     */
    public function custom_menu()
    {
        // For textarea html content
        $this->CI->app_scripts->add('tinymce-stickytoolbar', site_url('assets/plugins/tinymce-stickytoolbar/stickytoolbar.js'));
        $data['bodyclass'] = 'kb-article';

        staff_can_poly_utilities_custom_menu();

        // View display tab by: menu settup, menu clients, menu sidebar.
        $tab_menu = $this->input->get('menu');

        if ($tab_menu == 'setup') {
            $data['title'] = _l('poly_utilities_custom_setup_menu_extend');
            $data['active'] = 'setup';
            $this->load->view('custom_menu/menu_setup', $data);
        } elseif ($tab_menu == 'clients') {
            $data['title'] = _l('poly_utilities_custom_clients_menu_extend');
            $data['active'] = 'clients';
            $this->load->view('custom_menu/menu_clients', $data);
        } else {
            hooks()->remove_filter('sidebar_menu_items', 'app_poly_admin_sidebar_custom_options', 999);
            $data['title'] = _l('poly_utilities_custom_sidebar_menu_extend');
            $data['active'] = 'sidebar';
            $this->load->view('custom_menu/manage', $data);
        }
    }

    /**
     * Re init configs
     */
    public function ajax_reinit_configs()
    {
        $data = $this->input->post('data');
        // Refresh register the routes and hoooks
        poly_utilities_common_helper::require_in_file(APPPATH . 'config/my_routes.php', "FCPATH.'modules/" . POLY_UTILITIES_MODULE_NAME . "/config/my_routes.php'");
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
    }

    /**
     * Retrieve the list of sidebar menus + custom sidebar menus
     */
    public function ajax_sidebar_menu_items()
    {
        $items = $this->app_menu->get_sidebar_menu_items();
        array_unshift($items, ['name' => 'Root', 'slug' => 'root', 'href' => '#']);
        header('Content-Type: application/json');
        echo json_encode($items);
        exit();
    }

    /**
     * Retrieve the list of custom sidebar menus
     */
    public function ajax_custom_sidebar_menu_items()
    {
        $data = get_option(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE);
        $data = $data ? json_decode($data, true) : [];
        if (!empty($data) && is_array($data)) {
            $data = array_values($data);
        }
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Retrieve the list of setup menus
     */
    public function ajax_setup_menu_items()
    {
        $items = $this->app_menu->get_setup_menu_items();
        array_unshift($items, ['name' => 'Root', 'slug' => 'root', 'href' => '#']);
        header('Content-Type: application/json');
        echo json_encode($items);
        exit();
    }

    /**
     * Retrieve the list of custom setup menus
     */
    public function ajax_custom_setup_menu_items()
    {
        $data = get_option(POLY_MENU_SETUP_CUSTOM_ACTIVE);

        $data = $data ? $data : '[]';

        $menu_items = json_decode($data, true);

        if (!is_array($menu_items)) {
            $menu_items = [];
        }

        if (array_keys($menu_items) !== range(0, count($menu_items) - 1)) {
            $menu_items = array_values($menu_items);
        }

        header('Content-Type: application/json');
        echo json_encode($menu_items);
        exit();
    }

    /**
     * Retrieve the list of clients menus
     */
    public function ajax_client_menu_items()
    {
        $menu_items_custom = get_option(POLY_MENU_CLIENTS);
        if (empty($menu_items_custom) || $menu_items_custom === '[]') {
            hooks()->do_action('clients_init');
            $menu_items_custom = poly_get_clients_menu_items();
            $menu_items_custom = poly_utilities_common_helper::json_decode($menu_items_custom, true);
            foreach ($menu_items_custom as $key => &$item) {
                if (isset($item['is_custom']) && $item['is_custom'] === 'true') {
                    $item['parent_slug'] = 'root';
                }
            }
            unset($item);
        } else {
            $menu_items_custom = poly_utilities_common_helper::json_decode($menu_items_custom, true);
        }
        if (!is_array($menu_items_custom)) {
            $menu_items_custom = [];
        }

        $menu_items_custom = array_values($menu_items_custom);
        $flat_menu_items = poly_flatten_menu_items($menu_items_custom);

        //////////// Clients Logged ////////////
        poly_add_default_menu_items($flat_menu_items, $menu_items_custom);
        //////////// Clients Logged ////////////

        // Update full clients menu
        update_option(POLY_MENU_CLIENTS, json_encode($menu_items_custom));

        // SVG
        foreach ($menu_items_custom as $key => &$item) {
            if (isset($item['icon']) && strpos($item['icon'], 'svg') !== false) {
                $item['svg'] = $item['icon'];
                $item['icon'] = 'menu-icon';
            }

            // $item['children'] level 2
            if (isset($item['children']) && is_array($item['children'])) {
                foreach ($item['children'] as &$child) {
                    if (isset($child['icon']) && strpos($child['icon'], 'svg') !== false) {
                        $child['svg'] = $child['icon'];
                        $child['icon'] = 'menu-icon';
                    }
                }
                unset($child);
            }
        }
        unset($item);

        array_unshift($menu_items_custom, ['name' => 'Root', 'slug' => 'root', 'href' => '#']);
        header('Content-Type: application/json');
        echo json_encode($menu_items_custom);
        exit();
    }

    /**
     * Add or update a custom clients menu.
     */
    public function update_custom_clients_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post();
            if (!isset($menu_item['parent_slug'])) { //Fix lost parent slug; 
                $menu_item['parent_slug'] = 'root';
            }
            $isEdit = $menu_item['is_edit'];
            unset($menu_item['is_edit']);
            if ($isEdit !== 'true') {
                $menu_items_custom = get_option(POLY_MENU_CLIENTS_CUSTOM_ACTIVE);
                $menu_items_custom = poly_utilities_common_helper::json_decode($menu_items_custom, TRUE);

                $menu_object = poly_create_menu_item($menu_item);

                $menu_items_custom[] = $menu_object;

                update_option(POLY_MENU_CLIENTS_CUSTOM_ACTIVE, json_encode($menu_items_custom));

                $custom_items_position = get_option(POLY_MENU_CLIENTS);

                if (!empty($custom_items_position)) {
                    $custom_items_position = poly_utilities_common_helper::json_decode($custom_items_position, TRUE);

                    //Object => parent root => add => update
                    if ($menu_object['parent_slug'] === 'root') {
                        array_unshift($custom_items_position, $menu_object);
                        update_option(POLY_MENU_CLIENTS, json_encode($custom_items_position));
                    } else { //Object !root => find parent => add => update.
                        $found = $this->poly_add_menu_item_to_parent($custom_items_position, $menu_object);
                        if ($found) {
                            update_option(POLY_MENU_CLIENTS, json_encode($custom_items_position));
                        }
                    }
                }
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                set_alert('success', _l('poly_utilities_response_add_success'));
            } else { // Update

                $menu_item['require_login'] = (isset($menu_item['require_login']) && $menu_item['require_login'] == 'on') ? 'on' : '';

                $menu_items = poly_utilities_common_helper::json_decode(get_option(POLY_MENU_CLIENTS), TRUE);
                poly_utilities_menu_sidebar_update($menu_items, $menu_item, true);
                update_option(POLY_MENU_CLIENTS, json_encode($menu_items));

                $menu_items_custom = poly_utilities_common_helper::json_decode(get_option(POLY_MENU_CLIENTS_CUSTOM_ACTIVE), TRUE);
                poly_utilities_menu_sidebar_update($menu_items_custom, $menu_item);
                update_option(POLY_MENU_CLIENTS_CUSTOM_ACTIVE, json_encode($menu_items_custom));

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
                set_alert('success', _l('poly_utilities_response_update_success'));
            }
            exit();
        }
    }

    /**
     * Retrieve the list of custom clients menus
     */
    public function ajax_custom_clients_menu_items()
    {
        $data = get_option(POLY_MENU_CLIENTS_CUSTOM_ACTIVE);

        $data = $data ? $data : '[]';

        $menu_items = json_decode($data, true);

        if (!is_array($menu_items)) {
            $menu_items = [];
        }

        if (array_keys($menu_items) !== range(0, count($menu_items) - 1)) {
            $menu_items = array_values($menu_items);
        }

        header('Content-Type: application/json');
        echo json_encode($menu_items);
        exit();
    }

    /**
     * Retrieve the list of roles
     */
    public function ajax_roles()
    {
        $this->load->model("Roles_model");
        $data = $this->Roles_model->get();

        $data_slim_objects = array_map(function ($role) {
            return [
                'roleid' => $role['roleid'],
                'name' => $role['name']
            ];
        }, $data);

        $data_slim_objects = $data_slim_objects ? $data_slim_objects : [];
        header('Content-Type: application/json');
        echo json_encode($data_slim_objects);
        exit();
    }

    /**
     * Retrieve the list of clients based on search keywords.
     */
    public function ajax_clients_search()
    {
        $result = [];
        if (isset($_GET['search'])) {
            $search_keywords = $_GET['search'];
            $this->db->select('userid, company, address, phonenumber');
            $this->db->from(db_prefix() . 'clients');
            $this->db->group_start();
            $this->db->like('company', $search_keywords);
            $this->db->or_like('address', $search_keywords);
            $this->db->or_like('phonenumber', $search_keywords);
            $this->db->or_like('address', $search_keywords);
            $this->db->group_end();
            $this->db->order_by('company', 'ASC');
            $result = $this->db->get()->result_array();
            unset($value);
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

    /**
     * Retrieve the list of users based on search keywords.
     */
    public function ajax_users_search()
    {
        $result = [];
        if (isset($_GET['search'])) {
            $search_keywords = $_GET['search'];
            if (has_permission('staff', '', 'view')) {
                $this->db->select('staffid, firstname, lastname');
                $this->db->from(db_prefix() . 'staff');
                $this->db->group_start();
                $this->db->like('firstname', $search_keywords);
                $this->db->or_like('lastname', $search_keywords);
                $this->db->or_like("CONCAT(firstname, ' ', lastname)", $search_keywords, false);
                $this->db->or_like("CONCAT(lastname, ' ', firstname)", $search_keywords, false);
                $this->db->or_like('phonenumber', $search_keywords);
                $this->db->or_like('email', $search_keywords);
                $this->db->group_end();
                $this->db->order_by('firstname', 'ASC');
                $result = $this->db->get()->result_array();

                foreach ($result as $key => &$value) {
                    if ($value['staffid'] == 1) { //Exclude Administrator id 1
                        unset($result[$key]);
                        continue;
                    }
                    $value['avatar'] = staff_profile_image_url($value['staffid']);
                }
                unset($value);
            }
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

    /**
     * Retrieve the list of widgets area
     */
    public function ajax_widgets_area()
    {
        $items = poly_utilities_widget_helper::$widget_blocks;
        header('Content-Type: application/json');
        echo json_encode($items);
        exit();
    }

    public function update_users_access_modules()
    {
        if ($this->current_user_id != 1) {
            poly_utilities_ajax_response_helper::response_unauthorize(_l('poly_utilities_response_unauthorized'));
        }

        $users_access = $this->input->post('users_access');
        $users_custom_menu = $this->input->post('users_custom_menu');
        $full_menu_items = [
            'users_access' => $users_access,
            'users_custom_menu' => $users_custom_menu
        ];
        update_option(POLY_UTILITIES_USERS_ACCESS_MODULES, json_encode($full_menu_items));
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
    }

    public function get_users_access_modules()
    {
        $data = poly_utilities_user_helper::get_users_access_modules();
        header('Content-Type: application/json');
        echo $data;
        exit();
    }

    /**
     * Update the order of sidebar menu items.
     */
    public function update_sidebar_menu_positions()
    {
        if ($this->input->post()) {
            $full_menu_items = $this->input->post('data'); // Full menu display;
            update_option(POLY_MENU_SIDEBAR, json_encode($full_menu_items));

            $flat_menu_items = poly_flatten_menu_items($full_menu_items); // Convert flatten array;

            $menu_items_custom = poly_utilities_common_helper::json_decode(get_option(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE), TRUE);
            foreach ($flat_menu_items as $full_item) {
                foreach ($menu_items_custom as &$custom_item) {
                    if (isset($custom_item['id']) && $custom_item['id'] === $full_item['id']) {
                        $custom_item['parent_slug'] = $full_item['parent_slug'];
                    }
                }
            }
            update_option(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE, json_encode($menu_items_custom));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        }
    }

    /**
     * Add or update a custom sidebar menu.
     */
    public function update_custom_sidebar_menu()
    {
        if ($this->input->post()) {

            $menu_item = $this->input->post(null, false);
            if ($menu_item) {
                $menu_item = poly_utilities_common_helper::clean_xss_except($menu_item, ['popup_description']);
            }

            if (!isset($menu_item['parent_slug'])) { //Fix lost parent slug; 
                $menu_item['parent_slug'] = 'root';
            }

            $isEdit = $menu_item['is_edit'];
            unset($menu_item['is_edit']);
            if ($isEdit !== 'true') { // Add new
                $menu_items_custom = get_option(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE);
                $menu_items_custom = poly_utilities_common_helper::json_decode($menu_items_custom, TRUE);

                $menu_object = poly_create_menu_item($menu_item);
                $menu_items_custom[] = $menu_object;

                update_option(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE, json_encode($menu_items_custom));

                $custom_items_position = get_option(POLY_MENU_SIDEBAR);

                if (!empty($custom_items_position)) {
                    $custom_items_position = poly_utilities_common_helper::json_decode($custom_items_position, TRUE);

                    if ($menu_object['parent_slug'] === 'root') { // Add first array when root
                        array_unshift($custom_items_position, $menu_object);
                        update_option(POLY_MENU_SIDEBAR, json_encode($custom_items_position));
                    } else {
                        $found = $this->poly_add_menu_item_to_parent($custom_items_position, $menu_object);
                        if ($found) {
                            update_option(POLY_MENU_SIDEBAR, json_encode($custom_items_position));
                        }
                    }
                }

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                set_alert('success', _l('poly_utilities_response_add_success'));
            } else { // Update
                // List: menu sidebar + custom.
                $menu_items = poly_utilities_common_helper::json_decode(get_option(POLY_MENU_SIDEBAR), TRUE);
                poly_utilities_menu_sidebar_update($menu_items, $menu_item, true);
                update_option(POLY_MENU_SIDEBAR, json_encode($menu_items));

                // List: menu custom.
                $menu_items_custom = poly_utilities_common_helper::json_decode(get_option(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE), TRUE);
                poly_utilities_menu_sidebar_update($menu_items_custom, $menu_item);
                update_option(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE, json_encode($menu_items_custom));

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
                set_alert('success', _l('poly_utilities_response_update_success'));
            }
            exit();
        }
    }

    /**
     * Recursively searches for the parent_slug in the menu structure and appends the menu_object to the parent's children.
     *
     * @param array $menu_items The array of menu items that may contain nested children.
     * @param array $menu_object The new menu item to be added to its parent's children.
     * @return bool Returns true if the parent is found and the child is added, otherwise false.
     */
    public function poly_add_menu_item_to_parent(&$menu_items, $menu_object)
    {
        foreach ($menu_items as &$item) {
            if ($item['id'] === $menu_object['parent_slug']) {
                if (!isset($item['children'])) {
                    $item['children'] = [];
                }
                array_push($item['children'], $menu_object);
                return true;
            }

            if (isset($item['children']) && !empty($item['children'])) {
                $found = $this->poly_add_menu_item_to_parent($item['children'], $menu_object);
                if ($found) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Delete a custom sidebar menu.
     */
    public function delete_custom_sidebar_menu()
    {
        $this->db->trans_begin();

        foreach (['id'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
        }

        $rest_delete_in_sidebar = poly_utilities_delete_custom_sidebar_menu_by_id($id, POLY_MENU_SIDEBAR);
        $rest_delete_in_custom_sidebar = poly_utilities_delete_custom_sidebar_menu_by_id($id, POLY_MENU_SIDEBAR_CUSTOM_ACTIVE);

        if ($rest_delete_in_sidebar && $rest_delete_in_custom_sidebar) {
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                poly_utilities_ajax_response_helper::response_error("Error occurred while deleting.");
            } else {
                $this->db->trans_commit();
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            }
        } else {
            $this->db->trans_rollback();
            poly_utilities_ajax_response_helper::response_error("Error occurred while deleting.");
        }
    }

    /**
     * Update the order of setup menu items.
     */
    public function update_setup_menu_positions()
    {
        if ($this->input->post()) {
            $full_menu_items = $this->input->post('data'); // Full menu display;
            update_option(POLY_MENU_SETUP, json_encode($full_menu_items));

            $flat_menu_items = poly_flatten_menu_items($full_menu_items); // Convert flatten array;

            $menu_items_custom = poly_utilities_common_helper::json_decode(get_option(POLY_MENU_SETUP_CUSTOM_ACTIVE), TRUE);
            foreach ($flat_menu_items as $full_item) {
                foreach ($menu_items_custom as &$custom_item) {
                    if (isset($custom_item['id']) && $custom_item['id'] === $full_item['id']) {
                        $custom_item['parent_slug'] = $full_item['parent_slug'];
                    }
                }
            }
            update_option(POLY_MENU_SETUP_CUSTOM_ACTIVE, json_encode($menu_items_custom));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        }
    }

    public function update_custom_setup_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post();
            if (!isset($menu_item['parent_slug'])) { //Fix lost parent slug; 
                $menu_item['parent_slug'] = 'root';
            }
            $isEdit = $menu_item['is_edit'];
            unset($menu_item['is_edit']);
            if ($isEdit !== 'true') {
                $menu_items_custom = get_option(POLY_MENU_SETUP_CUSTOM_ACTIVE);
                $menu_items_custom = poly_utilities_common_helper::json_decode($menu_items_custom, TRUE);

                $menu_object = poly_create_menu_item($menu_item);

                $menu_items_custom[] = $menu_object;

                update_option(POLY_MENU_SETUP_CUSTOM_ACTIVE, json_encode($menu_items_custom));

                $custom_items_position = get_option(POLY_MENU_SETUP);

                if (!empty($custom_items_position)) {
                    $custom_items_position = poly_utilities_common_helper::json_decode($custom_items_position, TRUE);

                    if ($menu_object['parent_slug'] === 'root') {
                        array_unshift($custom_items_position, $menu_object);
                        update_option(POLY_MENU_SETUP, json_encode($custom_items_position));
                    } else {
                        foreach ($custom_items_position as &$item) {
                            if ($item['slug'] === $menu_object['parent_slug']) {
                                if (!array_key_exists('children', $item)) {
                                    $item['children'] = array();
                                }
                                array_push($item['children'], $menu_object);
                            }
                        }
                        unset($item);
                        update_option(POLY_MENU_SETUP, json_encode($custom_items_position));
                    }
                }
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                set_alert('success', _l('poly_utilities_response_add_success'));
            } else {
                //Update
                $menu_items = poly_utilities_common_helper::json_decode(get_option(POLY_MENU_SETUP), TRUE);
                poly_utilities_menu_sidebar_update($menu_items, $menu_item, true);
                update_option(POLY_MENU_SETUP, json_encode($menu_items));

                $menu_items_custom = poly_utilities_common_helper::json_decode(get_option(POLY_MENU_SETUP_CUSTOM_ACTIVE), TRUE);
                poly_utilities_menu_sidebar_update($menu_items_custom, $menu_item);
                update_option(POLY_MENU_SETUP_CUSTOM_ACTIVE, json_encode($menu_items_custom));

                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_update_success'));
                set_alert('success', _l('poly_utilities_response_update_success'));
            }
            exit();
        }
    }

    /**
     * Delete a custom setup menu.
     */
    public function delete_custom_setup_menu()
    {
        $this->db->trans_begin();

        foreach (['id'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
        }

        $rest_delete_in_sidebar = poly_utilities_delete_custom_sidebar_menu_by_id($id, POLY_MENU_SETUP);
        $rest_delete_in_custom_sidebar = poly_utilities_delete_custom_sidebar_menu_by_id($id, POLY_MENU_SETUP_CUSTOM_ACTIVE);

        if ($rest_delete_in_sidebar && $rest_delete_in_custom_sidebar) {
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                poly_utilities_ajax_response_helper::response_error("Error occurred while deleting.");
            } else {
                $this->db->trans_commit();
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            }
        } else {
            $this->db->trans_rollback();
            poly_utilities_ajax_response_helper::response_error("Error occurred while deleting.");
        }
    }

    /**
     * Update the order of clients menu items.
     */
    public function update_clients_menu_positions()
    {
        if ($this->input->post()) {
            $full_menu_items = $this->input->post('data');
            update_option(POLY_MENU_CLIENTS, json_encode($full_menu_items));

            $flat_menu_items = poly_flatten_menu_items($full_menu_items); // All clients menu items

            $id_parent_slug_map = [];
            foreach ($flat_menu_items as $full_item) {
                if (isset($full_item['id']) && isset($full_item['parent_slug'])) {
                    $id_parent_slug_map[$full_item['id']] = $full_item['parent_slug'];
                }
            }

            $menu_items_custom = poly_utilities_common_helper::json_decode(get_option(POLY_MENU_CLIENTS_CUSTOM_ACTIVE), TRUE);
            foreach ($menu_items_custom as &$custom_item) {
                if (isset($custom_item['id']) && array_key_exists($custom_item['id'], $id_parent_slug_map)) {
                    $custom_item['parent_slug'] = $id_parent_slug_map[$custom_item['id']];
                }
            }

            update_option(POLY_MENU_CLIENTS_CUSTOM_ACTIVE, json_encode($menu_items_custom));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        }
    }

    /**
     * Clone a custom sidebar menu.
     */
    public function clone_sidebar_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post('data');
            poly_clone_menu_items($menu_item, POLY_MENU_SIDEBAR_CUSTOM_ACTIVE, POLY_MENU_SIDEBAR);
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            set_alert('success', _l('poly_utilities_response_add_success'));
            exit();
        }
    }

    /**
     * Clone a custom setup menu.
     */
    public function clone_setup_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post('data');
            poly_clone_menu_items($menu_item, POLY_MENU_SETUP_CUSTOM_ACTIVE, POLY_MENU_SETUP);
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            set_alert('success', _l('poly_utilities_response_add_success'));
            exit();
        }
    }

    /**
     * Clone a custom clients menu.
     */
    public function clone_clients_menu()
    {
        if ($this->input->post()) {
            $menu_item = $this->input->post('data');
            poly_clone_menu_items($menu_item, POLY_MENU_CLIENTS_CUSTOM_ACTIVE, POLY_MENU_CLIENTS);
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            set_alert('success', _l('poly_utilities_response_add_success'));
            exit();
        }
    }

    /**
     * Delete a custom sidebar menu.
     */
    public function delete_custom_clients_menu()
    {
        $this->db->trans_begin();

        foreach (['id'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
        }

        $rest_delete_in_sidebar = poly_utilities_delete_custom_sidebar_menu_by_id($id, POLY_MENU_CLIENTS);
        $rest_delete_in_custom_sidebar = poly_utilities_delete_custom_sidebar_menu_by_id($id, POLY_MENU_CLIENTS_CUSTOM_ACTIVE);

        if ($rest_delete_in_sidebar && $rest_delete_in_custom_sidebar) {
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                poly_utilities_ajax_response_helper::response_error("Error occurred while deleting.");
            } else {
                $this->db->trans_commit();
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            }
        } else {
            $this->db->trans_rollback();
            poly_utilities_ajax_response_helper::response_error("Error occurred while deleting.");
        }
    }

    /**
     * Reset a custom menu: sidebar, setup, clients
     */
    public function reset_custom_menu()
    {
        hooks()->do_action('before_poly_utilities_reset_custom_menu', true);
        if ($this->input->post()) {
            $menu = $this->input->post('menu');
            poly_reset_custom_menu($menu);
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        }
        poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_error'));
    }

    /**
     * Widgets
     * @return view
     */
    public function widgets()
    {
        $data['title'] = _l('poly_utilities_widgets_extend');
        $this->load->view('widgets/manage', $data);
    }

    /**
     * Support
     * @return view
     */
    public function support()
    {
        $poly_utilities_aio_supports = clear_textarea_breaks(get_option(POLY_SUPPORTS));
        $poly_utilities_aio_supports = !empty($poly_utilities_aio_supports) ? json_decode($poly_utilities_aio_supports, true) : [];

        $data['title'] = _l('poly_utilities_support');
        $data['poly_utilities_aio_supports'] = $poly_utilities_aio_supports;

        $this->load->view('support/manage', $data);
    }

    /**
     * Banenrs
     * @return view
     */
    public function banners()
    {
        $data['title'] = _l('poly_utilities_banners');
        $tab = $this->input->get('group');
        $data['current_tab'] = $tab;

        $data['tabs'] = [
            "manage" => $this->createTab("manage", _l('poly_utilities_banner_media_tabs_banners'), "poly_utilities/banners/manage", 5, "fa fa-th"),
            "announcements" => $this->createTab("announcements", _l('poly_utilities_banner_media_tabs_announcements'), "poly_utilities/banners/announcements", 10, "fa-solid fa-comments fa-fw"),
            "settings" => $this->createTab("settings", _l('poly_utilities_banner_media_tabs_settings'), "poly_utilities/banners/settings", 15, "fa fa-sliders-h")
        ];

        if (!$tab || (in_array($tab, $data['tabs']) && !is_admin())) {
            $tab = 'manage';
        }

        if ($tab === 'settings') {
            $data['tab'] = $this->createTab("settings", _l('poly_utilities_banner_media_tabs_settings'), "poly_utilities/banners/settings", 15, "fa fa-th");
        } else {
            if (!in_array($tab, $data['tabs'])) {
                $data['tab'] = $this->app_tabs->filter_tab($data['tabs'], $tab);
            }
        }
        $this->load->view('banners/index', $data);
    }

    public function createTab($slug, $name, $view, $position, $icon, $is_display = true)
    {
        return [
            "slug" => $slug,
            "name" => $name,
            "view" => $view,
            "position" => $position,
            "icon" => $icon,
            "is_display" => $is_display,
            "href" => "#",
            "badge" => [],
            "children" => []
        ];
    }

    /**
     * Settings
     * @return view
     */
    public function settings()
    {
        $poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));

        if (empty($poly_utilities_settings)) {
            $obj_settings = new stdClass();
            $obj_settings->is_sticky = false;
            $obj_settings->is_admin_breadcrumb = true;
            $obj_settings->is_toggle_sidebar_menu = false;
            $obj_settings->is_table_of_content = false;
            $obj_settings->is_active_scripts = true;
            $obj_settings->is_active_styles = true;
            $obj_settings->is_note_confirm_delete = true;
            $obj_settings->is_operation_functions = true;
            $obj_settings->is_scroll_to_top = false;
            update_option(POLY_UTILITIES_SETTINGS, json_encode($obj_settings));
            $poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));
        }
        $poly_utilities_settings = !empty($poly_utilities_settings) ? json_decode($poly_utilities_settings, true) : [];

        $data['title'] = _l('poly_utilities_settings');
        $data['poly_utilities_settings'] = $poly_utilities_settings;

        $this->load->view('settings', $data);
    }

    /**
     * Remove Quick Access Menu
     * @return view
     */
    public function delete_quick_access()
    {
        foreach (['link'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
            $$input_object = nl2br($$input_object);
        }

        $obj_storage = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            if (poly_utilities_common_helper::isExisted($obj_old_data, 'link', $link)) {
                $x = poly_utilities_common_helper::removeDataByField($obj_old_data, 'link', $link);
                update_option(POLY_QUICK_ACCESS_MENU, json_encode($x));
                poly_utilities_ajax_response_helper::response_success("Remove {$link}");
            }
        }
    }

    /**
     * Update Quick Access Menu
     * @return view
     */
    public function update_quick_access_menu()
    {
        $objs = $this->input->post('data', FALSE);
        update_option(POLY_QUICK_ACCESS_MENU, json_encode($objs));
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
    }

    /**
     * Add Quick Access Menu
     * @return view
     */
    public function save_quick_access()
    {
        foreach (['icon', 'title', 'link', 'shortcut_key', 'target', 'rel'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
            $$input_object = nl2br($$input_object);
        }

        $obj = new stdClass();
        $obj->index = poly_utilities_common_helper::generateUniqueID();
        $obj->icon = $icon;
        $obj->title = $title;
        $obj->link = $link;
        $obj->target = !empty($target) ? $target : '_self';
        $obj->rel = !empty($rel) ? $rel : 'nofollow';
        $obj->shortcut_key = $shortcut_key;

        $obj_storage = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);
            if (!poly_utilities_common_helper::isExisted($obj_old_data, 'link', $obj->link)) {
                $obj_old_data[] = $obj;
                update_option(POLY_QUICK_ACCESS_MENU, json_encode($obj_old_data));
            } else {
                poly_utilities_ajax_response_helper::response_data_exists(_l('poly_utilities_data_existed'));
            }
        } else {
            $obj_old_data[] = $obj;
            update_option(POLY_QUICK_ACCESS_MENU, json_encode($obj_old_data));
        }
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_add_success'));
    }

    /**
     * Uppdate All in One Supports
     * @return json
     */
    public function save_aio_supports()
    {
        $objs = $this->input->post('data', FALSE);
        update_option(POLY_SUPPORTS, json_encode($objs));
        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
    }

    /**
     * Add resource: js | css
     * @return view
     */
    public function save_resource()
    {
        foreach (['title', 'file', 'mode', 'is_lock', 'content', 'state', 'resource', 'is_embed', 'is_embed_position'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
        }

        if ($is_lock == 'true' && $this->current_user_id != 1) {
            poly_utilities_ajax_response_helper::response_error(_l('access_denied'));
        }

        switch ($mode) {
            case 'truetrue':
                $mode = 'admin_customers';
                break;
            case 'truefalse':
                $mode = 'admin';
                break;
            case 'falsetrue':
                $mode = 'customers';
                break;
        }
        $resourceTable = POLY_SCRIPTS;
        $resourceExtension = '.js';
        switch ($resource) {
            case 'js': {
                    $resourceTable = POLY_SCRIPTS;
                    $resourceExtension = '.js';
                    break;
                }
            case 'css': {
                    $resourceTable = POLY_STYLES;
                    $resourceExtension = '.css';
                    break;
                }
        }

        $obj = new stdClass();
        $obj->title = $title;
        $obj->file = ($file ? $file : poly_utilities_common_helper::create_slug($title));
        $obj->mode = $mode; //admin, customers, admin_customers;
        $obj->is_embed = $is_embed;
        $obj->is_embed_position = $is_embed_position;

        $obj_storage = clear_textarea_breaks(get_option($resourceTable));
        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);
            if (isset($state) && $state == true) {
                foreach ($obj_old_data as &$item) {
                    if ($item['file'] === $obj->file) {
                        $item['title'] =  $obj->title;
                        $item['mode'] = $mode;
                        $item['is_embed'] = $is_embed;
                        if ($this->current_user_id == 1) {
                            $item['is_lock'] = $is_lock;
                        }
                        $item['is_embed_position'] = $is_embed_position;
                    }
                }
                unset($item);

                $isSave = poly_utilities_common_helper::save_to_file($obj->file . $resourceExtension, POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource, $content, true);
                if ($isSave == 1) {
                    update_option($resourceTable, json_encode($obj_old_data));
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_update_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
                }
            }
        }

        if (poly_utilities_common_helper::isExisted($obj_old_data, 'file', $obj->file)) {
            poly_utilities_ajax_response_helper::response_data_exists(_l('poly_utilities_data_existed'));
        } else {
            $file_path = POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource . '/' . $file . $resourceExtension;
            if (file_exists($file_path)) {
                if (!unlink($file_path)) {
                    poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
                }
            }
        }

        $obj_old_data[] = $obj;
        $isSave = poly_utilities_common_helper::save_to_file($obj->file . $resourceExtension, POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource, $content);
        if ($isSave == 1) {
            update_option($resourceTable, json_encode($obj_old_data));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_add_success'));
        } else {
            poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
        }
    }

    /**
     * Remove resource: js | css
     * @return view
     */
    public function delete_resource()
    {
        foreach (['id', 'resource'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
        }

        $resourceTable = POLY_SCRIPTS;
        switch ($resource) {
            case 'js': {
                    $resourceTable = POLY_SCRIPTS;
                    break;
                }
            case 'css': {
                    $resourceTable = POLY_STYLES;
                    break;
                }
        }

        $obj_storage = clear_textarea_breaks(get_option($resourceTable));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            $obj_resource = poly_utilities_common_helper::getResourceObject($obj_old_data, 'file', $id);

            if (isset($obj_resource['is_lock']) && $obj_resource['is_lock'] === 'true' && $this->current_user_id != 1) {
                poly_utilities_ajax_response_helper::response_error(_l('access_denied'));
            }

            if ($obj_resource) {
                $x = poly_utilities_common_helper::removeDataByField($obj_old_data, 'file', $id);
                update_option($resourceTable, json_encode($x));
                $file_path = POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/' . $resource . '/' . $id . '.' . $resource;
                if (file_exists($file_path)) {
                    if (unlink($file_path)) {
                        poly_utilities_ajax_response_helper::response_success("Remove & delete file: {$id}");
                    }
                }
                poly_utilities_ajax_response_helper::response_success("Remove {$id}");
            }
        }
    }

    public function update_resource_status()
    {
        foreach (['id', 'mode', 'is_lock', 'resource'] as $input_object) {
            $$input_object = $this->input->post($input_object, FALSE);
            $$input_object = trim($$input_object);
        }

        if ($is_lock == 'true' && $this->current_user_id != 1) {
            poly_utilities_ajax_response_helper::response_error(_l('access_denied'));
        }

        $resourceTable = POLY_SCRIPTS;
        switch ($resource) {
            case 'js': {
                    $resourceTable = POLY_SCRIPTS;
                    break;
                }
            case 'css': {
                    $resourceTable = POLY_STYLES;
                    break;
                }
        }
        switch ($mode) {
            case 'truetrue':
                $mode = 'admin_customers';
                break;
            case 'truefalse':
                $mode = 'admin';
                break;
            case 'falsetrue':
                $mode = 'customers';
                break;
        }

        $obj_storage = clear_textarea_breaks(get_option($resourceTable));

        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            if ($obj_old_data) {
                $obj = poly_utilities_common_helper::getResourceObject($obj_old_data, 'file', $id);
                if ($this->current_user_id == 1) {
                    $obj['is_lock'] = $is_lock;
                }
                $obj['mode'] = $mode;
                $dataTableFiltersUpdate = poly_utilities_common_helper::updateDataByField($obj_old_data, 'file', $id, $obj);
                if (update_option($resourceTable, json_encode($dataTableFiltersUpdate)) === true) {
                    poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                } else {
                    poly_utilities_ajax_response_helper::response_data_not_saved(_l('poly_utilities_resource_access_error'));
                }
            }
        }
    }

    /**
     * Update Settings
     * @return view
     */
    public function update_settings()
    {
        if ($this->input->post()) {
            $objs = $this->input->post('data', FALSE);
            update_option(POLY_UTILITIES_SETTINGS, json_encode($objs));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        }
    }

    /**
     * Update the display filter configuration for the data tables.
     * @return view
     */
    public function save_data_filters()
    {
        if ($this->input->post()) {
            $objs = $this->input->post('data', FALSE);

            $obj = new stdClass();
            $obj->key = $objs['key'];
            $obj->value = $objs['value'];

            $dataFilters = get_option(POLY_TABLE_FILTERS);

            $dataTaleFilters = [];
            if (!empty($dataFilters)) {
                $dataTaleFilters = json_decode($dataFilters, true);
                //Update
                if (poly_utilities_common_helper::isExisted($dataTaleFilters, 'key', $obj->key)) {
                    $dataTableFiltersUpdate = poly_utilities_common_helper::updateDataByField($dataTaleFilters, 'key', $obj->key, $obj);
                    if (update_option(POLY_TABLE_FILTERS, json_encode($dataTableFiltersUpdate)) === true) {
                        poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                    } else {
                        poly_utilities_ajax_response_helper::response_data_not_saved('Error');
                    }
                }
            }

            $dataTaleFilters[] = $obj;
            if (update_option(POLY_TABLE_FILTERS, json_encode($dataTaleFilters)) === true) {
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
            } else {
                poly_utilities_ajax_response_helper::response_data_not_saved('Error');
            }
        }
    }

    public function update_widget()
    {
        if (is_admin() || has_permission('poly_utilities_widgets_extend', '', 'edit') || has_permission('poly_utilities_widgets_extend', '', 'delete')) {
            $objs = $this->input->post('data', FALSE);
            update_option(POLY_WIDGETS, json_encode($objs));
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } else {
            poly_utilities_ajax_response_helper::response_data_not_saved(_l('access_denied'));
        }
    }

    //#region display custom menu
    public function details($slug)
    {
        if (!$slug) {
            show_404();
        }
        $menu_items = poly_utilities_custom_menu_items(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE);
        if (empty($menu_items[$slug])) {
            $menu_items = poly_utilities_custom_menu_items(POLY_MENU_SETUP_CUSTOM_ACTIVE);
        }
        if (isset($menu_items[$slug]) && $menu_items[$slug]) {
            $object = $menu_items[$slug];
            $data['custom_menu'] = $object;
            $data['title'] = $object['name'];
            $this->load->view('custom_menu/details', $data);
        } else {
            show_404();
        }
    }
    //#endregion display custom menu

    //#region upload files

    /**
     * Retrieve the list of banners settings
     */
    public function ajax_banners_settings()
    {
        $data = get_option(POLY_BANNERS_SETTINGS);
        $data = $data ? $data : '[]';
        header('Content-Type: application/json');
        echo $data;
        exit();
    }

    /**
     * Update Banners Settings
     * @return view
     */
    public function update_banners_settings()
    {
        $post_data = $this->input->post();

        if (empty($post_data)) {
            poly_utilities_ajax_response_helper::response_error('Error');
            return;
        }
        $boolean_fields = ['active', 'is_autoplay', 'is_controls', 'is_thumbnails', 'active_announcements', 'is_autoplay_announcements', 'is_controls_announcements'];
        foreach ($boolean_fields as $field) {
            $post_data[$field] = isset($post_data[$field]) && ($post_data[$field] == 'on' || $post_data[$field] == '1') ? 1 : 0;
        }
        $result = update_option(POLY_BANNERS_SETTINGS, json_encode($post_data));
        if ($result == true) {
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } else {
            poly_utilities_ajax_response_helper::response_error('Error');
        }
    }

    /**
     * Retrieve the list of banners
     */
    public function ajax_banners_announcements()
    {
        $data = get_option(POLY_BANNERS_ANNOUNCEMENTS);
        $data = $data ? json_decode($data, true) : [];

        poly_utilities_common_helper::sortByFieldName($data, 'created');

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $items_per_page = isset($_GET['items_per_page']) ? intval($_GET['items_per_page']) : 10;

        $total_items = count($data);
        $total_pages = ceil($total_items / $items_per_page);

        $offset = ($page - 1) * $items_per_page;
        $paginated_data = array_slice($data, $offset, $items_per_page);

        $start_item = $offset + 1;
        $end_item = min($offset + $items_per_page, $total_items);

        $data_info = _l('poly_utilities_dt_info', [$start_item, $end_item, $total_items]) . ' ' . _l('poly_utilities_dt_entries');

        $response = [
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'banners' => $paginated_data,
            'data_info' => $data_info
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function banners_announcements_add()
    {
        $this->db->trans_begin();

        $widgets_area = $this->input->post('area');
        if (empty($widgets_area)) {
            poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_banner_response_widgets_area_required'));
            return;
        }

        $id = $this->input->post('id');
        $active = $this->input->post('active');
        $active = ($active == 'on') ? 1 : 0;

        $announcement = array(
            'title' => $this->input->post('title'),
            'area' => $widgets_area,
            'date_from' => $this->input->post('date_from'),
            'date_to' => $this->input->post('date_to'),
            'url' => $this->input->post('url'),
            'target' => $this->input->post('target'),
            'rel' => $this->input->post('rel'),
            'active' => $active,
            'updated' => time()
        );

        $content = $this->input->post('content');

        if (!empty($content)) {
            $content = clear_textarea_breaks($content);
            $content = html_purify($content);
            $announcement['content'] = $content;
        }

        $announcements = get_option(POLY_BANNERS_ANNOUNCEMENTS);
        $announcements = poly_utilities_common_helper::json_decode($announcements, TRUE);

        if (!empty($id)) {
            foreach ($announcements as &$existingBanner) {
                if ($existingBanner['id'] == $id) {
                    $existingBanner = array_merge($existingBanner, $announcement);
                    $announcement = $existingBanner;
                    break;
                }
            }
        } else {
            $announcement['id'] = poly_utilities_common_helper::generateUniqueID();
            $announcement['created'] = time();

            $announcements[] = $announcement;
        }

        update_option(POLY_BANNERS_ANNOUNCEMENTS, json_encode($announcements));

        poly_utilities_banners_helper::media_by_areas($announcements, POLY_BANNERS_ANNOUNCEMENTS_AREA);

        $this->db->trans_commit();
        poly_utilities_ajax_response_helper::response_data(array('status' => 'success', 'code' => 200, 'message' => _l('poly_utilities_response_success'), 'data' => $announcement));
    }

    public function delete_announcement()
    {
        $id = $this->input->post('id', TRUE);

        if ($id === NULL) {
            poly_utilities_ajax_response_helper::response_error("ID is missing.");
            return;
        }

        $id = trim($id);
        $id = nl2br($id);

        $obj_storage = clear_textarea_breaks(get_option(POLY_BANNERS_ANNOUNCEMENTS));
        $obj_old_data = [];

        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            if (poly_utilities_common_helper::isExisted($obj_old_data, 'id', $id)) {
                $this->db->trans_begin();
                foreach ($obj_old_data as $key => $announcement) {
                    if ($announcement['id'] == $id) {
                        unset($obj_old_data[$key]);
                        break;
                    }
                }
                update_option(POLY_BANNERS_ANNOUNCEMENTS, json_encode(array_values($obj_old_data)));
                $this->db->trans_commit();
                poly_utilities_banners_helper::media_by_areas($obj_old_data, POLY_BANNERS_ANNOUNCEMENTS_AREA);
                poly_utilities_ajax_response_helper::response_success("Announcement with ID {$id} was successfully deleted.");
            } else {
                poly_utilities_ajax_response_helper::response_error("Announcement with ID {$id} does not exist.");
            }
        } else {
            poly_utilities_ajax_response_helper::response_error("No announcement data available to delete.");
        }
    }

    public function update_announcement_status()
    {
        $data = get_option(POLY_BANNERS_ANNOUNCEMENTS);
        $announcements = json_decode($data, true);

        $status = $this->input->post('active');
        $id = $this->input->post('id');

        if ($id !== NULL) {
            foreach ($announcements as &$announcement) {
                if ($announcement['id'] == $id) {
                    $announcement['active'] = $status;
                    break;
                }
            }

            $updated = update_option(POLY_BANNERS_ANNOUNCEMENTS, json_encode($announcements));

            if ($updated) {
                poly_utilities_banners_helper::media_by_areas($announcements, POLY_BANNERS_ANNOUNCEMENTS_AREA);
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                return;
            }
        }

        poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_resource_access_error'));
    }

    /**
     * Retrieve the list of banners
     */
    public function ajax_banners()
    {
        $data = get_option(POLY_BANNERS);
        $data = $data ? json_decode($data, true) : [];

        poly_utilities_common_helper::sortByFieldName($data, 'created');

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $items_per_page = isset($_GET['items_per_page']) ? intval($_GET['items_per_page']) : 10;

        $total_items = count($data);
        $total_pages = ceil($total_items / $items_per_page);

        $offset = ($page - 1) * $items_per_page;
        $paginated_data = array_slice($data, $offset, $items_per_page);

        $start_item = $offset + 1;
        $end_item = min($offset + $items_per_page, $total_items);

        $data_info = _l('poly_utilities_dt_info', [$start_item, $end_item, $total_items]) . ' ' . _l('poly_utilities_dt_entries');

        $response = [
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'banners' => $paginated_data,
            'data_info' => $data_info
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function update_banner_status()
    {
        $data = get_option(POLY_BANNERS);
        $banners = json_decode($data, true);

        $status = $this->input->post('active');
        $id = $this->input->post('id');

        if ($id !== NULL) {
            foreach ($banners as &$banner) {
                if ($banner['id'] == $id) {
                    $banner['active'] = $status;
                    break;
                }
            }

            $updated = update_option(POLY_BANNERS, json_encode($banners));

            if ($updated) {
                poly_utilities_banners_helper::media_by_areas($banners);
                poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
                return;
            }
        }

        poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_resource_access_error'));
    }

    public function delete_banner()
    {
        $id = $this->input->post('id', TRUE);

        if ($id === NULL) {
            poly_utilities_ajax_response_helper::response_error("ID is missing.");
            return;
        }

        $id = trim($id);
        $id = nl2br($id);

        $obj_storage = clear_textarea_breaks(get_option(POLY_BANNERS));
        $obj_old_data = [];

        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage, true);

            if (poly_utilities_common_helper::isExisted($obj_old_data, 'id', $id)) {
                $this->db->trans_begin();
                foreach ($obj_old_data as $key => $banner) {
                    if ($banner['id'] == $id) {
                        if (!empty($banner['media'])) {
                            if (!poly_utilities_common_helper::deleteOldFiles($banner['media'])) {
                                $this->db->trans_rollback();
                                poly_utilities_ajax_response_helper::response_error("Failed to delete media files for banner with ID {$id}.");
                                return;
                            }
                        }
                        unset($obj_old_data[$key]);
                        break;
                    }
                }
                update_option(POLY_BANNERS, json_encode(array_values($obj_old_data)));
                $this->db->trans_commit();
                poly_utilities_banners_helper::media_by_areas($obj_old_data);
                poly_utilities_ajax_response_helper::response_success("Banner with ID {$id} was successfully deleted.");
            } else {
                poly_utilities_ajax_response_helper::response_error("Banner with ID {$id} does not exist.");
            }
        } else {
            poly_utilities_ajax_response_helper::response_error("No banner data available to delete.");
        }
    }

    public function banners_add()
    {
        $this->db->trans_begin(); // Start transaction

        $widgets_area = $this->input->post('area');
        if (empty($widgets_area)) {
            poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_banner_response_widgets_area_required'));
            return;
        }

        $id = $this->input->post('id');
        $filesIDS = [];
        $errors = [];
        $path = POLY_UTILITIES_MODULE_UPLOAD_MEDIA_FOLDER;

        // Handle file upload
        if (
            isset($_FILES['file']['name'])
            && ($_FILES['file']['name'] != '' || is_array($_FILES['file']['name']) && count($_FILES['file']['name']) > 0)
        ) {
            if (!is_array($_FILES['file']['name'])) {
                $_FILES['file']['name']     = [$_FILES['file']['name']];
                $_FILES['file']['type']     = [$_FILES['file']['type']];
                $_FILES['file']['tmp_name'] = [$_FILES['file']['tmp_name']];
                $_FILES['file']['error']    = [$_FILES['file']['error']];
                $_FILES['file']['size']     = [$_FILES['file']['size']];
            }

            for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                if (_perfex_upload_error($_FILES['file']['error'][$i])) {
                    $errors[$_FILES['file']['name'][$i]] = _perfex_upload_error($_FILES['file']['error'][$i]);
                    continue;
                }

                $tmpFilePath = $_FILES['file']['tmp_name'][$i];

                if (!empty($tmpFilePath) && $tmpFilePath != '') {
                    _maybe_create_upload_path($path);

                    $originalFilename = unique_filename($path, $_FILES['file']['name'][$i]);
                    $filename = app_generate_hash() . '.' . get_file_extension($originalFilename);

                    if (!_upload_extension_allowed($filename)) {
                        $errors[$_FILES['file']['name'][$i]] = 'File extension not allowed';
                        continue;
                    }

                    $newFilePath = rtrim($path, '/') . '/' . $filename;

                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $filesIDS[] = rtrim(POLY_UTILITIES_MEDIA_PATH, '/') . '/' . $filename;
                    }
                }
            }
        }

        $active = $this->input->post('active');
        $active = ($active == 'on') ? 1 : 0;

        $banner = array(
            'title' => $this->input->post('title'),
            'area' => $widgets_area,
            'date_from' => $this->input->post('date_from'),
            'date_to' => $this->input->post('date_to'),
            'url' => $this->input->post('url'),
            'target' => $this->input->post('target'),
            'rel' => $this->input->post('rel'),
            'active' => $active,
            'updated' => time()
        );

        $embed = $this->input->post('embed');
        $media = $this->input->post('media');

        if (!empty($embed)) {
            $embed = clear_textarea_breaks($embed);
            $embed = html_purify($embed);
            $banner['embed'] = $embed;
        }

        // Process media input if it's not a file upload
        if (!empty($media) && empty($_FILES['file']['name'])) {
            // Assume media is a URL if it's not a file
            $banner['media'] = filter_var($media, FILTER_VALIDATE_URL) ? $media : null;
        }

        $banners = get_option(POLY_BANNERS);
        $banners = poly_utilities_common_helper::json_decode($banners, TRUE);

        if (!empty($id)) {
            // Update existing banner
            foreach ($banners as &$existingBanner) {
                if ($existingBanner['id'] == $id) {
                    if (!empty($filesIDS)) {
                        if (!empty($existingBanner['media']) && $existingBanner['media'] != $media) {
                            if (!poly_utilities_common_helper::deleteOldFiles($existingBanner['media'])) {
                                $this->db->trans_rollback();
                                poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_error'));
                                return;
                            }
                        }
                        $banner['media'] = $filesIDS;
                    } elseif (!empty($media)) {
                        if (!empty($existingBanner['media']) && $existingBanner['media'] != $media) {
                            if (!filter_var($media, FILTER_VALIDATE_URL)) {
                                poly_utilities_common_helper::deleteOldFiles($existingBanner['media']);
                            }
                        }
                        $banner['media'] = $media;
                    } else {
                        $banner['media'] = $existingBanner['media'];
                    }
                    $existingBanner = array_merge($existingBanner, $banner);
                    $banner = $existingBanner; // Update banner with the latest information
                    break;
                }
            }
        } else {
            // Create new banner
            $banner['id'] = poly_utilities_common_helper::generateUniqueID();
            $banner['created'] = time();
            if (!empty($filesIDS)) {
                $banner['media'] = $filesIDS[0]; // Assign the first uploaded file
            } elseif (!empty($media)) {
                $banner['media'] = filter_var($media, FILTER_VALIDATE_URL) ? $media : null;
            }

            $banners[] = $banner;
        }

        // Update banners option with the new or updated banner
        update_option(POLY_BANNERS, json_encode($banners));

        poly_utilities_banners_helper::media_by_areas($banners);

        $this->db->trans_commit();
        poly_utilities_ajax_response_helper::response_data(array('status' => 'success', 'code' => 200, 'message' => _l('poly_utilities_response_success'), 'data' => $banner));

        if (!empty($filesIDS)) {
            echo json_encode(['files' => $filesIDS]);
        } else {
            echo json_encode(['errors' => $errors]);
            $this->db->trans_rollback();
            poly_utilities_ajax_response_helper::response_error(_l('poly_utilities_response_error'));
        }
    }
    //#endregion upload files

    #region modules action
    public function ajax_modules_activate()
    {
        if ($this->input->post('data')) {
            $data = $this->input->post('data');
            $action = isset($data['action']) ? $data['action'] : '';
            $modules = isset($data['modules']) ? $data['modules'] : [];

            $modules = array_filter($modules, function ($module_name) {
                return $module_name !== 'poly_utilities';
            });

            $CI = &get_instance(); //poly_utilities

            if ($action === 'activate') {
                foreach ($modules as $module_name) {
                    $CI->app_modules->activate($module_name);
                }
            } elseif ($action === 'deactivate') {
                foreach ($modules as $module_name) {
                    $CI->app_modules->deactivate($module_name);
                }
            } else {
                poly_utilities_ajax_response_helper::response_error(_l('Invalid action'), 400);
                return;
            }
            poly_utilities_ajax_response_helper::response_success(_l('poly_utilities_response_success'));
        } else {
            poly_utilities_ajax_response_helper::response_error(_l('No data provided'), 400);
        }
    }

    #endregion modules action
}
