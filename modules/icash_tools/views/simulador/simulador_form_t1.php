<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open('', ['id' => 'simuladorForm', 'class' => 'row', 'autocomplete' => 'off']); ?>
<!-- Coluna esquerda -->

<style>
    .btn-group-toggle .btn {
        font-weight: bold;
        text-transform: uppercase;
    }
</style>
<div class="col-md-5">
    <div class="form-group">
        <label for="credenciadora">Credenciadora</label>
        <select class="form-control" name="credenciadora" id="credenciadora">
            <option value="">Selecione</option>
            <option value="CIELO">CIELO</option>
            <option value="CIELO">ADIQ</option>
            <!-- <option value="B">Credenciadora B</option> -->
        </select>
    </div>

    <div class="form-group">
        <label for="tabela">Tabela</label>
        <select class="form-control" name="tabela" id="tabela">
            <option value="CIELO">TABELA A</option>
            <option value="CIELO">TABELA B</option>
            <option value="CIELO">TABELA C</option>
        </select>
    </div>

    <!-- <div class="form-group">
        <label for="prazo">Prazo</label>
        <select class="form-control" name="prazo" id="prazo">
            <option value="" disabled selected>Selecione o prazo</option>
            <?php //for ($i = 2; $i <= 12; $i++): 
            ?>
                <option value="<?= $i ?>"><?= $i ?>x</option>
            <?php //endfor; 
            ?>
        </select>
    </div> -->


    <div class="form-group">
        <label for="valor">Valor</label>
        <input type="text" class="form-control" name="valor" id="valor" placeholder="R$:">
    </div>

    <div class="form-group">
        <label class="d-block mb-2">O valor informado é:</label>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary w-50 type-operator-control" data-type="liquido">LÍQUIDO</button>
            <button type="button" class="btn btn-warning w-50 type-operator-control" data-type="limite">LIMITE</button>
        </div>
    </div>

    <input type="hidden" name="valor_liberado" value="">
    <input type="hidden" name="valor_parcela" value="">
    <input type="hidden" name="prazo_info" value="">
    <input type="hidden" name="limite_utilizado" value="">

</div>

<!-- Coluna direita -->
<div class="col-md-7">
    <div class="card">
        <div class="card-body" id="resultadoSimulacao">
            <p><strong>Valor liberado:</strong><br>R$: <span id="valor_liberado">-</span></p>
            <p><strong>Valor Parcela:</strong><br>R$: <span id="valor_parcela">-</span></p>
            <p><strong>Prazo:</strong><br><span id="prazo_info">-</span></p>
            <p><strong>Limite utilizado do cartão de crédito:</strong><br>R$: <span id="limite_utilizado">-</span></p>
        </div>
    </div>
</div>

<!-- Botão -->
<div class="d-flex justify-content-end gap-2 mt-4">
    <button type="button" id="btn-next" class="btn btn-success">Próximo</button>
</div>

<?php echo form_close(); ?>