<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Icash_tables_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Função que retorna as tabelas
    public function get_tables()
    {
        return $this->db->get(db_prefix() . 'icash_tabelas')->result_array();
    }

    // Método para obter uma tabela por ID
    public function get_table_by_id($id)
    {
        // Consulta a tabela pelo ID
        $this->db->where('id', $id);
        $query = $this->db->get('icash_tabelas');
        return $query->row_array(); // Retorna um array associativo com o registro encontrado
    }


    public function tratar_dados($dados)
    {
        // Implemente a lógica para tratar os dados
        // Pode ser salvar no banco, realizar cálculos, etc.

        // Exemplo de inserção no banco de dados

        return true;
        if ($this->db->insert('nome_da_tabela', $dados)) {
            return true;
        } else {
            return false;
        }
    }
}
