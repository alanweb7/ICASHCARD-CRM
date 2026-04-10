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
class Contracts extends REST_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

    /**
     * @api {get} api/contracts/:id Request Contract information
     * @apiVersion 0.3.0
     * @apiName GetContract
     * @apiGroup Contracts
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message No data were found.
     *
     * @apiParam {Number} id Contact unique ID
     *
     * @apiSuccess {Object} Contracts information.
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *	    {
     *	    "id": "1",
     *	    "content": "",
     *	    "description": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
     *	    "subject": "New Contract",
     *	    "client": "9",
     *	    "datestart": "2022-11-21",
     *	    "dateend": "2027-11-21",
     *	    "contract_type": "1",
     *	    "project_id": "0",
     *	    "addedfrom": "1",
     *	    "dateadded": "2022-11-21 12:45:58",
     *	    "isexpirynotified": "0",
     *	    "contract_value": "13456.00",
     *	    "trash": "0",
     *	    "not_visible_to_client": "0",
     *	    "hash": "31caaa36b9ea1f45a688c7e859d3ae70",
     *	    "signed": "0",
     *	    "signature": null,
     *	    "marked_as_signed": "0",
     *	    "acceptance_firstname": null,
     *	    "acceptance_lastname": null,
     *	    "acceptance_email": null,
     *	    "acceptance_date": null,
     *	    "acceptance_ip": null,
     *	    "short_link": null,
     *	    "name": "Development Contracts",
     *	    "userid": "9",
     *	    "company": "8web",
     *	    "vat": "",
     *	    "phonenumber": "",
     *	    "country": "0",
     *	    "city": "",
     *	    "zip": "",
     *	    "state": "",
     *	    "address": "",
     *	    "website": "",
     *	    "datecreated": "2022-08-11 14:07:26",
     *	    "active": "1",
     *	    "leadid": null,
     *	    "billing_street": "",
     *	    "billing_city": "",
     *	    "billing_state": "",
     *	    "billing_zip": "",
     *	    "billing_country": "0",
     *	    "shipping_street": "",
     *	    "shipping_city": "",
     *	    "shipping_state": "",
     *	    "shipping_zip": "",
     *	    "shipping_country": "0",
     *	    "longitude": null,
     *	    "latitude": null,
     *	    "default_language": "",
     *	    "default_currency": "0",
     *	    "show_primary_contact": "0",
     *	    "stripe_id": null,
     *	    "registration_confirmed": "1",
     *	    "type_name": "Development Contracts",
     *	    "attachments": [],
     *	    "customfields": [],
     *	    }
     */
    public function data_get($id = '')
    {
        // If the id parameter doesn't exist return all the
        $data = $this->Api_model->get_table('contracts', $id);
        // Check if the data store contains
        if ($data) {
            $data = $this->Api_model->get_api_custom_data($data, "contract", $id);
            // Set the response and exit
            $this->response($data, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

        } else {
            // Set the response and exit
            $this->response(['status' => FALSE, 'message' => 'No data were found'], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code

        }
    }

    /**
     * @api {delete} api/contracts/:id Delete Contract
     * @apiVersion 0.3.0
     * @apiName DeleteContract
     * @apiGroup Contracts
     *
     * @apiHeader {String} Authorization Basic Access Authentication token.
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Contract Deleted Successfully
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Contract Deleted Successfully"
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Contract Delete Fail
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Contract Delete Fail"
     *     }
     */
    public function data_delete($id = '')
    {
        $id = $this->security->xss_clean($id);
        if (empty($id) && !is_numeric($id)) {
            $message = array('status' => FALSE, 'message' => 'Invalid Contract ID');
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->load->model('contracts_model');
            $is_exist = $this->contracts_model->get($id);
            if (is_object($is_exist)) {
                $output = $this->contracts_model->delete($id);
                if ($output === TRUE) {
                    // success
                    $message = array('status' => TRUE, 'message' => 'Contract Deleted Successfully');
                    $this->response($message, REST_Controller::HTTP_OK);
                } else {
                    // error
                    $message = array('status' => FALSE, 'message' => 'Contract Delete Fail');
                    $this->response($message, REST_Controller::HTTP_NOT_FOUND);
                }
            } else {
                $message = array('status' => FALSE, 'message' => 'Invalid Contract ID');
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @api {post} api/contracts Add New Contract
     * @apiVersion 0.3.0
     * @apiName PostContract
     * @apiGroup Contracts
     *
     *  @apiHeader {String} Authorization Basic Access Authentication token.
     *
     *  @apiParam {String} subject                             Mandatory. Contract subject
     *	@apiParam {Date} datestart                             Mandatory. Contract start date
     *	@apiParam {Number} client                              Mandatory. Customer ID
     *	@apiParam {Date} dateend                               Optional.  Contract end date
     *	@apiParam {Number} contract_type                       Optional.  Contract type
     *  @apiParam {Number} contract_value             	 	   Optional.  Contract value
     *  @apiParam {String} description               	       Optional.  Contract description
     *  @apiParam {String} content              	 	       Optional.  Contract content
     *
     *  @apiParamExample {Multipart Form} Request-Example:
     *   [
     *		"subject"=>"Subject of the Contract,
     *		"datestart"=>"2022-11-11",
     *		"client"=>1,
     *		"dateend"=>"2023-11-11",
     *		"contract_type"=>1,
     *		"contract_value"=>12345,
     *		"description"=>"Lorem Ipsum is simply dummy text of the printing and typesetting industry",
     *		"content"=>"It has been the industry's standard dummy text ever since the 1500s"
     *	]
     *
     *
     * @apiSuccess {Boolean} status Request status.
     * @apiSuccess {String} message Contracts Added Successfully
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "status": true,
     *       "message": "Contract Added Successfully"
     *     }
     *
     * @apiError {Boolean} status Request status.
     * @apiError {String} message Contract add fail
     * @apiError {String} message The Start date field is required
     * @apiError {String} message The Subject field is required
     * @apiError {String} message The Customer ID field is required
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *       "message": "Contract ID Exists"
     *     }
     *
     * @apiErrorExample Error-Response:
     *   HTTP/1.1 404 Not Found
     *    {
     *	    "status": false,
     *	    "error": {
     *	        "newitems[]": "The Start date field is required"
     *	    },
     *	    "message": "<p>The Start date field is required</p>\n"
     *	}
     *
     * @apiErrorExample Error-Response:
     *   HTTP/1.1 404 Not Found
     *    {
     *	    "status": false,
     *	    "error": {
     *	        "subtotal": "The Subject field is required"
     *	    },
     *	    "message": "<p>The Subject field is required</p>\n"
     *	}
     *
     *  @apiErrorExample Error-Response:
     *   HTTP/1.1 404 Not Found
     *    {
     *	    "status": false,
     *	    "error": {
     *	        "total": "The Customer ID is required"
     *	    },
     *	    "message": "<p>The Customer ID is required</p>\n"
     *	}
     *
     */
    public function data_post()
    {
        \modules\api\core\Apiinit::the_da_vinci_code('api');
        $data = $this->input->post();
        $this->form_validation->set_rules('id', 'Contract ID', 'trim|numeric|greater_than[0]');
        $this->form_validation->set_rules('content', 'Content', 'trim');
        $this->form_validation->set_rules('description', 'Description', 'trim');
        $this->form_validation->set_rules('subject', 'Subject', 'trim|required');
        $this->form_validation->set_rules('client', 'Customer ID', 'trim|required|numeric|greater_than[0]');
        $this->form_validation->set_rules('contract_value', 'Contract Value', 'numeric');
        $this->form_validation->set_rules('datestart', 'Start date', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('dateend', 'End date', 'trim|max_length[255]');
        $this->form_validation->set_rules('contract_type', 'Contract type', 'trim|numeric|greater_than[0]');
        if ($this->form_validation->run() == FALSE) {
            $message = array('status' => FALSE, 'error' => $this->form_validation->error_array(), 'message' => validation_errors());
            $this->response($message, REST_Controller::HTTP_NOT_FOUND);
        } else {
            // $data['content'] = $this->onGetTemplateContract();
    
            if (isset($data['template_id']) && !empty($data['template_id'])) {
                $template_id = $data['template_id'];
                unset($data['template_id']);
                $data['content'] = $this->get_template_content($template_id);
            } 

            $this->load->model('contracts_model');
            $id = $this->contracts_model->add($data);
            if ($id > 0 && !empty($id)) {
                // success
                $message = array('status' => TRUE, 'message' => 'Contract Added Successfully');
                $this->response($message, REST_Controller::HTTP_OK);
            } else {
                // error
                $message = array('status' => FALSE, 'message' => 'Contract Add Fail');
                $this->response($message, REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function validate_contract_number($number, $contractid)
    {
        $isedit = 'false';
        if (!empty($contractid)) {
            $isedit = 'true';
        }
        $this->form_validation->set_message('validate_contract_number', 'The {field} is already in use');
        $original_number = null;
        $date = $this->input->post('date');
        if (!empty($contractid)) {
            $data = $this->Api_model->get_table('contracts', $contractid);
            $original_number = $data->number;
            if (empty($date)) {
                $date = $data->date;
            }
        }
        $number = trim($number);
        $number = ltrim($number, '0');
        if ($isedit == 'true') {
            if ($number == $original_number) {
                return TRUE;
            }
        }
        if (total_rows(db_prefix() . 'contracts', ['YEAR(date)' => date('Y', strtotime(to_sql_date($date))), 'number' => $number,]) > 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function onGetTemplateContract()
    {

        ob_start();
?>

        <table border="0" style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 5%;"></td>
                    <td style="width: 90%; text-align: center;">
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: center; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CONTRATO DE PRESTAÇÃO DE SERVIÇO</span></b><b></b></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: center; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;"></span></b></p>
                        <b></b><b></b><b></b>
                        <p class="MsoNormal" style="margin-bottom: 0cm; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CONTRATANTE: </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">MARIA ZUILDA NOLETO BRITO, CPF nº 41614275149, RG n° RG 1691004, residente e domiciliado(a) na AVENIDA DAS NACOES UNIDAS, Bairro NOVA CAPITAL, CIDADE: TORIXORÉU, TO.</span></p>
                        <p></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CONTRATADA: </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">Primme Companhia de Serviços, Representação Comercial e Construções LTDA, pessoa jurídica de direito privado inscrita no CNPJ sob o nº 41.497.036/0001-00 com sede na Rua Boa Ventura, nº S/N, Bairro Centro, na cidade de Pindobaçu / BA , CEP 44.770-000.</span></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">As partes acima identificadas acordam com o presente contrato de prestação de serviço, que se<b> </b>regerá pelas cláusulas seguintes:<b></b></span></p>
                        <b></b>
                        <p><b></b></p>
                        <b></b>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 1ª</span></b><b></b></p>
                        <b></b>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">O objeto do presente contrato constitui-se a prestação de serviço pela<b> </b>CONTRATADA e CONTRATANTE, de consultoria em análise de consistência cadastral, de idoneidade<b> </b>financeira,, para definir limites de risco e subsidiar<b> </b>proposta de contratação e disponibilização de crédito não fiscais e conversão de créditos<b> </b>financeiros, destinados à instituição financeira e para intermediação de negócios de natureza<b> </b>urgente para atendimento de interesse personalíssimo no âmbito de direitos disponíveis, nos limites<b> </b>da proposta.<b></b></span><b></b></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><span style="font-size: 12pt; font-family: 'Times New Roman', serif;"></span></p>
                        <b></b><b></b>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 2ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">O valor dos serviços de disponibilização do crédito sob análise é de <b><span style="color: #000000; background: #FFFFFF;">R$ 10.000,00.</span><span style="background: #FFFFFF;"></span></b></span><b></b></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><span style="font-size: 12pt; font-family: 'Times New Roman', serif;"><b><span style="color: #000000; background: #FFFFFF;"></span></b></span></p>
                        <b></b><b></b>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 3ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">As taxas e encargos da prestação de serviço é a diferença entre o valor total e o valor dos serviços descritos nas clausulas 2º e 4º deste contrato.</span></p>
                        <p></p>
                        <b></b>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 4ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">Nos termos, condições e encargos expressos nas cláusulas 1ª, 2ª e 3ª deste contrato, o CONTRATANTE reconhece e autoriza a CONTRATADA a proceder a cobrança do valor total de <b><span style="color: #000000; background: #FFFFFF;">R$ 1.250,00</span></b> por meio do cartão de crédito com dígitos finais <b>48546401******6964</b>, de bandeira <b>VISA</b>, sob titularidade de <b>MARIA ZUILDA NOLETO BRITO</b>, CPF: <span> </span><b>41614275149</b>, em total de <b>10 </b>parcelas de valor individual de <b>R$ 125,00</b>.<b></b></span></p>
                        <b></b>
                        <p><b></b></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 5ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">O CONTRATANTE declara ter preenchido fidedignamente os formulários apresentados, permite ainda ser fotografado no ato do contrato e a fornecer documentação necessária à contratada diretamente ou por meio do cadastro, exibindo os originais no momento do contrato e sempre que exigido a qualquer tempo. </span></p>
                        <p></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 6ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">O CONTRATANTE concorda que a CONTRATADA poderá contatá-lo por meio de cartas, e-mails, mensagens de texto (SMS) e chamadas telefônicas, inclusive para a oferta de produtos e serviços.</span></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><span style="font-size: 12pt; font-family: 'Times New Roman', serif;"></span></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 7ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">O CONTRATANTE declara estar ciente que mesmo após o término da Contratação, os dados pessoais e outras informações a ele relacionadas poderão ser conservados pelo CONTRATADA para cumprimento de obrigações legais e regulatórias.</span></p>
                        <p></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 8ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">Nos termos da Lei Geral de Proteção de Dados (Lei nº 13.709/18), o <span>CONTRATANTE reconhece que a CONTRATADA realiza o tratamento de dados pessoais com finalidades específicas e de acordo com as bases legais previstas na respectiva lei.</span></span></p>
                        <p></p>
                        <p class="MsoNormal" style="text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 9ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">A CONTRATADA, compromete-se a manter sigilo absoluto sobre todas as informações e dados obtidos no exercício de suas funções, respeitando integralmente as disposições da Lei Geral de Proteção de Dados (LGPD) – Lei nº 13.709/2018.</span></p>
                        <p></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 10ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">O CONTRATANTE, ao assinar o presente contrato, declara ter lido, ter sido bem informado e tomado pleno conhecimento das normas, cláusulas, termos e condições constantes neste instrumento de contrato, dos procedimentos adotados, das leis regentes e dos documentos exigidos para fins de celebração desse instrumento, bem como suficiente conhecimento das condições de execução do objeto, assim como declara a veracidade das declarações e informações prestadas e a autenticidade dos documentos prestados.</span></p>
                        <p></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 11ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">O CONTRATANTE se obriga a cumprir, sob pena de responsabilidade, toda a legislação, de qualquer natureza, que envolva a relação jurídica objeto deste contrato.</span></p>
                        <p></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 12ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">A contratada disponibiliza como canal de atendimento para eventuais dúvidas do contratante, o e-mail </span><a href="mailto:atendimento@icashcard.com.br"><span style="font-size: 12pt; font-family: 'Times New Roman', serif; text-decoration: none;">atendimento@icashcard.com.br</span></a><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">.</span></p>
                        <p></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 13ª. O </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CONTRATANTE<b> declara </b>que a assinatura deste <b>CONTRATO </b>ocorreu por meio eletrônico/digital, houve a coleta dos seus dados biométricos e/ou aceite eletrônico, nos termos do que autoriza o parágrafo §2°, do artigo 10, da Medida Provisória 2.200/01, a qual reconhece e admite como válida e aceita todas as cláusulas e condições estabelecidas.</span></p>
                        <p></p>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">CLÁUSULA 10ª. </span></b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">Para dirimir quaisquer controvérsias oriundas deste contrato, as partes elegem o foro da comarca de Pindobaçu / BA.</span></p>
                        <p pagebreak="true"></p>
                        <b><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">DECLARAÇÃO DE PRESTAÇÃO DE SERVIÇO</span></b><b></b>
                        <p><b></b></p>
                        <b></b><b></b>
                        <p class="MsoNormal" style="margin-bottom: 0cm; line-height: normal;"><span style="font-size: 12pt; font-family: 'Times New Roman', serif;">A quem possa interessar, eu <b>MARIA ZUILDA NOLETO BRITO</b>, CPF nº <b>41614275149</b>, RG n° RG <b>1691004</b>, titular do cartão de nº <b>48546401******696</b></span></p>
                        <p><b></b></p>
                        <b></b>
                        <p class="MsoNormal" style="margin-bottom: 0cm; text-align: justify; line-height: normal;"><span style="font-size: 12pt; font-family: 'Times New Roman', serif;"><b><span style="font-size: 12pt; line-height: 107%; font-family: 'Times New Roman', serif;">4 </span></b><span style="font-size: 12pt; line-height: 107%; font-family: 'Times New Roman', serif;">de bandeira <b>VISA </b>utilizado na transação contestada, afirmo e reconheço a compra efetuada na data de<b> 11 de Dezembro de 2024</b>, de valor integral <b><span style="color: #000000; background: #FFFFFF;">R$ 1.250,00</span></b> no prazo de <b>10 </b>parcelas e recebi corretamente as mercadorias/serviços adquiridos da empresa <b>Primme Companhia de Serviços, Representação Comercial e</b> <b>Construções LTDA, CNPJ nº 41.497.036/0001 00 </b>no dia, e autorizo a recobrança dos valores anteriormente contestados. Declaro, ainda, que as autorizações concedidas por mim e constantes nesta declaração perdurarão até a quitação integral desta prestação de serviço.</span></span></p>
                        <p></p>
                    </td>
                    <td style="width: 5%;"></td>
                </tr>
            </tbody>
        </table>

<?php
        $template = ob_get_clean(); // ob_get_clean() pega o conteúdo e limpa o buffer

        return $template;
    }

    public function get_template_content($template_id)
    {
        // Busca a coluna 'content' na tabela 'tbltemplates' com base no ID do template
        $this->db->select('content');
        $this->db->from('tbltemplates');
        $this->db->where('id', $template_id); // Substitua 'id' pelo nome correto da coluna para identificar o template

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->content; // Retorna o conteúdo do template
        }

        return null; // Retorna null se o template não for encontrado
    }
}
