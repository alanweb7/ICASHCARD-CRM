<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: PolyUtilities
Description: Foram adicionadas funcionalidades utilitárias integradas ao Perfex CRM para melhorar as operações e otimizar o fluxo de trabalho. Estas incluem widgets, um menu dobrável para pesquisa e reorganização, um menu de acesso rápido, um menu personalizado (administração, configuração, clientes, agrupamento), um filtro de tabela de dados para apresentação de colunas, botão de suporte tudo-em-um, JavaScript/CSS personalizado/incorporado e funções operacionais adicionais.
Version: 2.4.1
Requires at least: 3.0.0
Author: Scripworld-Soluções em websistemas
Author URI: https://www.scriptworld.shop
Author URI: https://codecanyon.net/user/polyxgo
*/

define('POLYUTILS_ISMINIFIED', true);
define('POLY_UTILITIES_VERSION', '2.4.1');
define('POLY_UTILITIES_MODULE_NAME', 'poly_utilities');
define('POLY_UTILITIES_MODULE_FOLDER', module_dir_path(POLY_UTILITIES_MODULE_NAME));
define('POLY_UTILITIES_MODULE_UPLOAD_FOLDER', module_dir_path(POLY_UTILITIES_MODULE_NAME, 'uploads'));
define('POLY_UTILITIES_MODULE_UPLOAD_MEDIA_FOLDER', POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/media');
define('POLY_UTILITIES_MEDIA_PATH', base_url('/modules/' . POLY_UTILITIES_MODULE_NAME . '/uploads/media'));
define('POLY_UTILITIES_SETTINGS', 'poly_utilities_settings');
define('POLY_WIDGETS', 'poly_utilities_widgets');
define('POLY_CUSTOM_MENU', 'poly_utilities_custom_menu');
define('POLY_QUICK_ACCESS_MENU', 'poly_utilities_global_quick_access_menu');
define('POLY_SUPPORTS', 'poly_utilities_global_supports');
define('POLY_SCRIPTS', 'poly_utilities_global_scripts');
define('POLY_TABLE_FILTERS', 'poly_utilities_table_filters');
define('POLY_STYLES', 'poly_utilities_global_styles');

define('POLY_UTILITIES_USERS_ACCESS_MODULES', 'poly_utilities_global_users_access_modules');

define('POLY_MENU_SIDEBAR', 'poly_utilities_global_menu_sidebar_custom');
define('POLY_MENU_SIDEBAR_CUSTOM_ACTIVE', 'poly_utilities_global_menu_sidebar_custom_active');

define('POLY_MENU_SETUP', 'poly_utilities_global_menu_setup_custom');
define('POLY_MENU_SETUP_CUSTOM_ACTIVE', 'poly_utilities_global_menu_setup_custom_active');

define('POLY_MENU_CLIENTS', 'poly_utilities_global_menu_clients_custom');
define('POLY_MENU_CLIENTS_CUSTOM_ACTIVE', 'poly_utilities_global_menu_clients_custom_active');

define('POLY_BANNERS', 'poly_utilities_banners');
define('POLY_BANNERS_ANNOUNCEMENTS', 'poly_utilities_banners_announcements');
define('POLY_BANNERS_AREA', 'poly_utilities_banners_area');
define('POLY_BANNERS_ANNOUNCEMENTS_AREA', 'poly_utilities_banners_announcements_area');
define('POLY_BANNERS_SETTINGS', 'poly_utilities_banners_settings');

define('POLY_UTILITIES_CUSTOM_MENU_CLIENTS_SLUG', 'article');

class POLYUTILITIES
{
    private $CI;
    private $poly_utilities_settings;
    private $quick_access_menu;
    private $current_user_id;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->current_user_id = get_staff_user_id();
        $this->poly_utilities_settings = clear_textarea_breaks(get_option(POLY_UTILITIES_SETTINGS));

        if ($this->poly_utilities_settings) {
            $this->poly_utilities_settings = json_decode($this->poly_utilities_settings);

            $dataFilters = get_option(POLY_TABLE_FILTERS);
            $this->poly_utilities_settings->data_filters = !empty($dataFilters) ? json_decode($dataFilters, true) : [];

        } else {

            $this->poly_utilities_settings = new stdClass();

        }
        $this->quick_access_menu = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));

        register_activation_hook(POLY_UTILITIES_MODULE_NAME, array($this, 'poly_utilities_module_activation_hook'));

        /**
         * Dactivation module hook
         */
        register_deactivation_hook(POLY_UTILITIES_MODULE_NAME, array($this, 'poly_utilities_module_deactivation_hook'));

        hooks()->add_action('admin_init', [$this, 'poly_utilities_common']);/* language, define */

        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_menu');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_banners');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_user');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_widget');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_common');
        $this->CI->load->helper(POLY_UTILITIES_MODULE_NAME . '/poly_utilities_ajax_response');

        hooks()->add_action('pre_admin_init', [$this, 'poly_utilities_module_permisson']);

        hooks()->add_action('admin_init', [$this, 'poly_utilities_module_init_menu_items']);

        hooks()->add_action('app_admin_head', [$this, 'assets_head'], 1);
        hooks()->add_action('app_admin_footer', [$this, 'assets_footer'], 1);

        hooks()->add_action('app_admin_head', [$this, 'poly_utilities_scripts_styles_admin_header'], 1);

        /**
         * Admin | Customers | Both => scripts, styles.
         */
        hooks()->add_action('app_customers_head', [$this, 'poly_utilities_admin_head']);

        hooks()->add_action('app_admin_footer', [$this, 'poly_utilities_scripts_styles_admin_footer']);
        hooks()->add_action('app_customers_footer', [$this, 'poly_utilities_scripts_styles_customers_footer']);

        /**
         * Admin login form
         */
        hooks()->add_action('app_admin_authentication_head', [$this, 'poly_utilities_hook_widgets_clients']);

        /**
         * Register language files, must be registered if the module is using languages
         */
        register_language_files(POLY_UTILITIES_MODULE_NAME, [POLY_UTILITIES_MODULE_NAME]);

        /**
         * The hook method is processed before showing the sidebar menu
         */
        hooks()->add_filter('sidebar_menu_items', 'app_admin_poly_custom_sidebar_menu_items', 999);
        hooks()->add_filter('setup_menu_items', 'app_admin_poly_custom_setup_menu_items', 999);
        /**
         * Handle permission clients menu items.
         */
        hooks()->add_action('clients_init', 'app_admin_poly_custom_clients_menu_items', 999);
        /**
         * Handle defined clients menu items.
         */
        hooks()->add_action('clients_init', [$this, 'poly_utilities_theme_menu_items'], 9); // 9 before priority 10

        /**
         * Reset the custom menu settings when the modules are activated or deactivated
         * TODO: Need to handle the case of maintaining the order of the menus when there are changes in activating/deactivating various modules, including poly_utilities.
         */
        hooks()->add_action("pre_activate_module", [$this, 'poly_utilities_when_activate_modules']);
        hooks()->add_action("pre_deactivate_module", [$this, 'poly_utilities_when_deactivate_modules']);

        hooks()->add_action("pre_admin_init", [$this, 'poly_utilities_clearn_migrations']);

        //poly_utilities_common_helper::debug_reset();
    }

    /**
     * Remove hook action add_default_theme_menu_items. Funtion init clients menu.
     */
    public function poly_utilities_theme_menu_items()
    {
        if (function_exists('add_default_theme_menu_items')) {
            hooks()->remove_action('clients_init', 'add_default_theme_menu_items');
        }

        $menu_items_custom = get_option(POLY_MENU_CLIENTS);
        
        if ($menu_items_custom === '[]' || $menu_items_custom === '') {
            $menu_items_custom = get_option(POLY_MENU_CLIENTS_CUSTOM_ACTIVE);
        }
        $custom_clients_menu_items = poly_utilities_common_helper::json_decode($menu_items_custom, TRUE);
        $flat_menu_items = poly_flatten_menu_items($custom_clients_menu_items);

        // Define
        if (is_knowledge_base_viewable(true)) {
            $current_object = poly_utilities_find_menu_item_by_slug($flat_menu_items, 'knowledge-base');
            if (!$current_object) {
                add_theme_menu_item('knowledge-base', [
                    'name'     => _l('clients_nav_kb'),
                    'href'     => site_url('knowledge-base'),
                    'position' => 5,
                ]);
            }
        }

        if (!is_client_logged_in() && get_option('allow_registration') == 1) {
            $current_object = poly_utilities_find_menu_item_by_slug($flat_menu_items, 'register');
            if (!$current_object) {
                add_theme_menu_item('register', [
                    'name'     => _l('clients_nav_register'),
                    'href'     => site_url('authentication/register'),
                    'position' => 99,
                ]);
            }
        }

        if (!is_client_logged_in()) {
            $current_object = poly_utilities_find_menu_item_by_slug($flat_menu_items, 'login');
            if (!$current_object) {
                add_theme_menu_item('login', [
                    'name'     => _l('clients_nav_login'),
                    'href'     => site_url('authentication/login'),
                    'position' => 100,
                    'icon'     => 'fa-regular fa-user',
                ]);
            }
        }
        if(is_client_logged_in()){
            // Remove menu items that the current client does not have permission to access.
            poly_process_menu_items($flat_menu_items, $custom_clients_menu_items);
        }
    }

    /**
     * Default theme menu items
     * In most cases you will want to add this hook because of all the features
     */
    public function poly_utilities_clearn_migrations()
    {
        $migrationPath = APP_MODULES_PATH . 'poly_utilities/migrations';
        $files = get_dir_contents($migrationPath);
        $latestFile = null;
        $latestNumber = null;

        if ($files) {
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $fileNameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
                    $fileParts = explode('_', $fileNameWithoutExt);
                    $fileNumber = intval($fileParts[0]);

                    if ($latestNumber === null || $fileNumber > $latestNumber) {
                        $latestNumber = $fileNumber;
                        $latestFile = $file;
                    }
                }
            }

            if ($latestFile) {
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                        $fileNameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
                        $fileParts = explode('_', $fileNameWithoutExt);
                        $fileNumber = intval($fileParts[0]);
                        if ($fileNumber !== $latestNumber) {
                            unlink($file);
                        }
                    }
                }
            }
        }
    }

    /**
     * Handle data, configuration when activating the module
     */
    public function poly_utilities_when_activate_modules()
    {
        //TODO: init configs
    }

    /**
     * Handle data, configuration when deactivating the module
     */
    public function poly_utilities_when_deactivate_modules()
    {
        //TODO: delete configs
    }

    public function poly_utilities_hook_widgets_clients()
    {
        $this->poly_utilities_styles_customers();

        echo '<script src="' . site_url() . 'assets/plugins/jquery/jquery.min.js"></script>';
        echo '<script src="' . site_url() . 'assets/plugins/bootstrap/js/bootstrap.min.js"></script>';

        $this->poly_utilities_settings_scripts(true);

        $this->poly_utilities_scripts_customers_public_head();
        $this->poly_utilities_scripts_customers_public_scripts();

        // Widgets area
        echo '<div class="poly-area-login-top-page-element-helper"></div>';
    }

    public function poly_utilities_settings_scripts($is_widget = false)
    {
        $public_settings = $this->poly_utilities_settings;
        if (!empty($this->poly_utilities_settings)) {
            $public_settings = $this->poly_utilities_settings;
            unset($public_settings->data_filters);
            $public_settings = json_encode($public_settings);
        } else {
            $public_settings = [];
        }
        $poly_utilities_banners = poly_utilities_banners_helper::banners();
?>
        <script>
            var poly_utilities_settings = <?php echo $public_settings ?>;
            <?php
            if ($is_widget == true) {
            ?>
                poly_utilities_settings.banners_settings = <?php echo json_encode($poly_utilities_banners), false ?>; //Banners
                poly_utilities_settings.widgets = <?php echo $this->widgets_generate_content_poly_utilities(true) ?>;
                poly_utilities_settings.widgets_hook = <?php echo $this->widgets_generate_content_poly_utilities() ?>;
            <?php
            }
            ?>
            poly_utilities_settings.logged_client = <?php echo json_encode(poly_client_logged_in_can_access(), true); ?>;
        </script>
        <?php
    }

    public function poly_utilities_admin_head()
    {
        $this->poly_utilities_settings_scripts();
        $this->poly_utilities_settings('customers');
        $this->poly_utilities_scripts_customers_public_head();
    }

    public function poly_utilities_scripts_customers_public_head()
    {
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/head.js') . '"></script>';
    }

    public function poly_utilities_scripts_customers_public_scripts()
    {
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/script.js') . '"></script>';
    }

    public function poly_utilities_module_activation_hook()
    {
        require_once(__DIR__ . '/install.php');

        // Register the routes and hoooks
        poly_utilities_common_helper::require_in_file(APPPATH . 'config/my_routes.php', "FCPATH.'modules/" . POLY_UTILITIES_MODULE_NAME . "/config/my_routes.php'");
    }

    public function poly_utilities_module_deactivation_hook()
    {
        // Remove the routes and hooks
        poly_utilities_common_helper::unrequire_in_file(APPPATH . 'config/my_routes.php', "FCPATH.'modules/" . POLY_UTILITIES_MODULE_NAME . "/config/my_routes.php'");
    }

    /**
     * Enqueues scripts and styles common Admin & Clients.
     * @return void
     */
    public function poly_utilities_settings($mode)
    {
        $poly_utilities_aio_supports = clear_textarea_breaks(get_option(POLY_SUPPORTS));
        $poly_utilities_aio_supports = !empty($poly_utilities_aio_supports) ? json_decode($poly_utilities_aio_supports, true) : [];

        if (($poly_utilities_aio_supports && $poly_utilities_aio_supports['is_admin'] === 'true' && $mode == 'admin') || ($poly_utilities_aio_supports && $poly_utilities_aio_supports['is_clients'] === 'true' && $mode == 'customers')) {
            // Menu
            $sidebar_menu_slim = poly_utilities_custom_menu_slim(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE);
            $setup_menu_slim = poly_utilities_custom_menu_slim(POLY_MENU_SETUP_CUSTOM_ACTIVE);
            $clients_menu_slim = poly_utilities_custom_menu_slim(POLY_MENU_CLIENTS_CUSTOM_ACTIVE);

            $poly_utilities_banners = poly_utilities_banners_helper::banners();
        ?>
            <script>
                poly_utilities_settings.aio_supports = <?php echo json_encode($poly_utilities_aio_supports), false ?>; //Widgets
                poly_utilities_settings.banners_settings = <?php echo json_encode($poly_utilities_banners), false ?>; //Banners
                poly_utilities_settings.widgets = <?php echo $this->widgets_generate_content_poly_utilities(true) ?>;
                poly_utilities_settings.widgets_hook = <?php echo $this->widgets_generate_content_poly_utilities() ?>;
                poly_utilities_settings.menu = {
                    sidebar: <?php echo json_encode($sidebar_menu_slim, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                    setup: <?php echo json_encode($setup_menu_slim, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                    clients: <?php echo json_encode($clients_menu_slim, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
                };
            </script>
        <?php
        }
    }

    public function poly_utilities_scripts_styles_admin_header()
    {
        $this->poly_utilities_settings('admin');
        $this->poly_utilities_resource_css_files('admin', 'header');
        $this->poly_utilities_resource_js_files('admin', 'header');
    }

    /**
     * Enqueues scripts and styles for Admin (Footer).
     * @return void
     */
    public function poly_utilities_scripts_styles_admin_footer()
    {
        $this->poly_utilities_resource_css_files('admin', 'footer');
        $this->poly_utilities_resource_js_files('admin', 'footer');
    }

    /**
     * Enqueues scripts and styles for Clients (Footer)).
     * @return void
     */
    public function poly_utilities_scripts_styles_customers_footer()
    {
        $this->poly_utilities_resource_css_files('customers', 'footer');
        $this->poly_utilities_resource_js_files('customers');

        $this->poly_utilities_styles_customers();

        $this->poly_utilities_js_library();

        $this->poly_utilities_scripts_customers();
    }

    public function poly_utilities_js_library()
    {
        echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/sweetalert2/11.7.31/sweetalert2.min.js') . '"></script>';
        echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/clipboardjs/2.0.11/clipboard.min.js') . '"></script>';
    }

    public function poly_utilities_styles_customers()
    {
        echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/css/public/style.css') . '"/>';
    }
    public function poly_utilities_scripts_customers()
    {
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/script.js') . '"></script>';
    }

    /**
     * Enqueues JavaScript files based on the specified mode area.
     * @param string $mode_area The area mode to load scripts for. Value: admin or customers.
     * @return void
     */
    public function poly_utilities_resource_js_files($mode_area, $position = 'footer')
    {
        //if(!isset($this->poly_utilities_settings)) return;
        if (property_exists($this->poly_utilities_settings, 'is_active_scripts') && $this->poly_utilities_settings->is_active_scripts !== 'true') return;

        $obj_storage = clear_textarea_breaks(get_option(POLY_SCRIPTS));
        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage);
            foreach ($obj_old_data as $resource) {
                if ($resource->mode === $mode_area || $resource->mode === 'admin_customers') {
                    if ($resource->is_embed_position === $position) {
                        if ($resource->is_embed === 'true') {
                            echo poly_utilities_common_helper::read_file($resource->file . '.js', POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/js');
                        } else {
                            echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/uploads/js/' . $resource->file . '.js', true, true) . '"></script>';
                        }
                    }
                }
            }
        }
    }

    /**
     * Enqueues Cascading Style Sheet files based on the specified mode area.
     * @param string $mode_area The area mode to load scripts for. Value: admin or customers.
     * @return void
     */
    public function poly_utilities_resource_css_files($mode_area, $position = 'header')
    {
        //if(!isset($this->poly_utilities_settings)) return;
        if (property_exists($this->poly_utilities_settings, 'is_active_styles') && $this->poly_utilities_settings->is_active_styles !== 'true') return;

        $obj_storage = clear_textarea_breaks(get_option(POLY_STYLES));
        $obj_old_data = [];
        if (!empty($obj_storage)) {
            $obj_old_data = json_decode($obj_storage);
            foreach ($obj_old_data as $resource) {
                if ($resource->mode === $mode_area || $resource->mode === 'admin_customers') {
                    if ($resource->is_embed_position === $position) {
                        if ($resource->is_embed === 'true') {
                            echo poly_utilities_common_helper::read_file($resource->file . '.css', POLY_UTILITIES_MODULE_UPLOAD_FOLDER . '/css');
                        } else {
                            echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/uploads/css/' . $resource->file . '.css', true, true) . '"/>';
                        }
                    }
                }
            }
        }
    }

    /**
     * Load CSS/JS assets in the head
     * @return void
     */
    public function assets_head()
    {
        // Handle for data filter
        if ($this->CI->session->staff_user_id) {
            $this->poly_utilities_settings->uid = $this->CI->session->staff_user_id;
            $this->poly_utilities_settings->segments = $this->CI->uri->segments;
            $this->poly_utilities_settings->version = $this->CI->app_css->core_version();
            if (isset($this->CI->load->_ci_cached_vars['tab']) && $this->CI->load->_ci_cached_vars['tab']) {
                $this->poly_utilities_settings->tab = $this->CI->load->_ci_cached_vars['tab'];
            } else {
                $this->poly_utilities_settings->tab = '';
            }
        }

        echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/css/admin/style.css') . '"/>';
        echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/css/public/style.css') . '"/>';

        ?>
        <script>
            <?php
            $confirmPopup = _l('poly_utilities_delete_object');
            if ($confirmPopup === 'poly_utilities_delete_object') {
                $decodedPopup = '{}';
            } else {
                $decodedPopup = html_entity_decode($confirmPopup);
            }

            $modalObjects = _l('poly_utilities_modals');
            if ($modalObjects === 'poly_utilities_modals') {
                $decodedModals = '[]';
            } else {
                $decodedModals = html_entity_decode($modalObjects);
            }

            $tableOfContents = _l('poly_utilities_table_of_contents_header');
            $tableOfContents = ($tableOfContents === 'poly_utilities_table_of_contents_header') ? '' : html_entity_decode($tableOfContents);

            $favicon = get_option('favicon');
            $favicon_path = (!empty($favicon)) ? base_url('uploads/company/' . $favicon) : '';

            $sidebar_menu_slim = poly_utilities_custom_menu_slim(POLY_MENU_SIDEBAR_CUSTOM_ACTIVE);
            $setup_menu_slim = poly_utilities_custom_menu_slim(POLY_MENU_SETUP_CUSTOM_ACTIVE);
            $clients_menu_slim = poly_utilities_custom_menu_slim(POLY_MENU_CLIENTS_CUSTOM_ACTIVE);
            ?>
            var poly_utilities_settings = <?php echo (!empty($this->poly_utilities_settings) ? json_encode($this->poly_utilities_settings) : []) ?>;
            var poly_quick_access_menu = <?php echo json_encode(!empty($this->quick_access_menu) ? $this->quick_access_menu : []); ?>;

            poly_utilities_settings.favicon_path = '<?php echo $favicon_path ?>';
            poly_utilities_settings.popup_delete = <?php echo $decodedPopup ?>;
            poly_utilities_settings.modals = <?php echo $decodedModals ?>;
            poly_utilities_settings.table_of_content_header = '<?php echo $tableOfContents ?>';

            poly_utilities_settings.lang = <?php echo poly_utilities_common_helper::poly_utilities_render_language() ?: '{}'; ?>;

            poly_utilities_settings.alphabet = <?php echo json_encode(poly_utilities_common_helper::$alphabet, true) ?>;
            poly_utilities_settings.numbers = <?php echo json_encode(poly_utilities_common_helper::$numbers, true) ?>;
            poly_utilities_settings.targets = <?php echo json_encode(poly_utilities_common_helper::$targets, true) ?>;
            poly_utilities_settings.rels = <?php echo json_encode(poly_utilities_common_helper::$rels, true) ?>;

            //Widgets
            poly_utilities_settings.widgets = <?php echo $this->widgets_generate_content_poly_utilities(true) ?>;
            poly_utilities_settings.widgets_hook = <?php echo $this->widgets_generate_content_poly_utilities() ?>;

            // Menu
            poly_utilities_settings.menu = {
                sidebar: <?php echo json_encode($sidebar_menu_slim, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                setup: <?php echo json_encode($setup_menu_slim, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
                clients: <?php echo json_encode($clients_menu_slim, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
            };
        </script>

    <?php
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/head.js') . '"></script>';
    }

    public function widgets_generate_content_poly_utilities($is_default = false)
    {
        $widget_objects = json_decode(clear_textarea_breaks(get_option(POLY_WIDGETS)));

        $result = [];
        if ($is_default == true) {
            foreach ($widget_objects as $value) {
                if ($value->default == 'true') {
                    $result[] = $value;
                }
            }
        } else {
            foreach ($widget_objects as $value) {
                if ($value->default == 'false') {
                    $result[] = $value;
                }
            }
        }

        $objs = [];
        foreach ($result as $value) {
            $content = [];
            if (isset($value->widgets) && $value->widgets) {
                foreach ($value->widgets as $item) {
                    if ($item->roles[0]) {
                        if ($item->roles[0]->name === 'active' && $item->roles[0]->value === 'true') {
                            foreach ($item->fields as $item2) {
                                if ($item2->name == 'description') {
                                    $content[] = $item2->value;
                                }
                            }
                        }
                    }
                    $objs[$value->id] = $content;
                }
            }
        }
        return json_encode($objs, true);
    }

    /**
     * Load CSS/JS assets in the footer
     * @return void
     */
    public function assets_footer()
    {
        $this->poly_utilities_js_library();

        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/script.js') . '"></script>';
        echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/script.js') . '"></script>';
    }

    /**
     * Render the quick access menu into the main menu bar.
     * @return void
     */
    public function before_render_aside_menu_poly_utilities()
    {
        //if(!isset($this->poly_utilities_settings)) return;
        if (isset($this->poly_utilities_settings->is_quick_access_menu) && $this->poly_utilities_settings->is_quick_access_menu !== 'true') return;
        $obj_storage = clear_textarea_breaks(get_option(POLY_QUICK_ACCESS_MENU));
        $obj_old_data = [];
    ?>
        <div id="poly_utilities_quick_access_menu" class="poly-absolute poly-hide">
            <div class="poly_utilities_quick_access_menu">
                <span class="menu-items" data-toggle="dropdown"><i class="fas fa-bars"></i></span>
                <ul class="dropdown-menu dropdown-menu-right animated fadeIn tw-text-base">
                    <li class="dropdown-header tw-mb-1">Quick Access Menu</li>
                    <?php
                    if (!empty($obj_storage)) {
                        $obj_old_data = json_decode($obj_storage);
                        foreach ($obj_old_data as $key => $item) {
                            $icon = $item->icon ? $item->icon : 'fas fa-link';
                    ?>
                            <li>
                                <a href="<?php echo $item->link ?>" target="<?php echo (!empty($item->target) ? $item->target : '_self') ?>" rel="<?php echo (!empty($item->rel) ? $item->rel : 'nofollow') ?>" class="tw-group tw-inline-flex tw-space-x-0.5 tw-text-neutral-700">
                                    <i class="<?php echo $icon ?>"></i>&nbsp;<span><?php echo $item->title . ($item->shortcut_key ? "&nbsp;<span class='poly-quick-access-shortcut-key pull-right' data-toggle='tooltip' data-title='Shortcut key'>{$item->shortcut_key}</span>" : '') ?></span>
                                </a>
                            </li>
                        <?php
                        }
                    }
                    if (has_permission('poly_utilities_shortcut_menu_extend', '', 'create')) {
                        ?>
                        <li>
                            <hr class="hr" />
                            <a href="<?php echo admin_url('poly_utilities/quick_access') ?>"><i class="fas fa-plus"></i>&nbsp;<?php echo _l('poly_utilities_quick_access_menu_mini_add') ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
<?php
    }

    public function flattenMenuItems($items, &$flatArray = [])
    {
        foreach ($items as $item) {
            if (isset($item['href'])) {
                $flatArray[] = $item;
            }
            if (isset($item['children']) && is_array($item['children'])) {
                $this->flattenMenuItems($item['children'], $flatArray);
            }
        }
        return $flatArray;
    }

    public function hrefExistsInMenu($href, $menu)
    {
        $flatMenu = $this->flattenMenuItems($menu);
        foreach ($flatMenu as $menuItem) {
            if ($menuItem['href'] === $href && $menuItem['is_custom'] === 'true') {
                return $menuItem;
            }
        }
        return false;
    }

    public function poly_utilities_module_permisson()
    {
        // Only process the list of custom setup menu slugs. Even default menus with access will be denied if they are on the list.
        $custom_menu_items_option = get_option(POLY_MENU_SETUP);
        $custom_menu_sidebar_items_option = get_option(POLY_MENU_SIDEBAR);
        $custom_menu_items = [];
        $custom_menu_sidebar_items = [];
        if (!empty($custom_menu_items_option) && $custom_menu_items_option != '[]') {
            $custom_menu_items = ($custom_menu_items_option != null) ? json_decode($custom_menu_items_option, true) : [];
        }

        if (!empty($custom_menu_sidebar_items_option) && $custom_menu_sidebar_items_option != '[]') {
            $custom_menu_sidebar_items = ($custom_menu_sidebar_items_option != null) ? json_decode($custom_menu_sidebar_items_option, true) : [];
        }

        $merged_menu_items = array_merge($custom_menu_items ?? [], $custom_menu_sidebar_items ?? []);

        if ($merged_menu_items) {

            $currentUrl = poly_utilities_common_helper::get_current_url();

            $item = $this->hrefExistsInMenu($currentUrl, $merged_menu_items);

            if ($item !== false) {
                //Roles
                $user_can_access = false;
                $role_can_access = false;

                if (!empty($item['roles'])) {
                    $role_by_staffid = poly_utilities_user_helper::get_user_role($this->current_user_id);
                    if ($role_by_staffid !== null) {
                        $roleid_by_user = $role_by_staffid->role;
                        $roles_access = poly_utilities_common_helper::json_decode($item['roles'], true);
                        $role_can_access = poly_utilities_common_helper::get_item_by($roles_access, 'id', $roleid_by_user);
                    }
                } else {
                    $role_can_access = true;
                }

                //Users
                if (!empty($item['users'])) {
                    $users = poly_utilities_common_helper::json_decode($item['users'], true);
                    $user_can_access = poly_utilities_common_helper::get_item_by($users, 'id', $this->current_user_id);
                } else {
                    $user_can_access = true;
                }

                //Remove menu items from the list if the account or group does not have access permission.
                if (!$role_can_access && !$user_can_access && ($this->current_user_id != 1 && $this->current_user_id != 2)) { // 2 for demo. Need to add Settings. && !is_admin(). Need to denie !admin 1 when access menu slug
                    set_alert('danger', _l('access_denied'));
                    redirect(admin_url('access_denied'));
                }
            }
        }
    }

    /**
     * Init goals module menu items in setup in app_init hook
     * @return void
     */
    public function poly_utilities_module_init_menu_items()
    {
        // ==== Quick Access Menu ==== //
        hooks()->add_action('admin_navbar_start', [$this, 'before_render_aside_menu_poly_utilities'], 10);

        if (!poly_utilities_is_user_access_module($this->current_user_id) && $this->current_user_id != 1) {
            return '';
        }

        //=========== Menu setup PolyUtilities
        if ((is_admin() && ($this->current_user_id == 1 || poly_utilities_is_user_access_custom_menu($this->current_user_id))) && (has_permission('poly_utilities_custom_menu_extend', '', 'view'))) {
            $this->CI->app_menu->add_setup_menu_item('poly_utilities_settings', [
                'name'     => _l('poly_utilities_name'),
                'href'     => admin_url('poly_utilities/custom_menu'),
                'position' => 9999,
            ]);
        }
        //=========== Menu setup PolyUtilities

        // ==== Menu sidebar ==== //
        if (has_permission('poly_utilities', '', 'view')) {
            $this->CI->app_menu->add_sidebar_menu_item('poly_utilities', [
                'name'     => _l('poly_utilities_name'),
                'collapse' => true,
                'icon'     => 'fas fa-user-clock',
                'position' => 3,
            ]);
        }

        // ==== Quick Access Menu ==== //
        if (has_permission('poly_utilities_shortcut_menu_extend', '', 'view') || has_permission('poly_utilities_shortcut_menu_extend', '', 'edit') || has_permission('poly_utilities_shortcut_menu_extend', '', 'create') || has_permission('poly_utilities_shortcut_menu_extend', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_shortcut_menu_extend',
                'name'     => _l('poly_utilities_shortcut_menu_extend'),
                'icon'     => 'fa-solid fa-list-check',
                'href'     => admin_url('poly_utilities/quick_access'),
                'position' => 1,
            ]);
        }

        // ==== Custom Menu ==== //
        if ((is_admin() && ($this->current_user_id == 1 || poly_utilities_is_user_access_custom_menu($this->current_user_id))) && (has_permission('poly_utilities_custom_menu_extend', '', 'view'))) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_custom_menu_extend',
                'name'     => _l('poly_utilities_custom_menu_extend'),
                'icon'     => 'fa-solid fa-list-ul fa-fw',
                'href'     => admin_url('poly_utilities/custom_menu'),
                'position' => 2,
            ]);
        }

        // ==== Widgets ==== //
        if (has_permission('poly_utilities_widgets_extend', '', 'view') || has_permission('poly_utilities_widgets_extend', '', 'edit') || has_permission('poly_utilities_widgets_extend', '', 'create') || has_permission('poly_utilities_widgets_extend', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_widgets_extend',
                'name'     => _l('poly_utilities_widgets_extend'),
                'icon'     => 'fa-solid fa-palette fa-fw',
                'href'     => admin_url('poly_utilities/widgets'),
                'position' => 3,
            ]);
        }

        // ==== Scripts ==== //
        if (has_permission('poly_utilities_scripts_extend', '', 'view') || has_permission('poly_utilities_scripts_extend', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_scripts_extend',
                'name'     => _l('poly_utilities_scripts_extend'),
                'icon'     => 'fas fa-file-code',
                'href'     => admin_url('poly_utilities/scripts'),
                'position' => 4,
            ]);
        }

        if (has_permission('poly_utilities_scripts_extend', '', 'view') || has_permission('poly_utilities_scripts_extend', '', 'create') || has_permission('poly_utilities_scripts_extend', '', 'edit')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_scripts_add_extend',
                'name'     => _l('poly_utilities_scripts_extend'),
                'icon'     => 'fas fa-file-code',
                'href'     => admin_url('poly_utilities/scripts_add'),
                'position' => 5,
            ]);
        }

        // ==== Styles ==== //
        if (has_permission('poly_utilities_styles_extend', '', 'view') || has_permission('poly_utilities_styles_extend', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_styles_extend',
                'name'     => _l('poly_utilities_styles_extend'),
                'icon'     => 'fas fa-file-alt',
                'href'     => admin_url('poly_utilities/styles'),
                'position' => 6,
            ]);
        }
        if (has_permission('poly_utilities_styles_extend', '', 'view') || has_permission('poly_utilities_styles_extend', '', 'create') || has_permission('poly_utilities_styles_extend', '', 'edit')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_styles_add_extend',
                'name'     => _l('poly_utilities_styles_extend'),
                'icon'     => 'fas fa-file-alt',
                'href'     => admin_url('poly_utilities/styles_add'),
                'position' => 7,
            ]);
        }

        // ==== Support ==== //
        if (has_permission('poly_utilities_supports', '', 'view') || has_permission('poly_utilities_supports', '', 'edit') || has_permission('poly_utilities_supports', '', 'create') || has_permission('poly_utilities_supports', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_supports',
                'name'     => _l('poly_utilities_support'),
                'icon'     => 'fa-solid fa-headset',
                'href'     => admin_url('poly_utilities/support'),
                'position' => 8,
            ]);
        }

        // ==== Banners: banners, announcements ==== //
        if (has_permission('poly_utilities_banners', '', 'view') || has_permission('poly_utilities_banners', '', 'edit') || has_permission('poly_utilities_banners', '', 'create') || has_permission('poly_utilities_banners', '', 'delete')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_banners',
                'name'     => _l('poly_utilities_banners'),
                'icon'     => 'fa-solid fa-photo-film fa-fw',
                'href'     => admin_url('poly_utilities/banners'),
                'position' => 9,
            ]);
        }

        // ==== Settings ==== //
        if (has_permission('poly_utilities_settings', '', 'view') || has_permission('poly_utilities_settings', '', 'edit')) {
            $this->CI->app_menu->add_sidebar_children_item('poly_utilities', [
                'slug'     => 'poly_utilities_settings',
                'name'     => _l('poly_utilities_settings'),
                'icon'     => 'fa fa-cog',
                'href'     => admin_url('poly_utilities/settings'),
                'position' => 10,
            ]);
        }
        $this->poly_utilities_permissions();
    }

    /**
     * Init module common
     * @return void
     */
    public function poly_utilities_common()
    {
        poly_utilities_widget_helper::init();
    }

    /**
     * Initialize module permissions during setup in the admin_init hook.
     * @return void
     */
    public function poly_utilities_permissions()
    {
        // ==== PolyUtilities ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view')
        ];
        register_staff_capabilities('poly_utilities', $capabilities, _l('poly_utilities'));

        // ==== JavaScripts ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_scripts_extend', $capabilities, _l('poly_utilities_scripts_extend') . ' (' . _l('poly_utilities') . ')');

        // ==== Custom menu ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_custom_menu_extend', $capabilities, _l('poly_utilities_custom_menu_extend') . ' (' . _l('poly_utilities') . ')');

        // ==== Widgets ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_widgets_extend', $capabilities, _l('poly_utilities_widgets_extend') . ' (' . _l('poly_utilities') . ')');


        // ==== Styles ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_styles_extend', $capabilities, _l('poly_utilities_styles_extend') . ' (' . _l('poly_utilities') . ')');

        // ==== Quick Access Menu ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_shortcut_menu_extend', $capabilities, _l('poly_utilities_shortcut_menu_extend') . ' (' . _l('poly_utilities') . ')');

        // ==== AIO Supports ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'edit'   => _l('permission_edit'),
            'create'   => _l('permission_create'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_supports', $capabilities, _l('poly_utilities_support') . ' (' . _l('poly_utilities') . ')');

        // ==== Banners ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'edit'   => _l('permission_edit'),
            'create'   => _l('permission_create'),
            'delete' => _l('permission_delete'),
        ];
        register_staff_capabilities('poly_utilities_banners', $capabilities, _l('poly_utilities_banners') . ' (' . _l('poly_utilities') . ')');

        // ==== Settings ==== //
        $capabilities = [];
        $capabilities['capabilities'] = [
            'view'   => _l('permission_view'),
            'edit'   => _l('permission_edit'),
        ];
        register_staff_capabilities('poly_utilities_settings', $capabilities, _l('poly_utilities_settings') . ' (' . _l('poly_utilities') . ')');
    }
}
new POLYUTILITIES();
