<?php defined('BASEPATH') or exit('No direct script access allowed');

init_head();

echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/vuejs/3.4.27/vue.global.prod.js') . '"></script>';
echo '<link rel="stylesheet" href="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/css/lib/select2/select2.min.css') . '">';

$is_active_logout_buttons = isset($poly_utilities_settings['is_active_logout_buttons']) ? $poly_utilities_settings['is_active_logout_buttons'] : 'false';
$is_sticky = isset($poly_utilities_settings['is_sticky']) ? $poly_utilities_settings['is_sticky'] : 'false';
$is_search_menu = isset($poly_utilities_settings['is_search_menu']) ? $poly_utilities_settings['is_search_menu'] : 'false';
$is_data_table_filters_column = isset($poly_utilities_settings['is_data_table_filters_column']) ? $poly_utilities_settings['is_data_table_filters_column'] : 'true';
$is_button_data_table_filters_column = isset($poly_utilities_settings['is_button_data_table_filters_column']) ? $poly_utilities_settings['is_button_data_table_filters_column'] : 'true';
$is_quick_access_menu = isset($poly_utilities_settings['is_quick_access_menu']) ? $poly_utilities_settings['is_quick_access_menu'] : 'true';
$is_quick_access_menu_icons = isset($poly_utilities_settings['is_quick_access_menu_icons']) ? $poly_utilities_settings['is_quick_access_menu_icons'] : 'true';
$is_table_of_content = isset($poly_utilities_settings['is_table_of_content']) ? $poly_utilities_settings['is_table_of_content'] : 'false';
$is_active_scripts = isset($poly_utilities_settings['is_active_scripts']) ? $poly_utilities_settings['is_active_scripts'] : 'true';
$is_active_styles = isset($poly_utilities_settings['is_active_styles']) ? $poly_utilities_settings['is_active_styles'] : 'true';
$is_note_confirm_delete = isset($poly_utilities_settings['is_note_confirm_delete']) ? $poly_utilities_settings['is_note_confirm_delete'] : 'true';
$is_operation_functions = isset($poly_utilities_settings['is_operation_functions']) ? $poly_utilities_settings['is_operation_functions'] : 'true';

$is_scroll_to_top = isset($poly_utilities_settings['is_scroll_to_top']) ? $poly_utilities_settings['is_scroll_to_top'] : 'false';
$scroll_icon_button_right = isset($poly_utilities_settings['scroll_to_top_right']) ? $poly_utilities_settings['scroll_to_top_right'] : 10;
$scroll_icon_button_bottom = isset($poly_utilities_settings['scroll_to_top_bottom']) ? $poly_utilities_settings['scroll_to_top_bottom'] : 30;

