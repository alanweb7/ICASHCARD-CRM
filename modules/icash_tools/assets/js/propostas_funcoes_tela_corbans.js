window.addEventListener('DOMContentLoaded', function () {
    // Listener dedicado para o checkbox de recusa (modal de documentos).
    $(document).off('change.icashRecusa', '#recusar_doc').on('change.icashRecusa', '#recusar_doc', function () {
        const checked = $(this).is(':checked');
        if (checked) {
            $('#motivo_recusa_div').css('display', 'block');
            $('#save_info').css('display', 'inline-block');
        } else {
            $('#motivo_recusa_div, #save_info').css('display', 'none');
        }
    });

    $(document).ready(function () {
        // Quando o modal é aberto, carregar o ID da proposta no campo correspondente
        $('#editProposalStatusModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Botão que acionou o modal
            var proposalId = button.data('proposal-id'); // Pega o ID da proposta do botão
            var currentStatusText = button.data('status-text');
            $('#proposalId').val(proposalId); // Define o valor no campo

            if (currentStatusText) {
                // Limpa seleção anterior
                $("#proposalStatus option").prop("selected", false);

                // Marca pelo texto (case-insensitive)
                $("#proposalStatus option").filter(function () {
                    return $(this).text().trim().toLowerCase() === currentStatusText.toLowerCase();
                }).prop("selected", true);
            }
        });

        // Enviar o formulário via AJAX
        // Enviar o formulário via AJAX
        $('#saveProposalStatus').on('click', function () {
            var $btn = $(this); // referência ao botão
            var $btnCancel = $("#cancelModal"); // botão cancelar
            var formData = $('#editProposalStatusForm').serialize();

            // Desabilita o botão e mostra um spinner
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
            $btnCancel.prop('disabled', true);

            // Se quiser bloquear a tela toda:
            // $('body').append('<div class="loading-overlay"><div class="spinner-border text-primary" role="status"></div><span class="ms-2">Aguarde...</span></div>');

            $.ajax({
                url: admin_url + 'icash_tools/gerenciar_propostas/update_status',
                type: 'POST',
                data: formData,
                success: function (response) {
                    console.log("Resposta ao salvar: ");
                    console.log(response);

                    const message = response.message ?? 'Erro ao atualizar o status.';

                    if (response.success) {
                        alert_float('success', 'Status atualizado com sucesso!');
                    } else {
                        alert_float('danger', message);
                    }

                    setTimeout(() => {
                        $('#editProposalStatusModal').modal('hide');
                        $('#editProposalStatusForm')[0].reset();
                        location.reload();
                    }, 1000);
                },
                error: function (xhr) {
                    alert_float('danger', 'Erro ao atualizar o status!!. Tente novamente mais tarde.');
                },
                complete: function () {
                    // Reabilita o botão e remove o overlay
                    $btn.html('Salvar');
                    // $btnCancel.prop('disabled', false);
                    // $('.loading-overlay').remove();
                }
            });
        });



    });



    // controle dos checkbox enviar documentos
    $(document).ready(function () {

        // Verifica ao digitar
        $('#motive_info').on('input', function () {
            const valor = $(this).val().trim();
            const valido = valor.length >= 10;
            $('#send_link_document').prop('disabled', !valido);

            if (!valido) {
                $('#linkWarning').show();
            } else {
                $('#linkWarning').hide();
            }
        });

        $('#parameterForm input[type="checkbox"]').change(function () {
            const info_data = JSON.parse($('input#info_data').val());
            console.log(info_data.hash);

            const params = {};

            // Iterar sobre os checkboxes marcados
            $('#parameterForm input[type="checkbox"]:checked').each(function () {
                const name = $(this).attr('name');
                const value = $(this).val();

                // Adicionar valores ao parâmetro ou criar novo
                if (params[name]) {
                    params[name] += ',' + value; // Concatena com vírgula
                } else {
                    params[name] = value;
                }
            });

            // Construir string de query
            const query = Object.entries(params)
                .map(([key, value]) => `${key}=${value}`)
                .join('&');

            // Atualizar o link gerado
            var hash = info_data.hash;
            var linkBase = `https://icashcard.com.br/enviar-documentos/?proposta_hash=${hash}&`;
            // $('#generatedLink').text(linkBase + query);
            if (query) {
                // $('#copyLink').attr('data-link', linkBase + query);
                $('#inputLink').val(linkBase + query);
                $('#link-gerado').show();
                $('#recusar_doc, #aprove_doc').prop('checked', false);
                $('#informations_sub, .action_document').hide();

            } else {
                // $('#copyLink').attr('data-link', "");
                $('#inputLink').val("");
                $('#link-gerado').hide();
                $('#informations_sub').show();
            }
        });
    });



    // FNÇÃO QUE ENVIA AUTOMATICAMENTE OS ARQUIVOS
    $(document).on('change', '.input-image', function (e) {
        console.log('Enviando arquivos...');
        const input = this;
        const file = input.files[0];
        if (!file) return;
        const extension = file.name.split('.').pop().toLowerCase();

        const docType = $(input).data('type'); // Ex: rg_frente
        const propostaHash = $(input).data('proposta_hash'); // Ex: 123abc

        const formData = new FormData();
        formData.append(docType, file);
        formData.append('proposta_hash', propostaHash);

        const actionElements = document.querySelectorAll(`.action-${docType}`);
        actionElements.forEach(el => {
            el.style.pointerEvents = 'none';   // Desativa cliques
            el.style.opacity = '0.6';          // Visualmente "desabilitado"
            el.setAttribute('disabled', 'disabled'); // Se for <button> ou <input>
        });


        // Opcional: mostrar loading no botão
        const spanSend = $(`span#${docType}`);
        const labelSend = $(`label#label-${docType}`);
        $(spanSend).show();
        $(labelSend).hide();

        const fileNameExt = `${docType}.${extension}`

        const fileUrlBase = `https://icashcard.com.br/wp-content/uploads/imagens/propostas/serve_file.php?key=12f3-34g5-7980&amp;hash=${propostaHash}&amp;file=${fileNameExt}&amp;176ec13`;
        const baseSite = "https://icashcard.com.br";

        $.ajax({
            url: baseSite + '/wp-json/custom/v1/upload-files',
            type: 'POST',
            data: formData,
            processData: false, // Impede jQuery de processar FormData
            contentType: false, // Impede jQuery de definir o tipo (deixa o navegador fazer)
            cache: false,
            enctype: 'multipart/form-data', // Importante para uploads
            success: function (res) {
                console.log('Upload ok:', res);

                if (res.status === 'success') {
                    console.log('Sucesso"');
                }

            },
            error: function (err) {
                console.error('Erro no upload:', err);
                alert('Erro ao enviar o arquivo.');
            },
            complete: function () {
                setTimeout(() => {
                    $(spanSend).hide();
                    $(labelSend).show();

                    // Habilita o clique novamente
                    actionElements.forEach(el => {
                        el.style.pointerEvents = 'auto'; // força permitir clique
                        el.style.opacity = '1'; // visual normal
                        el.removeAttribute('disabled'); // se por acaso usou disabled em algum botão

                        // Reatribui a função modalViewImage
                        el.onclick = () => {
                            modalViewImage(fileUrlBase, fileNameExt);
                        };
                    });

                    const deleteButton = document.getElementById(`${docType}_btn`);
                    if (deleteButton) {
                        deleteButton.disabled = false;
                    }

                }, 2000);
            },
            // ⚠️ Isso evita o erro causado por hooks jQuery antigos do WP
            beforeSend: function (xhr, settings) {
                // Desativa tratamento global que pode tentar serializar o formData
                if (typeof settings.data === 'object' && settings.data instanceof FormData) {
                    settings.processData = false;
                    settings.contentType = false;
                }
            }
        });

    });

    // carrega um preview da imagem

    $(document).on('change', '.input-image', function () {

        const input = this;
        const file = input.files[0];
        if (!file) return;


        const targetSelector = $(this).data('target');
        const preview = $(targetSelector)[0];

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block'; // Mostra se estava oculto
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    });



    // document.getElementById("copyLink").addEventListener("click", function () {
    //     const link = this.getAttribute("data-link");
    //     navigator.clipboard.writeText(link).then(() => {
    //         alert("Link copiado para a área de transferência!");
    //     }).catch(err => {
    //         console.error("Erro ao copiar o link: ", err);
    //     });
    // });


    // checkbox do modal

    // document.getElementById('observation').addEventListener('input', function () {
    //     const submitButton = document.getElementById('no-payment');
    //     if (this.value.trim() !== '') {
    //         submitButton.removeAttribute('disabled');
    //         submitButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
    //         submitButton.classList.add('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
    //     } else {
    //         submitButton.setAttribute('disabled', true);
    //         submitButton.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
    //         submitButton.classList.add('bg-gray-400', 'cursor-not-allowed');
    //     }
    // });


});

