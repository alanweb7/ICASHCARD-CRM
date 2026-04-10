<?php

defined('BASEPATH') || exit('No direct script access allowed');

if (!function_exists('convertSerializeDataToObject')) {
    function convertSerializeDataToObject($data)
    {
        return json_decode(json_encode(unserialize($data)));
    }
}

if (!function_exists('isAuthorized')) {
    function isAuthorized()
    {
        if (checkModuleStatus()) {
            return [
                'response' => [
                    'message' => checkModuleStatus()['response']['message'],
                ],
                'response_code' => 404,
            ];
        }

        $loggedInClient = get_instance()->authorization_token->validateToken();
        if (!$loggedInClient['status']) {
            return [
                'response' => [
                    'message' => $loggedInClient['message'],
                ],
                'response_code' => 401,
            ];
        }

        // get_instance()->db->where('id', $loggedInClient['data']->contact_id);
        // $contact = get_instance()->db->get(db_prefix() . 'contacts');
        get_instance()->db->where('staffid', $loggedInClient['data']->contact_id);
        $contact = get_instance()->db->get(db_prefix() . 'staff');

        $token = $contact->row()->customer_api_key;
        if (empty($token)) {
            return [
                'response' => [
                    'message' => _l('login_to_continue'),
                ],
                'response_code' => 401,
            ];
        }

        $authToken = get_instance()->input->request_headers()['Authorization'];

        if (trim($token) !== trim($authToken)) {
            return [
                'response' => [
                    'message' => _l('login_to_continue'),
                ],
                'response_code' => 401,
            ];
        }

        // $isClientActive = get_client($contact->row()->userid)->active;
        $isClientActive = get_client($contact->row()->staffid)->active;

        if ($isClientActive == 0) {
            return [
                'response' => [
                    'message' => _l('admin_auth_inactive_account'),
                ],
                'response_code' => 401,
            ];
        }

        return $loggedInClient;
    }
}

if (!function_exists('isBasicAuthorized')) {
    function isBasicAuthorized()
    {
        $CI = get_instance();
        $headers = $CI->input->request_headers();

        // Configurações salvas
        $expectedName = get_option('api_name');
        $expectedToken = get_option('apikey_token');

        if (empty($expectedToken)) {
            return [
                'response' => [
                    'message' => _l('login_to_continue'),
                ],
                'response_code' => 401,
            ];
        }

        // Extrai o token do header
        $authHeader = $headers['Authorization'] ?? null;
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return [
                'response' => [
                    'message' => _l('login_to_continue'),
                ],
                'response_code' => 401,
            ];
        }

        $authToken = trim(str_replace('Bearer', '', $authHeader));

        // Divide o JWT
        $parts = explode('.', $authToken);
        if (count($parts) !== 3) {
            return [
                'response' => [
                    'message' => 'Token malformado',
                ],
                'response_code' => 401,
            ];
        }

        list($header, $payload, $signature) = $parts;

        // Decodifica payload (base64url → base64 → JSON)
        $decodedPayload = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        if (!is_array($decodedPayload)) {
            return [
                'response' => [
                    'message' => 'Token inválido',
                ],
                'response_code' => 401,
            ];
        }

        // Validação do conteúdo do token
        $tokenName = $decodedPayload['name'] ?? null;
        $tokenIat  = $decodedPayload['iat'] ?? 0;
        $now       = time();

        if ($tokenName !== $expectedName) {
            return [
                'response' => [
                    'message' => 'Nome inválido no token',
                ],
                'response_code' => 403,
            ];
        }

        if ($tokenIat < $now) {
            return [
                'response' => [
                    'message' => 'Token expirado',
                ],
                'response_code' => 403,
            ];
        }

        // Token válido
        return [
            'status' => true,
            'payload' => $decodedPayload,
            'response' => [
                'message' => _l('logged_in_successfully'),
            ],
            'response_code' => 200,
        ];
    }
}


function authorizeOrExit()
{
    $auth = isBasicAuthorized();

    if (!isset($auth['status'])) {
        $CI = get_instance();
        $CI->response($auth['response'], $auth['response_code']);
        exit;
    }

    return $auth['payload']; // ou status
}




if (!function_exists('checkModuleStatus')) {
    function checkModuleStatus()
    {
        get_instance()->load->library('app_modules');
        if (get_instance()->app_modules->is_inactive('advanced_api')) {
            return [
                'response' => [
                    'message' => 'Advanced REST API module is deactivated. Please reactivate or contact support',
                ],
                'response_code' => 404,
            ];
        }
    }
}

if (!function_exists('get_invoice_status_by_id')) {
    function get_invoice_status_by_id($status = '')
    {
        if (!class_exists('Invoices_model', false)) {
            get_instance()->load->model('invoices_model');
        }

        if (Invoices_model::STATUS_UNPAID == $status) {
            $status = _l('invoice_status_unpaid');
        } elseif (Invoices_model::STATUS_PAID == $status) {
            $status = _l('invoice_status_paid');
        } elseif (Invoices_model::STATUS_PARTIALLY == $status) {
            $status = _l('invoice_status_not_paid_completely');
        } elseif (Invoices_model::STATUS_OVERDUE == $status) {
            $status = _l('invoice_status_overdue');
        } elseif (Invoices_model::STATUS_CANCELLED == $status) {
            $status = _l('invoice_status_cancelled');
        } else {
            // status 6
            $status = _l('invoice_status_draft');
        }

        return $status;
    }
}

if (!function_exists('get_proposals_status_by_id')) {
    function get_proposals_status_by_id($status)
    {
        if (1 == $status) {
            $status      = _l('proposal_status_open');
        } elseif (2 == $status) {
            $status      = _l('proposal_status_declined');
        } elseif (3 == $status) {
            $status      = _l('proposal_status_accepted');
        } elseif (4 == $status) {
            $status      = _l('proposal_status_sent');
        } elseif (5 == $status) {
            $status      = _l('proposal_status_revised');
        } elseif (6 == $status) {
            $status      = _l('proposal_status_draft');
        }

        return $status;
    }
}

if (!function_exists('onNotificationToWebhook')) {
    function onNotificationToWebhook($data = ['data' => "Dados de exemplo"])
    {

        $jsonData = json_encode($data);
        $webhook = get_option('webhook_notification');

        if (!$webhook) {
            return;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $webhook,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/plain'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
    }
}

/* End of file "advanced_api.".php */