$is_toggle_sidebar_menu = isset($poly_utilities_settings['is_toggle_sidebar_menu']) ? $poly_utilities_settings['is_toggle_sidebar_menu'] : 'false';
$is_admin_breadcrumb = isset($poly_utilities_settings['is_admin_breadcrumb']) ? $poly_utilities_settings['is_admin_breadcrumb'] : 'true';
$is_edit = has_permission('poly_utilities_settings', '', 'edit');
$current_user_id = get_staff_user_id();
?>
<div id="wrapper">
  <div class="content">
    <div class="row poly_utilities_settings">
      <div class="col-md-12">
        <div class="tw-mb-2 sm:tw-mb-4">
          <?php echo form_open($this->uri->uri_string(), array('class' => 'quick_access-form', 'id' => 'poly_utilities_settings_form')); ?>
          <div class="panel_s">
            <div class="panel-body">
              <div class="wrap">
                <!-- Is active logout button? -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_active_logout_buttons" id="poly_utilities_is_active_logout_buttons" <?php echo (($is_active_logout_buttons == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_is_active_logout_buttons"><?php echo _l('poly_utilities_is_active_logout_buttons'); ?></label>
                  </div>
                </div>
                <!-- Is search menu? -->

                <!-- Is search menu? -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_search_menu" id="poly_utilities_is_search_menu" <?php echo (($is_search_menu == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_is_search_menu"><?php echo _l('poly_utilities_is_search_menu'); ?></label>
                  </div>
                </div>
                <!-- Is search menu? -->

                <!-- Is sticky menu? -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_topbar_is_sticky" id="poly_utilities_topbar_is_sticky" <?php echo (($is_sticky == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_topbar_is_sticky"><?php echo _l('poly_utilities_topbar_is_sticky'); ?></label>
                  </div>
                </div>
                <!-- Is sticky menu? -->

                <!-- Is sticky menu? -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_admin_breadcrumb" id="poly_utilities_is_admin_breadcrumb" <?php echo (($is_admin_breadcrumb == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_is_admin_breadcrumb"><?php echo _l('poly_utilities_is_admin_breadcrumb'); ?></label>
                  </div>
                </div>
                <!-- Is sticky menu? -->

                <!-- Is toggle sidebar menu? -->
                <?php
                $favicon = get_option('favicon');
                $favicon_path = (!empty($favicon)) ? base_url('uploads/company/' . $favicon) : '';
                ?>
                <div class="form-group tw-mb-0 relative poly-inline-flex">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_toggle_sidebar_menu" id="poly_utilities_is_toggle_sidebar_menu" <?php echo (($is_toggle_sidebar_menu == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_is_toggle_sidebar_menu"><?php echo _l('poly_utilities_is_toggle_sidebar_menu_icon_help'); ?></label>
                  </div>
                  <div class="poly-favicon"><a href="<?php echo base_url('admin/settings?group=general') ?>" target="_blank"><i class="fa fa-edit"></i></a><img class="poly-favicon-thumb" src="<?php echo $favicon_path ?>" /></div>
                </div>
                <!-- Is toggle sidebar menu? -->

                <!-- Enable Quick Access Menu? -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_quick_access_menu" id="poly_utilities_is_quick_access_menu" <?php echo (($is_quick_access_menu == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_is_quick_access_menu"><?php echo _l('poly_utilities_is_quick_access_menu'); ?></label>
                  </div>
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_quick_access_menu_icons" id="poly_utilities_is_quick_access_menu_icons" <?php echo (($is_quick_access_menu_icons == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_is_quick_access_menu_icons"><?php echo _l('poly_utilities_is_quick_access_menu_icons'); ?></label>
                    <i class="fa-regular fa-circle-question cursor" data-toggle="tooltip" data-title="<?php echo _l('poly_utilities_is_quick_access_menu_icons_message') ?>">&nbsp;</i>
                  </div>
                </div>
                <!-- Enable Quick Access Menu? -->

                <!-- Is Table of content? -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_is_table_of_content" id="poly_utilities_is_table_of_content" <?php echo (($is_table_of_content == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_is_table_of_content"><?php echo _l('poly_utilities_is_table_of_content'); ?></label>
                  </div>
                </div>
                <!-- Is Table of content? -->

                <!-- Enable custom JS -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_scripts" id="poly_utilities_enable_scripts" <?php echo (($is_active_scripts == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_enable_scripts"><?php echo _l('poly_utilities_enable_scripts'); ?></label>
                  </div>
                </div>
                <!-- Enable cusom JS -->

                <!-- Enable custom CSS -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_styles" id="poly_utilities_enable_styles" <?php echo (($is_active_styles == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_enable_styles"><?php echo _l('poly_utilities_enable_styles'); ?></label>
                  </div>
                </div>
                <!-- Enable custom CSS -->

                <!-- Active confirm delete note -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_note_confirm_delete" id="poly_utilities_enable_note_confirm_delete" <?php echo (($is_note_confirm_delete == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_enable_note_confirm_delete"><?php echo _l('poly_utilities_enable_note_confirm_delete'); ?></label>
                  </div>
                </div>
                <!-- Active confirm delete note -->

                <!-- Active operation actions -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_operation_functions" id="poly_utilities_enable_operation_functions" <?php echo (($is_operation_functions == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_enable_operation_functions"><?php echo _l('poly_utilities_enable_operation_functions'); ?></label>
                  </div>
                </div>
                <!-- Active operation actions -->

                <!-- Active scroll to top -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">

                    <input type="hidden" value="<?php echo $scroll_icon_button_right ?>" name="poly_utilities_scroll_icon_right" id="poly_utilities_scroll_icon_right" class="form-control poly_utilities_scroll_icon_right">
                    <input type="hidden" value="<?php echo $scroll_icon_button_bottom ?>" name="poly_utilities_scroll_icon_bottom" id="poly_utilities_scroll_icon_bottom" class="form-control poly_utilities_scroll_icon_bottom">

                    <input type="checkbox" name="poly_utilities_enable_scroll_to_top" id="poly_utilities_enable_scroll_to_top" <?php echo (($is_scroll_to_top == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_enable_scroll_to_top"><?php echo _l('poly_utilities_enable_scroll_to_top'); ?></label>

                    <div class="inline-block">
                      <div class="input-group">
                        <span class="btn btn-default poly-utilities-scroll-to-top-icon-select-position" style="padding:2px 4px">
                          <i class="fa-solid fa-arrows-to-circle fa-fw"></i>
                        </span>
                      </div>
                    </div>

                  </div>
                </div>
                <!-- Active scroll to top -->

                <!-- Data Table filters -->
                <div class="form-group tw-mb-0">
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_data_table_filters_column" id="poly_utilities_enable_data_table_filters_column" <?php echo (($is_data_table_filters_column == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_enable_data_table_filters_column"><?php echo _l('poly_utilities_enable_data_table_filters_column'); ?></label>
                  </div>
                  <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="poly_utilities_enable_button_data_table_filters_column" id="poly_utilities_enable_button_data_table_filters_column" <?php echo (($is_button_data_table_filters_column == 'true') ? ' checked' : '') . (!$is_edit ? ' disabled' : '') ?>>
                    <label for="poly_utilities_enable_button_data_table_filters_column"><?php echo _l('poly_utilities_enable_button_data_table_filters_column'); ?></label>
                  </div>
                </div>
                <!-- Data Table filters -->

              </div>

            </div>
          </div>
          <?php echo form_close(); ?>
        </div>
      </div>

      <!-- Roles -->
      <div id="polyApp" v-cloak>
        <div class="col-md-12<?php echo ($current_user_id != 1 ?  ' disabled' : '') ?>">
          <div class="tw-mb-2 sm:tw-mb-4">
            <?php echo form_open($this->uri->uri_string(), array('class' => 'poly_utilities_roles-form', 'id' => 'poly_utilities_roles-form', '@submit.prevent' => 'handleSubmit')); ?>
            <div class="panel_s">
              <div class="panel-body">
                <div class="wrap">
                  <div>
                    <h5 class="col-12"><?php echo _l('poly_utilities_users_can_access_modules'); ?></h5>
                    <div class="form-group poly-utilities-users-search">
                      <select id="users" style="width: 100%" class="select2 users form-control" name="users[]" multiple="multiple"></select>
                    </div>
                    <div class="poly-help-message">
                      <?php echo _l('poly_utilities_users_can_access_modules_message'); ?>
                    </div>
                  </div>
                  <div>
                    <h5 class="col-12"><?php echo _l('poly_utilities_users_can_access_custom_menu'); ?></h5>
                    <div class="form-group poly-utilities-users-search">
                      <select id="users_custom_menu" style="width: 100%" class="select2 users form-control" name="users[]" multiple="multiple"></select>
                    </div>
                    <div class="poly-help-message">
                      <?php echo _l('poly_utilities_users_can_access_custom_menu_message'); ?>
                    </div>
                  </div>
                </div>
              </div>
              <?php if ($current_user_id == 1) { ?>
                <div class="panel-footer">
                  <div class="tw-flex tw-items-center">
                    <button type="submit" class="btn btn-primary"><?php echo _l('poly_utilities_custom_menu_button_save'); ?></button>
                  </div>
                </div>
              <?php
              }
              ?>
            </div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
      <!-- //Roles -->

    </div>
  </div>
</div>
<?php init_tail(); ?>
<?php
echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/settings.js') . '"></script>';
echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/settings_vue.js') . '"></script>';
echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/select2/select2.min.js') . '"></script>';
