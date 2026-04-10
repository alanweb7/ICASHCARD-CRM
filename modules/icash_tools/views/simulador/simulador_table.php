<?php defined('BASEPATH') or exit('No direct script access allowed');

$sIndexColumn = 'id';
$pTable = db_prefix() . 'proposals';

$aColumns = [
    $pTable . '.id',
    db_prefix() . 'clients.company',
    'CONCAT(' . db_prefix() . 'staff.firstname, " ", ' . db_prefix() . 'staff.lastname) as staff_fullname',
    'cf_etapa.value as proposal_etapa',
    $pTable . '.datecreated',
    $pTable . '.update_at',
];


$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'proposals.rel_id',
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'proposals.assigned',
    'LEFT JOIN ' . db_prefix() . 'customfieldsvalues cf_etapa ON cf_etapa.relid = ' . db_prefix() . 'proposals.id AND cf_etapa.fieldto = "proposal" AND cf_etapa.fieldid = 64',
];

$where = [];

$cliente = $this->ci->input->post('filtro_cliente');
$etapa  = $this->ci->input->post('filtro_status');

if ($cliente) {
    $where[] = 'AND ' . db_prefix() . 'clients.company LIKE "%' . $this->ci->db->escape_like_str($cliente) . '%"';
}

if ($etapa) {

    $etapas = [
        1 => 'PEN - Envio Documento',
        21 => 'Em análise documental',
        22 => 'Reprova documental',
        23 => 'Link Pag. Enviado',
        24 => 'Link Pag. Aprovado',
        25 => 'Link Pag. Reprovado',
        26 => 'Aguardando formalização',
        27 => 'Em análise formalização',
        28 => 'Liberar Crédito',
        29 => 'Crédito Enviado',
        30 => 'Aguardando Confirmação',
        2  => 'Cancelada',
    ];

    $where[] = 'AND cf_etapa.value = "' . $etapas[$etapa] . '"';
}


/**
 * Filtros baseado em niveis e perfis
 */

// Obtém o ID do usuário (staff) logado
$staff_id = get_staff_user_id();

// Obter o papel do usuário
$role = $this->ci->db
    ->select('tblroles.roleid, tblroles.name')
    ->from('tblstaff')
    ->join('tblroles', 'tblroles.roleid = tblstaff.role')
    ->where('tblstaff.staffid', $staff_id)
    ->get()
    ->row();

$roleName = $role->name;
$roleID = $role->roleid;

// Obter IDs da hierarquia do GERENTE
$subordinados = $this->ci->db
    ->select('staffid')
    ->from('tblstaff')
    ->where('team_manage', $staff_id)
    ->get()
    ->result_array();

$idsRede = array_column($subordinados, 'staffid');

// Adiciona a cláusula WHERE para filtrar as propostas pelo staff logado

// Admin ou Supervisor: sem restrições
if (is_admin() || strtolower($roleName) == "supervisor") {
    // Sem where adicional
} elseif (staff_can('view_employee', 'corban_proposals')) {
    // ATENDENTE
    array_push($where, 'AND ' . $pTable . '.atendente_id = ' . $staff_id);
} elseif (staff_can('view_own', 'corban_proposals')) {
    // CORBAN
    array_push($where, 'AND ' . $pTable . '.assigned = ' . $staff_id);
} elseif (staff_can('view_network', 'corban_proposals') && strtolower($roleName) == "gerente comercial") {
    // GERENTE COMERCIAL
    $where[] = 'AND ' . $pTable . '.assigned IN (' . implode(',', !empty($idsRede) ? $idsRede : [0]) . ')';
}

$result  = data_tables_init($aColumns, $sIndexColumn, $pTable, $join, $where, []);
$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];

    foreach ($aColumns as $column) {
        if (strpos($column, 'as') !== false) {
            $col = trim(explode(' as ', $column)[1]);
            $_data = $aRow[$col];
            
        } else {

            if (in_array($column, [$pTable . '.datecreated', $pTable . '.update_at'])) {
                $_data = _dt($_data);
            }else{
                $_data = $aRow[$column];
            }

            
        }

        $row[] = $_data;
    }

    // ✅ Adiciona a coluna extra de ações
    $row[] =
    '<div class="btn-group nowrap" role="group" style="display:flex;">
        <a href="' . admin_url('proposals/proposal/' . $aRow['id']) . '" 
           class="btn btn-default btn-icon" 
           title="Ver Detalhes">
           <i class="fa fa-search"></i>
        </a>

        <a href="' . admin_url('proposals/docs/' . $aRow['id']) . '" 
           class="btn btn-default btn-icon" 
           title="Documentos">
           <i class="fa fa-folder-open"></i>
        </a>

        <a href="' . admin_url('proposals/contract/' . $aRow['id']) . '" 
           class="btn btn-default btn-icon" 
           title="Contrato">
           <i class="fa-solid fa-file-contract"></i>
        </a>
    </div>';

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
