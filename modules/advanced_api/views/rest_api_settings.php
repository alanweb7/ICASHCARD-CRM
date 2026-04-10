<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-6">
        <?php render_yes_no_option('allow_register_api', 'settings_allow_register_api', 'settings_allow_register_api_help'); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="settings[api_name]" class="control-label">
                <?php echo _l('API name'); ?> <span class="text-danger">*</span>
            </label>
            <input type="text" name="settings[api_name]" class="form-control" required value="<?php echo get_option('api_name'); ?>">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="settings[apikey_token]" class="control-label">
                <?php echo _l('API Token'); ?> <span class="text-danger">*</span>
            </label>
            <textarea name="settings[apikey_token]" class="form-control" required rows="3"><?php echo get_option('apikey_token'); ?></textarea>
        </div>
        <div class="alert alert-info mt-2" role="alert">
            <i class="fa fa-info-circle"></i>
            O token deve ser enviado no cabeçalho da requisição (<strong>Authorization</strong>).
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="settings[autentique_endpoint_secret]" class="control-label">
                <?php echo _l('Endpoint Secret (Autentique)'); ?>
            </label>
            <input type="text" name="settings[autentique_endpoint_secret]" class="form-control" value="<?php echo get_option('autentique_endpoint_secret'); ?>">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="settings[autentique_api_token]" class="control-label">
                <?php echo _l('Autentique API Token'); ?>
            </label>
            <input type="text" name="settings[autentique_api_token]" class="form-control" value="<?php echo get_option('autentique_api_token'); ?>">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="settings[autentique_email]" class="control-label">
                <?php echo _l('Autentique email (ADM)'); ?>
            </label>
            <input type="text" name="settings[autentique_email]" class="form-control" value="<?php echo get_option('autentique_email'); ?>">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="settings[webhook_notification]" class="control-label">
                <?php echo _l('Webhook Notification'); ?>
            </label>
            <input type="text" name="settings[webhook_notification]" class="form-control" value="<?php echo get_option('webhook_notification'); ?>">
        </div>
    </div>
</div>