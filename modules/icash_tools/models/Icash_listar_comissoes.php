<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Icash_listar_comissoes extends App_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'icash_tabelas';
    }

    // Método para obter todos os registros
    public function get_all()
    {
        return $this->db->get($this->table)->result_array();
    }

    // Método para obter um registro específico pelo ID
    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row_array();
    }

	public function load_commission_policy($id = '')
	{
		if ($id != '') {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'commission_policy')->row();
		}

		return $this->db->get(db_prefix() . 'commission_policy')->result_array();
	}


	public function load_items_tables($id = '')
	{
		if ($id != '') {
			$this->db->where('id', $id);
			return $this->db->get(db_prefix() . 'items')->row();
		}

		return $this->db->get(db_prefix() . 'items')->result_array();
	}

}