function openModalManageProposal() {
    $('#editProposalStatusModal').modal('show');
}

// MODAL DE IMAAGEM
function modalViewImage(url, name = 'Visualizar Documento') {

    let newHashTime = Date.now().toString(36).slice(-5);
    $('#modal-title').text(name);
    $('#content-edit-data').html(`
        <div class="text-center" style="width:100%">
            <img src="${url}${newHashTime}" alt="Imagem" class="img-fluid rounded shadow" style="width:100%">
        </div>
    `);
    $('#generalModalEdit').modal('show');
}


// MODAL DETALHES DA PROPOSTA
function openModalclientDetail(cliente) {
    $('#info_data').val(JSON.stringify(cliente));
    // variavel cliente alimentada por controller listar_propostas

    // esconder botao editar    
    const proposal_fields = cliente.proposal_fields;

    var proposalEtapa = null;
    if (proposal_fields) {
        proposalEtapa = proposal_fields.Etapa;
        console.log("Etapa");
        console.log(proposalEtapa);
    }

    var disableButton = ["Crédito Enviado", "Link Pag. Aprovado", "Aguardando formalização", "Em análise formalização", "Liberar Crédito", "Aguardando Confirmação"];


    if (disableButton.includes(proposalEtapa)) {
        $("button#edit-proposal").hide();
    } else {
        $("button#edit-proposal").show();
    }


    let html = '';
    if (isObjectEmptyOrAllNull(cliente)) {
        html = "<p>Sem dados disponíveis.</p>";
        $("#content-client-modal").html(html);
    } else {
        var title = `Detalhes da Proposta (#${cliente.proposal_id})`;
        $("#title_proposal_id").text(title);
        // controller
        $.get(admin_url + 'icash_tools/templates/templates_tools/proposal_details', function (template) {

            const data = {
                proposal_to: cliente.proposal_to,
                tipo_chave_pix: cliente.proposal_tipo_de_chave,
                chave_pix: cliente.proposal_chave_pix,
                banco: cliente.proposal_banco_2,
                agencia: cliente.proposal_agencia,
                conta: cliente.proposal_conta,
                email: cliente.proposal_email,
                endereco: cliente.proposal_endereco_cliente,
                cpf: cliente.proposal_fields.CPF,
                rg: cliente.cliente_rg,
                data_nasc: cliente.cliente_data_nasc,
                cpf: cliente.proposal_fields.CPF,
                telefone: cliente.proposal_telefone,

                tabela: cliente.proposal_fields.Tabela,
                prazo: cliente.proposal_fields.Parcelas,
                total_bruto: cliente.proposal_fields['Total Bruto'],
                total_liquido: cliente.proposal_fields['Total Líq.'],
                link_pagamento: cliente.payment_link,
                payment_message: cliente.bank_message,
                payment_description: cliente.payment_description,
                proposal_refusal: cliente.proposal_refusal,
                proposal_observation: cliente.proposal_observation,
                valor_parcela: cliente.proposal_fields['Valor Parcela'],
            };

            // Substituir variáveis no template
            let filledTemplate = template;
            for (const key in data) {
                const regex = new RegExp(`\\$\\{${key}\\}`, 'g');
                filledTemplate = filledTemplate.replace(regex, data[key]);
            }

            // popular campo
            $("#content-client-modal").html(filledTemplate);

        });

    }

    $('#clientDetailModal').modal('show');

    // carregar histórico
    onGetProposalHistory(cliente.proposal_id);
}


