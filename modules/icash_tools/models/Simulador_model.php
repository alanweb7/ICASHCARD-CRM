<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Simulador_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('proposals_model');
    }

    /**
     * Get a single record or all records
     * @param  int|null $id
     * @return array|object|null
     */
    public function get($id = null)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get($this->table)->row();
        }

        return $this->db->get($this->table)->result_array();
    }

    /**
     * Insert a new record
     * @param  array $data
     * @return int|false
     */
    public function insert($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['date_updated'] = date('Y-m-d H:i:s');

        $this->db->insert($this->table, $data);
        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Update an existing record
     * @param  int   $id
     * @param  array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $data['date_updated'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete a record
     * @param  int $id
     * @return bool
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);

        return $this->db->affected_rows() > 0;
    }


    public function get_documents_client_by_contract($id)
    {
        if ($id) {

            $proposal =  $this->proposals_model->get($id);

            $custom_fields = get_custom_fields('proposal');

            $fields = [];
            foreach ($custom_fields as $field) {
                $fields[$field['slug']] = get_custom_field_value($id, $field['id'], 'proposal');
            }
            $proposal->custom_fields = $fields;

            return $proposal;
        }

        return [];
    }


    public function get_client_assignature_by_contract($id)
    {

        $contract =  $this->contracts_model->get();
        return $contract->signed;
    }

    // busca um cliente por email
    public function get_client_by_email($cpf = ' ')
    {

        if (!$cpf) return false;

        $this->db->where('vat', $cpf);
        $clienteExistente = $this->db->get(db_prefix() . 'clients')->row();



        if ($clienteExistente) {

            return $clienteExistente;
        }


        return false;
    }


    public function get_staff_custom_fields($id)
    {

        $custom_fields = get_custom_fields('staff');

        $fields = [];
        foreach ($custom_fields as $field) {
            $fields[$field['slug']] = get_custom_field_value($id, $field['id'], 'staff');
        }
        return $fields;
    }


    public function sendWhatsNotifications($data)
    {

        $jsonData = json_encode($data);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://connector.atendeai24h.com.br/message/sendText/icashcard',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'apikey: 0786271EE950-4962-8CE0-7CE12AA65AEF'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);


        return json_decode($response);
    }


    public function sendWebhookNotifications($data)
    {

        $webhook = trim(get_option('webhook_notification'));

        $jsonData = json_encode($data);

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
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return;
    }
}
