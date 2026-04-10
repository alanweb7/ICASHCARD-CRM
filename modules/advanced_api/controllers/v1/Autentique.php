<?php

defined('BASEPATH') || exit('No direct script access allowed');

require_once __DIR__ . '/../RestController.php';
// require_once __DIR__ . '/../../vendor/autoload.php';

use AdvancedApi\RestController;

class Autentique extends RestController
{
    public function __construct()
    {
        parent::__construct();
        register_language_files('advanced_api');
        load_client_language();

        $this->load->helper('advanced_api');

        $auth = isBasicAuthorized();

        if (!isset($auth['status'])) {
            $this->response($auth['response'], $auth['response_code']);
            exit; // 🔴 IMPORTANTE: garante que o código não continue executando
        }
    }

    /**
     * @api {post} /advanced_api/v1/autentique
     * Endpoint de resposta da autentique
     *
     * @apiName Login
     *
     * @apiGroup Authentication
     *
     * @apiVersion 1.0.0
     *
     * @apiSampleRequest off
     *
     * @apiBody {String} email    <span class="btn btn-xs btn-danger">Required</span> Customer's Email
     * @apiBody {String} password <span class="btn btn-xs btn-danger">Required</span> Customer's Password
     *
     * @apiSuccess {Boolean} status  Response status.
     * @apiSuccess {String}  data    Logged in customers details.
     * @apiSuccess {String}  message Success message.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "status": true,
     *         "data": {
     *             "client_id": "1",
     *             "contact_id": "1",
     *             "client_logged_in": true,
     *             "API_TIME": 1684385965,
     *             "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJjbGllbnRfaWQiOiIxIiwiY29udGFjdF9pZCI6IjEiLCJjbGllbnRfbG9nZ2VkX2luIjp0cnVlLCJBUElfVElNRSI6MTY4NDM4NTk2NX0.y2rJF2OjwDair7ObPD5NbergZTFZo6zB3JzvNOhEOQaRvp0oDKR1eV6-pLyrInGTUACxAKODxdV2E6YjaGWfwA"
     *         },
     *         "message": "You've logged in successfully"
     *     }
     */
    public function autentique_post()
    {

        $data = $this->post();

        $this->onNotificationToWebhook($data);

        if (!$data) {
            $this->response(['message' => "Faltando dados"], 403);
        }

        $this->load->model('contract_sign_model');

        $signature = $this->contract_sign_model->add_sign_event($data);

        if (false == $signature['success']) {
            $this->response(
                [
                    'message' => _l('Erro ao atualizar o Contracts sign events'),
                    'response' => $signature,
                ],
                400
            );
        }


        unset($signature['success']);
        $this->response($signature, $signature['code']);
    }


    public function onNotificationToWebhook($data)
    {


        $webhook_url = get_option("webhook_notification");

        if (!$webhook_url) {
            return;
        }

        $jsonData = json_encode($data);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $webhook_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }
}
/* End of file Authentication.php */
