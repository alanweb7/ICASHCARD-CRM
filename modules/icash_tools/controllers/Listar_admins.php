<?php defined('BASEPATH') or exit('No direct script access allowed');

class Listar_admins extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model'); // Modelo padrão do Perfex para staff
    }

    public function index()
    {
        // Obtém o ID do usuário (staff) logado
        $staff_id = $this->session->userdata('staff_user_id');

        // Consulta para pegar os staffs gerenciados pelo gerente logado junto com o nome da Role
        $this->db->select(
            'staff.staffid,
            staff.firstname, 
            staff.lastname, 
            staff.email, 
            staff.phonenumber, 
            staff.active, 
            staff.role,
            roles.name as role_name'
        );
        $this->db->from(db_prefix() . 'staff staff');
        $this->db->join(db_prefix() . 'roles roles', 'roles.roleid = staff.role', 'left');
        $this->db->where('staff.gerente_id', $staff_id);

        $query = $this->db->get();
        $data['staffs'] = $query->result_array();

        $this->load->view('atendentes/listar_admins', $data);
    }
}
