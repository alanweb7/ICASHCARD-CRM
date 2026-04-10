<?php defined('BASEPATH') or exit('No direct script access allowed');

init_head();
$is_edit = (is_admin() || has_permission('poly_utilities_widgets_extend', '', 'edit'));

function get_widget_by_id($widget_data, $id)
{
    foreach ($widget_data as $widget_area) {
        if (isset($widget_area->id) && $widget_area->id === $id) {
            return $widget_area;
        }
    }
    return null;
}

function poly_utilities_get_widgets_area($widget_data, $area)
{
    if (empty($widget_data)) return '';

    $widget_object = get_widget_by_id($widget_data, $area);
    if (isset($widget_object->widgets) && $widget_object->widgets) {
        $widgets = $widget_object->widgets;
        if ($widgets) {
            $widgets_rest = [];
            foreach ($widgets as $key => $widget) {
                $widget_object = array();
                $widget_object['name'] = $widget->name;
                $widget_object['type'] = $widget->type;
                $obj_fields = $widget->fields;
                $obj_roles = $widget->roles;
                $roles = [];
                $fields = [];

                foreach ($obj_fields as $field) {
                    $fields[] = array(
                        'name' => $field->name,
                        'type' => $field->type,
                        'label' => $field->label ?? '',
                        'value' => $field->value
                    );
                }
                $widget_object['fields'] = $fields;

                foreach ($obj_roles as $role) {
                    if ($role->name == 'active') {
                        $roles[] = array(
                            'name' => $role->name,
                            'type' => $role->type,
                            'label' => $role->label ?? '',
                            'value' => $role->value
                        );
                    }
                }
                $widget_object['roles'] = $roles;

                $widgets_rest[] = $widget_object;
            }
            return $widgets_rest;
        }
    }
    return '';
}

function poly_utilities_render_widgets_area($widget_areas, $widget_data, $is_disabled = false)
{
    foreach ($widget_areas as $widget_block) {
        $widgets = poly_utilities_get_widgets_area($widget_data, $widget_block['id']);
        $is_default = ($widget_block['default'] === 'true') ? 'true' : 'false';
?>
        <ul class="poly-widgets-area">
            <li class="block" data-block-id="<?php echo $widget_block['id'] ?>" id="<?php echo $widget_block['id'] ?>" default="<?php echo $is_default ?>">
                <div class="widget cursor"><span class="header"><?php echo $widget_block['name'] ?></span><a href="#" class="tw-mr-1 text-muted toggle-widgets widget-item-blocks pull-right">
                        <i class="fa-solid fa-caret-up"></i></a>
                </div>
                <div class="widget-block poly-hide tw-mt-2.5" block-target="<?php echo $widget_block['id'] ?>">
                    <p class="poly-widget-description"><?php echo $widget_block['description'] ?></p>
                    <ul id="poly-widget-list-active" class="poly-widget-list active">
                        <?php
                        if (!empty($widgets)) {
                            foreach ($widgets as $key => $widget) {
                                poly_render_widget($widget, $is_disabled);
                            }
                        }
                        ?>
                    </ul>
                </div>
            </li>
        </ul>
    <?php
    }
}

