<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('icash_tools', 'assets/css/icash-tools-proposals-styles.css?v=3.1.10'); ?>">

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body panel-table-full">

                        <div class="row">
                            <!-- FILTROS AVANÇADOS -->
                            <div class="row mb-2">
                                <!-- <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="filtro-cliente">Cliente</label>
                                        <input type="text" id="filtro-cliente" name="filtro_cliente" class="form-control" placeholder="Buscar por cliente">
                                    </div>
                                </div> -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="filtro-status">Status</label>
                                        <select id="filtro-status" name="filtro_status" class="form-control">
                                            <option value="">Todos os Status</option>
                                            <option value="1"> PEN - Envio Documento </option>
                                            <option value="21"> Em análise documental </option>
                                            <option value="22"> Reprova documental </option>
                                            <option value="23"> Link Pag. Enviado </option>
                                            <option value="24"> Link Pag. Aprovado </option>
                                            <option value="25"> Link Pag. Reprovado </option>
                                            <option value="26"> Aguardando formalização </option>
                                            <option value="27"> Em análise formalização </option>
                                            <option value="28"> Liberar Crédito </option>
                                            <option value="29"> Crédito Enviado </option>
                                            <option value="30"> Aguardando Confirmação</option>
                                            <option value="2"> Cancelada </option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Terceira coluna (exemplo de espaço para mais filtros ou botão) -->
                                <div class="col-md-4">
                                    <!-- <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button class="btn btn-primary btn-block" onclick="tabela.ajax.reload()">Filtrar</button>
                                    </div> -->
                                </div>
                            </div>
                        </div>

                        <!-- TABELA -->
                        <?php
                        render_datatable([
                            'ID',
                            'Cliente',
                            'CORBAN',
                            'Status',
                            'Criado em',
                            'Atualizado em',
                            'Ações',
                        ], 'propostas');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MOSTRA O LOAD -->
<div class="table-loader">
    <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
</div>

<!-- MOSTRA O BADGE -->
<div id="update-badge" style="display: none; position: fixed; bottom: 20px; right: 20px; background: #28a745; color: white; padding: 10px; border-radius: 10px; z-index: 9999;">
    🔄 Propostas atualizadas!
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        // Inicializa a tabela com ordenação pela 5ª coluna (index 4) descendente
        fnServerParams = {
            "filtro_cliente": '[name="filtro_cliente"]',
            "filtro_status": '[name="filtro_status"]'
        };

        const tabela = initDataTable(
            '.table-propostas',
            admin_url + 'icash_tools/List_proposals',
            false,
            false,
            fnServerParams, // aqui está a mágica!
            [4, 'desc']
        );

        // Recarrega a tabela sempre que os filtros mudarem
        $('#filtro-cliente, #filtro-status').on('change keyup', function() {
            tabela.ajax.reload();
        });

        // Reload suave automático a cada 20 segundos
        setInterval(() => {
            tabela.ajax.reload(() => {
                console.log("Matrix reload....");
                $('.table-loader').hide(); // Esconde o loader, se houver
                showUpdateBadge(); // Chama badge de atualização (personalizado)
            }, false); // false = mantém paginação atual
        }, 40000);

        function showUpdateBadge() {
            $('#update-badge').fadeIn(300);
            setTimeout(() => {
                $('#update-badge').fadeOut(300);
            }, 3000);
        }
    });


    // Envia dados adicionais no request AJAX
    $.fn.DataTable.ext.errMode = 'none'; // Oculta erros padrão do DataTables
    $.fn.dataTable.pipeline = function(opts) {
        return function(request, drawCallback, settings) {
            request.cliente = $('#filtro-cliente').val();
            request.status = $('#filtro-status').val();
            $.ajax({
                type: 'POST',
                url: opts.url,
                data: request,
                dataType: 'json',
                success: function(json) {
                    drawCallback(json);
                }
            });
        };
    };
</script>
<!-- loader -->
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
                    if (!staff_can('status_edit_adm', 'corban_proposals')) {
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
                            <!-- <option value="30"> Descartado </option> -->
                            <option value="2"> Cancelada </option>
                            <!-- <option value="3"> Operação Finalizada </option> -->

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
<div class="modal fade" id="clientDetailModal" tabindex="-1" role="dialog" aria-labelledby="clientDetailModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detalhes da Proposta</h4>
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
                    <button type="button" class="btn btn-danger" id="no-payment" onclick="onPaymentProposal(false); return false;" disabled>Pagamento Devolvido</button>
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
<script src="https://painel.icashcard.com.br/modules/icash_tools/assets/js/propostas_funcoes_tela_corbans.js?ver=12.7.467"></script>


<!-- SCRIPT PARA BUSCAR PROIPOSTAS ATUALIZADAS -->
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


<!-- REINICIA SE NAO HOUVER INTERACAO -->
<!-- <script>
    let userIsActive = false;
    let lastActivityTime = new Date().getTime();

    function updateUserActivity() {
        lastActivityTime = new Date().getTime();
    }

    // Detecta interações
    ['keydown', 'mousemove', 'mousedown', 'touchstart', 'scroll', 'input', 'focus'].forEach(event => {
        window.addEventListener(event, updateUserActivity, true);
    });

    // Intervalo de 10 segundos com checagem de inatividade
    setInterval(function () {
        const now = new Date().getTime();
        const secondsSinceLastActivity = (now - lastActivityTime) / 1000;

        if (secondsSinceLastActivity >= 10) {
            console.log("Matrix Reload...");
            $('.table-propostas').DataTable().ajax.reload(null, false);
        }
    }, 10000); // checa a cada 10 segundos
</script> -->

</body>

</html>