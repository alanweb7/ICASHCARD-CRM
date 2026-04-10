<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<script src="https://painel.icashcard.com.br/modules/poly_utilities/uploads/js/icash_tools_custom_js_functions.js?ver=12.7.8"></script>

<style>
    .badget_info {
        padding: 5px 15px;
        background-color: #d5d5d5;
        border-radius: 50px;
    }

    .info_account {
        float: right;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('Tabela de Comissões'); ?></h4>
                        <hr>
                        <table class="table dt-table">
                            <thead>
                                <tr>
                                    <!-- <th>#</th> -->
                                    <th><?php echo _l('Nome'); ?></th>
                                    <?php
                                    if (staff_can('view',  'corban_commissions')) {
                                        echo '<th style="text-align: center;">Comissão</th>';
                                    }
                                    ?>

                                    <th style="text-align: center;"><?php echo _l('Copiar Link'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comissoes as $comissao): ?>
                                    <tr>
                                        <!-- <td><?php echo $comissao['id']; ?></td> -->
                                        <td><?php echo $comissao['nome']; ?></td>
                                        <?php if (staff_can('view',  'corban_commissions')) { ?>
                                            <td style="text-align: center;"><?php echo $comissao['comissao']; ?></td>
                                        <?php
                                        }
                                        ?>

                                        <td style="text-align: center;" class="link_container">
                                            <?php echo $comissao['link']; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="info_account">
                            <p><span class="badget_info"><b>Meu ID:</b> <?php echo $staff_info['id']; ?></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>