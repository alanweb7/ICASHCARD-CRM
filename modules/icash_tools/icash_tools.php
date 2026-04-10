<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Icash Card Advanced Tools
Description: icashCard - Ferramentas avançadas
Version: 7.0.0
Author: Alan Silva
Author URI: http://alan.tec.br/alan-silva-oficial
*/
/**
 * INFORMACOES:
 * LINK DE NOTIFICACAO DA CIELO: https://icashcard.com.br/wp-json/custom/v1/process-data/status/12
 * MODULO QUE RECEBE OS DADOS: API
 * CONTROLLER QUE RECEBE OS DADOS: lINK_MANAGER
 */

define('ICASH_TOOLS_MODULE_NAME', 'icash_tools');
define('ICASH_TOOLS_CLIENTS_UPLOADS', 'uploads/icash_tools/clients/');


// Register activation and deactivation hooks
register_activation_hook(ICASH_TOOLS_MODULE_NAME, 'icash_tools_activation_hook');
register_deactivation_hook(ICASH_TOOLS_MODULE_NAME, 'icash_tools_deactivation_hook');

// Register language files
register_language_files(ICASH_TOOLS_MODULE_NAME, [ICASH_TOOLS_MODULE_NAME]);

// Hook for admin menu initialization
hooks()->add_action('admin_init', 'icash_tools_admin_init_menu_items');


//inject permissions Feature and Capabilities for webhooks module
// hooks()->add_filter('staff_permissions', 'icash_tools_module_permissions_for_staff');
hooks()->add_filter('admin_init', 'icash_tools_module_permissions_for_staff');

// Activation hook
function icash_tools_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

// Deactivation hook
function icash_tools_deactivation_hook()
{
    require_once(__DIR__ . '/uninstall.php');
}

// ATUALIZA OS MERGES FIELDS
hooks()->add_filter('contract_merge_fields', 'icash_custom_merge_fields_injection', 10, 2);

/**
 * Modifica os merge fields antes de serem aplicados no conteúdo do contrato
 * 
 * @param array $fields Campos de merge atuais
 * @param object $contract Objeto do contrato
 * @return array
 */
function icash_custom_merge_fields_injection($fields, $contract)
{
    $CI = &get_instance();

    // Carrega os models necessários
    $CI->load->model('proposals_model');

    // Garante que o contrato foi carregado com informações completas
    $contractDetails = $CI->db
        ->select('proposal_id')
        ->from(db_prefix() . 'contracts')
        ->where('id', $contract['id'])
        ->get()
        ->row();

    // Agora você pode acessar:
    $proposal_id = $contractDetails ? $contractDetails->proposal_id : null;

    $documentos = '';

    // Se existir proposta vinculada, pode extrair dados dela
    if (!empty($proposal_id)) {
        $proposal = $CI->proposals_model->get($proposal_id);

        if ($proposal) {
            // Personaliza ainda mais com base na proposta
            $documentos .= on_merge_fields_documents($proposal_id);
        }
    }

    // Substitui o valor do campo personalizado (ajuste a chave se necessário)
    $fields['{contracts_documentos_cliente}'] = $documentos;

    return $fields;
}

// Admin menu items
function icash_tools_admin_init_menu_items()
{
    require_once __DIR__ . '/includes/sidemenu_links.php';
}


// Adiciona a ação ao hook quando o staff edita o perfil
hooks()->add_action('edit_logged_in_staff_profile', 'custom_module_handle_staff_custom_fields', 5);

function custom_module_handle_staff_custom_fields($dataL)
{

    $data = $_POST;
    $affectedRows = 0;

    if (isset($data['custom_fields'])) {
        $custom_fields = $data['custom_fields'];
        if (handle_custom_fields_post(get_staff_user_id(), $custom_fields)) {
            $affectedRows++;
        }
        unset($_POST['custom_fields']);
    }
}


function onNotificationWebhookUrl($data)
{

    $webhook_notification = get_option('webhook_notification');

    $dataJson = json_encode($data);
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
        CURLOPT_POSTFIELDS => $dataJson,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    // echo $response;
}


// Registra o hook para incluir o JavaScript
hooks()->add_action('app_admin_head', 'carregar_script_propostas');

