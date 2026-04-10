<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    #form_etapa_2 .form-control {
        padding: 0.55rem 0.75rem;
        font-size: 14px;
    }

    #form_etapa_2 .mb-3 {
        margin-bottom: 1.2rem !important;
    }

    #form_etapa_2 .card {
        padding: 25px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #e0e0e0;
    }

    #form_etapa_2 label {
        font-weight: 500;
        font-size: 14px;
    }

    .is-invalid {
        border-color: #dc3545 !important;
        background-color: #fff5f5;
    }
</style>

<?php echo form_open('', ['id' => 'form_etapa_2', 'autocomplete' => 'on']); ?>

<!-- <div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <label for="cliente">Cliente</label>
        <select class="form-control" id="cliente" name="cliente" required>
            <option value="">Selecione</option>
        
        </select>
    </div>
    <div class="col-md-2">
        <label>&nbsp;</label>
        <button type="button" class="btn btn-warning btn-block">NOVO</button>
    </div>
</div> -->

<div class="card p-3">

    <!-- Dados Pessoais -->
    <h5 class="mb-3">Dados Pessoais</h5>
    <div class="row">
        <div class="col-md-8 mb-3">
            <label for="nome_completo">Nome Completo</label>
            <input type="text" class="form-control" name="nome_completo" placeholder="Nome completo" required oninput="this.value = this.value.toUpperCase()">
        </div>
        <div class="col-md-4 mb-3">
            <label for="cpf">CPF</label>
            <input type="text" class="form-control" name="cpf" id="cpf" value=""  placeholder="CPF" required>
        </div>
        <div class="col-md-4 mb-3">
            <label for="data_nascimento">Data de Nascimento</label>
            <input type="date" class="form-control" name="data_nascimento" id="data_nascimento" required>
        </div>

        <div class="col-md-3 mb-3">
            <label for="rg">RG</label>
            <input type="text" class="form-control" name="rg" id="rg" placeholder="RG" required>
        </div>

        <div class="col-md-3 mb-3">
            <label for="ssp_uf">SSP/UF</label>
            <input type="text" class="form-control" name="ssp_uf" id="ssp_uf" placeholder="SSP/UF" required oninput="this.value = this.value.toUpperCase()">
        </div>

    </div>

    <hr>

    <!-- Endereço -->
    <h5 class="mb-3">Endereço</h5>
    <div class="row">
        <div class="col-md-3 mb-3">
            <input type="text" class="form-control" name="cep" id="cep" placeholder="CEP" required>
        </div>
        <div class="col-md-6 mb-3">
            <input type="text" class="form-control" name="rua" id="rua" placeholder="Rua/Av." required oninput="this.value = this.value.toUpperCase()">
        </div>
        <div class="col-md-3 mb-3">
            <input type="text" class="form-control" name="numero" placeholder="Nº" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <input type="text" class="form-control" name="setor" placeholder="Setor" oninput="this.value = this.value.toUpperCase()">
        </div>
        <div class="col-md-6 mb-3">
            <input type="text" class="form-control" name="cidade" id="cidade" placeholder="Cidade" required oninput="this.value = this.value.toUpperCase()">
        </div>
        <div class="col-md-3 mb-3">
            <input type="text" class="form-control" name="uf" id="uf" placeholder="UF" required oninput="this.value = this.value.toUpperCase()">
        </div>
    </div>



    <hr>

    <!-- Contato -->
    <h5 class="mb-3">Contato</h5>
    <div class="row">
        <div class="col-md-6 mb-3">
            <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                value=""
                placeholder="E-mail"
                required>
            <div id="email-feedback" style="color: red; margin-top: 5px; font-size: 0.9em;"></div>
        </div>
        <div class="col-md-6 mb-3">
            <input type="text" class="form-control" name="telefone" id="telefone" value="" placeholder="Telefone / Whatsapp" required>
        </div>
    </div>

    <hr>

    <!-- Dados do PIX -->
    <h5 class="mb-3">💳 Dados do PIX</h5>

    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="fa-brands fa-pix fa-lg mr-2" style="font-size: 1.5rem; color: #00b894;"></i>
        <span>
            Informe os dados bancários e a <strong>chave PIX</strong> correspondente à instituição informada.
        </span>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <?php
            $banks = get_bancos_brasilapi();
            echo render_select(
                'banco',
                $banks,
                ['name', 'name'],
                'Banco',
                null, // <- isso é essencial!
                [],
                [],
                'selectpicker',
                '',
                false
            );

            ?>
        </div>
        <div class="col-md-4 mb-3">
            <label for="agencia">Agência</label>
            <input type="text" class="form-control" name="agencia" id="agencia" placeholder="Ex: 0001" required>
        </div>
        <div class="col-md-4 mb-3">
            <label for="conta">Conta</label>
            <input type="text" class="form-control" name="conta" id="conta" placeholder="Ex: 123456-7" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="tipo_chave">Tipo de chave PIX</label>
            <select class="form-control" name="tipo_chave" id="tipo_chave" required>
                <option value="">Selecione</option>
                <option value="cpf">CPF</option>
                <option value="email">E-mail</option>
                <option value="telefone">Telefone</option>
                <option value="aleatoria">Chave Aleatória</option>
            </select>
        </div>
        <div class="col-md-8 mb-3">
            <label for="chave_pix">Chave PIX</label>
            <input type="text" class="form-control" name="chave_pix" id="chave_pix" value="" placeholder="Digite sua chave PIX" required>
        </div>
    </div>

</div>


<div class="d-flex justify-content-end gap-2 mt-4">
    <button type="button" id="btn-prev" class="btn btn-secondary">Voltar</button>
    <button type="button" id="btn-next" class="btn btn-success next-2">Próximo</button>
</div>
<?php echo form_close(); ?>

<?php

function get_bancos_brasilapi()
{
    $url = 'https://brasilapi.com.br/api/banks/v1';

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    if (!$response) {
        return [];
    }

    $bancos = json_decode($response, true);

    if (!is_array($bancos)) {
        return [];
    }

    // Filtrar apenas bancos com código válido
    $bancos = array_filter($bancos, function ($banco) {
        return !empty($banco['code']) && !empty($banco['name']);
    });

    // Ordenar por nome
    usort($bancos, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    // Formatar opções para select
    $options = [];

    foreach ($bancos as $banco) {
        $options[] = [
            'id' => $banco['code'],
            'name' => $banco['code'] . ' - ' . $banco['name'],
        ];
    }

    return $options;
}

?>