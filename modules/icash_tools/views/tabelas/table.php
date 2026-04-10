<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Define as colunas a serem usadas
$aColumns = [
    'nome_tabela',  // Nome da tabela
    'credenciadora' // Credenciadora
];

// Define a coluna de índice
$sIndexColumn = 'id';
$sTable       = db_prefix().'icash_tabelas'; // Nome da tabela no banco de dados

// Inicializa a tabela de dados
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

// Itera sobre os resultados e cria as linhas da tabela
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        // Adiciona links e opções apenas para a coluna "nome_tabela"
        if ($aColumns[$i] == 'nome_tabela') {
            $_data = '<a href="' . admin_url('icash_tools/tabela_view/' . $aRow['id']) . '">' . e($_data) . '</a>';
            $_data .= '<div class="row-options">';
            $_data .= '<a href="' . admin_url('icash_tools/tabela_view/' . $aRow['id']) . '">' . _l('view') . '</a>';

            if (staff_can('edit', 'icash_tools')) {
                $_data .= ' | <a href="' . admin_url('icash_tools/update_table/' . $aRow['id']) . '">' . _l('edit') . '</a>';
            }
            if (staff_can('delete', 'icash_tools')) {
                $_data .= ' | <a href="' . admin_url('icash_tools/delete_table/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }

            $_data .= '</div>';
        } else {
            // Outras colunas apenas exibição de dados
            $_data = e($_data);
        }
        
        $row[] = $_data;
    }

    // Adiciona opções de edição na última coluna
    $options = icon_btn('icash_tools/edit_table/' . $aRow['id'], 'pencil-square-o');
    $row[]   = $options;

    $output['aaData'][] = $row;
}

