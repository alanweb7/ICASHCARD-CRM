<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gerenciar_propostas extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // $this->load->model('icash_tools_model');
        // $this->load->library('form_validation'); // Carrega a biblioteca de validação
        $this->load->model('proposals_model');
    }

    public function index()
    {
        // Carregar a view passando os dados corretamente

    }



    public function update_status()
    {

        $this->load->model('proposals_model');

        $proposal_id = $this->input->post('proposal_id');
        $proposal = $this->proposals_model->get($proposal_id);

        $new_status = $this->input->post('status');

        header('Content-Type: application/json');

        if (!$proposal_id || !$new_status) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        $pipeline = [
            "proposalid" => $proposal_id,
            "status" => $new_status
        ];

        $data['rel_id']   = $proposal->rel_id; //ID do Cliente
        $data['rel_type'] = $proposal->rel_type;

        $etapas = [
            1 => "PEN - Envio Documento",
            20 => "PEN - Doc. Ilegível",
            21 => "Em análise documental",
            22 => "Reprova documental",
            23 => "Link Pag. Enviado",
            24 => "Link Pag. Aprovado",
            25 => "Link Pag. Reprovado",
            26 => "Aguardando formalização",
            27 => "Em análise formalização",
            28 => "Liberar Crédito",
            29 => "Crédito Enviado",
            30 => "Descartado",
            2 => "Cancelada",
            3 => "Operação Finalizada"
        ];

        $data["custom_fields"] = [
            "proposal" => [
                64 => $etapas[$new_status]
            ]
        ];

        $updated = $this->proposals_model->update($data, $proposal_id);

        if ($updated) {


            // chamada antes de atualizar o histórico
            hooks()->do_action('before_history_register', [
                'id_registro' => $proposal_id,
                'module'      => 'proposals',
                'history' => [
                    [
                        'method' => __FUNCTION__,
                        'step'       => $etapas[$new_status],
                        'status'      => $new_status,
                        'event'  => 'Status atualizado'
                    ]
                ]
            ]);

            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o status']);
        }
    }



    public function get_custom_field_value_db($proposal_id, $custom_field_id)
    {
        $sql = "SELECT cfv.value
                FROM tblcustomfieldsvalues as cfv
                JOIN tblcustomfields as cf ON cfv.fieldid = cf.id
                WHERE cfv.relid = ?
                AND cf.id = ?
                AND cfv.fieldto = 'proposal'";

        $query = $this->db->query($sql, array($proposal_id, $custom_field_id));

        // return json_encode($query);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->value;
        } else {
            return null;
        }
    }


    public function onUpdateProposal()
    {

        $this->load->model('proposals_model');

        if (!staff_can('edit', 'corban_proposals')) {
            set_alert('danger', 'Permissão Negada.');
            redirect(admin_url('icash_tools/listar_propostas#'));
            return;
        }


        $this->load->model('proposals_model');

        $proposal_id = $this->input->post('proposal_id', TRUE);
        $proposal = $this->proposals_model->get($proposal_id);


        if (!$proposal_id) {
            set_alert('danger', 'Dados inválidos.');
            redirect(admin_url('icash_tools/listar_propostas#'));
            return;
        }


        $proposal_fields = $this->input->post('proposal_fields');
        if (!is_array($proposal_fields)) {
            $proposal_fields = [];
        }
        $proposal_to = trim((string) $this->input->post('proposal_to', true));
        $proposal_cpf = isset($proposal_fields[23]) ? trim((string) $proposal_fields[23]) : '';
        // VERIFICA SE OS VALORES MUDARAM NOS CAMPOS PERSONALIZADOS

        // busca todos os campos atuais dessa proposta
        $this->db->where('relid', $proposal_id);
        $this->db->where('fieldto', 'proposal');
        $current_fields = $this->db->get(db_prefix() . 'customfieldsvalues')->result();

        // cria um mapa [fieldid => value]
        $current_map = [];
        foreach ($current_fields as $f) {
            $current_map[$f->fieldid] = $f->value;
        }

        // busca metadados dos custom fields
        $this->db->where('fieldto', 'proposal');
        $fields_meta = $this->db->get(db_prefix() . 'customfields')->result();

        // cria um mapa [id => nome]
        $field_names = [];
        foreach ($fields_meta as $meta) {
            $field_names[$meta->id] = $meta->name;
        }

        // percorre os enviados e compara
        $changed_fields = [];
        foreach ($proposal_fields as $field_id => $new_value) {
            $old_value = isset($current_map[$field_id]) ? $current_map[$field_id] : null;

            $new_value_norm = trim((string)$new_value);
            $old_value_norm = trim((string)$old_value);

            if ($field_id == 100) { // campo de data
                // transforma de YYYY-MM-DD ou qualquer formato que veio do DB para DD-MM-YYYY
                if (!empty($old_value_norm)) {
                    $old_value_norm = date('d-m-Y', strtotime($old_value_norm));
                }
            }

            if ($new_value_norm !== $old_value_norm) {
                $fieldName = isset($field_names[$field_id]) ? $field_names[$field_id] : 'Campo ' . $field_id;
                $changed_fields[] = [
                    'method' => __FUNCTION__,
                    'step' => "{$fieldName}: {$old_value_norm} -> {$new_value}",
                    'event' => "Campo atualizado",
                    'custom_field_id'  => $field_id,
                    'custom_field_name'  => $fieldName,
                    'old'  => $old_value,
                    'new'  => $new_value,
                ];
            }
        }

        $old_proposal_to = trim((string) $proposal->proposal_to);
        if ($proposal_to !== '' && $old_proposal_to !== $proposal_to) {
            $changed_fields[] = [
                'method' => __FUNCTION__,
                'step'   => "Nome do cliente: {$old_proposal_to} -> {$proposal_to}",
                'event'  => "Campo atualizado",
                'field'  => 'proposal_to',
                'old'    => $old_proposal_to,
                'new'    => $proposal_to,
            ];
        }

        // FIM DA VERIFICAÇÃO SE MUDOU OPS VALORES

        $custom_fields = [];

        foreach ($proposal_fields as $key => $value) {
            $custom_fields[$key] = $value;
        }

        $items = $proposal->items;

        $parcela = floatval(str_replace(',', '.', str_replace('.', '', $custom_fields[14])));
        $TotalLiquido = floatval(str_replace(',', '.', str_replace('.', '', $custom_fields[16])));

        $qty = $custom_fields[13];
        $parcelaLiq = $TotalLiquido / $qty;

        $description = $custom_fields[67];
        $items[0]['itemid'] = $items[0]['id'];
        $items[0]['qty'] = $custom_fields[13];
        $items[0]['rate'] = $parcelaLiq;
        $items[0]['description'] = $description;

        /** total deve ser o valor liquido / parcelas
         * 
         **/

        $total = $qty * $parcela;


        $updateData = [
            'rel_id'        => $proposal->rel_id,
            'rel_type'      => $proposal->rel_type,
            'assigned'      => $proposal->assigned,
            'proposal_to'   => $proposal_to,
            'status'        => $proposal->status,
            "total"         => $TotalLiquido,
            "subtotal"      => $TotalLiquido,
            "items"         => $items,
            'custom_fields' => [
                'proposal'  => $custom_fields
            ]
        ];

        // Sincroniza nome do cliente real (tblclients) e contato principal.
        // O modal alterava apenas "proposal_to" (campo "Para" da proposta).
        if ($proposal->rel_type === 'customer' && !empty($proposal->rel_id)) {
            $customer_id = (int) $proposal->rel_id;

            $current_client = $this->db
                ->select('vat, company')
                ->where('userid', $customer_id)
                ->limit(1)
                ->get(db_prefix() . 'clients')
                ->row();

            if ($proposal_to !== '') {
                $this->db->where('userid', $customer_id);
                $this->db->update(db_prefix() . 'clients', ['company' => $proposal_to]);
            }

            if ($proposal_cpf !== '') {
                $old_vat = $current_client->vat ?? '';
                if (trim((string) $old_vat) !== $proposal_cpf) {
                    $changed_fields[] = [
                        'method' => __FUNCTION__,
                        'step'   => "CPF cliente: {$old_vat} -> {$proposal_cpf}",
                        'event'  => "Campo atualizado",
                        'field'  => 'customer_vat',
                        'old'    => $old_vat,
                        'new'    => $proposal_cpf,
                    ];
                }

                // Atualiza VAT no cadastro principal do cliente.
                $this->db->where('userid', $customer_id);
                $this->db->update(db_prefix() . 'clients', ['vat' => $proposal_cpf]);

                // Atualiza também o custom field do cliente (customers, field id 3).
                $existing_customer_cf = $this->db
                    ->where('relid', $customer_id)
                    ->where('fieldto', 'customers')
                    ->where('fieldid', 3)
                    ->limit(1)
                    ->get(db_prefix() . 'customfieldsvalues')
                    ->row();

                if ($existing_customer_cf) {
                    $this->db->where('id', $existing_customer_cf->id);
                    $this->db->update(db_prefix() . 'customfieldsvalues', ['value' => $proposal_cpf]);
                } else {
                    $this->db->insert(db_prefix() . 'customfieldsvalues', [
                        'relid'   => $customer_id,
                        'fieldto' => 'customers',
                        'fieldid' => 3,
                        'value'   => $proposal_cpf,
                    ]);
                }
            }

            $primary_contact = $this->db
                ->where('userid', $customer_id)
                ->where('is_primary', 1)
                ->limit(1)
                ->get(db_prefix() . 'contacts')
                ->row();

            if ($primary_contact && $proposal_to !== '') {
                $name_parts = preg_split('/\s+/', $proposal_to, -1, PREG_SPLIT_NO_EMPTY);
                $first_name = $name_parts[0] ?? $proposal_to;
                $last_name = count($name_parts) > 1
                    ? implode(' ', array_slice($name_parts, 1))
                    : ((string) $primary_contact->lastname !== '' ? $primary_contact->lastname : $first_name);

                $this->db->where('id', $primary_contact->id);
                $this->db->update(db_prefix() . 'contacts', [
                    'firstname' => $first_name,
                    'lastname'  => $last_name,
                ]);
            }
        }


        $updated = $this->proposals_model->update($updateData, $proposal_id);

        if ($updated) {

            // // compara os campos principais
            // foreach (['rel_id', 'rel_type', 'assigned', 'status', 'total', 'subtotal'] as $field) {
            //     if ($proposal->{$field} != $updateData[$field]) {
            //         $updatedData[$field] = [
            //             'old' => $proposal->{$field},
            //             'new' => $updateData[$field]
            //         ];
            //     }
            // }

            // // compara custom_fields específicos
            // if (isset($updateData['custom_fields']['proposal'])) {
            //     foreach ($updateData['custom_fields']['proposal'] as $fieldId => $newValue) {
            //         // pega valor antigo do banco
            //         $oldValue = get_custom_field_value($proposal_id, $fieldId, 'proposal');

            //         if ($oldValue != $newValue) {
            //             $updatedData['custom_field_' . $fieldId] = [
            //                 'old' => $oldValue,
            //                 'new' => $newValue
            //             ];
            //         }
            //     }
            // }


            // // compara itens (simplificado, só primeiro item do exemplo)
            // if (!empty($proposal->items) && !empty($updateData['items'])) {
            //     foreach ($updateData['items'] as $index => $newItem) {
            //         $oldItem = $proposal->items[$index] ?? null;
            //         if ($oldItem) {
            //             foreach (['qty', /*'rate',*/ 'description'] as $field) {
            //                 if ($oldItem[$field] != $newItem[$field]) {
            //                     $updatedData["item_{$index}_{$field}"] = [
            //                         'old' => $oldItem[$field],
            //                         'new' => $newItem[$field]
            //                     ];
            //                 }
            //             }
            //         }
            //     }
            // }

            // // enviar notificação para webhook
            // $this->onNotificationToWebhook($updatedData);

            set_alert('success', 'Dados atualizados com sucesso.');
        } else {
            set_alert('success', 'Nada foi alterado.');
        }


        // se quiser salvar as alterações, pode chamar aqui o update/insert como antes
        if (!empty($changed_fields)) {

            // chamada antes de atualizar o histórico
            hooks()->do_action('before_history_register', [
                'id_registro' => $proposal_id,
                'module'      => 'proposals',
                'history' =>  $changed_fields
            ]);

            // enviar notificação para webhook
            // $this->onNotificationToWebhook($changed_fields);

            // log_activity('Proposta ID ' . $proposal_id . ' teve campos personalizados alterados: ' . json_encode($changed_fields));
        }

        redirect(admin_url('icash_tools/listar_propostas#'));
    }


    // buscar propostar que mudaram recentemente

    public function get_status_atualizados()
    {

        header('Content-Type: application/json');

        $staff_id = get_staff_user_id();

        $proposals = $this->db->select('id, status')
            ->from('tblproposals')
            ->where('status !=', 3)
            ->where("update_at >= NOW() - INTERVAL 600 SECOND", null, false)
            ->get()
            ->result_array();


        $response = [
            'success' => false,
            'message' => 'Propostas encontradas',
            "proposals" =>  $proposals,
            "staff_id" => $staff_id
        ];

        echo json_encode($response);
        exit;
    }


    public function onNotificationToWebhook($data)
    {

        $webhookUrl = get_option("webhook_notification");

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $webhookUrl,
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
