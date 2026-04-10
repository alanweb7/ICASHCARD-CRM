<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Listar_comissoes extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('icash_listar_comissoes'); // Modelo padrão do Perfex para staff
        $this->load->model('invoice_items_model'); // Modelo padrão do Perfex para staff
        $this->load->model('roles_model');
        // $this->load->model('commission_model'); // Modelo padrão do Perfex para staff
    }

    public function index()
    {

        // Substitua $myID pelo ID do usuário logado
        $myID = get_staff_user_id();

        // Obter o valor da coluna 'gerente_id' para o staff logado
        $staff = $this->db
            ->select('gerente_id') // Especifique a coluna que deseja
            ->from('tblstaff') // Nome da tabela
            ->where('staffid', $myID) // Condição para buscar pelo ID do usuário logado
            ->get();

        if ($staff->num_rows() > 0) {
            // Retorna o valor de 'gerente_id'
            $gerente_id = $staff->row()->gerente_id;
        }

        $productSett = $this->icash_listar_comissoes->load_commission_policy()[0]['ladder_product_setting'];
        // $productSett = $this->icash_listar_comissoes->load_commission_policy();
        // $productSett = json_decode($productSett[0]['ladder_product_setting']);

        
        $item_table = function ($id) {
            return $this->icash_listar_comissoes->load_items_tables($id);
        };

        $newListTable = [];
        foreach (json_decode($productSett, true) as $id => $item) {
            $tableData = $item_table($id);
            $tableName = $tableData->long_description;
            $apiName = $tableData->description;
            $newListTable[] = [
                "id" => $id,
                "commission" => $item['percent_enjoyed_ladder_product'][0],
                "name" => $tableName,
                "api_name" => $apiName
            ];
        }

        // Obter o papel do usuário
        $query = $this->db
            ->select('tblroles.roleid, tblroles.name')
            ->from('tblstaff')
            ->join('tblroles', 'tblroles.roleid = tblstaff.role')
            ->where('tblstaff.staffid', $myID)
            ->get();


        $roleName = $query->row()->name;
        $roleID = $query->row()->roleid;

        $permiteds = ["CORBAN", "ADM Sistema", "Diretor Financeiro"];

        $rolesNoLink = [1, 2, 10];

        $linkBase = "https://icashcard.com.br/simulador/{$myID}/";

        if (in_array($roleName, $permiteds)) {
            $linkBase = "https://icashcard.com.br/simulador/{$myID}/";
        } else {
            $linkBase = "https://icashcard.com.br/simulador/{$gerente_id}/";
        }

        $datatable = [];
        foreach ($newListTable as $key => $row) {
            $link = $linkBase . $row['api_name'] . "?atd={$myID}";
            $datatable["comissoes"][$key]['nome'] = "<a href='#'>{$row['name']}</a>";


            // permissoes de visualizações comissoes
            // if (in_array($roleName, $permiteds)) {
            //     $datatable["comissoes"][$key]['comissao'] = $row['commission'] . "%";
            // } else {
            //     $datatable["comissoes"][$key]['comissao'] = "-";
            // }

            if (staff_can('view',  'corban_commissions')) {
                $datatable["comissoes"][$key]['comissao'] = $row['commission'] . "%";
            } else {
                $datatable["comissoes"][$key]['comissao'] = "-";
            }




            if (!in_array($myID, $rolesNoLink)) {
                $datatable["comissoes"][$key]['link'] = '<a href="#" data-link="' . $link . '" class="btn btn-primary btn-sm" id="copy_link"><i class="fa-regular fa-copy" style="color:#fff;"></i></a>';
            } else {
                $datatable["comissoes"][$key]['link'] = "-";
            }
        }

        $datatable["staff_info"] = [
            "id" => $myID
        ];

        // Carregar a view passando os dados corretamente
        $this->load->view('listar_comissoes', $datatable);
    }



    public function onNotificationToWebhook($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://webhook.site/b428bc7e-d1e3-4801-b4cc-8b37884a9084',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data)
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }
}
