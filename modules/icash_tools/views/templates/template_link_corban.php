<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">


                    <style>
                        .copy-btn {
                            cursor: pointer;
                            transition: 0.3s;
                        }

                        .copy-btn:hover {
                            color: #0d6efd;
                        }

                        #linkInscricao {
                            width: 100%;
                            /* Garante que ocupe o espaço total disponível */
                            max-width: 500px;
                            /* Define um tamanho máximo */
                        }

                        .input-group {
                            padding: 15px;
                            width: 60%;
                            margin: auto;
                        }
                    </style>

                    <?php $staff_id = get_staff_user_id(); ?>
                    <div class="container d-flex justify-content-center align-items-center vh-100">
                        <div class="card shadow-lg p-4 text-center">
                            <h3 class="mb-3">Link exclusivo de auto cadastro!</h3>
                            <p class="text-muted">Clique no botão para abrir o link em nova janela:</p>

                            <div class="input-group mb-3 w-100">
                                <input type="text" id="linkInscricao" class="form-control text-center"
                                    value="https://icashcard.com.br/cadastro-corban/?gid=<?= $staff_id ?>" readonly>
                                <span class="input-group-text bg-white copy-btn" id="btnCopiar"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Copiar">
                                    <i class="fas fa-copy"></i>
                                </span>
                            </div>

                            <a href="https://icashcard.com.br/cadastro-corban/?gid=<?= $staff_id ?>" class="btn btn-primary" target="_blank">Accessar página de cadastro</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btnCopiar = document.getElementById("btnCopiar");
            const linkInput = document.getElementById("linkInscricao");

            btnCopiar.addEventListener("click", function() {
                navigator.clipboard.writeText(linkInput.value).then(() => {
                    btnCopiar.innerHTML = '<i class="fas fa-check text-success"></i>';
                    btnCopiar.setAttribute("title", "Copiado!");
                    btnCopiar.setAttribute("data-bs-original-title", "Copiado!");

                    const tooltip = bootstrap.Tooltip.getInstance(btnCopiar);
                    tooltip.show();

                    setTimeout(() => {
                        btnCopiar.innerHTML = '<i class="fas fa-copy"></i>';
                        btnCopiar.setAttribute("title", "Copiar");
                        tooltip.hide();
                    }, 2000);
                });
            });

            new bootstrap.Tooltip(btnCopiar);
        });
    </script>

    <?php init_tail(); ?>