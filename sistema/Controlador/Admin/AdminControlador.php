<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Controlador;
use sistema\Nucleo\Helpers;
use sistema\Controlador\UsuarioControlador;
use sistema\Nucleo\Sessao;

/**
 * Description of AdminControlador
 *
 * @author FernandoA
 */
class AdminControlador extends Controlador
{

    protected $usuario;

    public function __construct()
    {
        parent::__construct('templates/admin/views');

        $this->usuario = UsuarioControlador::usuario();
        if (!$this->usuario || ($this->usuario->fktipousuario != 2 && $this->usuario->fktipousuario != 3)) {
            $this->mensagem->erro("Faça login para acessar!")->flash();

            $sessao = new Sessao();
            $sessao->limpar('usuarioId');

            Helpers::redirecionar("admin/login");
        }
    }
}
