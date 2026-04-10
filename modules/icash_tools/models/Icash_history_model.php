<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Icash_history_model extends App_Model
{
    private $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'icash_history';
        $this->load->model('proposals_model');
        $this->load->model('contracts_model');
    }

    /**
     * Get a single record or all records
     * @param  int|null $id
     * @return array|object|null
     */
    public function get($id_registro = null, $modulo = null)
    {
        if (is_numeric($id_registro)) {
            $this->db->where('modulo', $modulo);
            $this->db->where('id_registro', $id_registro);
            return $this->db->get($this->table)->row();
        }

        return $this->db->get($this->table)->result_array();
    }

    public function search($where = [])
    {
        if (!empty($where) && is_array($where)) {
            $this->db->where($where);
            return $this->db->get($this->table)->result();
        }
    
        // Caso não tenha filtro, retorna tudo (cuidado!)
        return $this->db->get($this->table)->result();
    }
    

    /**
     * Insert a new record
     * @param  array $data
     * @return int|false
     */
    public function insert($data)
    {
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


    public function get_staff_custom_fields($id)
    {

        $custom_fields = get_custom_fields('staff');

        $fields = [];
        foreach ($custom_fields as $field) {
            $fields[$field['slug']] = get_custom_field_value($id, $field['id'], 'staff');
        }
        return $fields;
    }
}
