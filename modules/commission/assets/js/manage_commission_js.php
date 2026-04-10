<script>
  var commission_table,
    report_from_choose,
    fnServerParams,
    commission_chart,
    commission_client_table,
    commission_client_chart,
    statistics_cost_of_purchase_orders;
  (function($) {
    "use strict";

    commission_table = $('#commission_table');
    commission_chart = $('#commission-chart');
    commission_client_table = $('#commission-client-table');
    commission_client_chart = $('#commission-client-chart');
    report_from_choose = $('#report-time');
    fnServerParams = {
      "products_services": '[name="products_services"]',
      "staff_filter": '[name="staff_filter"]',
      "client_filter": '[name="client_filter"]',
      "status": '[name="status"]',
      "products_services_chart": '[name="products_services_chart"]',
      "staff_filter_chart": '[name="staff_filter_chart"]',
      "client_filter_chart": '[name="client_filter_chart"]',
      "report_months": '[name="months-report"]',
      "custom_date_from": '[name="custom_date_from"]',
      "custom_date_to": '[name="custom_date_to"]',
      "year_requisition": "[name='year_requisition']",
      "is_client": "[name='is_client']",
      "role_filter": "[name='role_filter']",
      "is_process_data": "[name='is_process_data']",
    }


    $('select[name="status"]').on('change', function() {
      gen_reports();
    });
    $('select[name="products_services"]').on('change', function() {
      gen_reports();
    });
    $('select[name="staff_filter"]').on('change', function() {
      gen_reports();
    });
    $('select[name="role_filter"]').on('change', function() {
      gen_reports();
    });
    $('select[name="client_filter"]').on('change', function() {
      gen_reports();
    });
    $('select[name="products_services_chart"]').on('change', function() {
      gen_reports();
    });
    $('select[name="staff_filter_chart"]').on('change', function() {
      gen_reports();
    });
    $('select[name="client_filter_chart"]').on('change', function() {
      gen_reports();
    });

    $('select[name="months-report"]').on('change', function() {
      gen_reports();
    });


    $('select[name="year_requisition"]').on('change', function() {
      gen_reports();
    });

    $('.table-commission').on('draw.dt', function() {
      var paymentReceivedReportsTable = $(this).DataTable();
      var sums = paymentReceivedReportsTable.ajax.json().sums;
      $(this).find('tfoot').addClass('bold');
      $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?> (<?php echo _l('per_page'); ?>)");
      $(this).find('tfoot td.total').html(sums.total);
      $(this).find('tfoot td.total_liq').html(sums.total_liq);
      $(this).find('tfoot td.total_commission').html(sums.total_commission);
    });

    init_report($('#btn_commission_table'), 'commission_table');
  })(jQuery);


  (function($) {
    "use strict";

    $(document).ready(function() {

      $('#filter_custom_dates').on('click', function(e) {
        e.preventDefault();

        var date_from = $('#custom_date_from').val();
        var date_to = $('#custom_date_to').val();

        if (date_from && date_to) {
          gen_reports();
          // Redirecionar ou enviar via AJAX para a URL com os parâmetros de data
          // var url = window.location.href;
          // var newUrl = updateUrlParameter(url, 'date_from', date_from);
          // newUrl = updateUrlParameter(newUrl, 'date_to', date_to);
          // window.location.href = newUrl;
        } else {
          alert('<?php echo _l('please_select_both_dates'); ?>');
        }
      });


      // botao processar items
      $('#process_items').on('click', function(e) {
        e.preventDefault();

        $('input[name="is_process_data"]').val(1);
        gen_reports();
        // init_commission_process_data(12);
      });

      function updateUrlParameter(url, param, paramVal) {
        var newAdditionalURL = "";
        var tempArray = url.split("?");
        var baseURL = tempArray[0];
        var additionalURL = tempArray[1];
        var temp = "";
        if (additionalURL) {
          tempArray = additionalURL.split("&");
          for (i = 0; i < tempArray.length; i++) {
            if (tempArray[i].split('=')[0] != param) {
              newAdditionalURL += temp + tempArray[i];
              temp = "&";
            }
          }
        }

        var rows_txt = temp + "" + param + "=" + paramVal;
        return baseURL + "?" + newAdditionalURL + rows_txt;
      }
    });

  })(jQuery);



  function init_report(e, type) {
    "use strict";

    var report_wrapper = $('#report');

    if (report_wrapper.hasClass('hide')) {
      report_wrapper.removeClass('hide');
    }

    $('head title').html($(e).text());

    report_from_choose.addClass('hide');

    $('#year_requisition').addClass('hide');

    commission_table.addClass('hide');
    commission_chart.addClass('hide');

    $('select[name="months-report"]').selectpicker('val', 'this_month');
    // Clear custom date picker
    $('#currency').removeClass('hide');

    if (type != 'commission_chart' && type != 'commission_client_chart') {
      report_from_choose.removeClass('hide');
    }
    if (type == 'commission_table') {

      $('input[name="is_client"]').val(0);

      commission_table.removeClass('hide');
      $('#div_staff_filter').removeClass('hide');
      $('#div_client_filter').addClass('hide');
    } else if (type == 'commission_client_table') {

      $('input[name="is_client"]').val(1);

      commission_table.removeClass('hide');
      $('#div_staff_filter').addClass('hide');
      $('#div_client_filter').removeClass('hide');
    } else if (type == 'commission_chart') {

      $('input[name="is_client"]').val(0);

      commission_chart.removeClass('hide');
      $('#year_requisition').removeClass('hide');
      $('#div_staff_filter_chart').removeClass('hide');
      $('#div_client_filter_chart').addClass('hide');
    } else if (type == 'commission_client_chart') {

      $('input[name="is_client"]').val(1);

      commission_chart.removeClass('hide');
      $('#year_requisition').removeClass('hide');
      $('#div_staff_filter_chart').addClass('hide');
      $('#div_client_filter_chart').removeClass('hide');
    }

    gen_reports();
  }


  function init_commission_table() {
    "use strict";

    if ($.fn.DataTable.isDataTable('.table-commission')) {
      $('.table-commission').DataTable().destroy();
    }

    // Inicializando DataTable com Ajax
    var table = initDataTable('.table-commission', admin_url + 'commission/commission_table', false, false, fnServerParams);


    // Capturar a resposta da tabela após o carregamento de dados
    table.on('xhr.dt', function(e, settings, json, xhr) {
      if (json && json.url) {
        // Se o servidor retornar uma URL, redireciona para ela
        window.location.href = json.url;
      }
    });
  }



  function init_commission_process_data() {

    "use strict";
    var data = {};
    data.is_process_data = 1;

    $.post(admin_url + 'commission/commission_table/', data)
      .done(function(response) {
        response = JSON.parse(response);
        console.log(response);
        // $('#list_commission').html(response.html);
        // $('#commission_detail_modal').modal('show');
      });


  }





  function init_commission_chart() {
    "use strict";
    var canvas = document.getElementById("commission_chart");
    var data = {};
    data.year = $('select[name="year_requisition"]').val();
    data.is_client = $('input[name="is_client"]').val();
    if (data.is_client == 1) {
      data.staff_filter = $('select[name="client_filter_chart"]').val();
    } else {
      data.staff_filter = $('select[name="staff_filter_chart"]').val();
    }
    data.products_services = $('select[name="products_services_chart"]').val();

    $.post(admin_url + 'commission/commission_chart/', data).done(function(response) {
      response = JSON.parse(response);
      Highcharts.setOptions({
        chart: {
          style: {
            fontFamily: 'inherit !important',
            fill: 'black'
          }
        },
        colors: ['#119EFA', '#ef370dc7', '#15f34f', '#791db2d1', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4', '#50B432', '#0d91efc7', '#ED561B']
      });
      Highcharts.chart('commission_chart', {
        chart: {
          type: 'column'
        },
        title: {
          text: '<?php echo _l('commission_chart') ?>'
        },
        subtitle: {
          text: ''
        },
        credits: {
          enabled: false
        },
        xAxis: {
          categories: response.month,
          crosshair: true,
        },
        yAxis: {
          min: 0,
          title: {
            text: response.name
          }
        },
        tooltip: {
          headerFormat: '<span>{point.key}</span><table>',
          pointFormat: '<tr><td>{series.name}: </td>' +
            '<td><b> {point.y:.0f}</b></td></tr>',
          footerFormat: '</table>',
          shared: true,
          useHTML: true
        },
        plotOptions: {
          column: {
            pointPadding: 0.2,
            borderWidth: 0
          }
        },

        series: [{
            name: '<?php echo _l('total'); ?>',
            data: response.data_total,
          },
          {
            name: '<?php echo _l('invoice_status_paid'); ?>',
            data: response.data_paid,
          }
        ]
      });



    })
  }

  // Main generate report function
  function gen_reports() {
    "use strict";

    if (!commission_table.hasClass('hide')) {
      init_commission_table();
    } else if (!commission_chart.hasClass('hide')) {
      init_commission_chart();
    }
  }

  function view_detail_commission_table($staffid) {
    "use strict";
    var data = {};
    data.products_services = $('select[name="products_services"]').val();
    data.report_months = $('select[name="months-report"]').val();
    data.is_client = $('input[name="is_client"]').val();

    $.post(admin_url + 'commission/get_data_detail_commission_table/' + $staffid, data).done(function(response) {
      response = JSON.parse(response);
      $('#list_commission').html(response.html);
      $('#commission_detail_modal').modal('show');
    });
  }
</script>