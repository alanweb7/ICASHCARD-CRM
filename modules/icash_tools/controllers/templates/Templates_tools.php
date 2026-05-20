<?php defined('BASEPATH') or exit('No direct script access allowed');

class Templates_tools extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model'); // Modelo padrão do Perfex para staff
    }

    public function index()
    {

        $data['staffs'] =  "staff";

        $this->load->view('templates/template-client-details', $data);
    }

    public function proposal_details()
    {

        $data['staffs'] =  "staff";

        $this->load->view('templates/template-client-details', $data);
    }

    public function my_link()
    {

        $data['staffs'] =  "staff";
        $this->load->view('templates/template_link_corban', $data);
    }

    public function proposal_edit()
    {
        $proposal_id = $this->input->post('proposal_id');



        // otions de tabelas
        $this->load->model('invoice_items_model');
        $items = $this->invoice_items_model->get();

        $options = [];
        foreach ($items as $item) {
            $options[] = [
                'itemid' => $item['itemid'],
                'slug' => $item['description'],          // slug = description
                'title' => $item['long_description'],     // title = long_description
                'unit'  => $item['unit'],
                'rate'  => $item['rate'],
            ];
        }


        $data['options'] = $options;

        if ($proposal_id) {
            // Obtenha os dados da proposta do banco de dados
            $proposal = $this->proposals_model->get($proposal_id);

            if ($proposal) {

                // $rg = get_custom_fields('customers', ['id' => 68], $proposal->rel_id);
                $rg = $this->get_custom_field_value_db($proposal->rel_id, 68, 'customers');
                $data_nasc = $this->get_custom_field_value_db($proposal->rel_id, 70, 'customers');
                $customer_cf_cpf = $this->get_custom_field_value_db($proposal->rel_id, 3, 'customers');

                $customer_row = $this->db
                    ->select('vat')
                    ->where('userid', (int) $proposal->rel_id)
                    ->get(db_prefix() . 'clients')
                    ->row();

                $customer_vat = '';
                if ($customer_row && !empty($customer_row->vat)) {
                    $customer_vat = $customer_row->vat;
                } elseif (!empty($customer_cf_cpf)) {
                    $customer_vat = $customer_cf_cpf;
                }

                $customer = [

                    "rg" => $rg,
                    "data_nasc" => $data_nasc,
                    "vat" => $customer_vat,

                ];


                $custom_fields = get_custom_fields('proposal', ['show_on_table' => 1], $proposal_id);
                $custom_data = [];

                foreach ($custom_fields as $field) {
                    $valor_campo = $this->get_custom_field_value_db($proposal_id, $field['id']);
                    $custom_data[$field['name']] = $valor_campo;
                }
                // Garante disponibilidade do CPF da proposta (CF id 23) no template.
                if (!isset($custom_data['CPF'])) {
                    $custom_data['CPF'] = $this->get_custom_field_value_db($proposal_id, 23);
                }
                // Passar dados para o template
                $proposal->custom_fields = $custom_data;
                $proposal->customer = $customer;
                $data['proposal'] = $proposal;
                // $data['proposal']['custom_fields'] = 123;
                $this->load->view('templates/template-edit-proposal', $data);
            } else {
                echo 'Proposta não encontrada.';
            }
        } else {
            show_error('ID da proposta não fornecido.', 400);
        }
    }

    public function get_custom_field_value_db($proposal_id, $custom_field_id, $fieldto = 'proposal')
    {
        $sql = "SELECT cfv.value
                FROM tblcustomfieldsvalues as cfv
                JOIN tblcustomfields as cf ON cfv.fieldid = cf.id
                WHERE cfv.relid = ?
                AND cf.id = ?
                AND cfv.fieldto = '{$fieldto}'";

        $query = $this->db->query($sql, array($proposal_id, $custom_field_id));

        // return json_encode($query);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->value;
        } else {
            return null;
        }
    }




    // DADOS DO HISTORICO
    public function get_proposal_history()
    {
        $proposal_id = $this->input->post('proposal_id');

        if (!$proposal_id) {
            show_error('ID da proposta não informado', 400);
        }

        // Pega a proposta
        $proposal = $this->db->where('id', $proposal_id)
            ->get(db_prefix() . 'proposals')
            ->row();

        if (!$proposal) {
            show_error('Proposta não encontrada', 404);
        }

        // Pega histórico customizado
        $row = $this->db->where('id_registro', $proposal_id)
            ->order_by('date_updated', 'DESC')
            ->get(db_prefix() . 'icash_history')
            ->row();

        $data['history'] = [];

        // adiciona evento inicial: criação da proposta

        $infoStaff = $this->onGetNameStaffByID($proposal->assigned);

        $nome       = $infoStaff['name'];
        $role_name  = $infoStaff['role_name'];


        $data['history'][] = [
            'action'    => 'created',
            'step'   => 'Proposta criada',
            'event'   => 'Proposta criada',
            'staff_id'   => $proposal->assigned,
            'staff_name'   => "{$nome} | {$role_name}",
            'event_date' => $proposal->datecreated,
        ];

        // se existir histórico customizado, adiciona
        if ($row && !empty($row->historico)) {
            $extraHistory = json_decode($row->historico, true);
            if (is_array($extraHistory)) {
                $data['history'] = array_merge($data['history'], $extraHistory);
            }
        }

        $this->load->view('icash_tools/proposal_history', $data);
    }


    private function onGetNameStaffByID($staff_id)
    {

        $this->load->model('staff_model');
        $this->load->model('custom_fields_model');
        $this->load->model('roles_model');

        // Obtém o ID do staff logado
        // $staff_id = $this->session->userdata('staff_user_id');
        $staff_details = $this->staff_model->get($staff_id);

        $role_name = "";
        if ($staff_details) {
            $role = $this->roles_model->get($staff_details->role);
            if ($role) {
                $role_name =  $role->name;
            }
        }

        // // Fazendo uma consulta para pegar os campos personalizados do staff
        $this->db->select('cfv.value, cf.name');
        $this->db->from('tblcustomfieldsvalues cfv');
        $this->db->join('tblcustomfields cf', 'cf.id = cfv.fieldid', 'left');
        $this->db->where('cfv.fieldto', 'staff');  // Relaciona base staff
        $this->db->where('cfv.relid', $staff_id);  // Relaciona com o staff pelo ID

        $custom_fields = $this->db->get()->result_array();

        // Verifica se a consulta retornou resultados
        $infoData = [];
        if (count($custom_fields) > 0) {
            foreach ($custom_fields as $field) {
                $infoData[$field['name']] = $field['value'];
            }
        }

        $nome = $infoData["Nome Fantasia"] ?? $staff_details->full_name;

        $levelLow = [1];
        if (in_array($staff_details->role, $levelLow)) {
            $nome = $staff_details->full_name;
        }

        $data = [
            "name" => $nome,
            "role_name" => $role_name,
        ];

        return $data;
    }
}
