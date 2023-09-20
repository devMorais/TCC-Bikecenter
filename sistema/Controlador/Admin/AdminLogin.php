<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Controlador;
use sistema\Nucleo\Helpers;
use sistema\Modelo\UsuarioModelo;
use sistema\Controlador\UsuarioControlador;

/**
 * Classe AdminLogin
 *
 * @author Fernando Aguiar
 */
class AdminLogin extends Controlador
{

    public function __construct()
    {
        parent::__construct('templates/admin/views');
    }

    public function login(): void
    {
        $usuario = UsuarioControlador::usuario();

        if ($usuario && ($usuario->fktipousuario == 3 || $usuario->fktipousuario == 2)) {
            Helpers::redirecionar('admin/dashboard');
        }

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if (in_array('', $dados)) {
                $this->mensagem->alerta('Todos os campos são obrigatórios!')->flash();
            } else {
                $usuarioModelo = new UsuarioModelo();
                $usuario = $usuarioModelo->login($dados, 2);

                if ($usuario) {

                    Helpers::redirecionar('admin/dashboard');
                } else {
                    $usuario = $usuarioModelo->login($dados, 3);
                    if ($usuario) {

                        Helpers::redirecionar('admin/dashboard');
                    } else {

                        Helpers::redirecionar('admin/login');
                    }
                }
            }
        }

        echo $this->template->renderizar('loginpainel.html', []);
    }
}
