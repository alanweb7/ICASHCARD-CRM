<?php defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();
$resumeJson = json_encode($receipt);

// Get Y position for the separation
$y = $pdf->getY();

$company_info = '<div>';
$company_info .= format_organization_info();
$company_info .= '</div>';

// Bill to
$client_details = get_staff_full_name($receipt->addedfrom);

$left_info  = $swap == '1' ? $client_details : $company_info;
// $right_info = $swap == '1' ? $company_info : $client_details;
$right_info = '';

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->SetFontSize(15);
$receit_heading = '<div style="text-align:center"><center>' . mb_strtoupper(_l('commission_payment_receipt'), 'UTF-8') . '</center></div>';

$pdf->Ln(2);
$pdf->writeHTMLCell(0, '', '', '', $receit_heading, 0, 1, false, true, 'L', true);

$pdf->SetFontSize(10);
$currentAmount = app_format_money($receipt->amount, $receipt->currency_name);
$txtDeclaracao = "<div style=\"text-align:center;font-size10px;\"><center>RECEBI A QUANTIA DE <strong>{$currentAmount} REFERENTE AO PAGAMENTO DE PRESTAÇÃO DE SERVIÇOS DE VENDAS. </strong>";
$txtDeclaracao .= "DECLARO, AINDA, QUE NÃO TRABALHO COM EXCLUSIVIDADE PESSOAL PARA ESTA EMPRESA E QUE NÃO TENHO VÍNCULO EMPREGATÍCIO COM A MESMA, ";
$txtDeclaracao .= "ESTANDO LIVRE, NA CONDIÇÃO DE VENDEDOR AUTÔNOMO, AVULSO OU FREE LANCE, PARA CONTINUAR VENDENDO, AO MESMO TEMPO, ";
$txtDeclaracao .= "PRODUTOS DE OUTRAS EMPRESAS, ATÉ MESMO CONCORRENTES, PELO QUE DOU PLENA, TOTAL, GERAL E IRREVOGÁVEL QUITAÇÃO</center></div>";

if (isset($_GET['debug']) && $_GET['debug'] == 77) {
    $txtDeclaracao .= json_encode($receipt);
}


$pdf->Ln(10);
$pdf->writeHTMLCell(0, '', '', '', $txtDeclaracao, 0, 1, false, true, 'L', true);



/** -------- DETALHES DO PAGAMENTO -------- */
$pdf->Ln(10);
$sale_name = $receipt->list_commission[0]['sale_name'];
$pdf->SetFontSize($font_size);

$receipt_name = $receipt->paymentmode_name;
$receipt_mode = $receipt->expense_details->paymentmode;

$receipt_method = $receipt->paymentmethod;

$receipt_list = [
    1 => 'Transf. Bancária',
    2 => 'Pix',
];


if (!empty($receipt_mode)) {
    $receipt_details = "<div style='text-align:left;margin-left:3px;'>";
    $receipt_details .= _l('payment_view_mode') .'  ' . $receipt_list[$receipt_mode];
    $receipt_details .= '<br>Titular: ' . $receipt->list_commission[0]['staff_custom_fields']['Nome do Titular'];
    $receipt_details .= '<br>Tipo de Chave: ' . $receipt->list_commission[0]['staff_custom_fields']['Tipo de Chave'];
    $receipt_details .= '<br>Pix: ' . $receipt->list_commission[0]['staff_custom_fields']['Pix'];
    $receipt_details .= "</div>";
}

$pdf->SetFontSize(9);

$tblhtml = '
<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="1">
    <tr style="text-align:center;">
        <td>' . _l(' Corretor: ') . ' ' . $sale_name . '</td>
        <td>' . _l(' payment_date') . ' ' . _d($receipt->date) . '</td>
        <td style="text-align:left; padding:3px;">' .  $receipt_details . '</td>
    </tr>
</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');


if (!empty($receipt->transactionid)) {
    $pdf->Ln(2);
    $pdf->writeHTMLCell(80, '', '', '', '<hr/>', 0, 1, false, true, 'L', true);
    $pdf->Cell(0, 0, _l('payment_transaction_id') . ': ' . $receipt->transactionid, 0, 1, 'L', 0, '', 0);
}