function carregar_script_propostas()
{
    // Verifica se estamos na página de propostas
    $CI = &get_instance();
    $current_url = $CI->uri->uri_string();

    // Modifique esta condição com o URI específico da página onde deseja carregar o script
    if (strpos($current_url, 'admin/proposals') !== false) {
        echo '<script src="' . base_url('modules/icash_tools/assets/js/icash-tools-proposals-utils.js') . '"></script>';
        echo '<link rel="stylesheet" href="' . base_url('modules/icash_tools/assets/css/icash-tools-proposals-styles.css') . '">';
    }

    if (strpos($current_url, 'admin/icash_tools/listar_comissoes') !== false) {
        echo '<script src="' . base_url('modules/icash_tools/assets/js/icash-tools-proposals-utils.js?ver=12.7') . '"></script>';
    }
}


// REDIRECIONA CASO ESTEJA LOGADO
hooks()->add_action('after_staff_login', 'icash_tools_redirect_to_dash');

function icash_tools_redirect_to_dash()
{
    $CI = &get_instance();
    // Verifica se o usuário está logado
    if (is_staff_logged_in()) {
        $current_uri = uri_string();

        // Se estiver na URL "access_denied"
        if ($current_uri == 'access_denied') {
            redirect(admin_url('icash_tools/custom_dashboard'));
            exit;
        } else {
            redirect(admin_url('icash_tools/custom_dashboard'));
        }
    }
}



hooks()->add_action('app_admin_head', 'change_dashboard_menu_link');
function change_dashboard_menu_link()
{
?>
    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                // Altera o link do menu "Painel"
                var dashboardMenuLink = document.querySelector('aside ul > li > a[href="<?php echo admin_url(''); ?>"]');
                if (dashboardMenuLink) {
                    dashboardMenuLink.setAttribute('href', '<?php echo admin_url('icash_tools/custom_dashboard'); ?>');
                }
            });
        })();
    </script>
    <?php
}



// desabilitar o mennu Definições
hooks()->add_action('app_admin_head', 'control_setup_menu_visibility');

function control_setup_menu_visibility()
{
    // IDs dos usuários permitidos a ver o menu "Definições"
    $allowed_user_ids = [1]; // Substitua pelos IDs que devem ter acesso

    // Obtém o ID do usuário logado
    $current_user_id = get_staff_user_id();

    if (!in_array($current_user_id, $allowed_user_ids)) {
    ?>
        <script>
            (function() {
                document.addEventListener('DOMContentLoaded', function() {
                    // Esconde o menu "Definições" com base no ID do li
                    var setupMenuItem = document.getElementById('setup-menu-item');
                    if (setupMenuItem) {
                        setupMenuItem.style.display = 'none';
                    }
                });
            })();
        </script>
    <?php
    }
}


hooks()->add_action('app_init', 'intercept_script_loading');

function intercept_script_loading()
{

    $CI = &get_instance();
    $current_url = $CI->uri->uri_string(); // Obtém a URL atual.

    // Verifica se a URL corresponde a uma página onde o script deve ser removido.
    if (preg_match('/^admin\/clients\/client\/\d+$/', $current_url)) {
        // Remove o script que foi enfileirado globalmente.
        ob_start(function ($buffer) {
            // Remove o script específico.
            return str_replace(
                '<script src="' . poly_utilities_common_helper::get_assets_minified('modules/poly_utilities/dist/assets/js/public/head.js') . '"></script>',
                '',
                $buffer
            );
        });
    }
}

