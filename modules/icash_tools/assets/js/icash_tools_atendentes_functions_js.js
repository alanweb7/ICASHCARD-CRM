window.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // esse carrega primeiro
    window.addEventListener('load', function () {
        'use strict';

        console.log("Carregou 1...");
        appValidateForm($("body").find('.job_position_training_add_edit'), {
            'training_type': 'required',
        });
    });

    $(document).ready(function () {
        'use strict';
        console.log("Carregou 2...");
        // Adiciona um evento de clique aos botões com a classe 'send_file'
        $('.send_file').on('click', function () {
            // Obtém o valor do atributo data-type do botão clicado
            var dataType = $(this).data('type');

            var typeName = formatText(dataType);

            // Define o valor do input com id "doc_type" para o valor de data-type
            $('#doc_type').val(dataType);
            $('#type_file').html(typeName);
        });
    });

    // body funciona bem (usar em todas a implementacoes)
    $("body").on('click', 'button.btn.btn-primary', function () {
        'use strict';
        console.log("Clicou!");
        $(this).parents('#contract-allowancetype').remove();
    });

    $("body").on('click', 'span.fa.fa-search', function () {
        'use strict';
        console.log("Buscar de novo..!");
        onTeste();
        $(this).parents('#contract-allowancetype').remove();
    });

    $("body").on('click', "a.fa-solid.fa-list.fa-fw", function () {
        'use strict';
        console.log("Colunas");
        onTeste();
        $(this).parents('#contract-allowancetype').remove();
    });



});


function editStaff(staff) {

    console.log(staff);
    // Alimenta os campos do modal com os dados recebidos
    document.getElementById('staffEmail').value = staff.email || '';
    document.getElementById('staffFullname').value = staff.firstname || '';
    document.getElementById('staffId').value = staff.staffid || '';
    document.getElementById('role').value = staff.role || '';
    // document.getElementById('staffcpf').value = staff.cpf_cnpj ? formatarCPF(staff.cpf_cnpj) : '';
    document.getElementById('staffphone').value = staff.phonenumber || '';
    document.getElementById('staffcpf').value = staff.cpf_cnpj || '';

    // Ativa o modal
    $('#editStaffModal').modal('show');
}

function formatarCPF(cpf) {
    cpf = cpf.replace(/\D/g, ''); // remove tudo que não for número

    if (cpf.length === 11) {
        return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }

    return cpf; // se não tiver 11 dígitos, retorna como está
}

function formatText(input) {
    // Substitui os underscores (_) por espaços
    var formattedText = input.replace(/_/g, ' ');

    // Converte para título (capitaliza cada palavra)
    formattedText = formattedText.replace(/\b\w/g, function (char) {
        return char.toUpperCase();
    });

    return formattedText;
}

function formatNumber(n) {
    'use strict';
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}

function onTeste() {
    'use strict';
    console.log('Testou...');
}

function toggleStaffStatus(staffId, isActive) {
    const status = isActive ? 1 : 0;

    // Dispara uma requisição AJAX para alternar o status
    $.ajax({
        url: "register_staff/onOff_staff",
        type: "POST",
        data: { staffid: staffId, active: status },
        success: function (response) {
            console.log(response.message); // Mensagem de retorno (opcional)
            location.reload(); // Atualiza a página para refletir a alteração
        },
        error: function (xhr) {
            alert('Ocorreu um erro ao alterar o status.');
        }
    });
}

function deleteStaff(staffId) {
    if (confirm('Tem certeza que deseja excluir este atendente?')) {
        $.post('register_staff/delete_staff', { id: staffId }, function (response) {
            if (response.success) {
                alert_float('success', 'Atendente excluído com sucesso.');
                location.reload();
            } else {
                alert_float('danger', 'Erro ao excluir atendente.');
            }
        }, 'json');
    }
}
