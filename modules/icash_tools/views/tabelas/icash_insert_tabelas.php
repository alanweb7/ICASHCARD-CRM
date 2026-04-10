<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('icash_insert_table'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open(admin_url('icash_tools/insert_table'), ['id' => 'icash-insert-table-form']); ?>

                        <!-- Nome da Tabela -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="nome_tabela"><?php echo _l('table_name'); ?></label>
                                    <input type="text" id="nome_tabela" name="nome_tabela" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="credenciadora"><?php echo _l('credenciadora'); ?></label>
                                    <input type="text" id="credenciadora" name="credenciadora" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <!-- Parcelas (Repeatable) -->
                        <div id="parcelas-wrapper">
                            <div class="form-group parcelas-group">
                                <label><?php echo _l('installments'); ?></label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="number" name="parcelas[0][numero]" class="form-control" placeholder="Nº" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="parcelas[0][valor]" class="form-control" placeholder="Valor" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-success btn-add-parcela"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    $(function() {
        let parcelasIndex = 1;
        $('.btn-add-parcela').on('click', function() {
            let newParcela = `
                <div class="form-group parcelas-group">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="number" name="parcelas[${parcelasIndex}][numero]" class="form-control" placeholder="Nº" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="parcelas[${parcelasIndex}][valor]" class="form-control" placeholder="Valor" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-remove-parcela"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            $('#parcelas-wrapper').append(newParcela);
            parcelasIndex++;
        });

        $(document).on('click', '.btn-remove-parcela', function() {
            $(this).closest('.parcelas-group').remove();
        });

        appValidateForm($('#icash-insert-table-form'), {
            nome_tabela: 'required',
            'parcelas[0][numero]': 'required',
            'parcelas[0][valor]': 'required'
        });
    });
</script>
</body>

</html>