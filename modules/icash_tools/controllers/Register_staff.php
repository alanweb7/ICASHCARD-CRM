<?php defined('BASEPATH') or exit('No direct script access allowed');

class Register_staff extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model'); // Modelo padrão do Perfex para staff
    }

    public function index()
    {
        $this->load->view('register_staff');
    }

    public function submit()
    {
        $data = $this->input->post();

        // Validação básica
        if (
            empty($data['fullname']) ||
            empty($data['email']) ||
            empty($data['password']) ||
            empty($data['cpf'])
        ) {
            set_alert('danger', 'Todos os campos são obrigatórios!');
            redirect(admin_url('icash_tools/listar_atendentes'));
        }

        $cpf = $data['cpf'];
        $cpf_limpo = preg_replace('/\D/', '', $cpf);
        $gerente_logado = get_staff_user_id();

        // if ($gerente_logado) {
        //     set_alert('danger', "Este CPF {} já foi cadastrado por você. ".$gerente_logado);
        // }
        // redirect(admin_url('icash_tools/listar_atendentes'));
        // return;

        // Verificar se o CPF já foi cadastrado por este gerente
        $this->db->where('cpf_cnpj', $cpf_limpo);
        $this->db->where('gerente_id', $gerente_logado);
        $existente = $this->db->get(db_prefix() . 'staff')->row();

        if ($existente) {
            set_alert('danger', 'Este CPF já foi cadastrado por você.');
            redirect(admin_url('icash_tools/listar_atendentes'));
            return;
        }

        // Preparar dados para inserção
        $staff_data = [
            'firstname'    => $data['fullname'],
            'email'        => $data['email'],
            'password'     => $data['password'],
            'phonenumber'  => $data['phone'],
            'cpf_cnpj'     => $cpf_limpo,
            'datecreated'  => date('Y-m-d H:i:s'),
            'active'       => 1,
            'role'         => 1, // Atendente
            'gerente_id'   => $gerente_logado
        ];

        // Inserir no banco
        $insert_id = $this->staff_model->add($staff_data);
        if ($insert_id) {
            set_alert('success', 'Membro registrado com sucesso!');
        } else {
            set_alert('danger', 'Erro ao registrar o membro.');
        }

        redirect(admin_url('icash_tools/listar_atendentes'));
    }




    public function submit_admin()
    {



        if (!staff_can('create', 'user_manager')) {

            set_alert('danger', 'Permissão negada');
            redirect(admin_url('icash_tools/listar_admins'));

            return;
        }

        $data = $this->input->post();

        // Validação básica
        if (empty($data['fullname']) || empty($data['email']) || empty($data['password']) || empty($data['cargo'])) {
            set_alert('danger', 'Todos os campos são obrigatórios!');
            redirect(admin_url('icash_tools/listar_admins'));
        }

        // Preparar dados para inserção
        $staff_data = [
            'firstname' => $data['fullname'],
            'email'     => $data['email'],
            'phonenumber' => $data['phone'],
            'password'  => $data['password'],
            'datecreated' => date('Y-m-d H:i:s'),
            'active'    => 1, // Ativar por padrão
            'role'    => $data['cargo'], // Atendente
            'gerente_id'    => $this->session->userdata('staff_user_id')
        ];

        // Inserir no banco
        $insert_id = $this->staff_model->add($staff_data);
        if ($insert_id) {
            set_alert('success', 'Membro registrado com sucesso!');
            redirect(admin_url('icash_tools/listar_admins'));
        } else {
            set_alert('danger', 'Erro ao registrar o membro.');
            redirect(admin_url('icash_tools/listar_admins'));
        }
    }

    public function add_staff()
    {
        $this->load->model('icash_tools_model');
        $data = [
            'name' => $this->input->post('name'),
            'phone' => $this->input->post('phone'),
            'email' => $this->input->post('email'),
            'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
        ];
        $this->icash_tools_model->add_staff($data);
        set_alert('success', 'Atendente adicionado com sucesso.');
        redirect(admin_url('icash_tools/staff_list'));
    }

    public function delete_staff()
    {
        $this->load->model('icash_tools_model');
        $id = $this->input->post('id');
        $this->icash_tools_model->delete_staff($id);
        echo json_encode(['success' => true]);
    }


    public function update_staff()
    {

        // Recebe os dados do formulário
        $data = $this->input->post();
        $staffid = $this->input->post('staffid');

        $urlRedirect = 'icash_tools/listar_atendentes';
        if ($this->input->post('action') == "editAdmin") {
            $urlRedirect = 'icash_tools/listar_admins';
        }

        if (!staff_can('edit', 'user_manager') && !is_admin()) {

            set_alert('danger', 'Permissão negada');
            redirect(admin_url($urlRedirect));

            return;
        }


        // Valida os dados recebidos
        if (empty($staffid)) {
            set_alert('error', 'Faltando o ID do funcionário.');
            redirect(admin_url($urlRedirect));
        }


        $role = 1;

        if (staff_can('edit', 'user_manager') && is_admin()) {
            $role = isset($data['role']) ? $data['role'] : 1;
        }


        $cpf = $data['cpf'];
        $cpf_limpo = preg_replace('/\D/', '', $cpf);


        // Monta os dados para atualização
        $staff_data = [
            'firstname' => $data['fullname'],
            'email'     => $data['email'],
            'phonenumber'  => $data['phone'],
            'cpf_cnpj'     => $cpf_limpo,
            'password'  => $data['password'], // Será tratado no método da model
            'active'    => isset($data['active']) ? $data['active'] : 1, // Valor padrão se não enviado
            'role'      => $role, // Valor padrão se não enviado
        ];

        // Atualiza os dados na model
        $updated = $this->staff_model->update_profile($staff_data, $staffid);

        // Define mensagens de alerta
        if ($updated) {
            set_alert('success', 'Registro atualizado com sucesso!');
        } else {
            set_alert('error', 'Nenhuma alteração detectada ou erro ao atualizar.');
        }

        // Redireciona para a lista
        redirect(admin_url($urlRedirect));
    }


    public function onOff_staff()
    {
        // Recebe o ID do funcionário a ser atualizado
        $staffid = $this->input->post('staffid');

        if (!$staffid) {
            set_alert('error', 'ID do funcionário não fornecido.');
            redirect(admin_url('icash_tools/listar_atendentes'));
        }

        // Consulta o status atual do funcionário
        $this->db->select('active');
        $this->db->from('tblstaff');
        $this->db->where('staffid', $staffid);
        $current_status = $this->db->get()->row();

        if (!$current_status) {
            set_alert('error', 'Funcionário não encontrado.');
            redirect(admin_url('icash_tools/listar_atendentes'));
        }

        // Alterna o status: 1 (ativo) para 0 (inativo) e vice-versa
        $new_status = $current_status->active == 1 ? 0 : 1;

        // Atualiza o status no banco de dados
        $this->db->where('staffid', $staffid);
        $updated = $this->db->update('tblstaff', ['active' => $new_status]);

        // Define mensagens de alerta
        if ($updated) {
            $message = $new_status == 1 ? 'Funcionário ativado com sucesso!' : 'Funcionário desativado com sucesso!';
            set_alert('success', $message);
        } else {
            set_alert('error', 'Erro ao atualizar o status do funcionário.');
        }

        // Redireciona para a lista
        redirect(admin_url('icash_tools/listar_atendentes'));
    }
}

