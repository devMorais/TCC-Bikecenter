<?php

use sistema\Nucleo\Helpers;

//Arquivo de configuração do sistema
//define o fuso horario
date_default_timezone_set('America/Sao_Paulo');

//informações do sistema
define('SITE_NOME', 'Bikecenter');
define('SITE_DESCRICAO', 'Bikecenter - Liberdade e segurança em pedalar');

//urls do sistema
define('URL_PRODUCAO', 'https://sempreumbug.com.br');
define('URL_DESENVOLVIMENTO', 'http://localhost/bikecenter');

if (Helpers::localhost()) {
    //dados de acesso ao banco de dados em localhost
    define('DB_HOST', 'localhost');
    define('DB_PORTA', '3306');
    define('DB_NOME', 'bd_bikecenter');
    define('DB_USUARIO', 'root');
    define('DB_SENHA', 'senac');

    define('URL_SITE', 'bikecenter/');
    define('URL_ADMIN', 'bikecenter/admin/');
} else {
    //dados de acesso ao banco de dados na hospedagem
    define('DB_HOST', 'localhost');
    define('DB_PORTA', '3306');
    define('DB_NOME', 'u846585591_bikecenter');
    define('DB_USUARIO', 'u846585591_bikecenter');
    define('DB_SENHA', 'Pc9zN$U^1Eo?');

    define('URL_SITE', '/');
    define('URL_ADMIN', '/admin/');
}

//autenticação do servidor de emails
define('EMAIL_HOST', 'smtp.hostinger.com');
define('EMAIL_PORTA', '465');
define('EMAIL_USUARIO', 'adm@sempreumbug.com.br');
define('EMAIL_SENHA', 'Caitana92*92');
define('EMAIL_REMETENTE', ['email' => EMAIL_USUARIO, 'nome' => SITE_NOME]);

