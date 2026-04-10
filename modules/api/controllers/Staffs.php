<?php

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require __DIR__ . '/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Staffs extends REST_Controller
{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('Api_model');
    }

    /**
     * @api {get} api/staffs/:id Request Staff information
     * @apiName GetStaff
     * @apiGroup Staffs
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     *
     * @apiParam {Number} id Staff unique ID.
     *
     * @apiSuccess {Object} Staff information.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "staffid": "8",
     *          "email": "data1.gsts@gmail.com",
     *          "firstname": "Đào Quang Dân",
     *          "lastname": "",
     *          "facebook": "",
     *          "linkedin": "",
     *          "phonenumber": "",
     *          "skype": "",
     *          "password": "$2a$08$ySLokLAM.AqmW9ZjY2YREO0CIrd5K4Td\/Bpfp8d9QJamWNUfreQuK",
     *          "datecreated": "2019-02-25 09:11:31",
     *          "profile_image": "8.png",
     *         ...
     *     }
     *
     * @apiError StaffNotFound The id of the Staff was not found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */
    public function data_get($id = '')
    {
        // If the id parameter doesn't exist return all the
        $data = $this->Api_model->get_table('staffs', $id);

        // Check if the data store contains
        if ($data) {
            $data = $this->Api_model->get_api_custom_data($data, "staff", $id);

            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No data were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    /**
     * @api {get} api/staffs/search/:keysearch Search Staff Information
     * @apiName GetStaffSearch
     * @apiGroup Staffs
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     *
     * @apiParam {String} keysearch Search keywords.
     *
     * @apiSuccess {Object} Staff information.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "staffid": "8",
     *          "email": "data1.gsts@gmail.com",
     *          "firstname": "Đào Quang Dân",
     *          "lastname": "",
     *          "facebook": "",
     *          "linkedin": "",
     *          "phonenumber": "",
     *          "skype": "",
     *          "password": "$2a$08$ySLokLAM.AqmW9ZjY2YREO0CIrd5K4Td\/Bpfp8d9QJamWNUfreQuK",
     *          "datecreated": "2019-02-25 09:11:31",
     *          "profile_image": "8.png",
     *         ...
     *     }
     *
     * @apiError StaffNotFound The id of the Staff was not found.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "No data were found"
     *     }
     */
    public function data_search_get($key = '')
    {
        $data = $this->Api_model->search('staff', $key);
        // Check if the data store contains
        if ($data) {
            $data = $this->Api_model->get_api_custom_data($data, "staff");

            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No data were found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }

    /**
     * @api {post} api/staffs Add New Staff
     * @apiName PostStaffs
     * @apiGroup Staffs
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     *
     * @apiParam {String} firstname             Mandatory Staff Name.
     * @apiParam {String} email                 Mandatory Staff Related.
     * @apiParam {String} password              Mandatory Staff password.
     * @apiParam {Number} [hourly_rate]         Optional hourly rate.
     * @apiParam {String} [phonenumber]         Optional Staff phonenumber.
     * @apiParam {String} [facebook]            Optional  Staff facebook.
     * @apiParam {String} [linkedin]            Optional  Staff linkedin.
     * @apiParam {String} [skype]               Optional Staff skype.
     * @apiParam {String} [default_language]    Optional Staff default language.
     * @apiParam {String} [email_signature]     Optional Staff email signature.
     * @apiParam {String} [direction]           Optional Staff direction.
     * @apiParam {String} [send_welcome_email]  Optional Staff send welcome email.
     * @apiParam {Number[]} [departments]  Optional Staff departments.
     *
     * @apiParamExample {Multipart Form} Request-Example:
     *     array (size=15)
     *     'firstname' => string '4' (length=1)
     *     'email' => string 'a@gmail.com' (length=11)
     *     'hourly_rate' => string '0' (length=1)
     *     'phonenumber' => string '' (length=0)
     *     'facebook' => string '' (length=0)
     *     'linkedin' => string '' (length=0)
     *     'skype' => string '' (length=0)
     *     'default_language' => string '' (length=0)
     *     'email_signature' => string '' (length=0)
     *     'direction' => string '' (length=0)
     *    'departments' => 
     *       array (size=5)
     *         0 => string '1' (length=1)
     *         1 => string '2' (length=1)
     *         2 => string '3' (length=1)
     *         3 => string '4' (length=1)
     *         4 => string '5' (length=1)
     *     'send_welcome_email' => string 'on' (length=2)
     *     'fakeusernameremembered' => string '' (length=0)
     *     'fakepasswordremembered' => string '' (length=0)
     *     'password' => string '1' (length=1)
     *     'role' => string '18' (length=2)
     *
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {String} message Staff add successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Staff add successful."
     *     }
     *
     * @apiError {String} status Request status.
     * @apiError {String} message Staff add fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Staff add fail."
     *     }
     * 
     */
    public function data_post()
    {


        \modules\api\core\Apiinit::the_da_vinci_code('api');

        // $this->response("Ok", REST_Controller::HTTP_OK);

        // form validation
        $this->form_validation->set_rules('firstname', 'First Name', 'trim|required|max_length[600]', array('is_unique' => 'This %s already exists please enter another Staff First Name'));
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email', array('is_unique' => 'This %s already exists please enter another Staff Email'));
        $this->form_validation->set_rules('password', 'Password', 'trim|required', array('is_unique' => 'This %s already exists please enter another Staff password'));
        if ($this->form_validation->run() == FALSE) {
            // form validation error
            $message = array(
                'status' => FALSE,
                'error' => $this->form_validation->error_array(),
                'message' => validation_errors()
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $departments = $this->Api_model->value($this->input->post('departments', TRUE));
            $insert_data = [
                'firstname' => $this->input->post('firstname', TRUE),
                'email' => $this->input->post('email', TRUE),
                'password' => $this->input->post('password', TRUE),
                'lastname' => '',
                'hourly_rate' => $this->Api_model->value($this->input->post('hourly_rate', TRUE)),
                'phonenumber' => $this->Api_model->value($this->input->post('phonenumber', TRUE)),
                'facebook' => $this->Api_model->value($this->input->post('facebook', TRUE)),
                'linkedin' => $this->Api_model->value($this->input->post('linkedin', TRUE)),
                'skype' => $this->Api_model->value($this->input->post('skype', TRUE)),
                'default_language' => $this->Api_model->value($this->input->post('default_language', TRUE)),
                'email_signature' => $this->Api_model->value($this->input->post('email_signature', TRUE)),
                'direction' => $this->Api_model->value($this->input->post('direction', TRUE)),
                'send_welcome_email' => $this->Api_model->value($this->input->post('send_welcome_email', TRUE)),
                'role' => '1',
                'permissions' => array(
                    'bulk_pdf_exporter' => array('view'),
                    'contracts' => array('create', 'edit', 'delete'),
                    'credit_notes' => array('create', 'edit', 'delete'),
                    'customers' => array('view', 'create', 'edit', 'delete'),
                    'email_templates' => array('view', 'edit'),
                    'estimates' => array('create', 'edit', 'delete'),
                    'expenses' => array('create', 'edit', 'delete'),
                    'invoices' => array('create', 'edit', 'delete'),
                    'items' => array('view', 'create', 'edit', 'delete'),
                    'knowledge_base' => array('view', 'create', 'edit', 'delete'),
                    'payments' => array('view', 'create', 'edit', 'delete'),
                    'projects' => array('view', 'create', 'edit', 'delete'),
                    'proposals' => array('create', 'edit', 'delete'),
                    'contracts' => array('view'),
                    'roles' => array('view', 'create', 'edit', 'delete'),
                    'settings' => array('view', 'edit'),
                    'staff' => array('view', 'create', 'edit', 'delete'),
                    'subscriptions' => array('create', 'edit', 'delete'),
                    'tasks' => array('view', 'create', 'edit', 'delete'),
                    'checklist_templates' => array('create', 'delete'),
                    'leads' => array('view', 'delete'),
                    'goals' => array('view', 'create', 'edit', 'delete'),
                    'surveys' => array('view', 'create', 'edit', 'delete'),
                )
            ];
            if ($departments != '') {
                $insert_data['departments'] = $departments;
            }


            // trata customizacoes vindas de form externo

            if (!empty($this->input->post('icash_action', TRUE))) {

                // action registradas: corban_register, corban_delete, etc...
                $action = $this->input->post('icash_action', TRUE);


                // $status = !empty($this->input->post('status_work', TRUE)) ? $this->input->post('status', TRUE) : "working";
                $birthday = $this->input->post('birthday', TRUE); // "21/10/2000"

                // Converte a data de 'd/m/Y' para 'Y-m-d' usando DateTime::createFromFormat
                $date = explode("/", $birthday);

                $newDate = "{$date[2]}-{$date[1]}-{$date[0]}";

                $comp_endereco = $this->input->post('comp_endereco', TRUE)[0];
                $contrato_social = $this->input->post('contrato_social', TRUE)[0];
                $doc_socio_principal = $this->input->post('doc_socio_principal', TRUE)[0];
                $foto_fachada = $this->input->post('foto_fachada', TRUE)[0];

                $marital_status = $this->input->post('marital_status', TRUE);
                $role = $this->input->post('role', TRUE) ?? 4;


                $fullName = $this->input->post('firstname', TRUE);
                $partes = explode(' ', $fullName);
                $firstname = $partes[0]; // O primeiro nome
                $lastname = implode(' ', array_slice($partes, 1)); // Junta o restante como sobrenome

                $custom_data = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    "marital_status" =>  $marital_status,
                    "comp_endereco" =>  $comp_endereco,
                    "contrato_social" => $contrato_social,
                    "doc_socio_principal" => $doc_socio_principal,
                    "foto_fachada" => $foto_fachada,
                    "team_manage" => $this->input->post('team_manage', TRUE),
                    "sex" => $this->input->post('sex', TRUE),
                    "birthday" => $newDate,
                    "status_work" =>  "working",
                    "workplace" =>  0,
                    "role" =>  $role,
                    "admin" =>  0,
                    "job_position" =>  2,
                    "custom_fields" => array(
                        "staff" => [
                            6 => $this->input->post('staff_tipo_de_chave', TRUE),
                            7 => $this->input->post('staff_pix', TRUE),
                            8 =>  $this->input->post('staff_banco', TRUE),
                            24 => $this->input->post('staff_cpf_cnpj', TRUE),
                            25 => $this->input->post('staff_cnpj', TRUE),
                            26 => $this->input->post('staff_razao_social', TRUE),
                            27 => $this->input->post('staff_nome_fantasia', TRUE),
                            28 => $this->input->post('staff_rg', TRUE),
                            29 => $this->input->post('staff_cep', TRUE),
                            30 => $this->input->post('staff_endereco_empresa', TRUE),
                            31 => $this->input->post('staff_numero_empresa', TRUE),
                            32 => $this->input->post('staff_bairro_empresa', TRUE),
                            33 => $this->input->post('staff_cidade', TRUE),
                            34 => $this->input->post('staff_uf', TRUE),
                            35 => $this->input->post('staff_nome_do_titular', TRUE),
                            36 => $this->input->post('marital_status', TRUE),
                            37 => $this->input->post('staff_orgao_expedidor', TRUE)
                        ]
                    )

                ];

                $insert_data  = array_merge($insert_data, $custom_data);
            }


            if (!empty($this->input->post('custom_fields', TRUE))) {
                $insert_data['custom_fields'] = $this->Api_model->value($this->input->post('custom_fields', TRUE));
            }
            // insert data
            $this->load->model('staff_model');
            $output = $this->staff_model->add($insert_data);

            if ($output > 0 && !empty($output)) {
                // success

                $staffid = $output;
                // atualizar o team number
                $teamNumber = "EC" . sprintf('%05d', $output);
                $this->db->where('staffid', $output);
                $this->db->update(db_prefix() . 'staff', [
                    'staff_identifi' => $teamNumber,
                ]);

                //atualiza o departamento
                $this->db->insert(db_prefix() . 'staff_departments', [
                    'staffid'      => $staffid,
                    'departmentid' => 1,
                ]);

                // salva o avatar
                if ($foto_fachada) {
                    $this->uploadAvatarFromUrlFile($foto_fachada, $staffid);
                }


                $message = array(
                    'status' => TRUE,
                    'message' => 'Staff add successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Staff add fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }



    public function uploadAvatarFromUrlFile($imageUrl, $staffid)
    {

        // $headers = get_headers($imageUrl, 1);
        // if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'image/') !== 0) {
        //     return;
        // }

        $uploadDir = HR_PROFILE_IMAGE_UPLOAD_FOLDER . "{$staffid}/";

        // Certifique-se de que o diretório existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Nome do arquivo com prefixos
        $imageName = basename($imageUrl);
        $thumbPath = $uploadDir . 'thumb_' . $imageName;
        $smallPath = $uploadDir . 'small_' . $imageName;

        // Baixa e salva a imagem
        $imageContent = file_get_contents($imageUrl);
        file_put_contents($thumbPath, $imageContent);
        file_put_contents($smallPath, $imageContent);

        $this->db->where('staffid', $staffid);
        $this->db->update(db_prefix() . 'staff', [
            'profile_image' => $imageName,
        ]);
    }


    public function formatDateForDatabase($inputDate)
    {
        // Tenta criar a data a partir do formato 'd/m/Y' (dia/mês/ano completo)
        $date = DateTime::createFromFormat('d/m/Y', $inputDate);

        // Se falhar, tenta o formato 'd/m/y' (dia/mês/ano abreviado)
        if (!$date) {
            $date = DateTime::createFromFormat('d/m/y', $inputDate);
        }

        // Verifica se a data foi criada corretamente
        if ($date) {
            return $date->format('Y-m-d'); // Retorna no formato esperado pelo banco
        }

        // Se a string já estiver no formato 'Y-m-d', retorna diretamente
        if (DateTime::createFromFormat('Y-m-d', $inputDate)) {
            return $inputDate;
        }

        // Retorna false se nenhum formato foi reconhecido
        return false;
    }





    /**
     * @api {delete} api/delete/staffs/:id Delete a Staff
     * @apiName DeleteStaff
     * @apiGroup Staffs
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     *
     * @apiParam {Number} id Staff unique ID.
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {String} message Staff registration successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Staff Delete."
     *     }
     *
     * @apiError {String} status Request status.
     * @apiError {String} message Not register your accout.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Staff Not Delete."
     *     }
     */
    public function data_delete($id)
    {
        $id = $this->security->xss_clean($id);
        if (empty($id) && !is_numeric($id)) {
            $message = array(
                'status' => FALSE,
                'message' => 'Invalid Staff ID'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            // delete data
            $this->load->model('staff_model');
            $output = $this->staff_model->delete($id, 0);
            if ($output === TRUE) {
                // success
                $message = array(
                    'status' => TRUE,
                    'message' => 'Staff Delete Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Staff Delete Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    /**
     * @api {put} api/staffs/:id Update a Staff
     * @apiName PutStaff
     * @apiGroup Staffs
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     *
     * @apiParam {String} firstname             Mandatory Staff Name.
     * @apiParam {String} email                 Mandatory Staff Related.
     * @apiParam {String} password              Mandatory Staff password.
     * @apiParam {Number} [hourly_rate]         Optional hourly rate.
     * @apiParam {String} [phonenumber]         Optional Staff phonenumber.
     * @apiParam {String} [facebook]            Optional  Staff facebook.
     * @apiParam {String} [linkedin]            Optional  Staff linkedin.
     * @apiParam {String} [skype]               Optional Staff skype.
     * @apiParam {String} [default_language]    Optional Staff default language.
     * @apiParam {String} [email_signature]     Optional Staff email signature.
     * @apiParam {String} [direction]           Optional Staff direction.
     * @apiParam {Number[]} [departments]  Optional Staff departments.
     *
     *
     * @apiParamExample {json} Request-Example:
     *  {
     *     "firstname": "firstname",
     *     "email": "aa454@gmail.com",
     *     "hourly_rate": "0.00",
     *     "phonenumber": "",
     *     "facebook": "",
     *     "linkedin": "",
     *     "skype": "",
     *     "default_language": "",
     *     "email_signature": "",
     *     "direction": "",
     *     "departments": {
     *          "0": "1",
     *          "1": "2"
     *      },
     *     "password": "123456"
     *  }
     *
     * @apiSuccess {String} status Request status.
     * @apiSuccess {String} message Staff Update Successful.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Staff Update Successful."
     *     }
     *
     * @apiError {String} status Request status.
     * @apiError {String} message Staff Update Fail.
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Staff Update Fail."
     *     }
     */
    public function data_put($id)
    {
        $_POST = json_decode($this->security->xss_clean(file_get_contents("php://input")), true);
        if (empty($_POST) || !isset($_POST)) {
            $message = array(
                'status' => FALSE,
                'message' => 'Data Not Acceptable OR Not Provided'
            );
            $this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
        }
        $this->form_validation->set_data($_POST);

        if (empty($id) && !is_numeric($id)) {
            $message = array(
                'status' => FALSE,
                'message' => 'Invalid Staff ID'
            );
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {

            $update_data = $this->input->post();
            $update_data['lastname'] = '';
            // update data
            $this->load->model('staff_model');
            $output = $this->staff_model->update($update_data, $id);

            if ($output > 0 && !empty($output)) {
                // success
                $message = array(
                    'status' => TRUE,
                    'message' => 'Staff Update Successful.'
                );
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array(
                    'status' => FALSE,
                    'message' => 'Staff Update Fail.'
                );
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
}
