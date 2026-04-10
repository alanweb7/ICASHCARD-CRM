<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gerenciar_contratos extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contracts_model');
        $this->load->model('proposals_model');
        $this->load->model('icash_history_model');
    }

    public function index()
    {
        // Carregar a view passando os dados corretamente

    }


    public function getContractById()
    {
        $id = $this->input->get('id'); // Obtém o ID via GET

        if (!$id) {
            echo json_encode(['error' => 'ID do contrato não fornecido']);
            return;
        }

        $contract = $this->contracts_model->get($id);

        if ($contract) {
            echo json_encode($contract);
        } else {
            echo json_encode(['error' => 'Contrato não encontrado']);
        }
    }



    // REMOVER ASSINATURA DO CONTRATO
    public function onUnsignedContract()
    {

        $this->load->model('icash_tools_model');

        $id = $this->input->post('id');

        header('Content-Type: application/json');


        if (!$id) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }

        if (!staff_can('edit', 'corban_proposals') && !is_admin()) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }


        $proposal = $this->proposals_model->get($id);

        $contract_id = $proposal->contract_id;

        if (!$contract_id) {

            // Retorne uma resposta JSON
            $response = [
                'success' => false,
                'message' => "Contrato não encontrado"
            ];

            set_alert('success', 'Contrato gerado com sucesso!');
            echo json_encode($response);
            exit;
        }


        $contract_details = $this->contracts_model->get($contract_id);

        $unSignature = $this->contracts_model->clear_signature($contract_id);

        if (!$unSignature) {
            $response = [
                'success' => false,
                'message' => 'Erro ao remover a assinatura do contrato',
            ];

            set_alert('danger', 'Erro ao remover a assinatura do contrato.');
            echo json_encode($response);
            exit();
        }


        set_alert('success', "Proposta #{$id} \nAssinatura removida com sucesso!");

        // ATUALIZAR A PROPOSTA

        $updateData = [
            'rel_id'                    => $proposal->rel_id,
            'rel_type'                  => $proposal->rel_type,
            'assigned'                  => $proposal->assigned,
            'custom_fields' => [
                'proposal'  => [
                    64 => "Aguardando formalização",
                ]
            ]
        ];

        $update = $this->proposals_model->update($updateData, $id);

        if (!$contract_details->id) {
            // Retorne uma resposta JSON
            $response = [
                'success' => false,
                'message' => 'Contrato não encontrado.',
            ];

            echo json_encode($response);
            exit();
        }

        $contractHash = $contract_details->hash;
        $contractID = $contract_details->id;
        $contract_link = base_url("contract/{$contractID}/{$contractHash}");


        /**
         * ENVIAR NOTIICAÇÃO PARA O CLIENTE
         */


        $proposal = $this->proposals_model->get($id);
        $telefone = get_custom_field_value($id, 91, 'proposal'); //telefone

        if ($telefone) {
            $proposal->customer_phone = preg_replace('/\D/', '', $telefone);

            $customer = $proposal->proposal_to;

            $text =  "👋🏼 {$customer}\n\n";
            $text .=  "Proposta: #{$id}\n";
            $text .=  "Segue o contrato para uma nova assinatura no link abaixo.\n";
            $text .=  "👇🏻👇🏻\n\n";
            $text .=  $contract_link;

            $data = [
                "number" =>  "55" . $proposal->customer_phone,
                "text" =>  $text
            ];

            // enviar link para o cliente
            $this->icash_tools_model->sendWhatsNotifications($data);
        }

        /**
         * ENVIAR NOTIICAÇÃO PARA O CLIENTE
         */


        $dataHistory = [
            'modulo'      => 'proposals',
            'etapa'       => "Aguardando formalização",
            'status'      => 26,
            'observacao'  => 'Link do contrato enviado para o cliente',
            'link'        => $contract_link,
            'staff_id'    => get_staff_user_id(), // ou ID manual
            'id_registro' => $proposal->id,
            'historico'   => serialize([
                'status'   => 'sucesso',
                'acao'    => 'Link do contrato enviado para o cliente',
                'mensagem' => 'cliente recebeu o contrato'
            ])
        ];

        // $history = $this->onSetHistory($dataHistory);

        // Retorne uma resposta JSON
        $response = [
            'success' => true,
            'message' => "Proposta #{$id} Assinatura removida com sucesso!",
            'contract_link' => $contract_link // ou qualquer outra info
        ];


        echo json_encode($response);
        exit();
    }

    /**
     * GERAR CONTRATO (BOTAO GERAR CONTRATO)
     */

    public function onContractGeneratorSend()
    {
        $this->load->model('icash_tools_model');

        $id = $this->input->post('id');

        header('Content-Type: application/json');

        if (!$id) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }

        if (!staff_can('edit', 'corban_proposals') && !is_admin()) {
            set_alert('danger', 'Sem Permissão para esta ação.');
            return;
        }

        $proposal = $this->proposals_model->get($id);

        if (!$proposal) {
            echo json_encode(['success' => false, 'message' => 'Proposta não encontrada.']);
            exit;
        }

        /**
         * CHAMAR FUNCAO GERAR CONTRATO
         */

        $generate_contract = $this->on_generate_contract($proposal->id);

        if (!$generate_contract['success']) {

            echo json_encode($generate_contract);
            exit();
        }

        $contract_link = $generate_contract['sign_link'];


        /**
         * ENVIAR NOTIICAÇÃO PARA O CLIENTE
         */

        $proposal = $this->proposals_model->get($id);
        $telefone = get_custom_field_value($id, 91, 'proposal'); //telefone

        $notifications_enabled = get_option('is_active_notification');

        if ($telefone && $notifications_enabled) {

            $proposal->customer_phone = preg_replace('/\D/', '', $telefone);

            $customer = $proposal->proposal_to;

            $text =  "👋🏼 {$customer}\n\n";

            $text .=  "Falta pouco para o seu PIX ser liberado! 🤩\n";
            $text .=  "Agora é só assinar o contrato no link abaixo.\n";
            $text .=  "👇🏻👇🏻\n\n";
            $text .=  $contract_link;

            $data = [
                "number" =>  "55" . $proposal->customer_phone,
                "text" =>  $text
            ];

            // enviar link para o cliente
            $this->icash_tools_model->sendWhatsNotifications($data);
        }


        /**
         * REGISTRO DO HISTÓRICO
         */

        hooks()->do_action('before_history_register', [
            'id_registro' => $proposal->id,
            'module'      => 'proposals',
            'history' => [
                [
                    'method'                => __FUNCTION__,
                    'step'                  => "Aguardando formalização",
                    'status'                => 'Link do contrato gerado',
                    'message'               => $contract_link,
                    'description'           => 'Link do contrato gerado',
                    'link'                  => $contract_link,
                    'event'                 => 'Gerar link de Contrato',
                    'event_slug'            => 'contract_link',
                    'font'                  => 'Autentique'
                ]
            ]
        ]);



        // Retorne uma resposta JSON
        $response = [
            'success' => true,
            'message' => 'Proposta ' . $proposal->id . ' encontrada.',
            'contract_link' => $contract_link // ou qualquer outra info
        ];

        set_alert('success', 'Contrato gerado com sucesso!');
        echo json_encode($response);
        exit;
    }


    function on_generate_contract($proposal_id)
    {
        $this->load->model('templates_model');

        $proposal = $this->proposals_model->get($proposal_id);
        if (!$proposal) return ['success' => false, 'message' => "Proposta #{$proposal_id} não encontrada."];

        $etapa = get_custom_field_value_db($proposal_id, 64); // Etapa da Proposta  
        $email = get_custom_field_value($proposal_id, 90, 'proposal'); //email
        $telefone = get_custom_field_value($proposal_id, 91, 'proposal'); //telefone
        $customer_phone = preg_replace('/\D/', '', $telefone);
        $cpf = get_custom_field_value($proposal_id, 23, 'proposal'); //cpf
        $customer_cpf = preg_replace('/\D/', '', $cpf);



        // Verifica se já tem contrato e se está na etapa correta
        // if ($proposal->contract_id || $etapa !== "Link Pag. Aprovado")
        if ($etapa !== "Link Pag. Aprovado")
            return ['success' => false, 'message' => "Proposta na etapa errada ou já tem um contrato gerado."];

        // Carrega template do contrato
        $template = $this->templates_model->getByType('contracts', ['id' => 1]);
        $content = is_array($template) ? ($template[0]['content'] ?? '') : ($template['content'] ?? '');
        if (!$content) return ['success' => false, 'message' => "Erro ao carregar o \"content\" do contrato"];

        // Pega campos personalizados da proposta
        $custom_fields_data = [
            'n_parcelas'         => get_custom_field_value_db($proposal_id, 13),
            'vl_parcela'         => get_custom_field_value_db($proposal_id, 14),
            'vl_solicitado'      => get_custom_field_value_db($proposal_id, 16),
            'vl_bruto'           => get_custom_field_value_db($proposal_id, 15),
            'card_number'        => get_custom_field_value_db($proposal_id, 74),
            'card_brand'         => get_custom_field_value_db($proposal_id, 76),
            'name_customer_card' => get_custom_field_value_db($proposal_id, 77),
        ];

        // Cria o contrato
        $contract_data = [
            'client'         => $proposal->rel_id,
            'subject'        => 'PRESTAÇÃO DE SERVIÇO',
            'description'    => 'Prestação de Serviço',
            'content'        => $content,
            'datestart'      => date('Y-m-d'),
            'proposal_id'    => $proposal_id,
            'contract_type'  => 1,
            'contract_value' => convert_to_decimal($custom_fields_data['vl_bruto']),
            'custom_fields'  => [
                "contracts" => [
                    71 => $custom_fields_data['n_parcelas'],
                    72 => $custom_fields_data['vl_parcela'],
                    87 => $custom_fields_data['vl_solicitado'],
                    88 => $custom_fields_data['vl_bruto'],
                    78 => $custom_fields_data['name_customer_card'],
                    73 => $custom_fields_data['card_number'],
                    75 => $custom_fields_data['card_brand'],
                    101 => $this->formatar_cpf($customer_cpf), // CPF Cliente
                    102 => $email // Email Cliente
                ]
            ]
        ];

        $contract_id = $this->contracts_model->add($contract_data);

        if (!$contract_id) {
            return [
                'success' => false,
                'message' => "Erro ao gerar contrato",
                'result' => $contract_id,
            ];
        }


        /**
         * GERAR ASSINATURA NA AUTENTIQUE
         */

        $data = [
            "contract_id" => $contract_id,
            "proposal" => [
                "id" => $proposal_id,
                "name" => $proposal->proposal_to,
                "email" => $email,
                "telefone" => $customer_phone,
                "cpf" => $customer_cpf
            ],
        ];

        $AutentiqueService = $this->onContractSendToAutentique($data);

        if (!$AutentiqueService['sign_link']) {
            return [
                'success' => false,
                'message' => "Erro ao gerar link de assinatura",
                'result' => $AutentiqueService,
            ];
        }


        $createDocument = $AutentiqueService['result']['data']['createDocument'];

        if (!$createDocument['id']) {

            return [
                'success' => false,
                'message' => "Erro ao pegar id do documento",
                'result' => $AutentiqueService,
            ];
        }

        // Assina o documento do administrador
        $onSignature = $this->onSignatureDocument($createDocument['id']);




        // ATUALIZA A PROPOSTA E OS CAMPOS PERSONALIZADOS
        $update_data = [
            'rel_id'                    => $proposal->rel_id,
            'rel_type'                  => $proposal->rel_type,
            'assigned'      => $proposal->assigned,
            'contract_id' => $contract_id,
            'sign_link' => $AutentiqueService['sign_link'],
            'sign_id' => $AutentiqueService['sign_id'],
            'document' => $createDocument['id'],
            'custom_fields' => [
                'proposal' => [
                    64 => "Aguardando formalização", // Etapa da proposta
                ]
            ]
        ];

        $update =  $this->proposals_model->update($update_data, $proposal_id);

        return [
            'success' => true,
            'contract_id' => $contract_id,
            'sign_link' => $AutentiqueService['sign_link'],
            'sign_id' => $AutentiqueService['sign_id'],
            'message' => "Contrato gerado com sucesso"
        ];
    }

    public function formatar_cpf($cpf)
    {
        // Remove tudo que não for número
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            return $cpf; // ou retornar null, lançar erro etc.
        }

        // Aplica a máscara
        return substr($cpf, 0, 3) . '.' .
            substr($cpf, 3, 3) . '.' .
            substr($cpf, 6, 3) . '-' .
            substr($cpf, 9, 2);
    }


    /**
     * FUNCOES AUTENTIQUE
     */

    public function onContractSendToAutentique($data)
    {
        $this->load->library('icash_tools/autentique/autentique_service');

        $contract_id = $data['contract_id'];
        $proposal = $data['proposal'];

        $contract = $this->contracts_model->get($contract_id);

        $result = $this->autentique_service->enviarContratoPDF($contract, $proposal);


        /**
         * PEGAR SIGN ID
         */


        $dataSign = $result['data'];

        if (!$dataSign) {
            return [
                'success'  => false,
                'message' => 'Erro ao gerar documento na Autentique',
                'result'  => $result
            ];
        }

        $createDocument = $dataSign['createDocument'];


        if (!$createDocument) {
            return [
                'success'  => false,
                'message' => 'Erro de documento na Autentique',
                'result'  => $result
            ];
        }

        $signatures = $createDocument['signatures'];
        $sign_id = $signatures[1]['public_id'];


        if (!$sign_id) {
            return [
                'success'  => false,
                'message' => 'Erro gerar public ID Autentique',
                'result'  => $result
            ];
        }

        $signLink = null;
        $signLink = $this->onGeneratorSignLink($sign_id);

        if (!$signLink) {
            return [
                'success'  => false,
                'message' => 'Erro gerar Link de Assinatura na Autentique.',
                'result'  => $result
            ];
        }

        return [
            'success'  => true,
            'message' => 'Contrato enviado para Autentique',
            'result'  => $result,
            'sign_id'  => $sign_id,
            'sign_link'  => $signLink
        ];
    }


    /**
     * GERAR LINK DE ASSINATURA
     */
    public function onAutentiqueSignLink($sign_id)
    {

        return "ok";
    }

    public function onGeneratorSignLink($sign_id)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.autentique.com.br/v2/graphql',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "query": "mutation{ createLinkToSignature(public_id: \\"' . $sign_id . '\\"){short_link}}",
            "variables": {}
        }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer f03b773d8355da8bcd3f9fce151caa3a2c624955e1db09236ce981d9dc3b1eb0',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);


        $data = json_decode($response, true)['data'];

        $createLinkToSignature = $data['createLinkToSignature'];

        if (!$createLinkToSignature) {
            return false;
        }

        return $createLinkToSignature['short_link'];
    }

    /**
     * ASSINAR UM DOCUMENTO
     */


    public function onSignatureDocument($document)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.autentique.com.br/v2/graphql',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "query": "mutation { signDocument(id: \\"' . $document . '\\") }",
    "variables": {}
}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer f03b773d8355da8bcd3f9fce151caa3a2c624955e1db09236ce981d9dc3b1eb0',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return;
    }


    /**
     * FUNCOE DE NOOTIFICACOES
     */

    public function onNotificationToWebhook($data)
    {

        $url = get_option('webhook_notification');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data)
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }
}
