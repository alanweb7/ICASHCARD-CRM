<?php

defined('BASEPATH') or exit('No direct script access allowed');

// use \Firebase\JWT\JWT;

class Icash_tools_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // \modules\icash_tools\core\Apiinit::the_da_vinci_code('api');
    }


    public function generate_token()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->output
                ->set_status_header(405)
                ->set_output(json_encode(['error' => 'Method Not Allowed']));
            return;
        }

        // Captura os dados do corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['error' => 'Invalid JSON format']));
            return;
        }

        if (!isset($data['username']) || !isset($data['password'])) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['error' => 'Username and password are required']));
            return;
        }

        // Continue com a lógica de autenticação
    }



    public function generate_token2()
    {

        // Capture os dados JSON enviados no corpo da requisição
        $data = json_decode(file_get_contents('php://input'), true);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));

        if (!$data) {
            $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['error' => 'Invalid JSON format']));
            return;
        }

        // $tables = [
        //     "success" => true,
        //     "code" => $this->input->post('username')
        // ];
        // $this->output
        //     ->set_content_type('application/json')
        //     ->set_output(json_encode($tables));

        // return $this->input->post('username');
        // $this->load->library('form_validation');
        // $this->form_validation->set_rules('username', 'Username', 'required');
        // $this->form_validation->set_rules('password', 'Password', 'required');

        // if ($this->form_validation->run() === false) {
        //     $this->output
        //         ->set_status_header(400)
        //         ->set_output(json_encode(['error' => 'Username and password are required']));
        //     return;
        // }

        // $username = $this->input->post('username');
        // $password = $this->input->post('password');

        // // Verifique as credenciais do usuário
        // $this->db->where('username', $username);
        // $user = $this->db->get(db_prefix() . 'staff')->row();

        // if ($user && password_verify($password, $user->password)) {
        //     // Gerar o token
        //     $this->load->library('jwt');
        //     $payload = [
        //         'id' => $user->staffid,
        //         'username' => $user->username,
        //         'exp' => time() + 3600, // Token expira em 1 hora
        //     ];
        //     $token = JWT::encode($payload, "your_secret_key");

        //     $this->output
        //         ->set_status_header(200)
        //         ->set_output(json_encode(['token' => $token]));
        // } else {
        //     $this->output
        //         ->set_status_header(401)
        //         ->set_output(json_encode(['error' => 'Invalid username or password']));
        // }
    }


    private function verify_token()
    {
        $headers = $this->input->request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            list($token) = sscanf($authHeader, 'Bearer %s');

            if ($token) {
                try {
                    $decoded = JWT::decode($token, "your_secret_key", array('HS256'));
                    return (array) $decoded;
                } catch (Exception $e) {
                    $this->output
                        ->set_status_header(401)
                        ->set_output(json_encode(['error' => 'Invalid or expired token']));
                    return null;
                }
            }
        }

        $this->output
            ->set_status_header(401)
            ->set_output(json_encode(['error' => 'Authorization header not found']));
        return null;
    }

    public function get_tables()
    {
        $user = $this->verify_token();
        if (!$user) {
            return;
        }

        $tables = $this->icash_tools_api_model->get_all_tables();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($tables));
    }

    public function get_teste()
    {
        $user = $this->verify_token();
        if (!$user) {
            return;
        }

        $tables = [
            "success" => true,
            "code" => "ok"
        ];
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($tables));
    }

    public function data_get()
    {
        // $user = $this->verify_token();
        // if (!$user) {
        //     return;
        // }

        $tables = [
            "success" => true,
            "code" => "ok"
        ];
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($tables));
    }
    public function data_post()
    {
        // $user = $this->verify_token();
        // if (!$user) {
        //     return;
        // }

        $tables = [
            "success" => true,
            "code" => "ok"
        ];
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($tables));
    }
}