function poly_utilities_avaible_widgets($is_disabled = false)
{
    foreach (poly_utilities_widget_helper::$avaible_widgets as $current_widget) {
        if ($current_widget['active'] === true) {
            poly_render_widget($current_widget, $is_disabled);
        }
    }
}
function poly_render_widget($current_widget, $is_disabled = false)
{
    ?>
    <li class="ui-widget-default" data-type="<?php echo $current_widget['type'] ?>" data-id="zzzz" data-name="<?php echo $current_widget['name'] ?>">
        <!-- Text widget -->
        <div class="widget"><span><?php echo $current_widget['name'] ?></span><a href="#" class="tw-mr-1 text-muted toggle-widgets widget-item-blocks pull-right"><i class="fa-solid fa-caret-up">&nbsp;</i></a>
            <?php
            if (is_admin() || has_permission('poly_utilities_widgets_extend', '', 'delete')) {
            ?>
                <a href="#" class="widget-delete text-muted pull-right"><i class="fas fa-trash">&nbsp;</i></a>
                <a href="#" class="widget-clone text-muted pull-right"><i class="fa-solid fa-clone fa-fw">&nbsp;</i></a>
            <?php
                if (!empty($current_widget['roles'])) {
                    foreach ($current_widget['roles'] as $role) {
                        echo '<label class="item-roles-label pull-right"><input class="item-roles-property" label="' . $role['label'] . '" type="' . $role['type'] . '" field="' . $role['name'] . '" ' . (($role['value'] === 'true') ? 'checked' : '') . $is_disabled . '/>&nbsp;</label>';
                    }
                }
            }
            ?>
        </div>
        <div class="widget-item-block poly-hide tw-mt-2.5" widget-target="zzzz">
            <div class="row<?php echo (($is_disabled == true) ? ' disabled' : '') ?>">
                <?php
                $fields = $current_widget['fields'];
                foreach ($fields as $field) {
                    switch ($field['type']) {
                        case 'text': {
                                echo  render_input('', $field['label'], $field['value'], 'text', array('placeholder' => 'Title', 'field' => $field['name'], 'label' => $field['label']), [], 'col-md-12', 'item-property');
                                break;
                            }
                        case 'textarea': {
                                echo render_textarea('', $field['label'], $field['value'], ['field' => $field['name'], 'type' => 'textarea', 'label' => $field['label'], 'id' => 'item-html-content'], [], 'col-md-12', 'item-property item-html-content');
                                break;
                            }
                        case 'checkbox': {
                                echo '<div class="col-md-12"><input class="item-property" label="' . $field['label'] . '" type="checkbox" field="' . $field['name'] . '" ' . (($field['value'] == true) ? 'checked' : '') . $is_disabled . '/> ' . $field['label'] . '</div>';
                                break;
                            }
                        case 'image': {
                                echo '<div class="col-md-12 tw-mb-2.5">
                                        <input class="item-property poly-hide" type="image" label="' . $field['label'] . '" field="' . $field['name'] . '" value="' . $field['value'] . '"/>
                                        <div class="poly-widget-image-add">' . $field['label'] . '</div>
                                    </div>
                                    <div class="col-md-12 tw-mb-2.5">Replace</div>';
                                break;
                            }
                    }
                }
                ?>
            </div>
            <?php
            if (is_admin() || has_permission('poly_utilities_widgets_extend', '', 'delete')) {
            ?>
                <div class="row">
                    <div class="col-md-12 poly_utilities_widgets_extend--action">
                        <a href="#" class="widget-delete"><?php echo _l('poly_utilities_widget_button_action_delete') ?></a> | <a href="#" class="widget-clone"><?php echo _l('poly_utilities_widget_button_action_clone') ?></a> | <a href="#" class="widget-close"><?php echo _l('poly_utilities_widget_button_action_done') ?></a>
                    </div>
                </div>
            <?php
            }
            if (is_admin() || has_permission('poly_utilities_widgets_extend', '', 'edit')) {
            ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn btn-primary pull-right poly-widgets-submit"><?php echo _l('poly_utilities_widget_button_action_save') ?></div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        <!-- END Text widget -->
    </li>
<?php
}
function poly_utilities_display_avaible_widgets($is_disabled = false)
{
?>
    <div id="left-column" class="poly-avaible-widgets col-md-3 tw-p-1">
        <h2 class="header"><?php echo _l('poly_utilities_widgets_avaible_header') ?></h2>
        <p class="poly-widget-description"><?php echo _l('poly_utilities_widgets_avaible_description') ?></p>
        <ul id="poly-widget-list" class="poly-widget-list<?php echo (($is_disabled == true) ? ' disabled' : '') ?>">
            <?php poly_utilities_avaible_widgets($is_disabled); ?>
        </ul>
    </div>
<?php
}
function poly_utilities_display_widgets_area($is_disabled = false)
{
    $widget_objects = json_decode(clear_textarea_breaks(get_option(POLY_WIDGETS)));
    $widget_blocks = hooks()->apply_filters('poly_utilities_widgets_init', poly_utilities_widget_helper::$widget_blocks);
    if (empty($widget_blocks)) return '';

    $array_length = count($widget_blocks);
    $part_size = ceil($array_length / 3);
    $first_column = array_slice($widget_blocks, 0, $part_size);
    $second_column = array_slice($widget_blocks, $part_size, $part_size);
    $third_column = array_slice($widget_blocks, $part_size * 2);

?>
    <div id="right-column" class="col-md-9 tw-p-1">
        <div class="col-md-4 tw-p-1">
            <?php
            poly_utilities_render_widgets_area($first_column, $widget_objects, $is_disabled);
            ?>
        </div>
        <div class="col-md-4 tw-p-1">
            <?php
            poly_utilities_render_widgets_area($second_column, $widget_objects, $is_disabled);
            ?>
        </div>
        <div class="col-md-4 tw-p-1">
            <?php
            poly_utilities_render_widgets_area($third_column, $widget_objects, $is_disabled);
            ?>
        </div>
    </div>
<?php
}
?>

<div id="wrapper">
    <div class="content">
        <div class="row poly_utilities_quick_access_menu_manage">
            <div class="col-md-12 tw-p-1">
                <?php
                $is_disabled = (is_admin() || has_permission('poly_utilities_widgets_extend', '', 'edit') ? '' : ' disabled');
                poly_utilities_display_avaible_widgets($is_disabled);
                poly_utilities_display_widgets_area($is_disabled);
                ?>
            </div>
        </div>
    </div>
</div>

<?php
init_tail();
echo '<script src="' . poly_utilities_common_helper::get_assets('modules/poly_utilities/dist/assets/js/lib/sortable/1.15.0/sortable.min.js') . '"></script>';
echo '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/admin/widgets.js') . '"></script>';
