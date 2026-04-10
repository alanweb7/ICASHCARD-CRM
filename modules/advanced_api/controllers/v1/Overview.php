<?php

defined('BASEPATH') || exit('No direct script access allowed');

require_once __DIR__.'/../RestController.php';

class Overview extends \AdvancedApi\RestController
{
    protected $clientInfo;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('advanced_api_model');
        $this->load->helper('advanced_api');

        if (!isset(isAuthorized()['status'])) {
            $this->response(isAuthorized()['response'], isAuthorized()['response_code']);
        }

        $this->clientInfo = isAuthorized();

        if (!has_contact_permission('projects', $this->clientInfo['data']->contact_id)) {
            $this->response(['message' => _l('not_permission_to_perform_this_action')], 403);
        }
    }
     
    public function overview_get()
    {
        // Perfex CRM Logo
        $perfex_logo = get_option('company_logo');
        $perfex_logo_dark = get_option('company_logo_dark');
        
        $tableContacts = db_prefix().'contacts';
        $this->db->where('id', $this->clientInfo['data']->contact_id);
        $contact = $this->db->get($tableContacts)->row();
        
        $tableClients = db_prefix().'clients';
        $this->db->where('userid', $this->clientInfo['data']->client_id);
        $client = $this->db->get($tableClients)->row();
        
        $where['clientid'] = $this->clientInfo['data']->client_id;
        $whereTicket['tickets.userid'] = $this->clientInfo['data']->client_id;
        $whereProposal['rel_id'] = $this->clientInfo['data']->client_id;
        
        $this->load->model('invoices_model');
        $this->load->model('projects_model');
        $this->load->model('estimates_model');
        $this->load->model('proposals_model');
        $this->load->model('tickets_model');
        
        $allInvoice = $this->invoices_model->get('', $where);
        
        $where['status'] = 1;
        $paidInvoice = $this->invoices_model->get('', $where);
        $notStartedProjects = $this->projects_model->get('', $where);
        $draftEstimates = $this->estimates_model->get('', $where);
        
        $whereTicket['status'] = 1;
        $openTickets = $this->tickets_model->get('', $whereTicket);
        
        $whereProposal['status'] = 1;
        $openProposals = $this->proposals_model->get('', $whereProposal);
        
        $where['status'] = 2;
        $unpaidInvoice = $this->invoices_model->get('', $where);
        $inProgressProjects = $this->projects_model->get('', $where);
        $sentEstimates = $this->estimates_model->get('', $where);
        
        $whereTicket['status'] = 2;
        $inProgressTickets = $this->tickets_model->get('', $whereTicket);
        
        $whereProposal['status'] = 2;
        $declinedProposals = $this->proposals_model->get('', $whereProposal);
        
        $where['status'] = 3;
        $partialyPaidInvoice = $this->invoices_model->get('', $where);
        $onHoldProjects = $this->projects_model->get('', $where);
        $declinedEstimates = $this->estimates_model->get('', $where);
        
        $whereTicket['status'] = 3;
        $answeredTickets = $this->tickets_model->get('', $whereTicket);
        
        $whereProposal['status'] = 4;
        $acceptedProposals = $this->proposals_model->get('', $whereProposal);
        
        $where['status'] = 4;
        $overdueInvoice = $this->invoices_model->get('', $where);
        $completedProjects = $this->projects_model->get('', $where);
        $acceptedEstimates = $this->estimates_model->get('', $where);
        
        $whereProposal['status'] = 4;
        $sentProposals = $this->proposals_model->get('', $whereProposal);
        
        $where['status'] = 5;
        $expiredEstimates = $this->estimates_model->get('', $where);
        
        $whereProposal['status'] = 5;
        $revisedProposals = $this->proposals_model->get('', $whereProposal);
        
        $whereTicket['status'] = 5;
        $closedTickets = $this->tickets_model->get('', $whereTicket);

        $client_data = [
            'client_id'         => $contact->userid, // Client's ID
            'client_name'       => $client->company, // Company Name
            'contact_id'        => $contact->id, // Contact ID
            'contact_firstname' => $contact->firstname, // Contact First Name
            'contact_lastname'  => $contact->lastname, // Contact Last Name
            'contact_email'     => $contact->email, // Contact Email
            'contact_phone'     => $contact->phonenumber, // Contact Phone Number
            'contact_title'     => $contact->title, // Contact Title
            'contact_active'    => $contact->active, // Contact Active Status
            'contact_image'     => contact_profile_image_url($contact->id, 'thumb'), // Contact Profile Image
            
            'perfex_logo'       => ($perfex_logo != '' ? base_url('uploads/company/' . $perfex_logo) : ''),
            'perfex_logo_dark'  => ($perfex_logo_dark != '' ? base_url('uploads/company/' . $perfex_logo_dark) : ''),
            
            'projects_notstarted'     => count($notStartedProjects), // Projects
            'projects_inprogress'     => count($inProgressProjects), // Projects
            'projects_onhold'         => count($onHoldProjects), // Projects
            'projects_finished'       => count($completedProjects), // Projects
            
            'invoices_total'          => count($allInvoice), // Invoices
            'invoices_unpaid'         => count($unpaidInvoice), // Invoices
            'invoices_paid'           => count($paidInvoice), // Invoices
            'invoices_overdue'        => count($overdueInvoice), // Invoices
            'invoices_partilypaid'    => count($partialyPaidInvoice), // Invoices
            
            'estimates_draft'         => count($draftEstimates), // Estimates
            'estimates_expired'       => count($expiredEstimates), // Estimates
            'estimates_sent'          => count($sentEstimates), // Estimates
            'estimates_declined'      => count($declinedEstimates), // Estimates
            'estimates_accepted'      => count($acceptedEstimates), // Estimates
            
            'proposals_open'          => count($openProposals), // Proposals
            'proposals_declined'      => count($declinedProposals), // Proposals
            'proposals_accepted'      => count($acceptedProposals), // Proposals
            'proposals_sent'          => count($sentProposals), // Proposals
            'proposals_revised'       => count($revisedProposals), // Proposals
            
            'tickets_open'            => count($openTickets), // Tickets
            'tickets_inprogress'      => count($inProgressTickets), // Tickets
            'tickets_answered'        => count($answeredTickets), // Tickets
            'tickets_closed'          => count($closedTickets), // Tickets
            
            'client_logged_in'  => true,
            'API_TIME'          => time(),
        ];

        $this->response(['data' => $client_data, 'message' =>  _l('data_retrived_success')], 200);
    }
}
