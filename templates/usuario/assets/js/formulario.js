$(document).ready(function () {
    $('#formulariosCadastro').submit(function (event) {
        var valido = true; // Inicialmente, assumimos que o formulário está válido

        var campos = [
            {campo: $('#nome'), minLength: 2, errorMessage: 'Nome muito curto!'},
            {campo: $('#email'), regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, errorMessage: 'Email inválido!'},
            {campo: $('#telefone'), minLength: 10, errorMessage: 'Insira um telefone válido!'},
            {campo: $('#cpf'), minLength: 11, errorMessage: 'O CPF precisa ser válido!'},
            {campo: $('#senha'), minLength: 6, errorMessage: 'Sua senha precisa ter 6 dígitos!'}
        ];

        for (var i = 0; i < campos.length; i++) {
            var campo = campos[i].campo;
            var minLength = campos[i].minLength;
            var errorMessage = campos[i].errorMessage;

            if (campo.val().trim() === '' || (minLength && campo.val().length < minLength)) {
                campo.addClass('is-invalid');
                campo.siblings('.invalid-feedback').html(errorMessage);
                campo.siblings('.valid-feedback').html('');
                valido = false; // Se algum campo for inválido, definimos como falso
            } else {
                campo.removeClass('is-invalid').addClass('is-valid');
                campo.siblings('.invalid-feedback').html('');
                campo.siblings('.valid-feedback').html('Campo válido');
            }

            if (campo.is($('#telefone'))) {
                if (!validarTelefone(campo.val().trim())) {
                    campo.addClass('is-invalid');
                    campo.siblings('.invalid-feedback').html(errorMessage);
                    campo.siblings('.valid-feedback').html('');
                    valido = false;
                }
            }
        }

        if (!valido) {
            event.preventDefault();
        }
    });
});

document.getElementById("cep").addEventListener("keyup", function (event) {
    var cep = event.target.value;
    cep = cep.replace(/\D/g, '');

    if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {

                        alerta('Cep não encontrado', 'red');
                      
                    } else {
                        // Preenche os campos do formulário com os dados do CEP
                        document.getElementById("logradouro").value = data.logradouro;
                        document.getElementById("cidade").value = data.localidade;
                        document.getElementById("bairro").value = data.bairro;
                        document.getElementById("estado").value = data.uf;
                        // Se a API não fornecer o número, você pode deixar em branco ou ajustar conforme necessário
                        // document.getElementById("numero").value = "";
                    }
                })
                .catch(error => {
                    console.error("Ocorreu um erro na busca de CEP:", error);
                });
    } else if (cep.length < 8) {
        // Limpa os campos quando o CEP for apagado
        document.getElementById("logradouro").value = "";
        document.getElementById("cidade").value = "";
        document.getElementById("bairro").value = "";
        document.getElementById("estado").value = "";
        document.getElementById("numero").value = "";
    }
});


function alerta(mensagem, cor) {
    new jBox('Notice', {
        content: mensagem,
        color: cor,
        animation: 'pulse',
        showCountdown: true
    });
}
