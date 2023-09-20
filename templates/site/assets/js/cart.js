
function carrinhoModal(idproduto) {
    event.preventDefault();

    $.ajax({
        url: "carrinho/inserir",
        method: "post",
        data: {idproduto},
        dataType: "text",
        success: function (mensagem) {
            $('#mensagem').removeClass();

            if (mensagem === 'Inserido com Sucesso!!') {
                $("#carrinhoModal").modal("hide");
                $('#mensagem').text(mensagem);
                atualizarCarrinho();
                location.reload();
            } else {
                $('#mensagem').text(mensagem);
            }
        }
    });
}

$(document).ready(function () {
    $.ajax({
        url: "carrinho/listar",
        method: "post",
        data: $('#frm').serialize(),
        dataType: "html",
        success: function (result) {
            $('#listar-carrinho').html(result);
        }
    });
});

function atualizarCarrinho() {
    $.ajax({
        url: "carrinho/listar",
        method: "post",
        data: $('#frm').serialize(),
        dataType: "html",
        success: function (result) {
            $('#listar-carrinho').html(result);

        }
    });
}

function deletarCarrinho(id) {

    event.preventDefault();

    $.ajax({

        url: "carrinho/excluir",
        method: "post",
        data: {id},
        dataType: "text",
        success: function (mensagem) {

            $('#mensagem').removeClass()

            if (mensagem == 'Excluido com Sucesso!!') {
                atualizarCarrinho();
                //$("#carrinhoModal").modal("show");

            } else {


            }

            $('#mensagem').text(mensagem)

        }

    });

}


function editarCarrinho(id) {

    var quantidade = document.getElementById('txtquantidade').value;
    event.preventDefault();

    $.ajax({

        url: "carrinho/editar/" + id,
        method: "post",
        data: {id, quantidade},
        dataType: "text",
        success: function (mensagem) {

            $('#mensagem').removeClass()

            if (mensagem == 'Editado com Sucesso!!') {
                atualizarCarrinho();
                //$("#modal-carrinho").modal("show");

            } else {


            }

            $('#mensagem').text(mensagem)

        }

    });


}











