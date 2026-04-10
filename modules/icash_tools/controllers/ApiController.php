<?php

defined('BASEPATH') or exit('No direct script access allowed');

class ApiController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Carregue os modelos ou bibliotecas necessárias
        // $this->load->model('icash_tables_model'); // exemplo
        $this->load->model('proposals_model');
    }

    // Função para processar dados
    public function processar_dados($id)
    {
        // Obtenha a proposta pelo ID
        $proposal = $this->get_proposal_by_id($id);
        $object = json_decode($proposal);

        if ($object) {

            // Construa o array de dados para a requisição cURL com base nos dados da proposta
            $invoiceData = array(
                'clientid' => $object->rel_id,
                'number' => $object->id, // Número automático ou ajuste conforme necessário
                'date' => date('Y-m-d'), // Data atual ou ajuste conforme necessário
                'currency' => '3', // Ajuste conforme necessário
                'subtotal' => $object->subtotal, // Ajuste conforme necessário
                'total' => $object->total, // Ajuste conforme necessário
                'billing_street' => "Mesmo do Cadastro",
                'newitems' =>  $object->items,
                'allowed_payment_modes' => array('1'), // Ajuste conforme necessário
                'sale_agent' => $object->assigned, // Ajuste conforme necessário
                'status' => 2 // Ajuste conforme necessário
            );

            // Enviar a requisição cURL usando o array criado
            $invoice = $this->send_invoice_curl($invoiceData);

            // Retorne a resposta ou faça o que for necessário com ela
            $this->output
                ->set_content_type('application/json')
                ->set_output($invoice)
                ->set_status_header(200);
        } else {
            // Se a proposta não for encontrada, envie uma resposta de erro
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Proposta não encontrada']))
                ->set_status_header(404);
        }
    }

    private function send_invoice_curl($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://painel.icashcard.com.br/api/invoices',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array(
                'authtoken: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiYXBwLWNvcmJhbiIsIm5hbWUiOiJhcHAtY29yYmFuIiwiQVBJX1RJTUUiOjE3MTczNzU1MzF9.Ct-p5fUOkBWuKpW364Y3sGzQ3lKmEZTHy_VEg_aIIL8',
                'Content-Type: application/x-www-form-urlencoded',
                'Cookie: csrf_cookie_name=5d8b51f0409f3559fdc4a5d5341b4f52; sp_session=qf30ubp0s8fvpe9qtnqiuvl1slua7fmb'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }




    public function get_proposal_by_id($id)
    {
        // Obtendo a proposta pelo ID


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://painel.icashcard.com.br/api/proposals/' . $id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'authtoken: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiYXBwLWNvcmJhbiIsIm5hbWUiOiJhcHAtY29yYmFuIiwiQVBJX1RJTUUiOjE3MTczNzU1MzF9.Ct-p5fUOkBWuKpW364Y3sGzQ3lKmEZTHy_VEg_aIIL8',
                'Cookie: csrf_cookie_name=5d8b51f0409f3559fdc4a5d5341b4f52; sp_session=lt8lk859mubied869hgomisuaq6mfvqq'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }


    public function marcar_fatura_como_paga($invoice_id, $valor_pago = null)
    {
        // Carregue o modelo de faturas
        $this->load->model('invoices_model');
        $this->load->model('payments_model');

        // Verifique se a fatura existe
        $invoice = $this->invoices_model->get($invoice_id);

        if (!$invoice) {
            return array('status' => 'error', 'message' => 'Fatura não encontrada.');
        }

        // Se o valor pago não for fornecido, considerar o valor total da fatura
        if ($valor_pago === null) {
            $valor_pago = $invoice->total;
        }

        // Dados do pagamento
        $payment_data = array(
            'amount' => $valor_pago,
            'invoiceid' => $invoice_id,
            'paymentmode' => 'bank_transfer', // Ajuste conforme necessário
            'date' => date('Y-m-d'),
            'daterecorded' => date('Y-m-d H:i:s'),
            'note' => 'Pagamento automático via API.',
        );

        // Registrar o pagamento
        $payment_id = $this->payments_model->add($payment_data);

        if ($payment_id) {
            // Atualizar o status da fatura
            $this->invoices_model->update_invoice_status($invoice_id);

            // return array('status' => 'success', 'message' => 'Fatura marcada como paga com sucesso.');
        } else {
            // return array('status' => 'error', 'message' => 'Erro ao registrar o pagamento.');
        }
    }
}