// $pdf->Ln(1);
// $pdf->SetFillColor(132, 197, 41);
// $pdf->SetTextColor(255);
// $pdf->SetFontSize(12);
// $pdf->Cell(80, 10, _l('Bonificação Total:') . ' ' . app_format_money($receipt->amount, $receipt->currency_name), 0, 1, 'C', '1');


$pdf->SetTextColor(0);
$pdf->SetFont($font_name, '', 12);
$pdf->Ln(1);

// Header

$dataRecpt = json_encode($receipt->list_commission);
$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
<tr height="30" style="color:#fff;font-size:12px;text-align:center" bgcolor="#3A4656">
    <th width="10%;">' . _l('Nº Proposta') . '</th>
    <th width="10%;">' . _l('DATA DIGITAÇÃO') . '</th>
    <th width="10%;">' . _l('DATA AVERBAÇÃO') . '</th>
    <th width="10%;">' . _l('CPF') . '</th>
    <th width="23%;">' . _l('client') . '</th>
    <th width="10%;">' . _l('VALOR LIQ.') . '</th>
    <th width="12%;">' . _l('PLANO') . '</th>
    <th width="10%;">' . _l('BONIFICAÇÃO') . '</th>
    <th width="5%;">' . _l('%') . '</th>';


$tblhtml .= '</tr>';

$tblhtml .= '<tbody>';

$TotalAmount = 00.00;
foreach ($receipt->list_commission as $key => $value) {

    $TotalAmount += $value['total'];

    $client_custom_fields = $value['client_custom_fields'];
    $percent_paid = ($value['amount'] / $value['total']) * 100;
    $percent_paid = number_format($percent_paid, 2);
    if (isset($value['invoice_items']) && count($value['invoice_items'])) {
        $plano = $value['invoice_items'][0];
        $descShort = "{$plano['description']}";

        $descLong = "";
        if ($plano['long_description'] !== "")
            $descLong = " - {$plano['long_description']}";
    }


    $jsonPlano = json_encode($plano);
    $tblhtml .= '<tr style="color:#000;font-size:12px;">';
    $tblhtml .= '<td>' . $value['proposal_id'] . '</td>';
    // $tblhtml .= '<td>' . format_invoice_number($value['invoice_id']) . '</td>';
    $tblhtml .= '<td>' . _d($value['date']) . '</td>';
    $tblhtml .= '<td>' . _d($value['date']) . '</td>';
    $tblhtml .= '<td>' . $client_custom_fields['CPF'] . '</td>';
    $tblhtml .= '<td>' . $value['company'] . '</td>';
    $tblhtml .= '<td>' . app_format_money($value['total'], $receipt->currency_name) . '</td>';
    $tblhtml .= '<td>' . strtoupper($descShort . $descLong) . '</td>';
    $tblhtml .= '<td>' . app_format_money($value['amount'], $receipt->currency_name) . '</td>';
    $tblhtml .= '<td>' . $percent_paid . '%</td>';
    $tblhtml .= '</tr>';
}

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

/** ---- RESUMO ----- */
$totalRegisters = count($receipt->list_commission);

$pdf->writeHTMLCell(0, 0, '', '', '<hr />', 0, 1, false, true, 'L', true); //divider

$pdf->SetTextColor(0);
$pdf->SetFont($font_name, '', $font_size);
$pdf->SetFontSize(12);

$tblhtml = '
<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
    <tr style="text-align:center;">
        <td>' . _l('Total de Registros: ') . $totalRegisters . '</td>
        <td>' . _l('Total Valor Líquido: ') . app_format_money($TotalAmount, $receipt->currency_name) . '</td>
        <td>' . _l('Total Valor Bonificação: ') . app_format_money($receipt->amount, $receipt->currency_name) . $receipt_name . '</td>
    </tr>
</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');

// $pdf->Ln(1);
// $pdf->Cell(0, 0, _l('Total de registros') . ': ' . $totalRegisters, 0, 1, 'L', 0, '', 0);

// $pdf->Ln(1);
// $pdf->Cell(0, 0, _l('Total Valor Líquido') . ': ' . app_format_money($TotalAmount, $receipt->currency_name), 0, 1, 'L', 0, '', 0);

// $pdf->Ln(1);
// $pdf->Cell(0, 0, _l('Total Valor Bonificação') . ': ' . app_format_money($receipt->amount, $receipt->currency_name), 0, 1, 'L', 0, '', 0);
