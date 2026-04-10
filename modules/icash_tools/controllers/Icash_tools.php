<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Icash_tools extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('icash_tools_model');
        $this->load->library('form_validation'); // Carrega a biblioteca de validação
        $this->load->model('proposals_model');

        \modules\icash_tools\core\Apiinit::the_da_vinci_code('api');
    }

    /* Lista todas as tabelas registradas */
    public function index()
    {
        if (staff_cant('view', 'icash_tables')) {
            access_denied('icash_tools');
        }
    }

    public function tables()
    {
        // Verifica se o usuário tem permissão para visualizar
        if (staff_cant('view', 'icash_tables')) {
            access_denied('icash_tools');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('icash_tools', 'tabelas/table'));
        } else {
            $data['title'] = _l('icash_tables');
            $this->load->view('tabelas/icash_manage_tabelas', $data);
        }
    }


    public function manage_access_profiles()
    {
        $data['user_icash'] = "Alan Silva";
        $data['title'] = _l('Gerenciar Sistema');
        $this->load->view('icash_tools/admin/manage_access_profiles', $data);
    }

    public function icash_insert_tabelas()
    {
        if (staff_cant('edit', 'icash_tables')) {
            access_denied('icash_tools');
        }
        $data['title'] = _l('Nova Tabela');
        $this->load->view('tabelas/icash_insert_tabelas', $data);
    }

    public function insert_table()
    {
        if (staff_cant('edit', 'icash_tables')) {
            access_denied('icash_tools');
        }
        $this->form_validation->set_rules('nome_tabela', 'Table Name', 'required');
        $this->form_validation->set_rules('credenciadora', 'Credenciadora', 'required');

        if ($this->form_validation->run() === FALSE) {
            set_alert('danger', validation_errors());
            $this->icash_insert_tabelas();
        } else {
            $data = array(
                'nome_tabela' => $this->input->post('nome_tabela'),
                'parcelas' => json_encode($this->input->post('parcelas')),
                'credenciadora' => $this->input->post('credenciadora'),
                'operator_id' => get_staff_user_id()
            );

            $success = $this->icash_tools_model->insert($data);
            set_alert($success ? 'success' : 'danger', $success ? 'Data saved successfully' : 'Failed to save data');

            redirect(admin_url('icash_tools'));
        }
    }


    public function update_table($id)
    {

        if (staff_cant('edit', 'icash_tables')) {
            access_denied('icash_tools');
        }
        if (staff_cant('edit', 'icash_tables')) {
            access_denied('icash_tools');
        }

        if (!$this->input->post()) {
            // Carregar o registro para edição
            $data['table'] = $this->db->where('id', $id)->get(db_prefix() . 'icash_tabelas')->row();
            $data['title'] = _l('icash_edit_tabela');
            $this->load->view('tabelas/icash_edit_tabela', $data);
        } else {
            // Atualizar o registro
            $postData = $this->input->post();
            $parcelas = json_encode($postData['parcelas']);

            $updateData = [
                'nome_tabela' => $postData['nome_tabela'],
                'credenciadora' => $postData['credenciadora'],
                'parcelas' => $parcelas
            ];

            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'icash_tabelas', $updateData);

            set_alert('success', _l('updated_successfully'));
            redirect(admin_url('icash_tools'));
        }
    }


    public function delete_table($id)
    {
        if (staff_cant('delete', 'icash_tables')) {
            access_denied('icash_tools');
        }
        if (staff_cant('delete', 'icash_tables')) {
            access_denied('icash_tools');
        }

        if (!$id) {
            set_alert('danger', _l('No table found'));
            redirect(admin_url('icash_tools'));
        }

        $success = $this->icash_tools_model->delete($id);
        set_alert($success ? 'success' : 'danger', $success ? _l('Table deleted successfully') : _l('Failed to delete table'));

        redirect(admin_url('icash_tools'));
    }

    public function tabela_view($id)
    {
        // Verifica se o usuário tem permissão para visualizar
        if (staff_cant('view', 'icash_tables')) {
            access_denied('icash_tools');
        }

        // Obtém os detalhes da tabela
        $data['table'] = $this->icash_tools_model->get($id);

        // Verifica se a tabela existe
        if (!$data['table']) {
            show_404();
        }

        // Define o título da página
        $data['title'] = _l('View Table');
        // Carrega a view de visualização
        $this->load->view('tabelas/icash_view_tabela', $data);
    }


    // controller de API Rest
    public function api_list_tables()
    {
        // Check authorization
        if (!$this->check_basic_auth()) {
            // Unauthorized response
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        // Load the database if not already loaded
        $this->load->database();

        // Fetch the tables
        $tables = $this->db->get(db_prefix() . 'icash_tabelas')->result();

        // Return the data as JSON
        echo json_encode(['status' => 'success', 'data' => $tables]);
    }

    private function check_basic_auth()
    {
        // Get the Authorization header
        $authHeader = $this->input->get_request_header('Authorization');


        return true;
        if ($authHeader == "YWRtaW46cGFzc3dvcmQ=") {
            return true;
        }

        if (!$authHeader) {
            return false;
        }

        // Extract and decode the credentials from the header
        list($username, $password) = explode(':', base64_decode(substr($authHeader, 6)));

        // Check credentials (you can replace this with database checks or config-based credentials)
        $validUsername = 'your_username';
        $validPassword = 'your_password';

        return $username === $validUsername && $password === $validPassword;
    }


    public function custom_dashboard()
    {

        $data = [];
        $banners = $this->onGetNotesByPolyUtilities("banners");
        $notes = $this->onGetNotesByPolyUtilities("notes");

        if (!empty($banners)) {
            $banners = $banners ? $banners : '[]';
            $data["banners"] = json_decode($banners, true);
        }

        if (!empty($notes)) {
            $notes = $notes ? $notes : '[]';
            $data["notes"] = json_decode($notes, true);
        }

        $this->load->view('admin/custom_dashboard', $data);
    }

    public function onGetNotesByPolyUtilities($area = "notes")
    {

        switch ($area) {
            case 'banners':
                $content = get_option('poly_utilities_banners');
                break;
            case 'notes':
                $content = get_option('poly_utilities_banners_announcements');
                break;

            default:
                # code...
                break;
        }

        return $content;
    }



        // SISTEMAS DE ENVIA DE ARQUIVOS
    public function upload_payment_doc()
    {
        $id = $this->input->post('id') ?? null;
        $doc_type = $this->input->post('type') ?? null;
        $action = $this->input->post('action') ?? 'replace';


        header('Content-Type: application/json');
        if (!$id || !$doc_type) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    "id" => $id,
                    "type" => $doc_type,
                    "action" => $action
                ]
            ]);
            return;
        }

        // Diretório de upload
        $uploadDir = ICASH_TOOLS_CLIENTS_UPLOADS . "proposals_docs/{$id}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }


        // AÇÃO: EXCLUIR
        if ($action === 'delete') {
            // Buscar dados da proposta
            $this->db->where('id', $id);
            $proposal = $this->db->get(db_prefix() . 'proposals')->row();

            // Limpar o campo no banco de dados
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'proposals', ["{$doc_type}"  => null]);

            echo json_encode([
                'status' => 'success', 
                'data' => [
                    "id" => $id,
                    "type" => $doc_type,
                    "action" => $action
                ]
            ]);
            return;
        }

        // AÇÃO: UPLOAD / TROCA
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            set_alert('danger', 'Nenhum arquivo enviado ou ocorreu um erro no upload.');
            redirect(admin_url('hr_profile/member/' . $id));
            return;
        }

        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt'];
        $maxSize = 15 * 1024 * 1024; // 15MB

        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedTypes)) {
            set_alert('danger', 'O tipo de arquivo enviado não é permitido.');
            redirect(admin_url('hr_profile/member/' . $id));
            return;
        }

        if ($fileSize > $maxSize) {
            set_alert('danger', 'O arquivo excede o tamanho máximo permitido de 15MB.');
            redirect(admin_url('hr_profile/member/' . $id));
            return;
        }

        $newFileName = uniqid('file_', true) . '.' . $fileExt;
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpName, $destination)) {
            // Excluir anterior, se existir
            $this->db->where('staffid', $id);
            $staff = $this->db->get(db_prefix() . 'staff')->row();
            if ($staff && isset($staff->$doc_type) && !empty($staff->$doc_type)) {
                $oldFilePath = FCPATH . str_replace(site_url(), '', $staff->$doc_type);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            $dbDocUrl = site_url() . $uploadDir . $newFileName;
            $this->db->where('staffid', $id);
            $this->db->update(db_prefix() . 'staff', [$doc_type => $dbDocUrl]);

            set_alert('success', 'Arquivo enviado com sucesso!');
        } else {
            set_alert('danger', 'Erro ao mover o arquivo para o diretório de destino.');
        }

        redirect(admin_url('hr_profile/member/' . $id));
    }


    public function upload_file()
    {
        $staffid = $this->input->post('staffid') ?? null;
        $doc_type = $this->input->post('doc_type') ?? null;

        if (!$staffid) {
            set_alert('danger', "Membro não encontrado.");
        }

        $data = [
            "staffid" => $staffid,
            "doc_type" => $doc_type
        ];

        $this->onUpdateStaffDoc($data);

        $uploadDir = ICASH_TOOLS_CLIENTS_UPLOADS . "documents/{$staffid}/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); // Cria o diretório se não existir
        }

        // Verificar se um arquivo foi enviado
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            set_alert('danger', 'Nenhum arquivo enviado ou ocorreu um erro no upload.');
            redirect(admin_url('hr_profile/member/' . $staffid));
            return;
        }

        // Configurações de tipos permitidos e tamanho máximo
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt'];
        $maxSize = 15 * 1024 * 1024; // 2MB

        // Obter informações do arquivo
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validar extensão do arquivo
        if (!in_array($fileExt, $allowedTypes)) {
            set_alert('danger', 'O tipo de arquivo enviado não é permitido.');
            redirect(admin_url('hr_profile/member/' . $staffid));
            return;
        }

        // Validar tamanho do arquivo
        if ($fileSize > $maxSize) {
            set_alert('danger', 'O arquivo excede o tamanho máximo permitido de 2MB.');
            redirect(admin_url('hr_profile/member/' . $staffid));
            return;
        }

        // Gerar um nome único para o arquivo
        $newFileName = uniqid('file_', true) . '.' . $fileExt;

        // Caminho completo para o arquivo de destino
        $destination = $uploadDir . $newFileName;

        // Mover o arquivo para o diretório de destino
        if (move_uploaded_file($fileTmpName, $destination)) {
            set_alert('success', 'Arquivo enviado com sucesso!');

            switch ($doc_type) {
                case 'contrato_social':

                    break;

                default:
                    // padrao
                    break;
            }

            $dbDocUrl = site_url() . $uploadDir . $newFileName;
            $this->db->where('staffid', $staffid);
            $this->db->update(db_prefix() . 'staff', [
                $doc_type => $dbDocUrl,
            ]);
        } else {
            set_alert('danger', 'Ocorreu um erro ao mover o arquivo para o diretório de destino.');
        }
        redirect(admin_url('hr_profile/member/' . $staffid));
    }


    // UPLOAD DO COMPROVANTE DE PAGAMENTO (PIX) LIBERAR CRÉDITO
    // public function upload_payment_doc()
    // {
    //     $staffid = $this->input->post('proposal_id') ?? null;
    //     $doc_type = $this->input->post('doc_type') ?? null;

    //     if (!$staffid) {
    //         set_alert('danger', "Membro não encontrado.");
    //     }

    //     $data = [
    //         "staffid" => $staffid,
    //         "doc_type" => $doc_type
    //     ];

    //     $this->onUpdateStaffDoc($data);

    //     $uploadDir = ICASH_TOOLS_CLIENTS_UPLOADS . "documents/{$staffid}/";

    //     if (!is_dir($uploadDir)) {
    //         mkdir($uploadDir, 0755, true); // Cria o diretório se não existir
    //     }

    //     // Verificar se um arquivo foi enviado
    //     if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    //         set_alert('danger', 'Nenhum arquivo enviado ou ocorreu um erro no upload.');
    //         redirect(admin_url('hr_profile/member/' . $staffid));
    //         return;
    //     }

    //     // Configurações de tipos permitidos e tamanho máximo
    //     $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt'];
    //     $maxSize = 15 * 1024 * 1024; // 2MB

    //     // Obter informações do arquivo
    //     $file = $_FILES['file'];
    //     $fileName = $file['name'];
    //     $fileTmpName = $file['tmp_name'];
    //     $fileSize = $file['size'];
    //     $fileError = $file['error'];
    //     $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    //     // Validar extensão do arquivo
    //     if (!in_array($fileExt, $allowedTypes)) {
    //         set_alert('danger', 'O tipo de arquivo enviado não é permitido.');
    //         redirect(admin_url('hr_profile/member/' . $staffid));
    //         return;
    //     }

    //     // Validar tamanho do arquivo
    //     if ($fileSize > $maxSize) {
    //         set_alert('danger', 'O arquivo excede o tamanho máximo permitido de 2MB.');
    //         redirect(admin_url('hr_profile/member/' . $staffid));
    //         return;
    //     }

    //     // Gerar um nome único para o arquivo
    //     $newFileName = uniqid('file_', true) . '.' . $fileExt;

    //     // Caminho completo para o arquivo de destino
    //     $destination = $uploadDir . $newFileName;

    //     // Mover o arquivo para o diretório de destino
    //     if (move_uploaded_file($fileTmpName, $destination)) {
    //         set_alert('success', 'Arquivo enviado com sucesso!');

    //         switch ($doc_type) {
    //             case 'contrato_social':

    //                 break;

    //             default:
    //                 // padrao
    //                 break;
    //         }

    //         $dbDocUrl = site_url() . $uploadDir . $newFileName;
    //         $this->db->where('staffid', $staffid);
    //         $this->db->update(db_prefix() . 'staff', [
    //             $doc_type => $dbDocUrl,
    //         ]);
    //     } else {
    //         set_alert('danger', 'Ocorreu um erro ao mover o arquivo para o diretório de destino.');
    //     }
    //     redirect(admin_url('hr_profile/member/' . $staffid));
    // }


    
    public function onNotificationToWebhook($data)
    {

        $dataJson = json_encode($data);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://webhook.site/8d3d2a81-8e1e-4815-a819-35f818cea326',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $dataJson,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function onUpdateStaffDoc($data)
    {

        $this->load->model('staff_model');


        return true;
    }
}
