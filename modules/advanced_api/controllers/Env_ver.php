<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Env_ver extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        show_404();
    }

    public function activate()
    {
        return true;
    }

    public function upgrade_database()
    {
        // $res = modules\advanced_api\core\Apiinit::pre_validate($this->input->post('module_name'), $this->input->post('purchase_key'));
        // if ($res['status']) {
        //     $res['original_url'] = $this->input->post('original_url');
        // }
        // echo json_encode($res);
    }

    public function registerToken() {}

    public function save_apikey_token()
    {
        // Só permite POST
        if ($this->input->method() !== 'post') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }

        $token = $this->input->post('token');

        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Token é obrigatório']);
            return;
        }

        // Verifica se a opção já existe
        if (!get_option('apikey_token')) {
            add_option('apikey_token', $token);
            $status = 'adicionado';
        } else {
            update_option('apikey_token', $token);
            $status = 'atualizado';
        }

        echo json_encode([
            'message' => "Token $status com sucesso",
            'token' => $token
        ]);
    }
}
