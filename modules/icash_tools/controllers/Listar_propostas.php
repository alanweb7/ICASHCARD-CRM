<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Listar_propostas extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contracts_model');
        $this->load->model('staff_model');
        $this->load->model('proposals_model');
        $this->load->model('icash_tools_model');
    }

    public function index()
    {


        // Obtém o ID do usuário (staff) logado
        $staff_id = get_staff_user_id();


        // Obter IDs da hierarquia do GERENTE
        $subordinados = $this->db
            ->select('staffid')
            ->from('tblstaff')
            ->where('team_manage', $staff_id)
            ->get()
            ->result_array();

        $idsRede = array_column($subordinados, 'staffid');


        // Obter o papel do usuário
        $role = $this->db
            ->select('tblroles.roleid, tblroles.name')
            ->from('tblstaff')
            ->join('tblroles', 'tblroles.roleid = tblstaff.role')
            ->where('tblstaff.staffid', $staff_id)
            ->get()
            ->row();

        $roleName = $role->name;
        $roleID = $role->roleid;


        $this->db->select(
            'proposals.id, 
            proposals.subject, 
            proposals.rg_frente, 
            proposals.rg_verso, 
            proposals.cartao_frente, 
            proposals.cartao_verso, 
            proposals.selfie_identidade, 
            proposals.extra_file, 
            proposals.assigned, 
            proposals.atendente_id, 
            proposals.datecreated, 
            proposals.update_at, 
            proposals.rel_id, 
            proposals.rel_type, 
            proposals.status, 
            proposals.nsu, 
            proposals.contract_id, 
            proposals.sign_link, 
            proposals.sign_id, 
            proposals.document, 
            proposals.payment_link, 
            proposals.payment_status, 
            proposals.payment_message, 
            proposals.payment_description, 
            proposals.bank_message, 
            proposals.payment_date, 
            proposals.payment_from_manager, 
            proposals.proposal_refusal, 
            proposals.proposal_to, 
            proposals.proposal_observation, 
            staff.firstname as staff_name, 
            staff.lastname as staff_lastname, 
            customers.company as customer_name, 
            cf.value AS custom_field_value'
        );
        $this->db->from(db_prefix() . 'proposals proposals');
        $this->db->join(db_prefix() . 'clients customers', 'proposals.rel_id = customers.userid AND proposals.rel_type = "customer"', 'left');
        $this->db->join(db_prefix() . 'staff staff', 'proposals.assigned = staff.staffid', 'left');

        //JOIN com a tabela de custom fields
        $this->db->join(db_prefix() . 'customfieldsvalues cf', 'cf.relid = proposals.id AND cf.fieldid = 64 AND cf.fieldto = "proposal"', 'left');



        /**
         * ==================================
         *    FILTROS EXTRAS (DATA E STATUS)
         * ==================================
         */

        $data_inicial = to_sql_date($this->input->get('data_inicial'));
        $data_final   = to_sql_date($this->input->get('data_final'));

        if (is_admin()) {
            $data_inicial = to_sql_date($this->input->get('data_inicial') ?: date('Y-m-d'));
            $data_final   = to_sql_date($this->input->get('data_final') ?: date('Y-m-d'));
        }


        if ($data_inicial && $data_final) {
            $this->db->where('DATE(proposals.update_at) >=', $data_inicial);
            $this->db->where('DATE(proposals.update_at) <=', $data_final);
        }

        // STATUS
        $status       = $this->input->get('status');
        if ($status) {
            // Supondo que o status está em campo personalizado (tblcustomfieldsvalues.value)
            // $this->db->where('cf.value', $status);
            $this->db->where_in('cf.value', [$status]);
        }

        /**
         * ====================================================================
         */


        if ($roleID == 8) { //Analista Financeiro
            $this->db->where_in('cf.value', ['Liberar Crédito', 'Crédito Enviado']);
        }


        // Adiciona a cláusula WHERE para filtrar as propostas pelo staff logado

        if (is_admin() || strtolower($roleName) == "supervisor") {
            // Se for admin, não aplica nenhuma restrição e retorna todas as propostas
        } elseif (staff_can('view_employee', 'corban_proposals')) {
            // ATENDENTE
            $this->db->or_where('proposals.atendente_id', $staff_id);
        } elseif (staff_can('view_own', 'corban_proposals')) {
            // CORBAN
            $this->db->where('proposals.assigned', $staff_id);
        } elseif (staff_can('view_network', 'corban_proposals') && strtolower($roleName) == "gerente comercial") {
            // GERENTE COMERCIAL
            $this->db->where_in('proposals.assigned', !empty($idsRede) ? $idsRede : [0]);
        }


        // Adiciona o ORDER BY
        $this->db->order_by('update_at', 'desc'); // Ordena por data de criação, mais recente primeiro

        $propostas = $this->db->get()->result_array();


        $uniq = $this->generateHash();
        // Obtém campos customizados das propostas
        $this->load->helper('custom_fields');


        foreach ($propostas as &$proposta) {
            $proposal_id = $proposta['id'];

            $proposta['atendente'] = "";
            if ($proposta['atendente_id']) {

                $atendente = $this->staff_model->get($proposta['atendente_id']);
                $proposta['atendente'] = "{$atendente->firstname} {$atendente->lastname} ";
            }


            // formatar data
            $timestamp = strtotime($proposta['datecreated']);

            // Formatar o timestamp para o formato desejado
            $proposta['datecreated'] = date('d-m-Y H:i', $timestamp);

            // NOME FANTASIA
            $corban_id = $proposta['assigned'];
            // $proposta['staff_fullname'] = "{$proposta['staff_name']} {$proposta['staff_lastname']}";
            $proposta['staff_fullname'] = get_custom_field_value($corban_id, 27, 'staff');


            $custom_fields = get_custom_fields('proposal', ['show_on_table' => 1], $proposta['id']);
            $proposta['custom_fields'] = [];


            $proposta_hash =  $this->get_custom_field_value_db($proposta['id'], 17);

            if ($proposta_hash) {
                $token = "key=12f3-34g5-7980&hash={$proposta_hash}";
                $urlBaseImages = "https://icashcard.com.br/wp-content/uploads/imagens/propostas/serve_file.php?{$token}&file=";
            }

            $completed = true;
            $info = [
                "action"            => 'docs',
                "rg_frente"         => isset($proposta['rg_frente']) ? $urlBaseImages . $proposta['rg_frente'] . "&{$uniq}" : false,
                "rg_verso"          => isset($proposta['rg_verso']) ? $urlBaseImages . $proposta['rg_verso'] . "&{$uniq}" : false,
                "cartao_frente"     => isset($proposta['cartao_frente']) ? $urlBaseImages . $proposta['cartao_frente'] . "&{$uniq}" : false,
                "cartao_verso"      => isset($proposta['cartao_verso']) ? $urlBaseImages . $proposta['cartao_verso'] . "&{$uniq}" : false,
                "selfie_identidade" => isset($proposta['selfie_identidade']) ? $urlBaseImages . $proposta['selfie_identidade'] . "&{$uniq}" : false
            ];

            foreach ($info  as $key => $value) {
                if (!$value) {
                    $completed = false;
                }
            }

            // arquivo extra
            $info['extra_file'] = isset($proposta['extra_file']) ? $urlBaseImages . $proposta['extra_file'] . "&{$uniq}" : false;

            $info['completed'] = $completed;
            $info['hash'] = $proposta_hash;
            $info['uniq'] = $uniq;
            $info['proposal_id'] = $proposta['id'];
            $info['payment_link'] = $proposta['payment_link'];
            $info['titular_cartao'] = get_custom_field_value($proposal_id, 77, 'proposal'); //Titular cartao
            $info['n_cartao_de_credito'] = get_custom_field_value($proposal_id, 74, 'proposal'); //cartao de credito


            // CAMPOS CUSTOMIZADOS
            $cliente = [];
            $cliente_fields = [74, 90, 91, 93, 94, 95, 96, 97, 98];
            $i = 0;


            $etapa = "";
            foreach ($custom_fields as $field) {

                $info['proposal_etapa'] = $proposta['custom_fields']['Etapa'];
                $infoJson = json_encode($info);

                $etapa = $proposta['custom_fields']['Etapa'];
                $disableBtn = [];
                // if (!staff_can('status_edit_adm', 'corban_proposals')) {
                //     $disableBtn = ["Aguardando Confirmação", "Aguardando formalização", "PEN - Envio Documento", "Crédito Enviado", "Link Pag. Reprovado", "Link Pag. Aprovado", "Link Pag. Enviado", "Crédito Enviado"];
                // }
                if (!staff_can('status_edit', 'corban_proposals') && !staff_can('status_edit_adm', 'corban_proposals')) {
                    $disableBtn = ["Aguardando Confirmação", "Aguardando formalização", "PEN - Envio Documento", "Crédito Enviado", "Link Pag. Aprovado", "Link Pag. Enviado", "Crédito Enviado"];
                }

                if (!$completed) {
                    $color = "red";
                    $icon = "error";
                    $fileIcon = "../../uploads/staff_profile_images/imagens/icone-error.jpg?ver=123";
                } else {
                    $color = "#f9bb03";
                    $fileIcon = "../../uploads/staff_profile_images/imagens/icone-ok.jpg";
                }

                if ($field['name'] == "DOC") {

                    $disableDocsArray = ["Crédito Enviado", "Descartado"];
                    // Defina a URL do link

                    $can_edit_docs = staff_can('edit_doc', 'corban_proposals') && (!in_array($etapa, $disableDocsArray) || is_admin());

                    $icon_html = '<i class="fa fa-folder" style="color: ' . $color . '; font-size: 1.8em;" aria-hidden="true"></i>';

                    $html = '';
                    if ($can_edit_docs) {

                        $html .= '<button type="button" class="btn btn-dark btn-floating" data-mdb-ripple-init>';
                        $viewDocs = true;
                        if ($roleName == "CORBAN") {
                            $viewDocs = false;
                        }

                        $html .= '<a href="#" onclick="openDocsProposal(' . htmlspecialchars($infoJson, ENT_QUOTES, 'UTF-8') . '); return false;" 
           title="Documentos da Proposta" class="doc-link">' . $icon_html . '</a>';

                        $html .= '</button>';
                    } else {

                        $html .= '<button type="button" class="btn btn-dark btn-floating" data-mdb-ripple-init disabled>';

                        if (staff_can('view_doc', 'corban_proposals')) {
                            $html .= '<a href="#" onclick="viewDocsProposal(' . htmlspecialchars($infoJson, ENT_QUOTES, 'UTF-8') . ', \'viewOnly\'); return false;" 
           title="Documentos da Proposta" class="doc-link">' . $icon_html . '</a>';
                        } else {
                            $html .= $icon_html;
                        }



                        $html .= '</button>';
                    }


                    $valor_campo = $html;
                } elseif ($field['name'] == "CPF") {
                    $valor_campo =  $this->get_custom_field_value_db($proposta['id'], $field['id']);
                    if (!empty($valor_campo)) {
                        // $valor_campo = $this->ofuscar_numeros($valor_campo);
                        $valor_campo = $valor_campo;
                    }
                } else {
                    $valor_campo = $this->get_custom_field_value_db($proposta['id'], $field['id']);
                }

                /**
                 * CONTEUDO DADOS DO CLIENTE
                 */

                if (in_array($field['id'], $cliente_fields)) {
                    $cliente[$field['slug']] = $valor_campo ?? "";
                }

                /**
                 * CONTEUDO COLUNA CONTRATO
                 */

                // informacoes do contrato
                $contract_id = $proposta['contract_id'];
                $noClick = "";
                $base_url = "#";

                $signed = false;

                // verifica se a etapa atual passou de "Em análise formalização"
                $isAnalyzed = $this->onVerifyStatusProposal($etapa, "Em análise formalização");

                if ($contract_id) {

                    $base_url = $proposta['sign_link'];

                    if ($isAnalyzed) {
                        $signed = true;
                    }

                    if (!$base_url) {
                        $contract = $this->onGetContractInformations($proposta['contract_id']);
                        $signed = $contract->signed;
                        $hash = $contract->hash;
                        $base_url = base_url() . "contract/{$contract_id}/{$hash}";
                    } else {
                        $document = $proposta['document'];
                        if ($signed) {
                            $base_url = "https://secret.autentique.com.br/documentos/{$document}/assinado.pdf";
                        }
                    }

                    $proposta['contrato'] = [
                        "contract_id" => $proposta['contract_id'],
                        "signed" => false,
                        "link" => "#"
                    ];

                    $color = "orange";
                    $class = "warning";
                    $txt = "Aguardando";
                    $icon = '<i class="fa fa-file" aria-hidden="true"></i>';

                    if ($signed) {
                        $color = "green";
                        $class = "success";
                        $txt = "Assinado";
                        $icon = '<i class="fa-solid fa-file-signature"></i>';
                    }
                } else {
                    $color = "#d0cfcd";
                    $class = "secondary";
                    $txt = "Não Gerado";
                    $noClick = " no-click ";
                    $icon = '<i class="fa fa-file" aria-hidden="true"></i>';
                }

                // CONTRATO

                $html = '';

                // mostrar par ADMIN
                if (staff_can('contract_click_link', 'client_contract')) {

                    $html .= '<button type="button" id="dv-contract-' . $contract_id . '" class="btn btn-outline-success btn-floating" data-mdb-ripple-init data-mdb-ripple-color="dark">';
                    $html .= '<a href="' . $base_url . '" target="_NEW" title="' . $txt . '" class="' . $noClick . '" style="text-align:center; display:inline-block;">';
                    $html .= '<span class="span-icon-contract" style="font-size: x-large; color: ' . $color . ';">' . $icon . '<span>';
                    $html .= '</a>';
                    $html .= '</button>';

                    // $html .= '<div id="dv-contract-' . $contract_id . '" style="margin-left:5px;margin-right:5px;"><a href="' . $base_url . '" target="_NEW" title="' . $txt . '" class="' . $noClick . '" style="text-align:center; display:inline-block;">';
                    // $html .= '<span class="span-icon-contract" style="font-size: x-large; color: ' . $color . ';">' . $icon . '<span>';
                    // $html .= '</a></div>';
                } else {
                    $html .= '<button type="button" class="btn btn-outline-success btn-floating" data-mdb-ripple-init data-mdb-ripple-color="dark" disabled>';
                    $html .= '<span class="span-icon-contract" style="font-size: x-large; color: ' . $color . ';">' . $icon . '<span>';
                    $html .= '</button>';
                    // $html .= '<div id="dv-contract-' . $contract_id . '" style="margin-left:5px;margin-right:5px;"><span class="span-icon-contract" style="font-size: x-large; color: ' . $color . ';"><i class="fa-solid fa-file-signature"></i><span></div>';
                }

                $proposta['contrato']['content'] = $html;

                // CONTRATO
                $proposta['custom_fields'][$field['name']] = $valor_campo;
                $i++;
            }


            // DADOS DO CLIENTE LINK MODAL DE DETALHES

            $rg = $this->get_custom_field_value_db($proposta['id'], 99, 'proposal');
            $data_nasc = $this->get_custom_field_value_db($proposta['id'], 100, 'proposal');
            $data_nasc = date('d-m-Y', strtotime($data_nasc)); // Converte para d-m-Y

            // CAMPOS EXTRAS
            $telefone   = $this->get_custom_field_value_db($proposta['id'], 91, 'proposal'); //telefone
            $vl_liq     = $this->get_custom_field_value_db($proposta['id'], 16, 'proposal'); //telefone
            $vl_parcela = $this->get_custom_field_value_db($proposta['id'], 14, 'proposal'); //telefone
            $tabela     = $this->get_custom_field_value_db($proposta['id'], 67, 'proposal'); //telefone
            $prazo      = $this->get_custom_field_value_db($proposta['id'], 13, 'proposal'); //telefone


            $valor_convertido = function ($valor) {
                $float = floatval(str_replace(',', '.', str_replace('.', '', $valor)));
                return number_format($float, 2, '.', ','); // <-- ponto decimal, vírgula milhar
            };

            $proposta['vl_liq']     = $vl_liq     ? $valor_convertido($vl_liq)     : 0.0;
            $proposta['vl_parcela'] = $vl_parcela ? $valor_convertido($vl_parcela) : 0.0;

            $proposta['telefone']         = $telefone;
            $proposta['tabela']           = $tabela;
            $proposta['prazo']            = $prazo;

            $proposal_to = $proposta['proposal_to'] ?? "";

            $cliente['proposal_to']             = mb_strtoupper($proposal_to, 'UTF-8');
            $cliente['proposal_fields']         = $proposta['custom_fields'];
            $cliente['proposal_id']             = $proposta['id'];
            $cliente['payment_link']            = $proposta['payment_link'] ?? "";
            $cliente['payment_status']          = $proposta['payment_status'] ?? "";
            $cliente['payment_message']         = $proposta['payment_message'] ?? "";
            $cliente['payment_description']     = $proposta['payment_description'] ?? "";
            $cliente['proposal_refusal']        = $proposta['proposal_refusal'] ?? "";
            $cliente['proposal_observation']    = $proposta['proposal_observation'] ?? "";
            $cliente['bank_message']            = $proposta['bank_message'] ?? "";
            $cliente['payment_date']            = $proposta['payment_date'] ?? "";
            $cliente['cliente_rg']              = $rg ?? "";
            $cliente['cliente_data_nasc']       = $data_nasc ?? "";

            $infoJson = json_encode($cliente);


            $html =  '<div style="text-align:center; font-size:20px; margin-right:5px">';
            $html .= '<a href="#" class="custom-link" onclick="openModalclientDetail(' . htmlspecialchars($infoJson, ENT_QUOTES, 'UTF-8') . '); return false;" title="Detalhes da Proposta" style="text-align:center;">';
            $html .= '<span><i class="fas fa-search"></i></span>';
            $html .= '</a>';
            $html .= '</div>';
            $proposta['details'] = $html;


            $icon = '<i class="fas fa-cloud-download-alt"></i>'; // Ícone moderno de nuvem com seta
            $html = sprintf(
                '<div class="download-wrapper">
        <a href="#" class="download-link" title="Baixar comprovante" onclick="onDownloadDoc(%d); return false;">%s</a>
    </div>',
                $cliente['proposal_id'],
                $icon
            );

            $proposta['download'] = $html;




            // CONFIGURAR ACA DO BOTAO ETAPA STATUS DA PROPOSTA
            $proposta['Etapa'] = $etapa;
            $background = false;

            // testar etapa
            if (isset($_GET['debug']) && $_GET['debug'] == "99") {
                $etapa = 'Liberar Crédito';
            }


            switch ($etapa) {
                case 'PEN - Envio Documento':
                case 'PEN - Doc. Ilegível':
                case 'Em análise documental':
                    $class = "warning";
                    break;

                case 'Reprova documental':
                case 'Link Pag. Reprovado':
                case 'Cancelada':
                case 'Descartado':
                    $class = "danger";
                    break;

                case 'Link Pag. Enviado':
                    $class = "info";
                    break;

                case 'Link Pag. Aprovado':
                    $class = "info_two";
                    break;

                case 'Crédito Enviado':
                    $class = "success";
                    break;

                case 'Aguardando formalização':
                case 'Aguardando Formalização':
                case 'Em análise formalização':
                    $class = "secondary";
                    break;

                case 'Operação Finalizada':
                    $class = "success";
                    break;

                case 'Liberar Crédito':
                    $class = "info";
                    $background = "background-color: #ebe204 !important; color: #0a0a0a !important;";
                    break;

                default:
                    $class = "primary";
                    break;
            }

            // Inicializa o botão com padrão de "somente visualização"
            $action = '';
            $button_attributes = 'style="width: 190px; pointer-events: none;" class="btn btn-' . $class . '"';

            if ($background && $etapa === "Liberar Crédito") {
                $button_attributes = 'style="' . $background . 'width:190px;" class="btn btn-' . $class . '"';
            }

            // Verifica permissões de edição de status
            if (in_array($etapa, $disableBtn)) {
                $button_attributes = 'style="width: 190px; pointer-events: none;" class="btn btn-' . $class . '"';
            } elseif (staff_can('status_edit', 'corban_proposals')) {
                // Verifica se é "super_manager" sem ser admin e etapa específica
                if (staff_can('edit', 'super_manager') && !is_admin()) {
                    if ($etapa === "Liberar Crédito") {
                        $action = ' onclick="openModalclientDetail(' . htmlspecialchars($infoJson, ENT_QUOTES, 'UTF-8') . '); return false;" ';
                    } else {
                        $button_attributes = 'style="width:190px;pointer-events: none;" class="btn btn-' . $class . '"';
                    }


                    // $button_attributes = 'style="width: 190px; pointer-events: none;" class="btn btn-' . $class . '" data-proposal-id="' . $proposal_id . '"';
                } else {
                    // Caso seja admin ou tenha outras permissões, habilita o modal de edição

                    $button_attributes = 'style="width: 190px;" class="btn btn-' . $class . '"  data-toggle="modal" data-target="#editProposalStatusModal"';
                }
            } else {
                $button_attributes = 'style="width: 190px; pointer-events: none;" class="btn btn-' . $class . '"';
            }



            // Constrói o botão final
            $status_texto_atual = htmlspecialchars($etapa);

            $btn_status = "
            <button type=\"button\" 
            data-proposal-id=\"{$proposal_id}\" 
            data-status-text=\"{$status_texto_atual}\" 
            {$button_attributes} {$action} > {$etapa} </button>";

            // Adiciona o botão ao campo personalizado da proposta
            $proposta['custom_fields']['Etapa'] = $btn_status;
        }


        $data = [
            'propostas' => $propostas
        ];


        // Carregar a view passando os dados corretamente
        $this->load->view('listar_propostas', $data);
    }


    // FILTROS COM AJAX
    public function lista_ajax()
    {
        $data_inicial = $this->input->get('filtro_data_inicial');
        $data_final   = $this->input->get('filtro_data_final');

        $roleID = get_staff_role(); // ou sua lógica de role

        $aColumns = ['tblproposals.id', 'tblproposals.subject', 'tblproposals.datecreated'];
        $sIndexColumn = 'id';
        $sTable = db_prefix() . 'proposals';

        $where = [];

        // Filtro por intervalo de datas
        if ($data_inicial && $data_final) {
            $di = to_sql_date($data_inicial);
            $df = to_sql_date($data_final);

            $where[] = "AND DATE(tblproposals.datecreated) BETWEEN '$di' AND '$df'";
        }

        // Filtro por role
        if ($roleID == 8) {
            $this->db->join('tblcustomfieldsvalues cf', 'cf.relid = tblproposals.id AND cf.fieldto = "proposals"', 'left');
            $where[] = "AND cf.value IN ('Liberar Crédito', 'Crédito Enviado')";
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, []);
        $output = $result['output'];
        $rResult = $result['rResult'];

        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = $aRow['id'];
            $row[] = $aRow['subject'];
            $row[] = _d($aRow['datecreated']);
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }





    public function generateHash()
    {
        // Obtém o timestamp atual
        $timestamp = microtime(true);

        // Gera um hash (usando SHA1 como exemplo)
        $hash = sha1($timestamp);

        // Retorna os 7 primeiros caracteres do hash
        return substr($hash, 0, 7);
    }

    public function get_custom_field_value_db($proposal_id, $custom_field_id, $table = 'proposal')
    {
        $sql = "SELECT cfv.value
                FROM tblcustomfieldsvalues as cfv
                JOIN tblcustomfields as cf ON cfv.fieldid = cf.id
                WHERE cfv.relid = ?
                AND cf.id = ?
                AND cfv.fieldto = '{$table}'";

        $query = $this->db->query($sql, array($proposal_id, $custom_field_id));

        // return json_encode($query);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->value;
        } else {
            return null;
        }
    }

    public function onVerifyStatusProposal($etapa_nome = '', $etapa_atual = "Em análise formalização")
    {

        if (!$etapa_nome) return false;

        $status_etapas = array_map('trim', explode("\n", get_option('proposal_step_status')));

        $etapa_numero = array_search($etapa_nome, $status_etapas);
        $etapa_atual_numero = array_search($etapa_atual, $status_etapas);

        if ($etapa_numero !== false && $etapa_atual_numero !== false && $etapa_numero >= $etapa_atual_numero) {
            return true;
        }

        return false;
    }







    public function ofuscar_numeros($numero)
    {
        // Expressão regular para encontrar grupos de dígitos entre pontos
        $padrao = '/(\d{3})\.(\d{3})\.(\d{3})-(\d{2})/';

        // Substitui os dois grupos intermediários por asteriscos
        $substituicao = '$1.***.***-$4';

        // Aplica a substituição usando preg_replace
        $resultado = preg_replace($padrao, $substituicao, $numero);

        return $resultado;
    }

    public function onGetContractInformations($id)
    {


        $this->db->select('signed, hash');
        $this->db->from(db_prefix() . 'contracts');
        $this->db->where('id', $id);

        $query = $this->db->get();

        return $query->row(); // Retorna um único objeto

        // $contract = $this->contracts_model->get($id);
        // return $contract;
    }

    public function my_ajax_function()
    {
        // Exemplo de dados recebidos
        $data = $this->input->post();

        // Faça algo com os dados
        $response = ['success' => true, 'message' => 'Requisição AJAX recebida com sucesso!', 'data' => $data];

        // Retorne uma resposta JSON
        echo json_encode($response);
        die(); // Termina a execução
    }

    public function onDeleteProposal()
    {
        $id = $this->input->post('id');

        if (!$id) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }
        if (!staff_can('delete', 'corban_proposals') && !is_admin()) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }

        $this->load->model('proposals_model');

        $delete = $this->proposals_model->delete($id);

        if ($delete) {
            set_alert('success', 'Proposta ' . $id . ' excluída com sucesso.');
            return;
        }

        set_alert('danger', 'Erro ao excluir a proposta');
    }

    public function aproveDocumentsInProposal()
    {
        $id = $this->input->post('id');
        $motivo = $this->input->post('observation');

        if (!$id) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }

        $this->load->model('proposals_model');
        $proposal = $this->proposals_model->get($id);


        // Dados para atualização
        $updateData = [
            'rel_id' => $proposal->rel_id,
            'rel_type' => $proposal->rel_type,
            'assigned' => $proposal->assigned,
            'status' => 5,
            'custom_fields' => [
                'proposal' => [
                    64 => "Reprova documental"
                ]
            ]
        ];


        // Atualizando no banco de dados
        $update = $this->proposals_model->update($updateData, $proposal->id);

        if($update){
            set_alert('success', "Proposta {$id} atualizada com sucesso!");
        }
        // chamada antes de atualizar o histórico
        $contentTxt = "Reprova documental: \n Motivo: {$motivo}";

        hooks()->do_action('before_history_register', [
            'id_registro' => $proposal->id,
            'module'      => 'proposals',
            'history' => [
                [
                    'method'        => __FUNCTION__,
                    'step'          => $contentTxt,
                    'status'        => "Reprova documental",
                    'description'   => $motivo,
                    'event'  => 'Reprova documental'
                ]
            ]
        ]);



        set_alert('danger', "Erro ao editar a proposta {$id}");
    }


    // GERAR E ENVIAR LINK DE PAGAMENTO
    public function onLinkPaymentGeneratorSend()
    {

        $this->load->model('icash_history_model');

        $id = $this->input->post('id');

        header('Content-Type: application/json');

        if (!$id) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }


        if (!staff_can('edit', 'corban_proposals') && !is_admin()) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }

        $this->load->model('proposals_model');
        $proposal = $this->proposals_model->get($id);

        if (!$proposal) {
            echo json_encode(['success' => false, 'message' => 'Proposta não encontrada.']);
            exit;
        }


        $checkout = self::onCheckoutGeneratorInCieloApi($proposal);

        // EM CASO DE ERRO AO GERAR LINK

        if (!$checkout['payment_link']) {
            $response = [
                'success' => false,
                'message' => 'Erro ao gerar link de pagamento',
                "response_checkout" =>  $checkout
            ];

            echo json_encode($response);
            exit;
        }




        $this->load->model('proposals_model');
        $proposal = $this->proposals_model->get($id);

        /**
         * ENVIAR NOTIICAÇÃO PARA O CLIENTE
         */

        $notifications_enabled = get_option('is_active_notification');
        $telefone = get_custom_field_value($id, 91, 'proposal'); //telefone

        if ($telefone && $notifications_enabled) {
            $proposal->customer_phone = preg_replace('/\D/', '', $telefone);

            $customer = $proposal->proposal_to;

            $text =  "{$customer}\n\n";
            $text .=  "Seus documentos para o pedido #{$id} foram aprovados com sucesso! 🎉\n\n";
            $text .=  "Agora é só acessar o link abaixo para efetivar o processo com cartão de crédito:\n\n";
            $text .=  "{$checkout['payment_link']}\n\n";
            $text .=  "Ficamos à disposição para qualquer dúvida!";

            $data = [
                "number" =>  "55" . $proposal->customer_phone,
                "text" =>  $text
            ];

            // enviar link para o cliente
            $this->icash_tools_model->sendWhatsNotifications($data);
        }


        // atualizar histórico
        $contentTxt = "Link de pagamento gerado: \n 🔗 {$checkout['payment_link']}";

        hooks()->do_action('before_history_register', [
            'id_registro' => $proposal->id,
            'module'      => 'proposals',
            'history' => [
                [
                    'method' => __FUNCTION__,
                    'step'       => $contentTxt,
                    'status'      => $checkout['payment_link'],
                    'event'  => 'Gerar Link de pagamento'
                ]
            ]
        ]);


        // Retorne uma resposta JSON
        $response = [
            'success' => true,
            'message' => 'Proposta ' . $proposal->id . ' encontrada.',
            'payment_link' => $checkout['payment_link'] // ou qualquer outra info
        ];

        set_alert('success', 'Link e pagamento gerado com sucesso!');
        echo json_encode($response);
        exit;
    }



    // ENVIAR LINK De DOCUMENTOS PENDENTES
    public function onSendLinkDocuments()
    {

        $this->load->model('icash_history_model');

        $id = $this->input->post('id');
        $link_documentos = $this->input->post('link_documentos');
        $motivo = $this->input->post('motivo');

        // HEADER JSON
        header('Content-Type: application/json');


        if (!$id) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }


        if (!staff_can('edit', 'corban_proposals') && !is_admin()) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }

        $this->load->model('proposals_model');
        $proposal = $this->proposals_model->get($id);
        $telefone = get_custom_field_value($id, 91, 'proposal'); //telefone


        $notifications_enabled = get_option('is_active_notification');


        if ($telefone && $notifications_enabled) {
            $proposal->customer_phone = preg_replace('/\D/', '', $telefone);

            $customer = $proposal->proposal_to;

            $text =  "Olá, {$customer}\n\n";
            $text .=  "Sua solicitação #{$id} de Conversão de Crédito se encontra PENDENTE.\n\n";
            $text .=  "MOTIVO: {$motivo}\n\n";
            $text .=  "Segue link para sanar pendências: {$link_documentos}";

            $data = [
                "number" =>  "55" . $proposal->customer_phone,
                "text" =>  $text
            ];

            // enviar link para o cliente
            $sendFromWhatsapp = $this->icash_tools_model->sendWhatsNotifications($data);
        }


        // Dados para atualização
        $updateData = [
            'rel_id' => $proposal->rel_id,
            'rel_type' => $proposal->rel_type,
            'assigned' => $proposal->assigned,
            'status' => 4,
            'custom_fields' => [
                'proposal' => [
                    64 => "PEN - Envio Documento"
                ]
            ]
        ];


        // Atualizando no banco de dados
        $update = $this->proposals_model->update($updateData, $proposal->id);

        // chamada antes de atualizar o histórico
        $contentTxt = "Link de documentos gerado: \n 🔗 {$link_documentos} \n Motivo: {$motivo}";

        hooks()->do_action('before_history_register', [
            'id_registro' => $proposal->id,
            'module'      => 'proposals',
            'history' => [
                [
                    'method'        => __FUNCTION__,
                    'step'          => $contentTxt,
                    'status'        => $link_documentos,
                    'description'   => $motivo,
                    'event'  => 'Link de documentos gerado'
                ]
            ]
        ]);



        // $history = $this->icash_history_model->insert($data);

        // Retorne uma resposta JSON
        echo json_encode([
            'success' => true,
            'message' => 'Link de documentos enviado com sucesso',
            'whatsapp_api' => $sendFromWhatsapp
        ]);

        exit;
    }


    /**
     * LIBERAR PAGAMENTO DOS CORBANS
     * O GESTOR BANCARIO LIBERA O PAGAMENTO DOS CORBANS
     * MUDAR STATUS DA PROPOSTA
     */

    public function onChangeStatusAfterPaymentCommission()
    {

        $proposal_id = $this->input->post('id', TRUE);

        $etapa = '';

        // $observation = $this->input->post('proposal_observation', TRUE) ?? NULL;
        $proposal_refusal = $this->input->post('proposal_refusal', TRUE);
        $payed = $this->input->post('payed', TRUE);
        // $action = $this->input->post('action', TRUE) ?? NULL; //payment


        if (!$proposal_id) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }

        if (!staff_can('edit', 'super_manager') && !is_admin()) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }

        $proposal = $this->proposals_model->get($proposal_id);

        if ($payed == 1) {
            $etapa = "Crédito Enviado";
        }

        if ($payed == 2) {
            $etapa = "Pagamento Devolvido";
        }

        // Dados para atualização
        $updateData = [
            'rel_id'                    => $proposal->rel_id,
            'rel_type'                  => $proposal->rel_type,
            'assigned'                  => $proposal->assigned,
            'custom_fields' => [
                'proposal'  => [
                    64 => $etapa,
                ]
            ]
        ];

        // if ($observation) {
        //     $updateData['proposal_observation'] = $observation;
        // }


        /**
         * ENVIAR NOTIICAÇÃO PARA O CLIENTE
         */

        $notifications_enabled = get_option('is_active_notification');

        if ($payed == 1 && $notifications_enabled) {
            $updateData['status'] = 3;



            $telefone = get_custom_field_value($proposal_id, 91, 'proposal'); //telefone

            if ($telefone) {
                $proposal->customer_phone = preg_replace('/\D/', '', $telefone);

                $customer = $proposal->proposal_to;

                $text =  "{$customer}\n\n";
                $text .=  "A solicitação foi concluída com sucesso e você já pode conferir o valor em sua conta!! 🤑🤑\n\n";
                $text .=  "Agradecemos a preferência, até breve!";


                $data = [
                    "number" =>  "55" . $proposal->customer_phone,
                    "text" =>  $text
                ];

                // enviar link para o cliente
                $this->icash_tools_model->sendWhatsNotifications($data);
            }

            /**
             * ENVIAR NOTIICAÇÃO PARA O CLIENTE
             */
        }

        if ($proposal_refusal) {
            $updateData['proposal_refusal'] = $proposal_refusal;
        }


        $update = $this->proposals_model->update($updateData, $proposal_id);

        if ($update) {

            /**
             * GERAR COMISSÃO PARA A REDE
             */
            $this->onGenerateInvoiceForPayment($proposal);

            // chamada antes de atualizar o histórico
            $contentTxt = $etapa;
            if ($payed == 2) {
                $contentTxt = "{$etapa}: \n Motivo: {$proposal_refusal}";
            }


            hooks()->do_action('before_history_register', [
                'id_registro' => $proposal_id,
                'module'      => 'proposals',
                'history' => [
                    [
                        'method' => __FUNCTION__,
                        'step'       => $contentTxt,
                        'status'      => $etapa,
                        'event'  => 'Liberar pagamento'
                    ]
                ]
            ]);
        }


        set_alert('success', 'Proposta attualizada com sucesso! (#' . $proposal_id . ')');
        return true;
    }



    public function onGenerateInvoiceForPayment($proposal)
    {

        $proposal_id = $proposal->id;

        // Recupera os campos personalizados da proposta
        $proposal_custom_fields = [];
        $custom_fields = get_custom_fields('proposal'); // Obtém os campos personalizados da proposta
        foreach ($custom_fields as $field) {
            $proposal_custom_fields[$field['slug']] = get_custom_field_value($proposal_id, $field['id'], 'proposal');
        }

        $totalLiq = $proposal_custom_fields['proposal_total_liq'];
        $TotalLiqFormatado = str_replace(['.', ','], ['', '.'], $totalLiq);



        $invoice_data = array(
            'clientid' => $proposal->rel_id,
            'sale_agent' => $proposal->assigned,
            'number' => get_option('next_invoice_number'),
            'date' => date('Y-m-d'),
            'duedate' => date('Y-m-d', strtotime('+30 days')),
            'currency' => $proposal->currency,
            'subtotal' => $TotalLiqFormatado,
            'total' => $TotalLiqFormatado,
            'status' => 2, // Pago
            'billing_street' => $proposal->address,
            'newitems' => $proposal->items,
            'allowed_payment_modes' => [1],
            'proposal_id' => $proposal_id
        );

        // Cria a fatura
        $this->load->model('invoices_model');
        $this->load->model('payments_model');

        $invoice_id = $this->invoices_model->add($invoice_data);

        // Se a fatura foi criada com sucesso, insere os campos personalizados mapeados
        if ($invoice_id) {
            // Lança o pagamento
            $payment_data = array(
                'amount' => $proposal->total,
                'invoiceid' => $invoice_id,
                'paymentmode' => 5,
                'date' => date('Y-m-d'),
                'daterecorded' => date('Y-m-d H:i:s'),
                'note' => 'Pagamento automático via hook de proposta aceita.',
            );

            // Adiciona o pagamento e marca a fatura como paga
            $this->payments_model->add($payment_data);

            // atuualiza a data de pagamento na proposta

            // Dados para atualização
            $updateData = [
                'rel_id'                    => $proposal->rel_id,
                'rel_type'                  => $proposal->rel_type,
                'assigned'                  => $proposal->assigned,
                'payment_from_manager'      => date('Y-m-d H:i:s')
            ];

            $update = $this->proposals_model->update($updateData, $proposal_id);

            log_message('info', 'Fatura criada e marcada como paga automaticamente para a proposta: ' . $proposal_id);
        } else {
            log_message('error', 'Erro ao criar fatura para a proposta: ' . $proposal_id);
        }
    }



    public function onCheckoutGeneratorInCieloApi($proposal)
    {

        $token = $this->onTokenGeneratorInCieloApi();

        if (!$token) {
            return false;
        }

        $proposal_id = $proposal->id;

        $total_bruto =  $this->get_custom_field_value_db($proposal_id, 15);
        $parcelas =  $this->get_custom_field_value_db($proposal_id, 13);

        // Remover o ponto dos milhares e substituir a vírgula por um ponto
        $valorFormatado = str_replace(['.', ','], ['', '.'], $total_bruto);

        // Converter para número inteiro
        $valorNumerico = (int) round((float) $valorFormatado * 100);

        $total = (int) (floatval($proposal->total) * 100);

        // $parcelas = (int)  floatval($proposal->items[0]['qty']);
        $tabela = $proposal->items[0]['description'];

        $dataForSend = [
            "OrderNumber" => $proposal_id,
            "sku" => $tabela,
            "type" => "Digital",
            "name" => "CONVERSÃO DE CRÉDITOS",
            "price" => $valorNumerico,
            "quantity" => "1",
            "maxNumberOfInstallments" => $parcelas,
            "fixedinstallments" => $parcelas,
            "shipping" => [
                "type" => "WithoutShipping"
            ]
        ];


        // return true;

        $jsonData = json_encode($dataForSend);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://cieloecommerce.cielo.com.br/api/public/v1/products/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'accept: application/json',
                'content-type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $data = json_decode($response);

        if (isset($data->id) && !empty($data->id)) {


            // Dados para atualização
            $updateData = [
                'rel_id' => $proposal->rel_id,
                'rel_type' => $proposal->rel_type,
                'assigned' => $proposal->assigned,
                'status' => 4,
                'payment_link' => $data->shortUrl,
                'link_id' => $data->id,
                'custom_fields' => [
                    'proposal' => [
                        64 => "Link Pag. Enviado"
                    ]
                ]
            ];

            // $updateData = (array) $proposal;

            // $updateData['custom_fields']['proposal'][64] = "Link Pag. Enviado";

            // unset($updateData['id']);

            // Carregando o modelo de propostas
            $this->load->model('proposals_model');

            // Atualizando no banco de dados
            $update = $this->proposals_model->update($updateData, $proposal_id);

            // Verificar se foi bem-sucedido
            if ($update) {
                $data = [
                    'status' => 4,
                    'payment_link' => $data->shortUrl,
                    "etapa" => "Link Pag. Enviado"
                ];
                return $data;
            } else {
                return false;
            }
        }


        return $data;
    }


    // DELETE LINK CIELO
    public function onDeleteLinkCielo($id)
    {

        $token = $this->onTokenGeneratorInCieloApi();

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://cieloecommerce.cielo.com.br/api/public/v1/products/' + $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' + $token,
                'accept: application/json',
                'Cookie: _abck=0B07292A5FCCB2C7F006CFD8EE4C24BD~-1~YAAQiJs+F0OyR1+XAQAAC1U5Zg4izsSMOhVFHs89R2nwm69TGTsJ9LTA/YooBvZDlavGnCKhijJA8abCR8ezkuN1shLUsLlIW3toZQkjI6h7qslNMJamJsdLuitzQsDuozUVF+9gDvjgOOPHEpRhvXhbky0KobgBpBrjpDMqz0s06XL8e1zW1pMjkGTVYQSxsYV3WM6T8cab2Kp+IlClZ57hs5FDbi/j0cAw4ouqwEz7T980C5tEwakWFOUnQtuP4gfRaeKgnJipzjkni9GOVCL7NC7NP2xFIOpxVGxIU4QKJeLPGRcYrL/5ntKj9FYyEEpE81GQAY5fLhUIT9tD/wi8GodcwnMl7IdgiZMN3EDSyjB6zdR6f3lZ2OkdXP34AIU1XIo+37TRarb81JuA9ABoxzoZacjlk7H0m1P1KFfua+iG7u658+jJskTYyFSvBaBZkBJQ5KhQ3Tm7xciLd9I=~-1~-1~-1; ak_bmsc=09186CB18AAEBE1B61E371F1DF943534~000000000000000000000000000000~YAAQiJs+F0SyR1+XAQAAC1U5ZhySn4ZzAbFzyZ4kakTxT7TCzCI5/TB1huA8HsOUgsZGj1oC9bMK4Ewn3Qmeo7dUllgvK/XKoHLTrvcRg21atOvLj9pJr+YvsKI81Hm/KwojJpR0fpOKE0/BGfuty49w2mIo734/h3oWRW2/YQhJBp5mnPw+DfTfs2LumChZI1xQ4PqUQkq4oNaC09hoZsELCeri/LzBb/LVXy8hmOZcdQ/z8N4flOs45FR4gklOZgPMCrQqNcO8Q1jLtb3g+y7+CyvOA4CidtWOwBHgtnL55YCTlYuhdK0wHI5Jh/ZFsOTLnGp/mhTDDt9HpcfyVSvYlMK/yFfLKs58RwKk; bm_sv=72E2F00A23B9B9E72C03F8E5662FE8C7~YAAQiJs+FxX+R1+XAQAAcLs6ZhxYKGZdaCk0dnaOKk4ujrowWf+JCLgwQEy1hdcy/PqwwqWgr+iBNdN0klYy17MEjk/O6hy4JBd+o1+MH1gRpIzwWNsHvWGUYKrgKlp55Fx5csmFdZKV2zHPgyUPlxNAS53kBMAbeaKqrUCfx2o4KN/Xbh3fEH5qqt1QR1FST1sRFOsgzIlZJxlzHHTjQByi+SmZg9wYSc64KGPPSFn1t2Za2QDr4TYFY+PrLS3MGnk=~1; bm_sz=C715948D8FD1CAC3AA82872C14EAB01A~YAAQiJs+F0WyR1+XAQAAC1U5ZhwMAFQOyrsPQFXMzPD1uYqSxSVq0I9YPVRBNFs7ywupcZSOfDsGzo+A7qfrjidaLdYgalpVUF5ycCqyvzH3ywf1b6fAiR6NMg2Fq9OQJvgazucvbwDFko+dBhR8Dy0c/IXtFcxuMZDfMAkUej+BtxZGOUf1vlKTk8thp9olssOIdhBBOOfS6PeaH741VXlEuv2CAmrqiQc/zjOqR6x4SHUvAUjCtcAmWZGdUe7I0QOM7+ITD1fAx9pJ36zcmeZGKRo2KUO1jF6WU/j6YEMTw/O/sC9XR2hEtv+PPz5fWTf3fzU7gPLXZ2R5WFSx9VqTZucNKnubeEon4maW6TDu~3618098~4403523; ARRAffinity=9e687ec3761280577f52971e78710466e511988b738c8aae8b08b61078e08b1c; _abck=0E068338A302B32C4B55AFE93AEEDD33~-1~YAAQxkLbF1vLomWXAQAASMg7Zg6gwpc7IyhHBzlXet7rfQzYDzo8byOkATk6SbhmOVGJuW11v0PYEg0eFLi8KIvNYrk+cUlFBEFgqsKCrVtxFdUwSmzlvZQgggj9phTgsoDeKxRxpO/s6JIDpFGmuUdZwc06vQ3sk9jQb/er4+tTiUIagkJHQEJBVavVbYI/yvdSjBgL7cEZCeDpeSrf3vR1AbFTRunQ/TlGwq+TStxO1B7J9Kab0f5tOM8aQ8FGRFe10XLK9vlvDbadUgTMNuNPGa9q+PUduMFHdBGIqNJk976eedNwYbrQYGLC5L5nMBLK5MdH232V4cvyFA/AnohgIceWlfL5V0IsO5DdTfX/R9+66hfMzFaBdpX+PDJ/d4qI4vrvGl5RUgbgqbsvHLzi52f2TVyzj9znRgKRszwlrFs5NceA6UMiHzpTXk1HjWn9~-1~-1~-1; bm_sz=2ED42A79341EEC8014B046A6FAED9407~YAAQxkLbF1zLomWXAQAASMg7ZhzIc7R+zajvV/qoLFxoEvayYEnijcV0pWcZH5RbxB/B3jBYdweDF4sozukdeCi7NTGZiBcr9t9cBdGAhERQ497AwlU+ueNKuesXljQFTyziOytSK3gcHSVy0p/jfz05CvgGsGAxxuxr9p4cviXId4QdQQ3F0+i0sJhfbVMtI/krITdNuQGOQjVVEvgdWp0xVpbbk+LbhgevB0ltksRm5bjJ1zFgkjZtq5I+sFQ7Rifwlp91vFAch3mQULFxBvlWNsBlscestryaEftiqCq1yq9GYHXQQsicgtQSBPBDOfnLv9UWvjoncxoAZQ87gwntmpm8vNhsvnHxPqIKvWun~4272198~4405060'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }




    /**
     * FUNCOES DE NOTIFICACOES
     * Nao editar a partir daqui
     */

    public function onNotificationTowebhook($data)
    {

        $webhook_notification = get_option('webhook_notification');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $webhook_notification,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function onTokenGeneratorInCieloApi()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://cieloecommerce.cielo.com.br/api/public/v2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic NDNkZWZhYTUtNDBlMS00YTQ0LTg4YWUtMGY5NzQxMjU1Nzg0OlNldVZYSGhrL0VhUFBHLzZIOG0ydkNsb3lDajRDTTh1OUFwVGJONlMxZzQ9',
                'Content-Type: application/x-www-form-urlencoded',
                'accept: application/json',
                'Cookie: _abck=EC601157E905482963D172BEE29F6198~-1~YAAQRt7aF2wK/ZOUAQAAP3RElQ11blaxW1FWWKp0Ct4LuvWWkAiz3MZ6XSDWr9o5x4oqrXQehyjej6WsgOMUpBJ8ioYFnRTro7aoGhIfhF1txqmGqKmKNMtHRdbrTxXMZ8Rcq/3zOxaBKOnqbWyCJFgs6P7Q6rsSre1TrpHECe5BBQeUXOu8BZ0vH/7O/T9nO3GEpsQNK5CvlmJ+ExR4X+hyxTIQBlHHbjUHhZsuVKSTV8cmSIDl2M604I8jW3ki5qDrvMVfC4W9tX8QZVy3P3emK0HPTHKdM0OHYYYwB1mX1D+b79xAZjEtIdKnDedlPGtz5lsgWKQfYI0RZuEw+I5MW2PRoGMQbTvheFmGT3/XF9BWseKdRHJsUR1Td2qOD6R6MI0iAEPT/7duMkRL6Q==~-1~-1~-1; bm_sz=892EE4086C3E35281ADD45161DF6CD5B~YAAQE2jcF/k3l3KUAQAA24gSlRrahIhOYPhgRe7erFnccZDMiieAEalwnzIl8z2NKCUp1217AEnBomk1AfYtROP+BiNyVLYKpERWn2q9hbaHSgsUcQIXM+EJLJlqNBRLX7Kknn20DDsx2DDy9dZdwr1HP9iD0Ceh68wFb6pMEvvxL/9nxlrqZ2nTvxcVeO7qo/2wjJnGVC54PT5P50/UUcqomTBpaBEG9FeGsifsvf+N9DowENcv0Un1dtyGO2q15/qHEihyWQdzxFwLeEtkLRGea1JE1JGKiNMRHXU1YZyppRW/iNEE60+yq4twJtLjew+GLzr0RMBwiaZel6Te9uosTgiu+v/FaHQOc78ANzuZ7d80D+NzFX0BzochUSpGVepGp9ArX55E~3359033~3159096; ARRAffinity=dd6c47bbfb1eac9b1ebc80752da623400adcbd1b729ccdd49fe78f01c6200759; _abck=969E995F78277F20B17190D2D6B8BDFB~-1~YAAQhUIVAtWIHKWUAQAAyJ2xlA1Ll2VCLxoy7sGo/SGP1myt2WtWhGsOqqTKInUqsRCvlf2tt+TumDLi54eDBr4WfEPtQdQglzB8slH/UoNCcoN9RJT9PzzQN2PT7R93KLBzsMls67dU6CPzdcf7vSa3T8uaGwvtCf2LxWyhtegAwXGhrkf5Lt9vPr228PITsFHw35sUVp5r/OyzKbXaTeaX42lxzAVrfTqilm/GOxOK9bbQijSE3xLAn2a2dmcbYlnsfOujeRGfbrMOtFMO6fD802jbhlXIJ2WJUE4eG+MDrKy5dMyjICiz5is3313XOwqqYFI/5xV4WGdhfYcYOTL175Bdoklm/h7BNC1sutk5/vNwQBAgBTv++BIHn6M19RGcJ1TkpyLTDKdyhXPjdcJKbYCcsVIgAMg3xA==~-1~-1~-1; bm_sz=C8C7901CC8D74E0D419EEB19B0A62A2B~YAAQhEIVAiV1M5WUAQAAOl5ElRrazA2iUEjx6GLMynNPwFnvtdCtJEvjYl7l+SfhlS3W3tHYNUacNb2W/h4cLCIDVCWIoFXp0ibfj7YC5zG0FpLR+QLFCsiSCgtx4p+a+G0WH4s38rPl335dvg0nWKhpJYtbwhOIEiJQhT19qI6fOukNlNyqpyAKXp51NSuSjcknLF+hYd1GNsImrcIk52BHEIGYwjn8mtopANSGsEDwmd2PhEsOx3/m9MkKK4dPJLycpDevZm+2M5JDq+AXayJhjnacUZYfEdxYOgElZdloBqNMPFaw2OEWPlqNh8CzUniqEkutk30/elk2pZy3EGxqdavo+gEVGnW/DJ3W38Kg~3683891~3748932'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($response);

        if (isset($data->access_token) && !empty($data->access_token)) {
            return $data->access_token;
        }

        return false;
    }
}
