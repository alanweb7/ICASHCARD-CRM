<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="spacer" style="margin-top: 20px;"></div>

<?php if (empty($history)) : ?>
    <p class="text-muted">Nenhum histórico encontrado para esta proposta.</p>
<?php else : ?>
    <div class="accordion" id="historyAccordion">
        <div class="accordion-item">
            <h4>
                Histórico da Proposta
            </h4>
            <div id="collapseHistory" class="accordion-collapse collapse show" aria-labelledby="headingHistory" data-bs-parent="#historyAccordion">
                <div class="accordion-body">

                    <div class="timeline">
                        <?php foreach ($history as $item) : ?>

                            <?php

                            $name = "Sistema";
                            if (isset($item['staff_id'])) {
                                $staffInfo   =  onGetNameStaffByID($item['staff_id']);
                                $staff_name  = $staffInfo['name'];
                                $role_name   = $staffInfo['role_name'];
                                $user_info   = !empty($staff_name) ? $staff_name . " | " . $role_name : "Sistema";
                            }

                            ?>
                            <div class="timeline-item mb-4 d-flex">
                                <!-- linha -->
                                <hr>
                                <!-- <div class="timeline-marker me-3"></div> -->

                                <!-- Conteúdo -->
                                <div class="timeline-content">
                                    <span class="text-muted small">

                                        <?php if ($item['event'] == "Link de documentos gerado") {  ?>

                                            📅 <?php echo _dt($item['event_date']); ?>: <em>PENDENTE</em><br>
                                            Motivo: <?= $item['description']; ?>
                                            Link de documentos para sanar pendências:<br>
                                            🔗 <?= $item['status']; ?>

                                        <?php  } elseif ($item['event'] == "Retorno operadora") {  ?>
                                            
                                            📅 <?php echo _dt($item['event_date']); ?>: <em><?php echo nl2br(htmlspecialchars($item['step'])); ?></em><br>
                                            Fonte: <?= $item['font']; ?><br>
                                            Descrição: <?= $item['bank_message']; ?><br>
                                            Código: <?= $item['code']; ?><br>

                                        <?php  } elseif ($item['event'] == "Gerar link de Contrato") {  ?>

                                            📅 <?php echo _dt($item['event_date']); ?>: <em><?php echo nl2br(htmlspecialchars($item['step'])); ?></em><br>
                                            Link para assinatura digital de contrato: <br>
                                            🔗 <?= $item['link']; ?>

                                        <?php  } else {  ?>
                                            📅 <?php echo _dt($item['event_date']); ?>: <em><?php echo nl2br(htmlspecialchars($item['step'])); ?></em>
                                        <?php

                                        }
                                        ?>
                                    </span>
                                    <div class="text-muted small">
                                        👤 Usuário: <?= $user_info  ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<style>
    /* Timeline estilos */
    .timeline {
        position: relative;
        margin-left: 20px;
        padding-left: 20px;
        border-left: 2px solid #dee2e6;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-marker {
        width: 14px;
        height: 14px;
        background: #0d6efd;
        /* azul bootstrap */
        border-radius: 50%;
        margin-top: 5px;
        flex-shrink: 0;
        position: relative;
    }

    .timeline-content {
        flex: 1;
    }
</style>