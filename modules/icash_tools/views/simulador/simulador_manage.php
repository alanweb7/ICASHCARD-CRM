<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create', 'icash_tools')) { ?>
                <div class="tw-mb-2 sm:tw-mb-4">
                    <a href="<?php echo admin_url('icash_tools/icash_insert_tabelas'); ?>" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('Nova Tabela'); ?>
                    </a>
                </div>
                <?php } ?>

                <!-- Filtros -->
                <!-- <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" id="filter-nome" class="form-control" placeholder="Filtrar por Nome">
                            </div>
                            <div class="col-md-6">
                                <select id="filter-credenciadora" class="form-control">
                                    <option value=""><?php echo _l('Filtrar por Credenciadora'); ?></option>
                                    <option value="Rede">Rede</option>
                                    <option value="Cielo">Cielo</option>
                                    <option value="Universal">Universal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('Nome'),
                            _l('Credenciadora')
                        ], 'icash_tools'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    var table = initDataTable('.table-icash_tools', window.location.href, [], []);

    // Filtra por Nome
    $('#filter-nome').on('keyup', function() {
        table.columns(0).search(this.value).draw(); // Coluna 0 é o 'Nome'
    });

    // Filtra por Credenciadora
    $('#filter-credenciadora').on('change', function() {
        table.columns(1).search(this.value).draw(); // Coluna 1 é a 'Credenciadora'
    });
});
</script>
</body>
</html>
