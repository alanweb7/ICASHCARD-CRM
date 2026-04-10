<?php defined('BASEPATH') or exit('No direct script access allowed');

class ApiAuthMiddleware
{
    private $valid_token = 'YWRt-W46c-GFzc3-dvcm';

    public function authenticate()
    {

        $CI = &get_instance();
        $headers = $CI->input->request_headers();

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            if ($token === $this->valid_token) {
                return true;
            }
        }

        // Responder com erro de autorização
        $CI->output
            ->set_content_type('application/json')
            ->set_status_header(401)
            ->set_output(json_encode(['error' => 'Unauthorized']));

        return false;
    }
}
