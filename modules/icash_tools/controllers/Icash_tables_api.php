<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Icash_tables_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('ApiAuthMiddleware');

        // Chama o middleware de autenticação para verificar o token
        if (!$this->apiauthmiddleware->authenticate()) {
            header('Content-Type: application/json');
            $errorData = [
                "status" => 401,
                "code" => "error",
                'error' => 'Unauthorized'
            ];
            echo json_encode($errorData);
            exit; // Interrompe a execução se não estiver autenticado
        }

        $this->load->model('icash_tables_model'); // Certifique-se de que esse model existe
    }

    // Função para listar as tabelas
    public function list_tables_all()
    {
        // Define o cabeçalho Content-Type como JSON
        header('Content-Type: application/json');

        // Obtém os dados das tabelas
        $tables = $this->icash_tables_model->get_tables();

        // Itera sobre cada tabela para decodificar o campo 'parcelas'
        foreach ($tables as &$table) {
            if (isset($table['parcelas'])) {
                $table['parcelas'] = json_decode($table['parcelas'], true);
            }
        }

        // Retorna os dados em formato JSON
        echo json_encode($tables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // Função para buscar uma tabela por ID
    public function get_table_by_id($id)
    {
        // Define o cabeçalho Content-Type como JSON
        header('Content-Type: application/json');

        // Verifica se o ID é válido
        if (!is_numeric($id)) {
            echo json_encode([
                "status" => 400,
                "code" => "error",
                "error" => "Invalid ID"
            ]);
            return;
        }

        // Obtém os dados da tabela pelo ID
        $table = $this->icash_tables_model->get_table_by_id($id);

        // Verifica se a tabela foi encontrada
        if ($table === null) {
            echo json_encode([
                "status" => 404,
                "code" => "error",
                "error" => "Table not found"
            ]);
            return;
        }

        // Decodifica o campo 'parcelas' se estiver presente
        if (isset($table['parcelas'])) {
            $table['parcelas'] = json_decode($table['parcelas'], true);
        }

        // Retorna os dados em formato JSON
        echo json_encode($table, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // Função para criar invoice
    public function register_invoice_from_proposta()
    {
        // Define o cabeçalho Content-Type como JSON
        // header('Content-Type: application/json');
        $data = $this->input->post();

        // Verifica $data
        // if (!$data) {
        //     echo json_encode([
        //         "status" => 400,
        //         "code" => "error",
        //         "error" => "Invalid ID"
        //     ]);
        //     return;
        // }

        // // Obtém os dados da tabela pelo ID
        // $table = $this->icash_tables_model->get_table_by_id($id);

        // // Verifica se a tabela foi encontrada
        // if ($table === null) {
        //     echo json_encode([
        //         "status" => 404,
        //         "code" => "error",
        //         "error" => "Table not found"
        //     ]);
        //     return;
        // }

        // // Decodifica o campo 'parcelas' se estiver presente
        // if (isset($table['parcelas'])) {
        //     $table['parcelas'] = json_decode($table['parcelas'], true);
        // }

        // // Retorna os dados em formato JSON
        // echo json_encode($table, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this;
    }

}
