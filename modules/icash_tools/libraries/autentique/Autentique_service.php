<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Autentique_service
{
    protected $apiUrl = 'https://api.autentique.com.br/v2/graphql';
    protected $token;

    public function __construct()
    {
        $this->token = get_option('autentique_api_token'); // ou token fixo para testes
        // $this->token = "f03b773d8355da8bcd3f9fce151caa3a2c624955e1db09236ce981d9dc3b1eb0";
    }

    private function requestMultipart($fields)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function enviarContratoPDF($contract, $proposal)
    {


        include_once(APPPATH . 'libraries/pdf/Contract_pdf.php');
        $pdf = new Contract_pdf($contract);
        $pdf->prepare();
        $pdf_data = $pdf->output('', 'S');

        $tmp_dir = FCPATH . 'uploads/tmp/';
        if (!file_exists($tmp_dir)) {
            mkdir($tmp_dir, 0755, true);
        }

        $file_path = $tmp_dir . 'contrato_' . $contract->id . '.pdf';
        file_put_contents($file_path, $pdf_data);

        // $fields = [
        //     'operations' => json_encode([
        //         'query' => 'mutation CreateDocumentMutation($document: DocumentInput!, $signers: [SignerInput!]!, $file: Upload!) {
        //             createDocument(document: $document, signers: $signers, file: $file) {
        //                 id name created_at signatures {
        //                     public_id name email created_at
        //                     action { name } link { short_link } user { id name email }
        //                 }
        //             }
        //         }',
        //         'variables' => [
        //             'document' => ['name' => 'Contrato #' . $contract->id." (Proposta nº {$proposal['id']})"],
        //             'signers' => [
        //                 ['email' => $proposal['email'], 'action' => 'SIGN']
        //             ],
        //             'file' => null
        //         ]
        //     ]),
        //     'map' => json_encode(['file' => ['variables.file']]),
        //     'file' => new CURLFile($file_path)
        // ];


        $fields = [
            'operations' => json_encode([
                'query' => 'mutation CreateDocumentMutation($document: DocumentInput!, $signers: [SignerInput!]!, $file: Upload!) {
                    createDocument(document: $document, signers: $signers, file: $file) {
                        id name created_at signatures {
                            public_id name email created_at
                            action { name } link { short_link } user { id name email }
                        }
                    }
                }',
                'variables' => [
                    'document' => [
                        'name' => "Proposta nº {$proposal['id']}",
                        "locale" => [
                            "country" => "BR",
                            "language" => "pt-BR", // Pode ser: `pt-BR` ou `en-US`, se não informado, assume-se `pt-BR`
                            "timezone" => "America/Sao_Paulo", // DateTimeZone com todas as timezones, se não informado, assume-se `America/Sao_Paulo`
                            // Uma lista completa pode ser encontrada em: https://www.php.net/manual/en/datetimezone.listidentifiers.php
                            "date_format" => "DD_MM_YYYY", // Enum, pode ser: DD_MM_YYYY ou MM_DD_YYYY, se não informado, assume-se `DD_MM_YYYY`
                        ]
                    ],
                    'signers' => [
                        [
                            "email" => "atendimento@icashcard.com.br",
                            'action' => 'SIGN'
                        ],
                        [
                            'email' => $proposal['email'],
                            "name" => "{$proposal['name']}",
                            "configs" =>  ["cpf" => $proposal['cpf']],
                            'action' => 'SIGN'
                        ]
                    ],
                    'file' => null
                ]
            ]),
            'map' => json_encode(['file' => ['variables.file']]),
            'file' => new CURLFile($file_path)
        ];

        $response = $this->requestMultipart($fields);

        @unlink($file_path);

        return json_decode($response, true);
    }



    public function onNotificationWebhook($data)
    {


        $jsonData = json_encode($data);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => '$proposal[\'email\']',
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
