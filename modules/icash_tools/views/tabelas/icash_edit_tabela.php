<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .btn-add-table {
        margin-top: auto;
        text-align: end;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('icash_edit_table'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open(admin_url('icash_tools/update_table/' . $table->id), ['id' => 'icash-edit-table-form']); ?>

                        <!-- Nome da Tabela -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-5">
                                    <label for="nome_tabela"><?php echo _l('table_name'); ?></label>
                                    <input
                                        type="text"
                                        id="nome_tabela"
                                        name="nome_tabela"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($table->nome_tabela, ENT_QUOTES, 'UTF-8'); ?>"
                                        required>
                                </div>
                                <div class="col-md-5">
                                    <label for="credenciadora"><?php echo _l('credenciadora'); ?></label>
                                    <input
                                        type="text"
                                        id="credenciadora"
                                        name="credenciadora"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($table->credenciadora, ENT_QUOTES, 'UTF-8'); ?>"
                                        required>
                                </div>
                            </div>

                        </div>

                        <div class="form-group">
                            <h3>Parcelamento</h3>
                        </div>

                        <!-- Parcelas (Repeatable) -->
                        <div id="parcelas-wrapper">
                            <?php
                            $parcelas = json_decode($table->parcelas, true);
                            if (!empty($parcelas)) {
                                foreach ($parcelas as $index => $parcela) {
                            ?>
                                    <div class="form-group parcelas-group">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <input type="number" name="parcelas[<?php echo $index; ?>][numero]" class="form-control" value="<?php echo htmlspecialchars($parcela['numero'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nº" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" name="parcelas[<?php echo $index; ?>][valor]" class="form-control" value="<?php echo htmlspecialchars($parcela['valor'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Valor" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-remove-parcela"><i class="fa fa-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>

                        <!-- Botão para Adicionar Nova Parcela -->
                        <div class="form-group">
                            <div class="row">
                            <div class="col-md-2"></div>
                            <div class="col-md-2"></div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success btn-add-parcela"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary"><?php echo _l('update_table'); ?></button>
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
        let parcelasIndex = <?php echo count($parcelas); ?>; // Começa com o número de parcelas existentes
        $('.btn-add-parcela').on('click', function() {
            let newParcela = `
                <div class="form-group parcelas-group">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="number" name="parcelas[${parcelasIndex}][numero]" class="form-control" placeholder="Nº" required>
                        </div>
                        <div class="col-md-5">
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

        appValidateForm($('#icash-edit-table-form'), {
            nome_tabela: 'required',
            'parcelas[0][numero]': 'required',
            'parcelas[0][valor]': 'required'
        });
    });
</script>
</body>

</html>