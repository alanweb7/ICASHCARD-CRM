window.addEventListener('DOMContentLoaded', function () {
    $(document).ready(function () {
        $(document).ready(function () {
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
    });

    function formatText(input) {
        // Substitui os underscores (_) por espaços
        var formattedText = input.replace(/_/g, ' ');

        // Converte para título (capitaliza cada palavra)
        formattedText = formattedText.replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });

        return formattedText;
    }
});
