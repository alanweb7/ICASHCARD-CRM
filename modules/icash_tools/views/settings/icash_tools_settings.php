<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="settings[proposal_step_status]" class="control-label">
                <?php echo _l('Etapas da Proposta'); ?> <span class="text-danger">*</span>
            </label>
            <textarea name="settings[proposal_step_status]" class="form-control" required rows="15"><?php echo get_option('proposal_step_status'); ?></textarea>
        </div>
        <div class="alert alert-info mt-2" role="alert">
            <i class="fa fa-info-circle"></i>
            Colocar as etapas das propostas em ordem de evolução uma por linha.
        </div>
    </div>
</div>
