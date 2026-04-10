<?php

defined('BASEPATH') || exit('No direct script access allowed');

require_once __DIR__ . '/../RestController.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use AdvancedApi\RestController;

class Consulta extends RestController
{
    public function __construct()
    {
        parent::__construct();
        register_language_files('advanced_api');
        load_client_language();

        $this->load->helper('advanced_api');
        if (checkModuleStatus()) {
            $this->response(checkModuleStatus()['response'], checkModuleStatus()['response_code']);
        }
    }

    /**
     * @api {post} /advanced_api/v1/consulta
     *
     **/

    public function consulta_post()
    {
        $requiredData = [
            'password' => '',
            'email'    => '',
        ];

        $postData = $this->post();
        $postData = array_merge($requiredData, $postData);

        $this->load->library('form_validation');

        $this->form_validation->set_data($postData);

        $this->form_validation->set_rules('password', _l('clients_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('clients_login_email'), 'trim|required|valid_email');

        if (false === $this->form_validation->run()) {
            $this->response(['message' => strip_tags(validation_errors())], 422);
        }

        $this->load->model('Authentication_model');

        $success = $this->Authentication_model->login(
            $postData['email'],
            $postData['password'],
            false,
            false
        );

        if (is_array($success) && isset($success['memberinactive'])) {
            $this->response(['message' => _l('inactive_account')], 400);
        } elseif (false == $success) {
            $this->response(['message' => _l('client_invalid_username_or_password')], 400);
        }

        $table = db_prefix() . 'staff';
        // $table = db_prefix().'contacts';

        $this->db->where('email', $postData['email']);
        $client = $this->db->get($table)->row();

        // $client_data = [
        //     'client_id'        => $client->userid, // Client's ID
        //     'contact_id'       => $client->id, // Contact ID
        //     'client_logged_in' => true,
        //     'API_TIME'         => time(),
        // ];

        $client_data = [
            'client_id'        => $client->staffid, // Client's ID
            'contact_id'       => $client->staffid, // Contact ID
            'client_logged_in' => true,
            'API_TIME'         => time(),
        ];

        $this->load->helper('jwt');
        $token                = $this->authorization_token->generateToken($client_data);
        $client_data['token'] = $token;

        // $this->db->update(db_prefix() . 'contacts', ['customer_api_key' => $token], ['id' => $client->id]);
        $this->db->update(db_prefix() . 'staff', ['customer_api_key' => $token], ['staffid' => $client->staffid]);

        $this->response(['data' => $client_data, 'message' => _l('logged_in_successfully')], 200);
    }

    // /advanced_api/v1/consulta
    public function consulta_get()
    {
        $requiredData = [
            'password' => '',
            'email'    => '',
        ];

        $postData = $this->get();
        $postData = array_merge($requiredData, $postData);

        $cpf = $postData['cpf'];
        $cpf_limpo = preg_replace('/\D/', '', $cpf);


        // Verificar se o CPF já existe com outro gerente
        $this->db->where('cpf_cnpj', $cpf_limpo);
        $existente = $this->db->get(db_prefix() . 'staff')->row();

        if ($existente) {
    
            $this->response(['data' => $postData, 'message' => 'CPF já cadastrado na base por outro gerente.'], 500);
            die();
        }

        $this->response(['data' => $postData, 'message' => 'Sucesso!'], 200);

        die();

        $this->load->library('form_validation');

        $this->form_validation->set_data($postData);

        $this->form_validation->set_rules('password', _l('clients_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('clients_login_email'), 'trim|required|valid_email');

        if (false === $this->form_validation->run()) {
            $this->response(['message' => strip_tags(validation_errors())], 422);
        }

        $this->load->model('Authentication_model');

        $success = $this->Authentication_model->login(
            $postData['email'],
            $postData['password'],
            false,
            false
        );

        if (is_array($success) && isset($success['memberinactive'])) {
            $this->response(['message' => _l('inactive_account')], 400);
        } elseif (false == $success) {
            $this->response(['message' => _l('client_invalid_username_or_password')], 400);
        }

        $table = db_prefix() . 'staff';
        // $table = db_prefix().'contacts';

        $this->db->where('email', $postData['email']);
        $client = $this->db->get($table)->row();

        // $client_data = [
        //     'client_id'        => $client->userid, // Client's ID
        //     'contact_id'       => $client->id, // Contact ID
        //     'client_logged_in' => true,
        //     'API_TIME'         => time(),
        // ];

        $client_data = [
            'client_id'        => $client->staffid, // Client's ID
            'contact_id'       => $client->staffid, // Contact ID
            'client_logged_in' => true,
            'API_TIME'         => time(),
        ];

        $this->load->helper('jwt');
        $token                = $this->authorization_token->generateToken($client_data);
        $client_data['token'] = $token;

        // $this->db->update(db_prefix() . 'contacts', ['customer_api_key' => $token], ['id' => $client->id]);
        $this->db->update(db_prefix() . 'staff', ['customer_api_key' => $token], ['staffid' => $client->staffid]);

        $this->response(['data' => $client_data, 'message' => _l('logged_in_successfully')], 200);
    }
}
/* End of file Authentication.php */
