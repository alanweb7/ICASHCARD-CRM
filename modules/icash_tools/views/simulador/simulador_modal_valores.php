<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Modal Simulador -->
<div class="modal fade" id="modalSimulador" tabindex="-1" role="dialog" aria-labelledby="modalSimuladorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalSimuladorLabel">Valor Solicitado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th></th>
                            <th>Parcela</th>
                            <th>Valor Total R$</th>
                            <th>Valor Parcela R$</th>
                            <th>Valor Receber R$</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-simulador"></tbody>
                </table>

            </div>

            <div class="modal-footer justify-content-between">
                <div>
                    <button type="button" class="btn btn-warning" id="btn-parcela-alerta" disabled>NENHUMA PARCELA SELECIONADA</button>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>

        </div>
    </div>
</div>