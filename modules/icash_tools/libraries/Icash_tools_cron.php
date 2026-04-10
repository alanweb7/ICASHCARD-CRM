<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Icash_tools_cron
{
    public function send_webhook_data()
    {
        // Configuração do Webhook
        $webhookUrl = 'https://webhook.site/9568a27f-fec6-4a17-a8bd-1065ec42b337';
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message'   => 'Este é um teste de webhook do Perfex CRM',
            'status'    => 'success',
        ];

        // Inicializa o cURL
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $webhookUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Executa o POST
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            log_activity('Erro ao enviar webhook: ' . curl_error($ch));
        } else {
            log_activity('Webhook enviado com sucesso: ' . $response);
        }

        curl_close($ch);
    }
}
