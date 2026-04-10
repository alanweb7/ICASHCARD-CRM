<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$version = '12.7.846';
if ($this->uri->segment(3) == 'listar_propostas') {
    // <!-- Bootstrap JS -->
    echo '<link href="' . module_dir_url('icash_tools', 'assets/css/icash-tools-proposals-styles.css?v=' . $version) . '" rel="stylesheet">';
    // echo '<link href="' . module_dir_url('icash_tools', 'assets/css/icash-tools-list-styles.css?v=' . $version) . '" rel="stylesheet">';
}
?>

<!-- <link rel="stylesheet" type="text/css" id="reset-css" href="https://painel.icashcard.com.br/modules/poly_utilities/uploads/css/css_corbans_propostas.css?v=3.1.8"> -->
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- FILTROS -->

                        <?php echo form_open(admin_url('icash_tools/listar_propostas'), ['method' => 'get']); ?>
                        <div class="row">

                            <?php

                            if (is_admin()) {
                                $dateInitial = $dateFinal = date('d-m-Y');
                            } else {
                                $dateInitial = date('d-m-Y', strtotime('-30 days'));
                                $dateFinal   = date('d-m-Y');
                            }

                            ?>
                            <div class="col-md-3">
                                <?= render_date_input('data_inicial', 'Data Inicial', $this->input->get('data_inicial') ?: $dateInitial); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_date_input('data_final', 'Data Final', $this->input->get('data_final') ?: $dateFinal) ?>
                            </div>


                            <div class="col-md-3">
                                <?php
                                $status_options = [
                                    [
                                        'id' => '',
                                        'name' => 'Todos os Status'
                                    ],
                                    ['id' => 'PEN - Envio Documento',      'name' => 'PEN - Envio Documento'],
                                    ['id' => 'PEN - Doc. Ilegível',        'name' => 'PEN - Doc. Ilegível'],
                                    ['id' => 'Em análise documental',      'name' => 'Em análise documental'],
                                    ['id' => 'Reprova documental',         'name' => 'Reprova documental'],
                                    ['id' => 'Link Pag. Enviado',          'name' => 'Link Pag. Enviado'],
                                    ['id' => 'Link Pag. Aprovado',         'name' => 'Link Pag. Aprovado'],
                                    ['id' => 'Link Pag. Reprovado',        'name' => 'Link Pag. Reprovado'],
                                    ['id' => 'Aguardando formalização',    'name' => 'Aguardando formalização'],
                                    ['id' => 'Em análise formalização',    'name' => 'Em análise formalização'],
                                    ['id' => 'Liberar Crédito',            'name' => 'Liberar Crédito'],
                                    ['id' => 'Crédito Enviado',            'name' => 'Crédito Enviado'],
                                    ['id' => 'Pagamento Devolvido',        'name' => 'Pagamento Devolvido'],
                                    ['id' => 'Cancelada',                  'name' => 'Cancelada'],
                                ];

                                echo render_select(
                                    'status',             // name do campo
                                    $status_options,      // array de opções
                                    ['id', 'name'],       // campos de valor e texto
                                    'Status',             // label
                                    $this->input->get('status'), // valor selecionado
                                    [],                   // atributos extras
                                    [],                   // wrapper attributes
                                    'no-mbot'             // class opcional para margens
                                );
                                ?>

                            </div>



                            <div class="col-md-3" style="margin-top: 25px;">
                                <button class="btn btn-info" type="submit">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <a href="<?= admin_url('icash_tools/listar_propostas'); ?>" class="btn btn-default">
                                    LIMPAR BUSCA
                                </a>
                            </div>
                        </div>

                        <?php echo form_close(); ?>

                        <hr>

                        <!-- FILTROS -->
                        <h4 class="no-margin"><?php echo _l('Propostas'); ?></h4>
                        <hr>
                        <table class="table dt-table dt-proposals">
                            <thead>
                                <tr>

                                    <?php
                                    if (staff_can('delete', 'corban_proposals') && is_admin()) {
                                        echo "<th>#Ação</th>";
                                    } else {
                                        echo '<th style="display:none"></th>';
                                    }
                                    ?>

                                    <th>#COD</th>
                                    <th>NSU</th>
                                    <th>Produto</th>
                                    <?php
                                    if (staff_can('view_col_staff', 'corban_proposals')) {
                                        echo "<th>CORBAN</th>";
                                    }
                                    ?>
                                    <?php
                                    if (staff_can('view_col_atd', 'corban_proposals')) {
                                        echo "<th>Digitador</th>";
                                    }
                                    ?>
                                    <th>Data de Criação</th>
                                    <th>Data L. Crédito</th>
                                    <th style="width: 200px !important;">Cliente</th>
                                    <th style="width: 200px !important;">CPF</th>
                                    <th class="custom-width">Status da Proposta</th>
                                    <th class="export_always">Telefone</th>
                                    <th class="export_always">Tabela</th>
                                    <th class="export_always">Prazo</th>
                                    <th class="export_always">Valor Líquido</th>
                                    <th class="export_always">Valor Parcela</th>
                                    <th style="text-align: center;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php foreach ($propostas as $proposta): ?>
                                    <tr id="proposta-<?= $proposta['id'] ?>">
                                        <?php
                                        if (staff_can('delete', 'corban_proposals') && is_admin()) {
                                        ?>
                                            <td><span class="btn" style="color: red;" onclick="onDeleteProposal(<?= $proposta['id'] ?>)"><i class="fa-regular fa-trash-can"></i></span></td>
                                        <?php
                                        } else {
                                            echo '<td style="display:none"></td>';
                                        }
                                        ?>
                                        <td><?php echo $proposta['id']; ?></td>
                                        <td><?php echo $proposta['nsu']; ?></td>
                                        <td><?php echo $proposta['subject']; ?></td>
                                        <?php if (staff_can('view_col_staff', 'corban_proposals')) { ?>
                                            <td>
                                                <div class="col_name"><?php echo $proposta['staff_fullname']; ?></div>
                                            </td>
                                        <?php } ?>
                                        <?php if (staff_can('view_col_atd', 'corban_proposals')) { ?>
                                            <td>
                                                <div class="col_name"><?php echo mb_strtoupper($proposta['atendente'], 'UTF-8'); ?></div>
                                            </td>
                                        <?php } ?>
                                        <td><?php echo $proposta['datecreated']; ?></td>
                                        <td><?php echo _d($proposta['payment_from_manager']); ?></td>
                                        <td>
                                            <div class="col_name"><?php echo mb_strtoupper($proposta['customer_name'], 'UTF-8'); ?></div>
                                        </td>
                                        <td>
                                            <div class="col_name"> <?php echo $proposta['custom_fields']['CPF']; ?></div>
                                        </td>
                                        <td style="text-align: center !important;">
                                            <?php echo $proposta['custom_fields']['Etapa']; ?>
                                        </td>
                                        <!-- COLUNAS HIDDEN -->
                                        <td class="export_always"><?php echo $proposta['telefone']; ?></td>
                                        <td class="export_always"><?php echo $proposta['tabela']; ?></td>
                                        <td class="export_always"><?php echo $proposta['prazo']; ?></td>
                                        <td class="export_always"><?php echo $proposta['vl_liq']; ?></td>
                                        <td class="export_always"><?php echo $proposta['vl_parcela']; ?></td>

                                        <td class="proposal-actions">


                                            <!-- BOTOES DE AÇÃO -->


                                            <?php if (staff_can('view_details', 'corban_proposals')) { ?>
                                                <button type="button" class="btn btn-primary btn-floating" data-mdb-ripple-init>
                                                    <?php echo $proposta['details']; ?>
                                                </button>
                                            <?php } ?>

                                            <?php echo $proposta['contrato']['content']; ?>

                                            <?php echo $proposta['custom_fields']['DOC']; ?>


                                            <?php if ($_GET['download']) {

                                            ?>
                                                <div class="proposal-item"><?php echo $proposta['download']; ?></div>

                                            <?php } ?>
                                        </td>

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
<div id="loader-overlay">
    <div id="fountainG">
        <div id="fountainG_1" class="fountainG"></div>
        <div id="fountainG_2" class="fountainG"></div>
        <div id="fountainG_3" class="fountainG"></div>
        <div id="fountainG_4" class="fountainG"></div>
        <div id="fountainG_5" class="fountainG"></div>
        <div id="fountainG_6" class="fountainG"></div>
        <div id="fountainG_7" class="fountainG"></div>
        <div id="fountainG_8" class="fountainG"></div>
    </div>
