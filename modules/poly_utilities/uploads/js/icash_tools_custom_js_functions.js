window.addEventListener('DOMContentLoaded', function() {
//TODO: Código JavaScript;

$(document).on('click', '#copy_link', function(e) {
            e.preventDefault();

            // Obter o valor do atributo data-link
            var link = $(this).data('link');

            // Criar um elemento temporário de input para copiar o texto
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(link).select();

            // Copiar o texto para a área de transferência
            document.execCommand('copy');

            // Remover o elemento temporário
            tempInput.remove();

            // Feedback visual (opcional)
            alert('Link Copiado: ' + link);
        });
  
  });