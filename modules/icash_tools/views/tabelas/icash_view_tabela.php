<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .col-installment-number {
        width: 150px;
        /* Ajuste o valor conforme necessário */
    }
    .table-bordered {
        width: 50%;
        /* Ajuste o valor conforme necessário */
    }
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold"><?php echo _l('Detalhes da Tabela'); ?></h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <strong><?php echo _l('Table Name'); ?>:</strong>
                                    <p><?php echo htmlspecialchars($table->nome_tabela, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="mb-3">
                                    <strong><?php echo _l('Credenciadora'); ?>:</strong>
                                    <p><?php echo htmlspecialchars($table->credenciadora, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="mb-3">
                                    <strong><?php echo _l('Empresa'); ?>:</strong>
                                    <p><?php echo htmlspecialchars($table->empresa, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="mb-3">
                                    <strong><?php echo _l('Parcelas'); ?>:</strong>
                                    <?php
                                    $parcelas = json_decode($table->parcelas, true);
                                    if (!empty($parcelas)) {
                                    ?>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="col-installment-number"><?php echo _l('Installment_Number'); ?></th>
                                                    <th><?php echo _l('value'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($parcelas as $parcela) { ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($parcela['numero'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($parcela['valor'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>

                                    <?php
                                    } else {
                                        echo '<p>' . _l('No installments available') . '</p>';
                                    }
                                    ?>
                                </div>
                                <div class="mb-3">
                                    <a href="<?php echo admin_url('icash_tools'); ?>" class="btn btn-default">
                                        <?php echo _l('Voltar para Tabelas'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>