function onGetProposalHistory(proposal_id) {
    $("#history-proposal").html('');
    $.post(
        admin_url + 'icash_tools/templates/templates_tools/get_proposal_history',
        { proposal_id: proposal_id },
        function (response) {

            console.log(response);
            // Aqui você já injeta direto o HTML retornado pelo controller
            $("#history-proposal").html(response);
        }
    ).fail(function (xhr) {
        alert_float('danger', 'Erro ao carregar o histórico.');
    });
}


function onEditProposal(info) {
    'use strict';
    const info_data = JSON.parse($('input#info_data').val());
    let proposal_id = info_data.proposal_id;
    console.log(`Proposta ID: ${proposal_id}`);


    $("#modal-title").text(`Editar Proposta (#${proposal_id})`);

    $.post(
        admin_url + 'icash_tools/templates/templates_tools/proposal_edit',
        { proposal_id: proposal_id },
        function (response) {
            // Supondo que o controlador retorna o template já com dados preenchidos
            $("#content-edit-data").html(response);
        }
    ).fail(function (xhr) {
        // Caso ocorra algum erro na requisição
        alert_float('danger', 'Erro ao carregar o template.');
    });

    $('#clientDetailModal').modal('hide');
    $('#generalModalEdit').modal('show');

}


// MODAL DOCUMENTOS

