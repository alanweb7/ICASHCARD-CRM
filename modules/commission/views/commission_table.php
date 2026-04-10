<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .agente-linha {
        width: 95%;
        background-color: #000a0a;
        color: #fff;
        position: absolute;
        padding: 5px;
        border-radius: 4px;
    }

    .td_container {
        height: 40px;
    }

    .total-comissao {
        float: right;
    }

    .table-commission th,
    .table-commission td {
        /* white-space: nowrap; */
        /* Evita que o texto quebre para a próxima linha */
    }

    .client_col {
        width: 150px !important;
        white-space: nowrap;
        /* Largura personalizada para a coluna 'Cliente' */
    }

    .table-commission th:nth-child(1),
    .table-commission td:nth-child(1) {
        white-space: nowrap;
        width: 150px !important;
        /* Coluna 'Cliente' */
    }

    .table-commission th:nth-child(2),
    .table-commission td:nth-child(2) {
        width: 120px;
        /* Coluna 'Contrato' */
    }

    .table-commission th:nth-child(3),
    .table-commission td:nth-child(3) {
        width: 100px;
        /* Coluna 'Banco' */
    }

    .table-commission th:nth-child(4),
    .table-commission td:nth-child(4) {
        width: 100px;
        /* Coluna 'Tabela' */
    }

    .table-commission th:nth-child(5),
    .table-commission td:nth-child(5) {
        text-align: center !important;
        /* Coluna 'Prazo' */
    }

    .table-commission th:nth-child(6),
    .table-commission td:nth-child(6) {
        width: 120px;
        /* Coluna 'VL. Liq.' */
    }

    .table-commission th:nth-child(7),
    .table-commission td:nth-child(7) {
        width: 120px;
        /* Coluna 'VL. Bruto' */
    }

    .table-commission th:nth-child(8),
    .table-commission td:nth-child(8) {
        width: 100px;
        /* Coluna 'Digitador' */
    }

    .table-commission th:nth-child(9),
    .table-commission td:nth-child(9) {
        width: 50px;
        /* Coluna '%' */
    }

    .table-commission th:nth-child(10),
    .table-commission td:nth-child(10) {
        width: 100px;
        /* Coluna 'Situação' */
    }

    .table-commission th:nth-child(11),
    .table-commission td:nth-child(11) {
        width: 100px;
        /* Coluna 'Status' */
    }
</style>
<div id="commission_table" class="hide">
    <div class="row">

        <?php if (staff_can('view_filters', 'corban_commissions')) { ?>
            <div class="col-md-3" id="div_role_filter">
                <?php
                if (isset($GET['debug']) && !empty($GET['debug'])) {
                    echo "Dados alimentados por controllers/manage_commission/manage_commission";
                }
                // Função para buscar os papéis (roles) do sistema
                $CI = &get_instance(); // Obter a instância do CodeIgniter

                // Consultar a tabela 'roles' para pegar os papéis
                $CI->db->select('roleid as id, name');
                $CI->db->from(db_prefix() . 'roles'); // Usar o prefixo do banco para garantir compatibilidade
                $query = $CI->db->get();
                $rolesArray = $query->result_array(); // Pega os resultados como array
                // Remover o papel "Atendente"


                $roles = array_filter($rolesArray, function ($role) {
                    $display_roles = ['CORBAN'];
                    return in_array($role['name'], $display_roles);
                });

                if (!is_admin()) {
                    if ($my_role == 4) {
                        $roles = array_filter($rolesArray, function ($role) {
                            return in_array($role['name'], ['Atendente']);
                        });
                    } elseif ($my_role == 5) {
                        $roles = array_filter($rolesArray, function ($role) {
                            $display_roles = ['CORBAN', 'Gerente Comercial'];
                            return in_array($role['name'], $display_roles);
                        });
                    }
                } else {
                    $roles = array_filter($rolesArray, function ($role) {
                        $display_roles = ['CORBAN', 'Gerente Comercial'];
                        return in_array($role['name'], $display_roles);
                    });
                }

                // Agora $roles contém os papéis no formato ['id' => roleid, 'name' => role_name]
                // Passa o array de roles para o render_select como options




                // VERIFICA SE É CORBAN
                $displayTo = [5];
                if (in_array($my_role, $displayTo) || is_admin() || staff_can('view_own',  'finance_manager')) {

                    echo render_select(
                        'role_filter', // Nome do campo
                        $roles, // Array de opções dinâmicas do banco de dados
                        array('id', 'name'), // Mapeamento do 'id' e 'name' para valor e rótulo
                        'Cargo', // Label do campo 
                        '', // Valor selecionado (em branco por enquanto)
                        ['multiple' => true, 'data-actions-box' => true], // Atributos extras: múltipla seleção e box de ações
                        [], // Atributos de grupo (não usados neste exemplo)
                        '', // Classe personalizada (opcional)
                        '', // ID personalizado (opcional)
                        false // Escolha do seletor Bootstrap multiselect (false para permitir múltiplas opções)
                    );
                }

                ?>

            </div>

            <div class="col-md-3" id="div_staff_filter">
                <?php
                // if (is_admin()) {
                echo render_select('staff_filter', $staffs_my_network, array('staffid', 'firstname', 'lastname'), 'staff', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
                // }
                ?>
            </div>

            <div class="col-md-3" id="div_client_filter">
                <?php
                // if (is_admin()) {
                echo render_select('client_filter', $clients, array('userid', 'company'), 'client', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
                // }
                ?>
            </div>
            <!-- Tabelas -->
            <!-- <div class="col-md-3">
                <?php
                // echo render_select('products_services', $products, array('id', 'label'), 'Tabelas', '', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
                ?>
            </div> -->
            <div class="col-md-3">
                <?php
                $statuss = [['id' => '2', 'label' => _l('invoice_status_unpaid')], ['id' => '1', 'label' => _l('invoice_status_paid')]];
                echo render_select('status', $statuss, array('id', 'label'), 'invoice_dt_table_heading_status', '2', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
                ?>
                <?php echo render_input('is_process_data', '', '', 'hidden', ['id' => 'is_process_data']); ?>
            </div>

        <?php } ?>
        <div class="clearfix"></div>
    </div>
    <?php
    if (isset($_GET['debug'])) {
        echo basename(__FILE__);
    }
    ?>
    <table class="table table-commission scroll-responsive">
        <thead>
            <tr>
                <th><?php echo "#ID"; ?></th>
                <th><?php echo "Data L. Crédito"; ?></th>
                <th class="client_col"><?php echo "Cliente"; ?></th>

                <th><?php echo "VL. Liq."; ?></th>
                <th><?php echo "VL. Bruto"; ?></th>
                <th><?php echo "Comissão"; ?></th>
                <th><?php echo "%"; ?></th>

                <th><?php echo "Tabela"; ?></th>
                <th class="text_center_col"><?php echo "Prazo"; ?></th>
                <th><?php echo "Digitador"; ?></th>
                <th><?php echo "Status"; ?></th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="total_liq"></td>
                <td class="total"></td>
                <td class="total_commission"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <?php
    if (!staff_can('process_commissions', 'icash_tools') && !is_admin()) {
        echo "";
    } else {
        echo '<button type="submit" id="process_items" class="btn btn-primary">Processar Comissões</button>';
    }

    ?>

</div>