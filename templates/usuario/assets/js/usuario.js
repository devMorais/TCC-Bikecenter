new jBox('Confirm', {
    confirmButton: 'Faça isso!',
    cancelButton: 'Não'
});



$(document).ready(function () {
    $('#tabela').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        }
    });
});


function entregaModal(idvenda) {
    event.preventDefault();
    $.ajax({
        url: "listar/itens",
        method: "post",
        data: {idvenda},
        dataType: "text",
        success: function (resultado) {
            if (resultado) {
                $('#buscaResultado').html(
                        "<div class='card'>" +
                        "<div class='card-body'>" +
                        resultado +
                        "</div>" +
                        "</div>"
                        );
            } else {
                $('#buscaResultado').html('<div class="alert alert-warning">Nenhum resultado encontrado!</div>');
            }
        }
    });
}