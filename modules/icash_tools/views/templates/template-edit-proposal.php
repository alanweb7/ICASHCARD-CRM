<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- alimentado pelo arquivo js do assets do template -->
<!-- informacoes globais (nao personalizadas) alimentado pelo metodo proposal_edit do controller Templates_tools-->
<div class="panel-body">
    <?php echo form_open('admin/icash_tools/gerenciar_propostas/onUpdateProposal', ['id' => 'update_proposal_form']); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="proposal_to"><?php echo _l('Nome completo'); ?></label>
                <input type="text" id="proposal_to" name="proposal_to" class="form-control" value="<?= $proposal->proposal_to; ?>" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="rg"><?php echo _l('Data de Nascimento'); ?></label>
                <input type="text" name="proposal_fields[100]" id="data_nasc" class="form-control" value="<?= _d($proposal->custom_fields['Data de Nascimento'] ?? $proposal->custom_fields['data_nasc']); ?>" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="rg"><?php echo _l('RG'); ?></label>
                <input type="text" name="proposal_fields[99]" id="rg" class="form-control" value="<?= $proposal->customer['RG'] ?? $proposal->custom_fields['RG']; ?>" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="cpf"><?php echo _l('CPF'); ?></label>
                <input type="text" name="proposal_fields[23]" id="cpf" class="form-control" value="<?= $proposal->customer['vat'] ?? ($proposal->custom_fields['CPF'] ?? ''); ?>" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="email"><?php echo _l('Email'); ?></label>
                <input type="email" id="email" name="proposal_fields[90]" class="form-control" value="<?= $proposal->custom_fields['Email']; ?>" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="Telefone"><?php echo _l('Telefone'); ?></label>
                <input type="text" id="Telefone" name="proposal_fields[91]" class="form-control" value="<?= $proposal->custom_fields['Telefone']; ?>" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="enderecoCliente"><?php echo _l('Endereço'); ?></label>
                <input type="text" id="enderecoCliente" name="proposal_fields[97]" class="form-control" value="<?= $proposal->custom_fields['Endereço (Cliente)']; ?>" required>
            </div>
        </div>
    </div>
    <hr class="my-4">
    <h4 class="mb-3">Dados da Operação</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="proposalTabela">Tabela</label>
                <select id="proposalTabela" name="proposal_fields[67]" class="form-control" required>
                    <option value="" disabled <?= $proposal->custom_fields['Tabela'] == '' ? 'selected' : '' ?>>Selecione</option>
                    <?php
                    foreach ($options as $opt) {
                        $selected = $opt['slug'] == $proposal->custom_fields['Tabela']  ? 'selected' : '';
                        echo '<option value="' . $opt['slug'] . '" ' . $selected . '>' . $opt['title'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="prazo">Prazo</label>
                <input type="number"
                    name="proposal_fields[13]"
                    id="prazo"
                    class="form-control"
                    value="<?= $proposal->custom_fields['Parcelas']; ?>"
                    min="2"
                    max="12"
                    required>
            </div>
            <div class="form-group">
                <label for="valorBruto">Valor Bruto</label>
                <input type="text" name="proposal_fields[15]" id="valorBruto" class="form-control money" placeholder="R$ 0,00" value="<?= $proposal->custom_fields['Total Bruto'] ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="valorLiquido">Valor Líquido</label>
                <input type="text" name="proposal_fields[16]" id="valorLiquido" class="form-control money" placeholder="R$ 0,00" value="<?= $proposal->custom_fields['Total Líq.']; ?>">
            </div>
            <div class="form-group">
                <label for="valorParcela">Valor Parcela</label>
                <input type="text" name="proposal_fields[14]" id="valorParcela" class="form-control money" placeholder="R$ 0,00" value="<?= $proposal->custom_fields['Valor Parcela']; ?>">
            </div>
        </div>
    </div>
    <hr class="my-4">
    <h4 class="mb-3">Dados Banco</h4>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="banco">Banco da Conta</label>
                <input type="text" id="banco" name="proposal_fields[93]" class="form-control" value="<?= $proposal->custom_fields['Banco']; ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="agencia">Agência</label>
                <input type="text" id="agencia" name="proposal_fields[94]" class="form-control" value="<?= $proposal->custom_fields['Agência']; ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="conta">Conta</label>
                <input type="text" id="conta" name="proposal_fields[95]" class="form-control" value="<?= $proposal->custom_fields['Conta']; ?>">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="tipoChave">Tipo de Chave <?= $proposal->custom_fields['Tipo de Chave'] ?></label>
                <select id="tipoChave" name="proposal_fields[98]" class="form-control" required>
                    <option value="" disabled <?= $proposal->custom_fields['Tipo de Chave'] == '' ? 'selected' : '' ?>>Selecione</option>
                    <option value="CPF" <?= $proposal->custom_fields['Tipo de Chave'] == 'CPF' ? 'selected' : '' ?>>CPF</option>
                    <option value="Email" <?= $proposal->custom_fields['Tipo de Chave'] == 'Email' ? 'selected' : '' ?>>Email</option>
                    <option value="Celular" <?= $proposal->custom_fields['Tipo de Chave'] == 'Celular' ? 'selected' : '' ?>>Celular</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="chave">Chave Pix</label>
                <input type="text" name="proposal_fields[96]" id="chave" class="form-control" value="<?= $proposal->custom_fields['Chave PIX'] ?>">
            </div>
        </div>
    </div>

    <input type="hidden" name="proposal_id" id="proposal_id" value="<?= $proposal->id ?>">
    <input type="hidden" name="rel_id" id="proposal_id" value="<?= $proposal->rel_id ?>">

    <button type="submit" class="btn btn-primary" id="on-submit-data"><?php echo 'Salvar dados'; ?></button>
    <?php echo form_close(); ?>
</div>
<script>
    $(document).ready(function() {
        $('#data_nasc').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, ''); // Remove caracteres não numéricos
            let formattedValue = '';

            if (value.length > 0) {
                formattedValue = value.substring(0, 2); // Dia
            }
            if (value.length > 2) {
                formattedValue += '-' + value.substring(2, 4); // Mês
            }
            if (value.length > 4) {
                formattedValue += '-' + value.substring(4, 8); // Ano
            }

            $(this).val(formattedValue);
        });

        $('#Telefone').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, ''); // Remove caracteres não numéricos
            let formattedValue = '';

            if (value.length > 0) {
                formattedValue = '(' + value.substring(0, 2); // Código de área
            }
            if (value.length > 2) {
                formattedValue += ') ' + value.substring(2, 3); // Primeiro dígito do número
            }
            if (value.length > 3) {
                formattedValue += ' ' + value.substring(3, 7); // Primeiros quatro dígitos
            }
            if (value.length > 7) {
                formattedValue += '-' + value.substring(7, 11); // Últimos quatro dígitos
            }

            $(this).val(formattedValue);
        });


        function formatReal(value) {
            return value
                .replace(/\D/g, '') // Remove caracteres não numéricos
                .replace(/(\d)(\d{2})$/, '$1,$2') // Adiciona a vírgula para os centavos
                .replace(/(?=(\d{3})+(\D))\B/g, '.'); // Adiciona os pontos para os milhares
        }

        // Aplica a máscara em todos os campos com a classe "money"
        document.querySelectorAll('.money').forEach(function(input) {
            input.addEventListener('input', function() {
                this.value = formatReal(this.value);
            });

            // Garante que ao clicar fora do campo, o valor seja formatado corretamente
            input.addEventListener('blur', function() {
                if (this.value && !this.value.startsWith('R$')) {
                    this.value = `${this.value}`;
                }
            });
        });
    });
</script>
