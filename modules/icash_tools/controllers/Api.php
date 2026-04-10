<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('icash_tools_model');
        $this->authenticate();
    }

    private function authenticate()
    {
        // Obtém as credenciais do cabeçalho Authorization
        $headers = $this->input->get_request_header('Authorization');
        if (empty($headers) || strpos($headers, 'Basic ') !== 0) {
            $this->send_unauthorized();
        }

        // Decodifica as credenciais
        $encodedCredentials = substr($headers, 6);
        $decodedCredentials = base64_decode($encodedCredentials);
        list($username, $password) = explode(':', $decodedCredentials, 2);

        // Valida as credenciais
        if ($username !== 'SEU_USERNAME' || $password !== 'SEU_PASSWORD') {
            $this->send_unauthorized();
        }
    }

    private function send_unauthorized()
    {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="API Access"');
        echo json_encode(['status' => false, 'message' => 'Unauthorized']);
        exit;
    }

    public function listar_tabelas()
    {
        $tabelas = $this->icash_tools_model->get_tabelas();
        echo json_encode($tabelas);
    }

    public function processar_pedido()
    {
        // header('HTTP/1.1 401 Unauthorized');
        // header('WWW-Authenticate: Basic realm="API Access"');
        echo json_encode(['status' => false, 'message' => 'Unauthorized']);
        exit;
    
    }
}