</div>

<div class="modal fade" id="commission_detail_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="content-modal">
                </div>
                <div id="info_payment" style="padding: 10px; background-color: #e9e9e9; border-radius: 7px; margin-top: 10px; display: none;">
                </div>
                <form id="parameterForm">
                    <h5>Marque para solicitar o envio:</h5>
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
                <div id="link-gerado" style="display: none;">
                    <div class="col-md-12">
                        <label for="motive_info">Pendência</label>
                        <input type="text" id="motive_info" name="motive_info" class="form-control" required>
                        <small id="linkWarning" class="text-danger" style="display:none;">Pendência precisa ter pelo menos 10 caracteres.</small>
                        <p></p>
                        <label><strong>Link Gerado:</strong></label>
                        <input type="text" id="inputLink" name="inputLink" class="form-control" readonly>
                    </div>
                    <div class="col-md-12">
                        <p></p>
                        <button type="button" class="btn btn-success" id="send_link_document" onclick="onSendLinkUpdateDocuments(); return false;" disabled>Enviar Link de Documentos</button>
                    </div>

                    <!-- <p id="generatedLink"></p> -->
                    <p id="containerCpLink">
                        <!-- <span id="copyLink" data-link="https://example.com/link-gerado">
                            Copiar Link <i class="fa-solid fa-copy"></i>
                        </span> -->
                    </p>
                </div>
                <div id="wait_icon" style="text-align: center; display:none;">
                    <img src="<?php echo module_dir_url('icash_tools', 'assets/images/direct_notificatios.gif'); ?>" alt="" class="icon_wait" width="150px">
                </div>
                <div id="contract_icon" style="text-align: center; display:none;">
                    <img src="<?php echo module_dir_url('icash_tools', 'assets/images/contract_generator.gif'); ?>" alt="" class="icon_wait" width="150px">
                </div>

            </div>
            <div class="modal-footer">
                <hr class="hr-panel-heading" />
                <div class="info_content" id="info_content">

                </div>
                <input type="hidden" name="info_data" id="info_data">
                <?php if (staff_can('approve_doc', 'corban_proposals')) { ?>

                    <div class="panel-body" id="informations_sub">
                        <!-- ENVIO DE IMAGENS -->
                        <!-- <div class="form-group">
                            <label for="file">Enviar arquivo</label>
                            <input type="file" name="file" required>
                            <input type="hidden" name="action" value="replace">
                            <input type="hidden" name="id" id="replaceImageId">
                            <input type="hidden" name="type" id="replaceImageType">
                        </div> -->
                        <!------------------------------------------------------------------------->
                        </hr>

                        <!-- Nome da Tabela -->
                        <div class="form-group" class="form-check-actions">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group action_document" id="motivo_recusa_div">
                                        <label for="motivo_recusa">Motivo da Recusa</label>
                                        <input type="text" id="motivo_recusa" name="motivo_recusa" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check" id="update_doc_div">
                                        <input type="checkbox" name="update_doc" id="update_doc" value="1" class="form-check-input">
                                        <label class="form-check-label" for="update_doc">Atualizar documentos</label>
                                    </div>
                                    <div class="form-check" id="recusar_doc_div">
                                        <input type="checkbox" name="recusar_doc" id="recusar_doc" value="1" class="form-check-input">
                                        <label class="form-check-label" for="recusar_doc">Recusar documentos</label>
                                    </div>
                                    <div class="form-check" id="aprove_doc_div">
                                        <input type="checkbox" name="aprove_doc" id="aprove_doc" value="1" class="form-check-input">
                                        <label class="form-check-label" for="aprove_doc">Gerar Link / Contrato</label>
                                    </div>
                                </div>
                            </div>


                        </div>

                        <button type="button" class="btn btn-primary action_document" id="save_info" onclick="onAproveDocumentsInProposal(); return false;">Salvar</button>
                        <button type="button" class="btn btn-primary action_document" id="generate_contract" onclick="onGenerateContract(); return false;">Gerar Contrato</button>
                        <button type="button" class="btn btn-primary action_document" id="unsigned_contract" onclick="onUnsignedContract(); return false;">Remover Assinatura</button>
                        <button type="button" class="btn btn-success action_document" id="link_payment" onclick="onGenerateLinkPayment(); return false;">Gerar Link de Pag.</button>


                    </div>
                    <div id="wait_text" style="display: none;">
                        Processando, aguarde...
                    </div>

                <?php } ?>

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
                    <?php
                    if (!staff_can('status_edit_adm', 'corban_proposals') && !staff_can('status_edit', 'corban_proposals')) {
                        $disabled = "disabled";
                    }
                    ?>
                    <div class="form-group">
                        <label for="proposalStatus">Novo Status</label>
                        <select id="proposalStatus" name="status" class="form-control" required>
                            <option value="" disabled selected>Selecione um status</option>
                            <option value="1" disabled> PEN - Envio Documento </option>
                            <!-- <option value="20" > PEN - Doc. Ilegível </option> -->
                            <option value="21" <?= $disabled ?>> Em análise documental </option>
                            <option value="22" <?= $disabled ?>> Reprova documental </option>
                            <option value="23" <?= $disabled ?>> Link Pag. Enviado </option>
                            <option value="24" <?= $disabled ?>> Link Pag. Aprovado </option>
                            <option value="25" <?= $disabled ?>> Link Pag. Reprovado </option>
                            <option value="26" <?= $disabled ?>> Aguardando formalização </option>
                            <option value="27" <?= $disabled ?>> Em análise formalização </option>
                            <option value="28"> Liberar Crédito </option>
                            <option value="29" <?= $disabled ?>> Crédito Enviado </option>
                            <option value="30" <?= $disabled ?>> Aguardando Confirmação</option>
                            <option value="2"> Cancelada </option>
                            <!-- <option value="3"> Operação Finalizada </option> -->

                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancelModal" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="saveProposalStatus" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="clientDetailModal" tabindex="-1" role="dialog" aria-labelledby="clientDetailModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="title_proposal_id">Detalhes da Proposta (#---)</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="content-client-modal">

                    <div id="fountainG" style="display: block;">
                        <div id="fountainG_1" class="fountainG"></div>
                        <div id="fountainG_2" class="fountainG"></div>
                        <div id="fountainG_3" class="fountainG"></div>
                        <div id="fountainG_4" class="fountainG"></div>
                        <div id="fountainG_5" class="fountainG"></div>
                        <div id="fountainG_6" class="fountainG"></div>
                        <div id="fountainG_7" class="fountainG"></div>
                        <div id="fountainG_8" class="fountainG"></div>
                    </div>

                </div>

                <!-- HISTÓRICO DA PROPOSTA -->
                <div id="history-proposal">
                    Carregando histórico...
                </div>

                <hr style="border-top: 1px solid #d5d5d5;">
                <div id="reprove_proposal" class="flex flex-col gap-4 mt-4">

                    <?php
                    if (staff_can('edit', 'super_manager') && !is_admin()) {

                    ?>
                        <label for="observation" class="font-medium text-gray-700">
                            Motivo de recusa:
                        </label>

                        <input
                            type="text"
                            name="observation"
                            id="observation"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                            placeholder="Adicione sua observação">
                        <span style="color:red;"> Para enviar uma recusa, preencha este campo</span>

                    <?php
                    }

                    ?>

                </div>

            </div>


            <div class="modal-footer">
                <input type="hidden" name="info_data" id="info_data">

                <?php
                if (staff_can('edit', 'corban_proposals')) {
                ?>
                    <button type="button" class="btn btn-primary" id="edit-proposal" onclick="onEditProposal(); return false;">Editar</button>
                <?php
                }

                ?>

                <?php
                if (staff_can('edit', 'super_manager') && !is_admin()) {

                ?>
                    <button type="button" class="btn btn-danger" id="no-payment" onclick="onPaymentProposal(false); return false;">Pagamento Devolvido</button>
                    <button type="button" class="btn btn-success" id="payment" onclick="onPaymentProposal(true); return false;">Crédito Enviado</button>
                <?php
                }

                ?>

                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <!-- <button type="button" class="btn btn-primary" id="save_modal" disabled>Salvar mudanças</button> -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de acao geral -->
<div class="modal fade" id="generalModalEdit" tabindex="-1" role="dialog" aria-labelledby="generalModalEdit" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-title"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="content-edit-data">Carregando...</div>
                <hr style="border-top: 1px solid #d5d5d5;">
            </div>
            <div class="modal-footer">
                <input type="hidden" name="info_data" id="info_data">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<script src="<?= module_dir_url('icash_tools', 'assets/js/propostas_funcoes_tela_corbans.js?ver=' . $version) ?>"></script>


<script>
    console.log('Variáves:');
    const can_approve_doc = window.customPerfexVars.can_approve_doc;
    console.log(window.customPerfexVars.user_name);
    console.log("Ver Propostas: ", window.customPerfexVars.can_view_propostas);
    console.log("Aprova documentos: ", can_approve_doc);
    console.log(Object.keys(window));
    if (!is_admin && !can_approve_doc) {
        document.addEventListener("contextmenu", function(event) {
            if (event.target.tagName === "IMG") {
                event.preventDefault();

                alert('⚠️ Acesso negado  ⚠️');


            }
        });

        document.addEventListener("dragstart", function(event) {
            if (event.target.tagName === "IMG") {
                event.preventDefault();

                alert('⚠️ Acesso negado ⚠️');

            }
        });

    }
</script>
<!-- <script>
   var second = 0;  
    setInterval(() => {
        $.ajax({
            url: admin_url + 'icash_tools/gerenciar_propostas/get_status_atualizados', // URL do endpoint que retorna as propostas com status atualizados
            method: 'GET',
            success: function(data) {
                console.log('Dados Recebidos');
                console.log(data);
                // data.forEach(function(proposta) {
                //     const td = document.querySelector(`.status[data-id="${proposta.id}"]`);
                //     if (td && td.innerText !== proposta.status) {
                //         td.innerText = proposta.status;
                //         td.classList.add('bg-warning'); // Marca a célula com cor de alerta
                //         setTimeout(() => td.classList.remove('bg-warning'), 1000); // Remove a marcação depois de 1 segundo
                //     }
                // });
            }
        });
    }, 30000); // Requisição a cada 5 segundos
</script> -->

<script>
    // atualiza a tela a cada 40 segundos

    let reloadTimeout;
    let userIsActive = false;

    // Função que reinicia o timer
    // function resetReloadTimer() {
    //     userIsActive = true;
    //     clearTimeout(reloadTimeout);
    //     reloadTimeout = setTimeout(() => {
    //         userIsActive = false;
    //         // Se o usuário não estiver interagindo, recarrega
    //         if (!userIsActive) {
    //             location.reload();
    //         }
    //     }, 40000); // 40 segundos
    // }

    // Detecta se o usuário está interagindo com a tela
    // ['keydown', 'mousemove', 'mousedown', 'touchstart', 'scroll', 'input', 'focus'].forEach(event => {
    //     window.addEventListener(event, resetReloadTimer, true);
    // });

    // Começa o timer assim que a página carrega
    // resetReloadTimer();
</script>


<?php init_tail(); ?>
<script>
    const ICASH_ASSETS_URL = '<?php echo module_dir_url('icash_tools', 'assets'); ?>';
</script>
<script>
    $(function() {
        const table = $('.dt-table').DataTable();
        table.on('buttons-action', function() {
            setTimeout(function() {
                $("li.dt-button.buttons-csv.buttons-html5").hide();
                $("li.dt-button.buttons-pdf.buttons-html5").hide();
                $("li.dt-button.buttons-print").hide();
            }, 50);
        });
    });
</script>