hooks()->add_action('app_admin_head', function () {

    $CI = &get_instance();  // Obtém uma instância do CodeIgniter
    $CI->load->model('staff_model');
    $CI->load->model('custom_fields_model');
    $CI->load->model('roles_model');

    // Obtém o ID do staff logado
    $staff_id = $CI->session->userdata('staff_user_id');
    // $staff_details = $CI->staff_model->get($staff_id);


    $infoStaff = onGetNameStaffByID($staff_id);

    $nome       = $infoStaff['name'];
    $role_name  = $infoStaff['role_name'];

    // if (isset($_GET['debug']) && $_GET['debug'] == 77) {
    //     echo "<pre>";
    //     var_dump($staff_details);
    //     echo "</pre>";
    //     die();
    // }

    // // // Fazendo uma consulta para pegar os campos personalizados do staff
    // $CI->db->select('cfv.value, cf.name');
    // $CI->db->from('tblcustomfieldsvalues cfv');
    // $CI->db->join('tblcustomfields cf', 'cf.id = cfv.fieldid', 'left');
    // $CI->db->where('cfv.fieldto', 'staff');  // Relaciona base staff
    // $CI->db->where('cfv.relid', $staff_id);  // Relaciona com o staff pelo ID

    // $custom_fields = $CI->db->get()->result_array();

    // // Verifica se a consulta retornou resultados
    // $infoData = [];
    // if (count($custom_fields) > 0) {
    //     foreach ($custom_fields as $field) {
    //         $infoData[$field['name']] = $field['value'];
    //     }
    // }

    // $nome = $infoData["Nome Fantasia"] ?? $staff_details->full_name;

    // $levelLow = [1];
    // if (in_array($staff_details->role, $levelLow)) {
    //     $nome = $staff_details->full_name;
    // }


    $html = <<<HTML
<div class="custom-profile-box">
    <!-- <div class="custom-profile-avatar">
        <i class="fa fa-user-circle"></i>
    </div> -->
    <div class="custom-profile-info">
        <div class="custom-profile-name">{$nome}</div>
        <div class="custom-profile-role">{$role_name} <span class="custom-profile-id">ID: {$staff_id}</span></div>
    </div>
</div>
HTML;

    // Estilos modernos com bordas suaves e responsividade
    $style = <<<STYLE
<style>
    .top-icon.notifications,
    ul.nav.navbar-nav.navbar-right.-tw-mt-px,
    span.tw-inline-flex.tw-items-center.tw-gap-x-3.tw-pt-0\\.5,
    .tw-flex.tw-flex-1.sm\\:tw-flex-initial,
    button.poly-copy-default.btn.btn-default.btn-sm.btn-copy-search-keywords {
        display: none !important;
    }

    .dropdown-toggle.profile,
    .dropdown.sidebar-user-profile {
        height: auto;
    }

    .custom-profile-box {
        display: flex;
        align-items: center;
        background: linear-gradient(90deg, #e2e2f3, #f7f7fb);
        border: 1px solid #c3c3e0;
        border-radius: 10px;
        padding: 12px 20px;
        gap: 15px;
        font-family: 'Segoe UI', sans-serif;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .custom-profile-avatar {
        font-size: 38px;
        color: #4e4e8f;
    }

    .custom-profile-info {
        display: flex;
        flex-direction: column;
    }

    .custom-profile-name {
        font-weight: bold;
        font-size: 14px;
        color: #333;
    }

    .custom-profile-role {
        font-size: 14px;
        color: #666;
    }

    .custom-profile-id {
        font-size: 13px;
        color: #999;
        margin-left: 8px;
    }
</style>
STYLE;


    $script = <<<SCRIPT
<script>
window.addEventListener('DOMContentLoaded', function () {
    var el = document.querySelector(".dropdown.sidebar-user-profile");
    if (el) {
        el.style.display = "block";
        el.innerHTML = `{$html}`;
    }
});
</script>
SCRIPT;

    echo $style;
    echo $script;
});

function get_custom_field_value_db($proposal_id, $custom_field_id, $module = 'proposal')
{
    $CI = &get_instance();  // Obtém uma instância do CodeIgniter

    $sql = "SELECT cfv.value
            FROM tblcustomfieldsvalues as cfv
            JOIN tblcustomfields as cf ON cfv.fieldid = cf.id
            WHERE cfv.relid = ?
            AND cf.id = ?
            AND cfv.fieldto = '{$module}'";

    $query = $CI->db->query($sql, array($proposal_id, $custom_field_id));

    // return json_encode($query);
    if ($query->num_rows() > 0) {
        $row = $query->row();
        return $row->value;
    } else {
        return null;
    }
}


function convert_to_decimal($value)
{
    // Remove os pontos (separador de milhar)
    $value = str_replace('.', '', $value);

    // Substitui a vírgula pelo ponto (separador decimal)
    $value = str_replace(',', '.', $value);

    // Converte para float ou decimal
    return (float) $value;
}


hooks()->add_filter('available_merge_fields', 'add_custom_merge_fields');

function add_custom_merge_fields($merge_fields)
{
    $merge_fields[] = [
        'name'      => 'Custom Field',
        'key'       => '{custom_field}', // O nome do campo merge
        'available' => 'contracts',   // Disponível em contratos
    ];

    return $merge_fields;
}



function on_merge_contract_asigned($contract_id)
{

    $CI = &get_instance();
    $CI->load->model('icash_tools/icash_tools_model');
    // $CI->load->model('contracts');

    if (method_exists($CI->icash_tools_model, 'get_client_assignature_by_contract')) {

        $signed = $CI->icash_tools_model->get_client_assignature_by_contract($contract_id);
    }

    $url = $signed ? site_url("uploads/contracts/{$contract_id}/signature.png") : site_url("uploads/contracts/icon-signed-612x612.jpg");
    $urlBase = [
        0 => site_url("uploads/contracts/icon-signed-612x612.jpg"),
        1 => site_url("uploads/contracts/{$contract_id}/signature.png")
    ];

    return '<img src="' . $url . '" alt="Assinatura do Cliente" style="width: 200px; height: auto; border: 1px solid #ccc; padding: 10px;">';
}


function on_merge_fields_documents($proposal_id)
{
    $CI = &get_instance();
    $CI->load->model('icash_tools/icash_tools_model');

    // Verifica se o modelo carregado contém o método antes de usá-lo
    if (method_exists($CI->icash_tools_model, 'get_documents_client_by_contract')) {
        // Substitua '123' pelo ID ou parâmetro correto, caso necessário
        $proposal = $CI->icash_tools_model->get_documents_client_by_contract($proposal_id);
        $fieldsLinks = ["proposal_rg_frente", "proposal_rg_verso", "proposal_cartao_c_frente", "proposal_cartao_c_verso"];

        $hash = $proposal->custom_fields['proposal_proposta_hash'];
        $length = 6;
        $TimeHash = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', $length)), 0, $length);


        if ($hash) {
            $token = "key=12f3-34g5-7980&hash={$hash}";
            $urlBaseImages = "https://icashcard.com.br/wp-content/uploads/imagens/propostas/serve_file.php?{$token}&ver={$TimeHash}&file=";
        } else {
            return "";
        }

        ob_start();

    ?>

        <style>
            img {
                width: 220px !important;
                height: auto;
            }
        </style>

        <table>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $html = '<tr>';
                if ($proposal->rg_frente) {
                    $html .= '<td><img src="' . $urlBaseImages . $proposal->rg_frente . '" alt="Imagem 1" class="img-contract"></td>';
                }
                if ($proposal->rg_verso) {
                    $html .= '<td><img src="' . $urlBaseImages . $proposal->rg_verso . '" alt="Imagem 1" class="img-contract"></td>';
                }
                if ($proposal->selfie_identidade) {
                    $html .= '<td><img src="' . $urlBaseImages . $proposal->selfie_identidade . '" alt="Imagem 1" class="img-contract"></td>';
                }

                $html .= '</tr>';
                $html .= '<tr>';
                if ($proposal->cartao_frente) {
                    $html .= '<td><img src="' . $urlBaseImages . $proposal->cartao_frente . '" alt="Imagem 1" class="img-contract"></td>';
                }
                if ($proposal->cartao_verso) {
                    $html .= '<td><img src="' . $urlBaseImages . $proposal->cartao_verso . '" alt="Imagem 1" class="img-contract"></td>';
                }
                if ($proposal->extra_file) {
                    $html .= '<td><img src="' . $urlBaseImages . $proposal->extra_file . '" alt="Imagem 1" class="img-contract"></td>';
                }
                $html .= '</tr>';
                // $html .= '<tr>';
                // if ($proposal->selfie_identidade) {
                //     $html .= '<td><img src="' . $urlBaseImages . $proposal->selfie_identidade . '" alt="Imagem 1" class="img-contract"></td>';
                // }
                // $html .= '</tr>';
                echo $html;
                ?>
            </tbody>
        </table>

<?php
        // Limpar o buffer de saída
        $htmlOutput = ob_get_clean();
        return $htmlOutput;
    } else {
        return 'Método testeCustom não encontrado no modelo icash_tools_model.';
    }
}


/**
 * UPDATE ROLES MASS
 * Atualiza as permissoes do novo staff criado
 */

hooks()->add_action('staff_member_created', 'atualizar_permissoes_novo_staff');

function atualizar_permissoes_novo_staff($staff_id)
{
    $CI = &get_instance();
    $CI->load->model('roles_model'); // Carrega o model de roles

    // Obtém os dados do novo funcionário
    $CI->db->where('staffid', $staff_id);
    $staff = $CI->db->get(db_prefix() . 'staff')->row();

    // Se não encontrar o staff, sai da função
    if (!$staff) {
        return;
    }

    $roleID = $staff->role;

    // Obtém as permissões do Role ID na tabela 'roles'
    $CI->db->where('roleid', $roleID);
    $role = $CI->db->get(db_prefix() . 'roles')->row();

    // Se não encontrar o role ou se não houver permissões, sai da função
    if (!$role || empty($role->permissions)) {
        return;
    }

    // Desserializa as permissões armazenadas na coluna 'permissions'
    $permissoes = unserialize($role->permissions);

    // Verifica se há permissões para o role
    if (!empty($permissoes) && is_array($permissoes)) {
        foreach ($permissoes as $feature => $capabilities) {
            foreach ($capabilities as $capability) {
                $data = [
                    'staff_id'   => $staff_id,  // Novo staff recebe as permissões
                    'feature'    => $feature,
                    'capability' => $capability
                ];
                $CI->db->insert(db_prefix() . 'staff_permissions', $data);
            }
        }
    }


    if ($roleID == 4) { //CORBAN

        // atualizar a hierarquia do CORBAN
        $team_manage = $staff->team_manage;
        $updateStaff = onUpdateStaffCommissions($staff);
    }
}


function onUpdateStaffCommissions($staff)
{

    $CI = &get_instance();

    $salesman = $staff->staffid;
    $coordinator = $staff->team_manage;

    $data = [
        'salesman'   => $salesman,  // Novo staff recebe as permissões
        'coordinator'    => $coordinator,
        'percent' => 0.5
    ];
    $CI->db->insert(db_prefix() . 'commission_hierarchy', $data);

    // applicable commission
    $data = [
        'commission_policy'   => 3,  // tabela cielo padrao no momento
        'applicable_staff'    => $salesman,
        'addedfrom' => get_staff_user_id() ?? 1
    ];
    $CI->db->insert(db_prefix() . 'applicable_staff', $data);
}


function onSendToWebhookNotificatio($staff_id)
{

    $data = [
        "data" => $staff_id
    ];

    $jsonData = json_encode($data);
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://webhook.site/19f4c1e0-d4f7-4b9f-971e-63c300205a37',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
}


/**
 * ACOES DO CONTRATO
 */

hooks()->add_action('after_contract_signed', 'verifica_assinatura_contrato');

function verifica_assinatura_contrato($data)
{
    $CI = &get_instance();
    $contract_id = $data['id'];

    // Busca o contrato atualizado
    $contract = $CI->contracts_model->get($contract_id);

    // Verifica se está assinado e se o campo da assinatura foi preenchido
    if ($contract && !empty($contract->signature)) {

        $notification = onNotificationWebhookUrl($contract);
        $proposal_id = $contract->proposal_id;


        $contractCF = get_custom_field_value_db($proposal_id, 64);

        if ($contractCF) {
            // Defina as condições para a exclusão
            $CI->db->where('relid', $proposal_id);       // ID da proposta
            $CI->db->where('fieldid', 64);      // ID do campo personalizado
            $CI->db->where('fieldto', 'proposal'); // Tipo do registro associado
            // Realiza a exclusão
            $deleted = $CI->db->delete(db_prefix() . 'customfieldsvalues');
        }


        // Dados para inserir
        $data = [
            'relid'   => $proposal_id,      // ID da Proposta
            'fieldid' => 64,    // ID do campo personalizado
            'fieldto' => 'proposal', // Tipo do registro associado (exemplo: customers, proposals, etc.)
            'value'   => "Em análise formalização", // Valor do campo personalizado
        ];

        // Insere na tabela tblcustomfieldsvalues
        $inserted = $CI->db->insert(db_prefix() . 'customfieldsvalues', $data);
    }
}

hooks()->add_action('after_activity_log_added', 'verifica_assinatura_contrato_by_log');

function verifica_assinatura_contrato_by_log($log_data)
{

    $notification = onNotificationWebhookUrl($log_data);
    if (strpos($log_data['description'], 'Contract signed by client') !== false) {
        // Pegue $log_data['rel_id'] para obter o ID do contrato
        $contract_id = $log_data['rel_id'];
        // Sua ação aqui
    }
}



/**
 * PERMISSOES
 */

function icash_tools_module_permissions_for_staff($permissions)
{

    $capabilities = [];

    $links_permissions['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_filters'   => _l('Ver Filtros de relatórios'),
        'view_my_link'   => _l('Ver link (Cadastro CORBAN)'),
        'view_receipt'   => 'Ver Recibos' . '(' . _l('permission_global') . ')',
        'paid_receipt'   => 'Pagar recibos',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        // 'process_commissions'   => _l('Processar Comissão'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('corban_links', $links_permissions, _l('Permissões de Links'));


    $corban_capabilities['capabilities'] = [
        'view_own'          => _l('permission_view')  . '(Criadas pelo CORBAN)', //criadas por ele
        'view'              => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_details'      => _l('Ver detalhes') . '(' . _l('permission_global') . ')',
        'view_employee'     => _l('permission_view') . '(Criadas pelo Atendente)',
        'view_network'      => _l('permission_view') . '(Criadas pela sua REDE)',
        'create'            => _l('permission_create'),
        'edit'              => _l('permission_edit'),
        'approve_doc'       => _l('Aprovar Documentos'),
        'edit_doc'          => _l('Editar Documentos'),
        'view_doc'          => _l('Visualizar Documentos'),
        'status_edit'       => _l('proposal_status_edit'),
        'status_edit_adm'   => _l('ADM editor de Status'),
        'view_col_staff'    => _l('view_col_staff'), //VER COLUNA CORBAN
        'view_col_atd'      => _l('view_col_atd'), //VER COLUNA ATENDENTE
        'delete'            => _l('permission_delete'),
    ];

    register_staff_capabilities('corban_proposals', $corban_capabilities, _l('corban_proposals'));

    $contract_capabilities['capabilities'] = [
        'view_own'   => _l('permission_view')  . '(' . _l('permission_sis_corban') . ')',
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'contract_click_link'   => _l('contract_click_link'),

    ];
    register_staff_capabilities('client_contract', $contract_capabilities, _l('client_contract_access'));

    $user_capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('user_manager', $user_capabilities, _l('user_manager'));


    $global_capabilities['capabilities'] = [
        'view_users'        => _l('Ver menu usuários'), // VER MENU ATENDENTES
        'view_employee'     => _l('Ver menu Atendentes'), // VER MENU ATENDENTES
        'view_corbans'     => _l('Ver menu CORBAN\'s'), // VER MENU CORBANS
        'view_admins'       => _l('Ver menu ADMIN\'s'), // VER MENU ADMIN'S
        'create'            => _l('permission_create'),
        'edit'              => _l('permission_edit'),
        'delete'            => _l('permission_delete'),
    ];
    register_staff_capabilities('icash_globals', $global_capabilities, _l('Permissões globais do sistema'));


    // ANALISTA FINANCEIRO
    $super_manager_capabilities['capabilities'] = [
        'view_own'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'edit'   => 'Liberar o Pagamento',
    ];
    register_staff_capabilities('super_manager', $super_manager_capabilities, _l('Super Admin'));

    // ANALISTA FINANCEIRO
    $super_finance_manager['capabilities'] = [
        'view_own'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'edit'   => 'Liberar o Pagamento',
    ];
    register_staff_capabilities('finance_manager', $super_finance_manager, _l('Diretor Financeiro'));


    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_filters'   => _l('Ver Filtros de relatórios'),
        'view_receipt'   => 'Ver Recibos' . '(' . _l('permission_global') . ')',
        'paid_receipt'   => 'Pagar recibos',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        // 'process_commissions'   => _l('Processar Comissão'),
        'delete' => _l('permission_delete'),
    ];


    register_staff_capabilities('corban_commissions', $capabilities, "Faturamento e Comissões (ICASHCARD)");
    register_staff_capabilities('icash_tools', $capabilities, _l('permissions_default'));
    register_staff_capabilities('icash_tables', $capabilities, _l('table_control'));
}


// perissoes globais para o javascript
hooks()->add_action('app_admin_head', function () {
    if (is_staff_logged_in()) {
        $CI = &get_instance();
        $staff = $CI->staff_model->get(get_staff_user_id());

        $custom_data = [
            'user_name' => $staff->firstname,
            'can_view_propostas' => has_permission('corban_proposals', '', 'view'),
            'can_approve_doc' => has_permission('corban_proposals', '', 'approve_doc'),
        ];

        echo '<script>';
        echo 'const icash_view = 123;';
        echo 'window.customPerfexVars = ' . json_encode($custom_data) . ';';
        echo '</script>';
    }
});


// HOOK: Registrar histórico
hooks()->add_action('before_history_register', 'before_history_register');

function before_history_register($payload)
{
    $CI = &get_instance();
    $table = "tblicash_history";

    if (!is_array($payload)) {
        return false; // nada a fazer
    }

    $id_registro = $payload['id_registro'] ?? null;
    $module      = $payload['module'] ?? 'proposal';

    $staff_id    = get_staff_user_id() ?? null;

    if (!$id_registro || empty($payload['history'])) {
        return false; // inválido
    }

    // Normaliza histórico recebido
    $new_entries = [];
    foreach ($payload['history'] as $entry) {
        $new_entries[] = array_merge($entry, [
            'staff_id'   => $staff_id,
            'version'    => $entry['version'] ?? "2.0",
            'event_date' => date('Y-m-d H:i:s'),
            'module'     => $module,
        ]);
    }

    // Buscar histórico existente
    $row = $CI->db->where('id_registro', $id_registro)
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get($table)
        ->row();

    if ($row) {
        // Decodifica histórico anterior
        $history = json_decode($row->historico, true);
        if (!is_array($history)) {
            $history = [];
        }

        // Adiciona novas entradas
        $history = array_merge($history, $new_entries);

        // Atualiza registro
        $CI->db->where('id_registro', $id_registro)
            ->update($table, [
                'staff_id'     => $staff_id,
                'historico'    => json_encode($history),
                'date_updated' => date('Y-m-d H:i:s'),
            ]);
    } else {
        // Criar novo histórico
        $history = $new_entries;

        $CI->db->insert($table, [
            'id_registro' => $id_registro,
            'staff_id'    => $staff_id,
            'modulo'      => $module,
            'historico'   => json_encode($history),
            'date_created'  => date('Y-m-d H:i:s'),
        ]);
    }

    // Dispara notificação externa
    onNotificationWebhookUrl([
        'id_registro'   => $id_registro,
        'method'        => 'history_register',
        'module'        => $module,
        'history'       => $new_entries,
    ]);
}

function onGetNameStaffByID($staff_id)
{

    $CI = &get_instance();  // Obtém uma instância do CodeIgniter
    $CI->load->model('staff_model');
    $CI->load->model('custom_fields_model');
    $CI->load->model('roles_model');

    // Obtém o ID do staff logado
    // $staff_id = $CI->session->userdata('staff_user_id');
    $staff_details = $CI->staff_model->get($staff_id);

    $role_name = "";
    if ($staff_details) {
        $role = $CI->roles_model->get($staff_details->role);
        if ($role) {
            $role_name =  $role->name;
        }
    }


    if (isset($_GET['debug']) && $_GET['debug'] == 77) {
        echo "<pre>";
        var_dump($staff_details);
        echo "</pre>";
        die();
    }

    // // Fazendo uma consulta para pegar os campos personalizados do staff
    $CI->db->select('cfv.value, cf.name');
    $CI->db->from('tblcustomfieldsvalues cfv');
    $CI->db->join('tblcustomfields cf', 'cf.id = cfv.fieldid', 'left');
    $CI->db->where('cfv.fieldto', 'staff');  // Relaciona base staff
    $CI->db->where('cfv.relid', $staff_id);  // Relaciona com o staff pelo ID

    $custom_fields = $CI->db->get()->result_array();

    // Verifica se a consulta retornou resultados
    $infoData = [];
    if (count($custom_fields) > 0) {
        foreach ($custom_fields as $field) {
            $infoData[$field['name']] = $field['value'];
        }
    }

    $nome = $infoData["Nome Fantasia"] ?? $staff_details->full_name;

    $levelLow = [1];
    if (in_array($staff_details->role, $levelLow)) {
        $nome = $staff_details->full_name;
    }

    $data = [
        "name" => $nome,
        "role_name" => $role_name,
    ];

    return $data;
}
