<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$version = '7.7.85';
if ($this->uri->segment(3) == 'simulador') {
    // CSS
    echo '<link href="' . module_dir_url('icash_tools', 'assets/css/icash-tools-simulador-styles.css?v=' . $version) . '" rel="stylesheet">';
}
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body panel-table-full">

                        <div id="form-etapas-container">
                            <div id="etapa-1" class="etapa-form">
                                <?php $this->load->view('simulador/simulador_form_t1'); ?>
                            </div>

                            <div id="etapa-2" class="etapa-form d-none">
                                <?php $this->load->view('simulador/simulador_form_t2'); ?>
                            </div>

                            <div id="etapa-3" class="etapa-form d-none">
                                <?php $this->load->view('simulador/simulador_form_t3'); ?>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        <?php $this->load->view('simulador/simulador_modal_valores'); ?>
    </div>
</div>

<?php init_tail(); ?>
<?php
if ($this->uri->segment(3) == 'simulador') {
    // JS
    echo '<script src="' . module_dir_url('icash_tools', 'assets/js/icash-tools-simulador-scripts.js?v=' . $version) . '"></script>';
}
?>


<!-- mascaras -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        $('input[name="cpf"]').mask('000.000.000-00', {
            reverse: true
        });
        $('input[name="cep"]').mask('00000-000', {
            reverse: true
        });
        $('input[name="telefone"]').mask('(00) 00000-0000');
    });
</script>


</body>

</html>