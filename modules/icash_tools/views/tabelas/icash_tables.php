<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <div class="panel-body">
        <?php if (has_permission('icash_tools', '', 'create')) { ?>
            <a href="<?php echo admin_url('icash_tools/icash_insert_tabelas'); ?>" class="btn btn-info pull-left">
                <?php echo _l('Adicionar Tabela'); ?>
            </a>
        <?php } ?>
        <div class="clearfix"></div>
        <hr class="hr-panel-heading"/>
        <?php render_datatable(array(
            _l('ID'),
            _l('Nome Tabela'),
            _l('Credenciadora'),
            _l('Parcelas'),
            _l('Ações')
        ), 'icash-tables'); ?>
    </div>
</div>
