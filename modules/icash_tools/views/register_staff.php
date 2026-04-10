<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('Registro de Atendente'); ?></h4>
                        <hr>
                        <?php echo form_open('icash_tools/register_staff/submit', ['id' => 'register_staff_form']); ?>
                        <div class="form-group">
                            <label for="fullname"><?php echo _l('Nome completo'); ?></label>
                            <input type="text" id="fullname" name="fullname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="phone"><?php echo _l('Telefone'); ?></label>
                            <input type="text" id="phone" name="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email"><?php echo _l('Email'); ?></label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password"><?php echo _l('Senha'); ?></label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo _l('Registrar'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
