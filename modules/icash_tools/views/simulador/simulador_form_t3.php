<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    #resumo-simulacao .section p {
        margin-bottom: 6px;
        font-size: 14px;
    }

    #resumo-simulacao h6 {
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 10px;
    }

    #resumo-simulacao .card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
    }

    #resumo-simulacao h5 {
        font-weight: 600;
        font-size: 16px;
        border-bottom: 2px solid #ccc;
        padding-bottom: 10px;
    }

    #resumo-simulacao .btn {
        min-width: 120px;
    }
</style>

<div class="card p-4" id="resumo-simulacao">
    <h5 class="text-muted mb-4">RESUMO DA SOLICITAÇÃO</h5>

    <div class="section mb-4">
        <h6 class="text-primary border-bottom pb-2">Dados Pessoais</h6>
        <p><strong>Cliente:</strong> <span id="resumo_cliente"></span></p>
        <p><strong>CPF:</strong> <span id="resumo_cpf"></span></p>
        <p><strong>RG:</strong> <span id="resumo_rg"></span></p>
        <p><strong>Data Nascimento:</strong> <span id="resumo_nascimento"></span></p>
        <p><strong>Endereço:</strong> <span id="resumo_endereco"></span></p>
        <p><strong>E-mail:</strong> <span id="resumo_email"></span></p>
        <p><strong>Telefone/Whatsapp:</strong> <span id="resumo_telefone"></span></p>
    </div>

    <div class="section mb-4">
        <h6 class="text-primary border-bottom pb-2">Dados da Proposta</h6>
        <p><strong>Credenciadora:</strong> <span id="resumo_credenciadora"></span></p>
        <p><strong>Tabela:</strong> <span id="resumo_tabela"></span></p>
        <p><strong>Prazo:</strong> <span id="resumo_prazo"></span></p>
        <p><strong>Valor Bruto:</strong> <span id="resumo_valor_bruto"></span></p>
        <p><strong>Valor Líquido:</strong> <span id="resumo_valor_liquido"></span></p>
        <p><strong>Valor Parcela:</strong> <span id="resumo_valor_parcela"></span></p>
    </div>

    <div class="section mb-4">
        <h6 class="text-primary border-bottom pb-2">Dados Para Pagamento</h6>
        <p><strong>Banco:</strong> <span id="resumo_banco"></span></p>
        <p><strong>Agência:</strong> <span id="resumo_agencia"></span></p>
        <p><strong>Conta:</strong> <span id="resumo_conta"></span></p>
        <p><strong>Tipo de Chave PIX:</strong> <span id="resumo_tipo_pix"></span></p>
        <p><strong>Chave PIX:</strong> <span id="resumo_chave_pix"></span></p>
    </div>

    <?php echo form_open('', ['id' => 'form_etapa_3', 'autocomplete' => 'off']); ?>


    <input type="hidden" value="send_data">
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" id="btn-prev" class="btn btn-secondary">Voltar</button>
        <button type="submit" class="btn btn-success" id="btn-final-submit">Finalizar Envio</button>
    </div>

    <?php echo form_close(); ?>


</div>