$(document).ready(function () {
    $('#formularioFornecedor').submit(function (event) {
        var valido = true; // Inicialmente, assumimos que o formulário está válido

        var campos = [
            {campo: $('#descricao'), minLength: 2, errorMessage: 'Nome muito curto!'},
            {campo: $('#email'), regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, errorMessage: 'Email inválido!'},
            {campo: $('#telefone'), minLength: 9, errorMessage: 'Seu telefone precisa ter 9 dígitos!'},
            {campo: $('#cnpj'), minLength: 11, errorMessage: 'O cnpj precisa ter 14 dígitos!'}

        ];

        for (var i = 0; i < campos.length; i++) {
            var campo = campos[i].campo;
            var minLength = campos[i].minLength;
            var regex = campos[i].regex;
            var errorMessage = campos[i].errorMessage;

            if (campo.val().trim() === '' || (minLength && campo.val().length < minLength) || (regex && !regex.test(campo.val().trim()))) {
                campo.addClass('is-invalid');
                campo.siblings('.invalid-feedback').html(errorMessage);
                campo.siblings('.valid-feedback').html('');
                valido = false; // Se algum campo for inválido, definimos como falso
            } else {
                campo.removeClass('is-invalid').addClass('is-valid');
                campo.siblings('.invalid-feedback').html('');
                campo.siblings('.valid-feedback').html('Campo válido');
            }
        }

        if (!valido) {
            event.preventDefault();
        }
    });
});


$(document).ready(function () {
    $('#formulariousuario').submit(function (event) {
        var valido = true; // Inicialmente, assumimos que o formulário está válido

        var campos = [
            {campo: $('#nome'), minLength: 2, errorMessage: 'Nome muito curto!'},
            {campo: $('#email'), regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, errorMessage: 'Email inválido!'},
            {campo: $('#telefone'), minLength: 9, errorMessage: 'Seu telefone precisa ter 9 dígitos!'},
            {campo: $('#cpf'), minLength: 11, errorMessage: 'O cpf precisa ter 11 dígitos!'},
            {campo: $('#senha'), minLength: 6, errorMessage: 'Sua senha precisa ter 6 dígitos!'}

        ];

        for (var i = 0; i < campos.length; i++) {
            var campo = campos[i].campo;
            var minLength = campos[i].minLength;
            var regex = campos[i].regex;
            var errorMessage = campos[i].errorMessage;

            if (campo.val().trim() === '' || (minLength && campo.val().length < minLength) || (regex && !regex.test(campo.val().trim()))) {
                campo.addClass('is-invalid');
                campo.siblings('.invalid-feedback').html(errorMessage);
                campo.siblings('.valid-feedback').html('');
                valido = false; // Se algum campo for inválido, definimos como falso
            } else {
                campo.removeClass('is-invalid').addClass('is-valid');
                campo.siblings('.invalid-feedback').html('');
                campo.siblings('.valid-feedback').html('Campo válido');
            }
        }

        if (!valido) {
            event.preventDefault();
        }
    });
});


