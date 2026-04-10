<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" id="reset-css" href="https://painel.icashcard.com.br/modules/poly_utilities/uploads/css/css_corbans_propostas.css?v=3.1.6">
<script src="https://painel.icashcard.com.br/modules/icash_tools/assets/js/propostas_funcoes_tela_corbans.js?ver=12.7.140"></script>
<style>
    .badget_info {
        padding: 5px 15px;
        background-color: #d5d5d5;
        border-radius: 50px;
    }

    .info_account {
        float: right;
    }

    .custom-width {
        width: 200px !important;
        padding-left: 10px;
        padding-right: 10px;
    }

    .status_p {
        display: block !important;
        text-align: center !important;
        width: 150px !important;
        align-items: center;
        border-radius: .375rem;
        display: inline-flex;
        padding: .45rem .5rem;
    }

    .bg-secondary {
        background-color: rgb(109, 109, 109);
        color: rgb(255, 255, 255);
    }

    .link-content {
        padding: 3px 5px;
        margin-top: 5px;
    }

    .col_name {
        min-width: 150px !important;
    }

    .no-click {
        pointer-events: none;
        /* Desativa cliques do mouse */
        cursor: default;
        /* Mantém o cursor padrão */
    }

    .document-grid {
        display: flex;
        flex-wrap: nowrap;
        flex-direction: row;
    }

    /* check box em linha */
    #parameterForm label {
        display: inline-block;
        margin-right: 15px;
    }

    /* estilo do link gerado */

    #copyLink {
        cursor: pointer;
        color: #007BFF;
        font-size: 16px;
        display: none;
    }

    #copyLink:hover {
        text-decoration: underline;
    }

    .document-item {}

    .custom-link {
        color: blue !important;
        /* Cor do texto */
        text-decoration: underline !important;
        /* Linha embaixo do texto */
        cursor: pointer !important;
        /* Cursor de mão ao passar o mouse */
    }

    .custom-link:hover {
        color: darkblue;
        /* Cor ao passar o mouse */
    }

    .table-responsive {
        max-height: 400px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('Propostas'); ?></h4>
                        <hr>
                        <table class="table dt-table dt-proposals">
                            <thead>
                                <tr>
                                    <?php
                                    if (staff_can('delete', 'corban_proposals') && is_admin()) {
                                        echo "<th>#Ação</th>";
                                    }
                                    ?>

                                    <th>#COD</th>
                                    <th>Produto</th>
                                    <?php
                                    if (staff_can('view_col_staff', 'corban_proposals')) {
                                        echo "<th>Corretor</th>";
                                    }
                                    ?>
                                    <?php
                                    if (staff_can('view_col_atd', 'corban_proposals')) {
                                        echo "<th>Digitador</th>";
                                    }
                                    ?>
                                    <th>Data de Criação</th>
                                    <th class="custom-width">Status da Proposta</th>
                                    <th>Contrato</th>
                                    <th style="width: 200px !important;">Cliente</th>
                                    <th>CPF</th>
                                    <th>Tabela</th>
                                    <th>Prazo</th>
                                    <th>Vl. Parcela</th>
                                    <th>Total Liq.</th>
                                    <th>Total Bruto</th>
                                    <th>DOC</th>

                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                foreach ($propostas as $proposta):
                                ?>

                                    <tr>
                                        <?php
                                        if (staff_can('delete', 'corban_proposals') && is_admin()) {
                                        ?>
                                            <td><span class="btn" style="color: red;" onclick="onDeleteProposal(<?= $proposta['id'] ?>)"><i class="fa-regular fa-trash-can"></i></span></td>
                                        <?php
                                        }
                                        ?>
                                        <td><?php echo $proposta['id']; ?></td>
                                        <td><?php echo $proposta['subject']; ?></td>
                                        <?php if (staff_can('view_col_staff', 'corban_proposals')) { ?>
                                            <td>
                                                <div class="col_name"><?php echo $proposta['staff_fullname']; ?></div>
                                            </td>
                                        <?php } ?>
                                        <?php if (staff_can('view_col_atd', 'corban_proposals')) { ?>
                                            <td>
                                                <div class="col_name"><?php echo $proposta['atendente']; ?></div>
                                            </td>
                                        <?php } ?>
                                        <td><?php echo $proposta['datecreated']; ?></td>
                                        <td style="width: 280px !important;">
                                            <?php

                                            if ($proposta['custom_fields']['Etapa']) {
                                                $status = $proposta['custom_fields']['Etapa'];
                                                switch ($status) {
                                                    case 'PEN - Envio Documento':
                                                    case 'PEN - Doc. Ilegível':
                                                    case 'Em análise documental':
                                                    case 'Liberar Crédito':
                                                        $class = "warning";
                                                        break;

                                                    case 'Reprova documental':
                                                    case 'Link Pag. Reprovado':
                                                    case 'Cancelada':
                                                    case 'Descartado':
                                                        $class = "danger";
                                                        break;

                                                    case 'Link Pag. Enviado':
                                                    case 'Link Pag. Aprovado':
                                                    case 'Crédito Enviado':
                                                        $class = "info";
                                                        break;

                                                    case 'Aguardando formalização':
                                                    case 'Em análise formalização':
                                                        $class = "secondary";
                                                        break;

                                                    case 'Operação Finalizada':
                                                        $class = "success";
                                                        break;

                                                    default:
                                                        $class = "primary";
                                                        break;
                                                }


                                                if (staff_can('contract_click_link', 'corban_proposals')) {

                                            ?>
                                                    <button type="button" style="width:170px;" class="btn btn-<?php echo $class; ?>" data-toggle="modal" data-target="#editProposalStatusModal" data-proposal-id="<?php echo $proposta['id'] ?>">
                                                        <?php echo $proposta['custom_fields']['Etapa']; ?>
                                                    </button>

                                                <?php

                                                } else {
                                                ?>
                                                    <button type="button" style="width:170px;pointer-events: none;" class="btn btn-<?php echo $class; ?>" data-proposal-id="<?php echo $proposta['id'] ?>">
                                                        <?php echo $proposta['custom_fields']['Etapa']; ?>
                                                    </button>

                                                <?php
                                                }
                                                ?>

                                            <?php } ?>
                                        </td>
                                        <td><?php echo $proposta['contrato']['content']; ?></td>
                                        <td>
                                            <div class="col_name"><?php echo $proposta['customer']; ?></div>
                                        </td>
                                        <td><?php echo $proposta['custom_fields']['CPF']; ?></td>
                                        <td><?php echo $proposta['custom_fields']['Tabela']; ?></td>
                                        <td><?php echo $proposta['custom_fields']['Parcelas']; ?></td>
                                        <td>R$ <?php echo $proposta['custom_fields']['Valor Parcela']; ?></td>
                                        <td>R$ <?php echo $proposta['custom_fields']['Total Líq.']; ?></td>
                                        <td>R$ <?php echo $proposta['custom_fields']['Total Bruto']; ?></td>
                                        <td><?php echo $proposta['custom_fields']['DOC']; ?></td>


                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="info_account">
                            <!-- <p><span class="badget_info"><b>Meu ID:</b> <?php echo $staff_info['id']; ?></span></p> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="commission_detail_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="content-modal">
                </div>
                <h5>Marque para solicitar o envio:</h5>
                <form id="parameterForm">
                    <label>
                        <input type="checkbox" name="rg" value="f"> RG Frente
                    </label>
                    <label>
                        <input type="checkbox" name="rg" value="v"> RG Verso
                    </label>
                    <label>
                        <input type="checkbox" name="card" value="f"> Cartão Frente
                    </label>
                    <label>
                        <input type="checkbox" name="card" value="v"> Cartão Verso
                    </label>
                    <label>
                        <input type="checkbox" name="selfie" value="1"> Selfie
                    </label>
                </form>
                <p><strong>Link Gerado:</strong></p>
                <!-- <p id="generatedLink"></p> -->
                <p id="containerCpLink">
                    <span id="copyLink" data-link="https://example.com/link-gerado">
                        Copiar Link <i class="fa-solid fa-copy"></i>
                    </span>
                </p>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="info_data" id="info_data">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="save_modal" disabled>Salvar mudanças</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editProposalStatusModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Status da Proposta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editProposalStatusForm">
                    <div class="form-group">
                        <label for="proposalId">ID da Proposta</label>
                        <input type="text" value="76" id="proposalId" name="proposal_id" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="proposalStatus">Novo Status</label>
                        <select id="proposalStatus" name="status" class="form-control" required>
                            <option value="" disabled selected>Selecione um status</option>
                            <option value="1"> PEN - Envio Documento </option>
                            <option value="20"> PEN - Doc. Ilegível </option>
                            <option value="21"> Em análise documental </option>
                            <option value="22"> Reprova documental </option>
                            <option value="23"> Link Pag. Enviado </option>
                            <option value="24"> Link Pag. Aprovado </option>
                            <option value="25"> Link Pag. Reprovado </option>
                            <option value="26"> Aguardando formalização </option>
                            <option value="27"> Em análise formalização </option>
                            <option value="28"> Liberar Crédito </option>
                            <option value="29"> Crédito Enviado </option>
                            <option value="30"> Descartado </option>
                            <option value="2"> Cancelada </option>
                            <option value="3"> Operação Finaliza </option>

                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="saveProposalStatus" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="clientDetailModal" tabindex="-1" role="dialog" aria-labelledby="clientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="clientModalLabel">Detalhes do Cliente</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="content-client-modal">
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="info_data" id="info_data">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <!-- <button type="button" class="btn btn-primary" id="save_modal" disabled>Salvar mudanças</button> -->
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>