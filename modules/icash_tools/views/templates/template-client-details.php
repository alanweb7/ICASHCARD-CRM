<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    b,
    strong {
        font-weight: 700;
    }
</style>

<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">
    <thead>
        <tr>
            <th style="text-align: left; border-bottom: 2px solid #ccc; padding: 8px;">Tipo</th>
            <th style="text-align: left; border-bottom: 2px solid #ccc; padding: 8px;">Informações</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 8px;">
                Dados Pessoais
            </td>
            <td style="padding: 8px;">
                <b>Nome:</b> ${proposal_to}<br>
                <b>CPF:</b> ${cpf}<br>
                <b>RG:</b> ${rg}<br>
                <b>Data de Nascimento:</b> ${data_nasc}<br>
                <b>Email:</b> ${email}<br>
                <b>Endereço:</b> ${endereco}<br>
                <b>Telefone:</b> ${telefone}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px;">Dados da Operação</td>
            <td style="padding: 8px;">
                <b>Tabela:</b> ${tabela}<br>
                <b>Prazo:</b> ${prazo}x<br>
                <b>Valor Bruto:</b> ${total_bruto}<br>
                <b>Valor Líquido:</b> ${total_liquido}<br>
                <b>Valor Parcela:</b> ${valor_parcela}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">Banco da Conta</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">
                <b>Banco:</b> ${banco}<br>
                <b>Agência:</b> ${agencia}<br>
                <b>Conta:</b> ${conta}<br>
                <b>Tipo:</b> ${tipo_chave_pix}<br>
                <b>Chave:</b> ${chave_pix}<br>
                <b>Valor Líquido:</b> ${total_liquido}<br>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">Links e Status</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">
                <b>Status:</b> ${payment_message}<br>
                <b>Descrição:</b> ${payment_description}<br>
                <b>Link de Pagamento:</b> ${link_pagamento}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">Observações</td>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">
                <b>Recusa:</b> ${proposal_refusal}<br>
                <b>Observação:</b> ${proposal_observation}<br>
            </td>
        </tr>
    </tbody>
</table>