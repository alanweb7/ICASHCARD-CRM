<?php defined('BASEPATH') or exit('No direct script access allowed');

class Simulador extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model'); // Modelo padrão do Perfex para staff
        $this->load->model('proposals_model');
    }
  
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('icash_tools', 'simulador/simulador_table'));
            return; // ou exit;
        }

        $data['title'] = 'Simulador';
        $this->load->view('simulador/simulador_form', $data);
    }

    public function load_proposals()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('icash_tools', 'tabelas/table_proposals'));
            return; // ou exit;
        }

        $data['title'] = 'Propostas';
        $this->load->view('list_proposals_view', $data);
    }

    public function add_proposal()
    {

        header('Content-Type: application/json');
        $valor = $this->input->post('valor');
        $nome_completo = $this->input->post('nome_completo');
        $full_data = $this->input->post();

        if (!$valor || !is_numeric($valor)) {
            echo json_encode(['success' => false, 'message' => 'Valor inválido.']);
            return;
        }

        if (!$nome_completo) {
            echo json_encode(['success' => false, 'message' => 'Valor inválido.']);
            return;
        }


        $email = $this->input->post('email');
        $cpf = $this->input->post('cpf');

        // adiciona um customer ou pega um existente

        $this->load->model('clients_model');
        $this->load->model('icash_tools/simulador_model');

        $email = $this->input->post('email');
        $nome  = $this->input->post('nome_completo');

        $clienteExistente = $this->simulador_model->get_client_by_email($cpf);



        if ($clienteExistente) {
            $customer_id = $clienteExistente->userid;
        } else {
            // Dados mínimos para criar um novo cliente
            $dadosNovoCliente = [
                'company' => $nome,
                'email' => $email,
                'datecreated' => date('Y-m-d H:i:s'),
                'active' => 1,
                'addedfrom' => get_staff_user_id()
            ];

            $customer_id = $this->clients_model->add($dadosNovoCliente);
        }


        $data = [
            'subject' => 'CONVERSÃO DE CRÉDITO',
            'rel_type' => 'customer',
            'rel_id' => $customer_id, // ID do cliente relacionado
            'content' => '<p>Conteúdo da proposta aqui...</p>',
            'proposal_to' => $nome_completo,
            'email' => $email,
            'date' => date('Y-m-d'),
            'open_till' => date('Y-m-d', strtotime('+15 days')),
            'currency' => 1, // ID da moeda
            'subtotal' => 1000,
            'total' => 1000,
            'discount_percent' => 0,
            'discount_total' => 0,
            'adjustment' => 0,
            'status' => 1, // 0 = rascunho, 1 = enviado, etc.
            'assigned' => get_staff_user_id(), // ID do responsável
            'datecreated' => date('Y-m-d H:i:s'),
        ];


        $newProposalId = $this->proposals_model->add($data);


        echo json_encode([
            'success' => true,
            'message' => "Proposta inserida com sucesso!",
            'cliente' => $clienteExistente,
            'client_id' => $customer_id
        ]);
    }


    public function get_table($tabela = '')
    {

        if (!$tabela) return false;

        // Tabela base de juros
        $baseParcelas = [
            1 =>  [
                2  => 1170.00,
                3  => 1185.00,
                4  => 1200.00,
                5  => 1205.00,
                6  => 1210.00,
                7  => 1215.00,
                8  => 1220.00,
                9  => 1222.50,
                10 => 1225.00,
                11 => 1230.00,
                12 => 1234.80
            ]
        ];

        return $baseParcelas[$tabela];
    }


    /**
     * Exemplo: calcular valor para R$ 1500 em 4 parcelas
     * $resultado = calcularNovaParcela(1500, 4);
     */
    public function consultar()
    {
        $valor = $this->input->post('valor');
        // $parcelas = $this->input->post('parcelas');

        if (!$valor) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
            return;
        }

        // Tabela base de juros
        $tabela  = 1;
        $baseParcelas = $this->get_table($tabela); // array de parcelas baseadas em R$1000

        $valorBase = 1000.00;
        $valor = $this->input->post('valor');

        if (!$valor || !is_numeric($valor)) {
            echo json_encode(['success' => false, 'message' => 'Valor inválido.']);
            return;
        }

        $resultados = [];

        foreach ($baseParcelas as $qtdParcelas => $totalBaseParcelado) {
            $fator = $totalBaseParcelado / $valorBase;
            $total = $valor * $fator;
            $parcela = $total / $qtdParcelas;

            $resultados[] = [
                'parcelas'         => $qtdParcelas,
                'valor_parcela'    => number_format($parcela, 2, ',', '.'),
                'valor_total'      => number_format($total, 2, ',', '.'),
                'fator'            => number_format($fator, 4, ',', '.'),
                'juros_percentual' => number_format(($fator - 1) * 100, 2, ',', '.') . '%'
            ];
        }

        echo json_encode([
            'success' => true,
            'valor_base' => $valor,
            'resultados' => $resultados
        ]);


        // echo json_encode([
        //     'success' => true,
        //     'parcelas' => $parcelas,
        //     'valor_total' => number_format($total, 2, ',', '.'),
        //     'valor_parcela' => number_format($valorParcela, 2, ',', '.'),
        //     'fator' => number_format($fator, 4, ',', '.')
        // ]);
    }
}