function openDocsProposal(info, viewOnly) {
    'use strict';

    console.log(info);
    console.log(viewOnly);

    //  esconde os botoes de acao
    $('.action_document, #parameterForm, #update_doc_div, #recusar_doc_div, #aprove_doc_div, #informations_sub').hide();

    // desmarca todos os checknboxs
    $('input[type="checkbox"]').prop('checked', false);
    $('#parameterForm')[0].reset();


    // limpar dados do cartao
    $('#info_payment').empty().hide();

    // insere as informacoes no info_data
    $('#info_data').val(JSON.stringify(info));

    // const data = JSON.parse(info);
    const etapa = info.proposal_etapa;
    const etapaNorm = String(etapa || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
    const isEtapaEmAnaliseDocumental = etapaNorm.startsWith('em analise documenta');
    const hash = info.hash;
    var completed = info.completed;
    var action = info.action;
    var uniq = info.uniq;

    var c_card = info.n_cartao_de_credito;
    var titular_card = info.titular_cartao;

    const activeIn = ["PEN - Envio Documento", "Aguardando formalização", "Link Pag. Enviado"];
    const contractAct = ["Link Pag. Aprovado", "Aguardando formalização"];
    const signatureAct = ["Em análise formalização"];
    const linkAct = ["Em análise documental", "Link Pag. Enviado"];


    switch (etapa) {
        case "PEN - Envio Documento":
            $('#update_doc_div, #recusar_doc_div, #informations_sub').show();
            break;

        case "Em análise documental":
        case "Link Pag. Enviado":
        case "Link Pag. Aprovado":
            $('#update_doc_div, #recusar_doc_div, #aprove_doc_div, #informations_sub').show();
            break;

        case "Aguardando formalização":
        case "Link Pag. Aprovado":
        case "Em análise formalização":
            $('#aprove_doc_div, #informations_sub').show();
            break;

        default:
            $('#parameterForm, #update_doc_div, #recusar_doc_div, #aprove_doc_div, #informations_sub').hide();
            break;
    }

    /**
     * ACOES DOS CHECKBOXES
     */

    $('.form-check-input').off('change.icashDocs').on('change.icashDocs', function () {
        let infoData;

        try {
            infoData = JSON.parse($('#info_data').val());
        } catch (e) {
            console.error("Erro ao ler info_data:", e);
            return;
        }


        // Desmarca todos os outros checkboxes
        $('.form-check-input').not(this).prop('checked', false);

        // Atualiza visibilidade do parameterForm com base no update_doc
        $('#parameterForm').toggle($('#update_doc').is(':checked'));



        // Mostrar campo de recusa e botão salvar se "Recusar" estiver marcado

        if ($('#recusar_doc').is(':checked')) {
            // Força exibição para evitar conflito com CSS base (#save_info { display:none; }).
            $('#motivo_recusa_div').css('display', 'block');
            $('#save_info').css('display', 'inline-block');
        } else {
            $('#motivo_recusa_div, #save_info').css('display', 'none');
        }


        // Lógica para checkbox de aprovação geral link e contrato
        if ($('#aprove_doc').is(':checked')) {

            // Esconde todos por padrão
            $('#generate_contract, #link_payment, #unsigned_contract').hide();

            // Mostra se estiver nas etapas corretas
            if (etapa == "Em análise formalização") {
                $('#unsigned_contract').show();
            }
            else if (etapa == "Link Pag. Enviado" || etapa == "Em análise documental") {
                $('#link_payment').show();
            }
            else if (etapa == "Link Pag. Aprovado" || etapa == "Em análise documental") {
                $('#generate_contract').show();
            }


            // if (contractAct.includes(etapa)) {
            //     $('#generate_contract').show();
            //     $('#unsigned_contract').show(); // caso precise exibir também aqui
            // }

            // if (linkAct.includes(etapa)) {
            //     $('#link_payment').show();
            // }

        } else {
            $('#generate_contract, #link_payment, #unsigned_contract').hide();
        }



    });


    /**
     * ACOES DOS CHECKBOXES
     */

    // // Atualiza o texto do label
    // if (etapa === "Link Pag. Aprovado") {
    //     $('label[for="aprove_doc"]').text('Gerar Contrato');
    // } else {
    //     $('label[for="aprove_doc"]').text('Gerar Link de Pagtº');
    // }


    if (c_card) {
        var infoHtml = '';
        infoHtml += '<p>';
        infoHtml += '<h5>Dados do Cartão</h5>';
        infoHtml += `<span><b>Titular: </b> ${titular_card}</span><br>`;
        infoHtml += `<span><b>Nº C. Crédito: </b> ${c_card}</span>`;
        infoHtml += '</p>';
        $('#info_payment').show().empty().append(infoHtml);
    }

    var fileIcon = "../../uploads/staff_profile_images/imagens/icone-ok.jpg";
    if (!completed) {
        fileIcon = "../../uploads/staff_profile_images/imagens/icone-error.jpg";
    }

    const docsArray = [
        {
            type: "rg_frente",
            name: "RG Frente", // Certifique-se de que "contractStatus" esteja definido em JS
            // file: "../../uploads/staff_profile_images/imagens/carteira-de-identidade.jpg",
            file: info.rg_frente,
        },
        {
            type: "rg_verso",
            name: "RG Verso", // Certifique-se de que "contractStatus" esteja definido em JS
            // file: "../../uploads/staff_profile_images/imagens/carteira-de-identidade.jpg",
            file: info.rg_verso,
        },
        {
            type: "cartao_frente",
            name: "Cartão Frente",
            file: info.cartao_frente,
        },
        {
            type: "cartao_verso",
            name: "Cartão Verso",
            file: info.cartao_verso,
        },
        {
            type: "selfie_identidade",
            name: "Selfie",
            file: info.selfie_identidade,
        },
        {
            type: "extra_file",
            name: "Arquivo Extra",
            file: info.extra_file,
        },
    ];


    const html = docsArray.map((doc) => {
        const hasImage = !!doc.file;

        const disabled = doc.file ? '' : 'disabled';

        // OS ARQUIVOS SERÃO ENVIADOS AUTOMATICAMENTE AO SELECIONAR PELA FUNÇÃO: 
        const actionsBtns = `
                    <label 
                    id="label-${doc.type}"
                    class="btn btn-sm btn-outline-primary mt-1 position-relative overflow-hidden">
                        <i class="fa fa-upload me-1"></i> Trocar
                        <input type="file"
                            id="file-upload"
                            name="${doc.type}"
                            accept="image/*,application/pdf"
                            class="position-absolute top-0 start-0 w-100 h-100 opacity-0 input-image"
                            style="cursor: pointer;"
                            data-type="${doc.type}"
                            data-proposta_hash="${hash}"
                            data-target="#${doc.type}">
                    </label> 
                    <span id="${doc.type}" style="display:none"><img src="${ICASH_ASSETS_URL}/images/send-file.gif" width="80px" /></span>       
                    <button class="btn btn-sm btn-outline-danger" id="${doc.type}_btn" title="Excluir" onclick="deleteImage(${info.proposal_id}, '${doc.type}')" ${doc.file ? '' : 'disabled'}>
                        <i class="fa fa-trash"></i>
                    </button>
        `;

        const hasFile = !!doc.file;
        const previewUrl = doc.file || 'https://icashcard.com.br/wp-content/uploads/2025/07/no-image-available-icon-vector.jpg';
        const disableAttr = hasFile ? '' : 'style="pointer-events: none; opacity: 0.6;"';
        const onclickAttr = hasFile ? `onclick="modalViewImage('${doc.file}', '${doc.name}')"` : '';

        const resp = `
    <tr>
        <td>
            <img src="${previewUrl}"
                alt="Imagem"
                class="img-thumbnail me-2 action-${doc.type}"
                id="${doc.type}"
                style="width: 50px; height: auto; cursor: pointer; ${!hasFile ? 'pointer-events: none; opacity: 0.6;' : ''}"
                ${onclickAttr}>

            <a href="javascript:void(0)" class="action-${doc.type}" ${onclickAttr} ${disableAttr}>
                ${doc.name}
            </a>
        </td>
        <td>
            ${actionsBtns}
        </td>
    </tr>
`;


        return resp;
    });



    const contratoHtml = "<div><h5>Informações sobre o Contrato:</h5></div>";

    var contentModalDocuments = `
                <table class="table user-list">
                    <thead>
                        <tr>
                            <th><span>Documento</span></th>
                            <th><span>Ações</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        ${html.join("")}
                    </tbody>
                </table>
        `;

    if (!info.completed) {
        var urlSendFiles = "https://icashcard.com.br/enviar-documentos/?cpf=575.305.132-49&proposta_hash=ICC-677420151f742&rg=f,v&card=f,v&selfie=1";
        var hferDocs = `${urlSendFiles}`;
    }


    const proposta = {
        modal_documentos: contentModalDocuments,
        modal_contrato: `<div class="document-grid">${contratoHtml}</div>`,
    };

    if (action == "docs") {
        $('#content-modal').html(proposta.modal_documentos);
    }
    else if (action == "contrato") {
        $('#content-modal').html(proposta.modal_contrato);
    }

    // abrir modal
    $('#commission_detail_modal').modal('show');

}

// ABRIR MODAL DE VISUALIZAÇÃOD DE DOCUMENTOS
function viewDocsProposal(info, viewOnly) {
    'use strict';

    console.log(info);
    console.log(viewOnly);

    //  esconde os botoes de acao
    $('.action_document, #parameterForm, #update_doc_div, #recusar_doc_div, #aprove_doc_div, #informations_sub').hide();

    // desmarca todos os checknboxs
    $('input[type="checkbox"]').prop('checked', false);
    $('#parameterForm')[0].reset();


    // limpar dados do cartao
    $('#info_payment').empty().hide();

    // insere as informacoes no info_data
    $('#info_data').val(JSON.stringify(info));

    // const data = JSON.parse(info);
    const proposal_id = info.proposal_id;
    const etapa = info.proposal_etapa;
    const hash = info.hash;
    var completed = info.completed;
    var action = info.action;
    var uniq = info.uniq;


    var titleModal = `Proposta: #${proposal_id}`;
    $("h5#modal-title").text(titleModal);


    var fileIcon = "../../uploads/staff_profile_images/imagens/icone-ok.jpg";
    if (!completed) {
        fileIcon = "../../uploads/staff_profile_images/imagens/icone-error.jpg";
    }

    const docsArray = [
        {
            type: "rg_frente",
            name: "RG Frente", // Certifique-se de que "contractStatus" esteja definido em JS
            // file: "../../uploads/staff_profile_images/imagens/carteira-de-identidade.jpg",
            file: info.rg_frente,
        },
        {
            type: "rg_verso",
            name: "RG Verso", // Certifique-se de que "contractStatus" esteja definido em JS
            // file: "../../uploads/staff_profile_images/imagens/carteira-de-identidade.jpg",
            file: info.rg_verso,
        },
        {
            type: "cartao_frente",
            name: "Cartão Frente",
            file: info.cartao_frente,
        },
        {
            type: "cartao_verso",
            name: "Cartão Verso",
            file: info.cartao_verso,
        },
        {
            type: "selfie_identidade",
            name: "Selfie",
            file: info.selfie_identidade,
        },
        {
            type: "extra_file",
            name: "Arquivo Extra",
            file: info.extra_file,
        },
    ];


    const html = docsArray.map((doc) => {
        const hasImage = !!doc.file;

        const disabled = doc.file ? '' : 'disabled';

        // OS ARQUIVOS SERÃO ENVIADOS AUTOMATICAMENTE AO SELECIONAR PELA FUNÇÃO: 
        const actionsBtns = `-`;

        const hasFile = !!doc.file;
        const previewUrl = doc.file || 'https://icashcard.com.br/wp-content/uploads/2025/07/no-image-available-icon-vector.jpg';
        const disableAttr = hasFile ? '' : 'style="pointer-events: none; opacity: 0.6;"';
        const onclickAttr = hasFile ? `onclick="modalViewImage('${doc.file}', '${doc.name}')"` : '';

        const resp = `
            <tr>
                <td>
                    <img src="${previewUrl}"
                        alt="Imagem"
                        class="img-thumbnail me-2 action-${doc.type}"
                        id="${doc.type}"
                        style="width: 50px; height: auto; cursor: pointer;">
                    <span>
                        ${doc.name}
                    </span>
                </td>
                <td>
                </td>
            </tr>
        `;


        return resp;
    });



    const contratoHtml = "<div><h5>Informações sobre o Contrato:</h5></div>";

    var contentModalDocuments = `
                <div><h5>${titleModal}</h5></div>
                <table class="table user-list">
                    <thead>
                        <tr>
                            <th><span>Documento</span></th>
                            <th><span></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        ${html.join("")}
                    </tbody>
                </table>
        `;

    if (!info.completed) {
        var urlSendFiles = "https://icashcard.com.br/enviar-documentos/?cpf=575.305.132-49&proposta_hash=ICC-677420151f742&rg=f,v&card=f,v&selfie=1";
        var hferDocs = `${urlSendFiles}`;
    }


    const proposta = {
        modal_documentos: contentModalDocuments,
        modal_contrato: `<div class="document-grid">${contratoHtml}</div>`,
    };

    if (action == "docs") {
        $('#content-modal').html(proposta.modal_documentos);
    }

    // abrir modal
    $('#commission_detail_modal').modal('show');

}


function onGetProposalInformation(proposal) {
    'use strict';

    console.log("Clicou...");
    $.ajax({
        url: admin_url + 'icash_tools/listar_propostas/my_ajax_function', // URL para o controlador
        type: 'POST', // Método HTTP
        data: {
            key1: 'valor1',
            key2: 'valor2'
        },
        success: function (response) {
            // Converte a resposta JSON
            const data = JSON.parse(response);
            if (data.success) {
                // alert(data.message);
                $('#commission_detail_modal').modal('show');
            } else {
                alert('Erro: ' + data.message);
            }
        },
        error: function (xhr, status, error) {
            alert('Erro na requisição AJAX: ' + error);
        }
    });
}

function onSendLinkUpdateDocuments() {
    'use strict';

    const info_data = JSON.parse($('input#info_data').val());
    let proposalId = info_data.proposal_id;

    // mostra aguarde
    $("#informations_sub, #link-gerado,#parameterForm").hide();
    $("#wait_text, #wait_icon").show();

    // Exibe uma mensagem de confirmação
    if (confirm(`Enviar link de documentos para o cliente?`)) {

        const dataSend = {
            link_documentos: $('input#inputLink').val(),
            motivo: $('input#motive_info').val(),
            id: proposalId
        };

        console.log(info_data);
        console.log(dataSend);


        // Dispara uma requisição AJAX para alternar o status
        $.ajax({
            url: "listar_propostas/onSendLinkDocuments",
            type: "POST",
            data: dataSend,
            success: function (response) {
                console.log(response); // Mensagem de retorno (opcional)
                var html = "";
                if (response.success) {

                    $('#parameterForm')[0].reset(); // limpa tudo sem dar aviso

                    location.reload(true); // Atualiza a página para refletir a alteração

                } else {

                }

                $('#info_content').append(html);

            },
            error: function (xhr) {
                alert('Ocorreu um erro ao alterar o status.');
            }
        });
    } else {
        // Ação caso o usuário cancele
        console.log("Ação de exclusão cancelada pelo usuário.");
    }


}

function generateHash() {
    // Obtém o timestamp atual
    const timestamp = Date.now();

    // Converte o timestamp para uma string em base64
    const base64 = btoa(timestamp.toString());

    // Remove caracteres não alfanuméricos e retorna os 7 primeiros caracteres
    return base64.replace(/[^a-zA-Z0-9]/g, '').substring(0, 7);
}

function isEmpty(variable) {
    if (variable == null) {
        // Verifica se é null ou undefined
        return true;
    }
    if (typeof variable === 'object') {
        // Verifica arrays e objetos
        if (Array.isArray(variable)) {
            // Retorna true se for um array vazio
            return variable.length === 0;
        } else {
            // Retorna true se for um objeto sem propriedades
            return Object.keys(variable).length === 0;
        }
    }
    if (typeof variable === 'string') {
        // Retorna true se for uma string vazia
        return variable.trim() === '';
    }
    // Retorna false para números, booleanos e outros tipos
    return false;
}

function isObjectEmptyOrAllNull(obj) {
    if (obj == null || typeof obj !== 'object') {
        return true; // Não é um objeto válido
    }

    // Obtém as chaves do objeto e verifica se todas têm valores `null`
    return Object.keys(obj).length > 0 && Object.values(obj).every(value => value === null);
}

function onAproveDocumentsInProposal() {
    // Exibe uma mensagem de confirmação
    const info_data = JSON.parse($('input#info_data').val());
    let proposalId = info_data.proposal_id;
    observation = $("#motivo_recusa").val();
    if (confirm(`Recusar documentos da proposta ${proposalId} ?`)) {
        // Dispara uma requisição AJAX para alternar o status
        $.ajax({
            url: "listar_propostas/aproveDocumentsInProposal",
            type: "POST",
            data: {
                id: proposalId,
                observation: observation
            },
            success: function (response) {
                console.log(response.message); // Mensagem de retorno (opcional)
                location.reload(); // Atualiza a página para refletir a alteração
            },
            error: function (xhr) {
                alert('Ocorreu um erro ao alterar o status.');
            }
        });
    } else {
        // Ação caso o usuário cancele
        console.log("Ação de exclusão cancelada pelo usuário.");
    }
}
function onDeleteProposal(proposalId) {
    // Exibe uma mensagem de confirmação
    if (confirm(`Tem certeza que deseja deletar a proposta ${proposalId} ?`)) {
        // Dispara uma requisição AJAX para alternar o status
        $.ajax({
            url: "listar_propostas/onDeleteProposal",
            type: "POST",
            data: { id: proposalId },
            success: function (response) {
                console.log(response.message); // Mensagem de retorno (opcional)
                location.reload(); // Atualiza a página para refletir a alteração
            },
            error: function (xhr) {
                alert('Ocorreu um erro ao alterar o status.');
            }
        });
    } else {
        // Ação caso o usuário cancele
        console.log("Ação de exclusão cancelada pelo usuário.");
    }
}

function onGenerateLinkPayment() {
    const info_data = JSON.parse($('input#info_data').val());
    let proposalId = info_data.proposal_id;

    // desabilitar botao de gerar link
    $("#link_payment").attr('disabled', true);

    // Exibe uma mensagem de confirmação
    if (confirm(`Enviar link de pagamento para o cliente?`)) {
        // mostra aguarde
        $("#informations_sub").hide();
        $("#wait_text, #wait_icon").show();

        // Dispara uma requisição AJAX para alternar o status
        $.ajax({
            url: "listar_propostas/onLinkPaymentGeneratorSend",
            type: "POST",
            data: { id: proposalId },
            success: function (response) {
                console.log(response); // Mensagem de retorno (opcional)
                var html = "";
                if (response.success) {

                    $('#link_payment').hide();

                    html += "<span>";
                    html += `Link de pagamento: ${response.payment_link}`;
                    html += "</span>";

                    location.reload(); // Atualiza a página para refletir a alteração

                } else {
                    html += "<span style='color:red;'>";
                    html += 'Erro ao gerar link de pagamento';
                    html += "</span>";
                }

                $('#info_content').append(html);

            },
            error: function (xhr) {
                alert('Ocorreu um erro ao alterar o status.');
            }
        });
    } else {
        // Ação caso o usuário cancele
        console.log("Ação de exclusão cancelada pelo usuário.");
    }
}


function onGenerateContract() {
    const info_data = JSON.parse($('input#info_data').val());
    let proposalId = info_data.proposal_id;

    // desabilitar botao de gerar link
    $("#link_payment").attr('disabled', true);

    // Exibe uma mensagem de confirmação
    if (confirm(`Gerar contrato para a Proposta #${proposalId}?`)) {
        // mostra aguarde
        $("#informations_sub").hide();
        $("#wait_text, #contract_icon").show();

        // Dispara uma requisição AJAX para alternar o status
        var url_old = "listar_propostas/onContractGeneratorSend";

        $.ajax({
            url: "gerenciar_contratos/onContractGeneratorSend",
            type: "POST",
            data: { id: proposalId },
            success: function (response) {
                console.log(response); // Mensagem de retorno (opcional)
                var html = "";
                if (response.success) {

                    $('#link_payment').hide();

                    location.reload(); // Atualiza a página para refletir a alteração

                } else {

                }

                // $('#info_content').append(html);

            },
            error: function (xhr) {
                alert('Ocorreu um erro ao alterar o status.');
            }
        });
    } else {
        // Ação caso o usuário cancele
        $("#informations_sub").show();
        $("#wait_text, #contract_icon").hide();

        console.log("Ação de gerar contrato cancelada pelo usuário.");
    }
}

function onUnsignedContract() {
    const info_data = JSON.parse($('input#info_data').val());
    let proposalId = info_data.proposal_id;

    // desabilitar botao de gerar link
    $("#link_payment").attr('disabled', true);

    // Exibe uma mensagem de confirmação
    if (confirm(`Remover Assinatura da Proposta #${proposalId}?`)) {
        // mostra aguarde
        $("#informations_sub").hide();
        $("#wait_text, #contract_icon").show();

        // Dispara uma requisição AJAX para alternar o status
        $.ajax({
            url: "gerenciar_contratos/onUnsignedContract",
            type: "POST",
            data: { id: proposalId },
            success: function (response) {
                console.log(response); // Mensagem de retorno (opcional)

                if (response.success) {

                    location.reload(true); // Atualiza a página para refletir a alteração

                } else {

                }


            },
            error: function (xhr) {
                alert('Ocorreu um erro ao alterar o status.');
            }
        });
    } else {
        // Ação caso o usuário cancele
        console.log("Ação de exclusão cancelada pelo usuário.");
    }
}


function onPaymentProposal(payed) {
    const info_data = JSON.parse($('input#info_data').val());
    let proposalId = info_data.proposal_id;

    console.log(info_data);

    const message = payed ? 'Liberar pagamento para a proposta?' : 'Colocar proposta em Revisão';

    let observation = '';

    if (!payed) {
        observation = $("#observation").val();
        console.log("Motivo: " + observation);
    }

    // Exibe uma mensagem de confirmação
    if (confirm(`${message}`)) {
        $('#clientDetailModal').modal('hide');
        $('#loader-overlay').show();
        // carrega o loading

        // Dispara uma requisição AJAX para alternar o status
        $.ajax({
            url: "listar_propostas/onChangeStatusAfterPaymentCommission",
            type: "POST",
            data: {
                id: proposalId,
                payed: payed ? 1 : 2,
                proposal_refusal: observation,
                action: 'payment'
            },
            success: function (response) {
                console.log(response.message); // Mensagem de retorno (opcional)
                location.reload(); // Atualiza a página para refletir a alteração
            },
            error: function (xhr) {
                alert('Ocorreu um erro ao alterar o status.');
            },
            complete: function () {
                // Remove o loader após a conclusão da requisição
                $('#loader-overlay').hide();
            },
        });


    } else {
        // Ação caso o usuário cancele
        console.log("Ação de exclusão cancelada pelo usuário.");
    }
}


function deleteImage(id, type = '') {

    if (!type || !id) {
        return false;
    }

    if (!confirm('Tem certeza que deseja excluir a imagem?')) return;

    $.post(admin_url + 'icash_tools/upload_payment_doc', {
        id: id,
        action: 'delete',
        type: type
    }).done(function (response) {

        console.log(response);
        if (response.status === 'success') {
            const doc_type = type; // ex: 'rg_frente', 'selfie', etc.
            const img = document.getElementById(doc_type);
            if (img) {
                // Troca a imagem
                img.src = 'https://icashcard.com.br/wp-content/uploads/2025/07/no-image-available-icon-vector.jpg';

                // desabilitar o clique
                const actionElements = document.querySelectorAll(`.action-${type}`);
                actionElements.forEach(el => {
                    el.style.pointerEvents = 'none';   // Desativa cliques
                    el.style.opacity = '0.6';          // Visualmente "desabilitado"
                    el.setAttribute('disabled', 'disabled'); // Se for <button> ou <input>
                });

                const deleteButton = document.getElementById(`${type}_btn`);
                if (deleteButton) {
                    deleteButton.disabled = true;
                }
            }
        }

        alert('Imagem excluída com sucesso!');
        // location.reload();
    });
}
