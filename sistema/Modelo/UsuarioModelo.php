<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;
use sistema\Nucleo\Sessao;
use sistema\Nucleo\Helpers;

/**
 * Classe de usuarios
 *
 * @author FernandoA
 */
class UsuarioModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('usuario');
    }

    public function buscaPorEmail(string $email): ?UsuarioModelo
    {
        $busca = $this->busca("email = :e", "e={$email}");
        return $busca->resultado();
    }

    public function login(array $dados, int $tipo = 1)
    {
        $usuario = (new UsuarioModelo())->buscaPorEmail($dados['email']);

        if (!$usuario) {
            $this->mensagem->alerta("Os dados informados para o login estão incorretos!")->flash();
            return false;
        }

        if (!Helpers::verificarSenha($dados['senha'], $usuario->senha)) {
            $this->mensagem->alerta("Os dados informados para o login estão incorretos!")->flash();
            return false;
        }
        if ($usuario->fktipousuario != $tipo) {
            $this->mensagem->alerta("Você não tem permissão para acessar essa área!")->flash();
            return false;
        }
        
        if ($usuario->status != 1) {
            $this->mensagem->alerta("Você foi desativado, entre em contato conosco.")->flash();
            return false;
        }

        $usuario->ultimo_login = date('Y-m-d H:i:s');
        $usuario->salvar();

        (new Sessao())->criar('usuarioId', $usuario->id);

        $this->mensagem->sucesso("{$usuario->nome}, seja bem vindo! ")->flash();
        return true;
    }

    public function salvar(): bool
    {
        if ($this->busca("email = :e AND id != :id", "e={$this->email}&id={$this->id}")->resultado()) {
            $this->mensagem->alerta("O E-mail " . $this->dados->email . "  já está cadastrado!");
            return false;
        }

        parent::salvar();

        return true;
    }
}
