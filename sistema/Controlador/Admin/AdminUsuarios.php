<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Helpers;
use sistema\Modelo\UsuarioModelo;
use sistema\Modelo\TipoUsuarioModelo;
use sistema\Modelo\VendaModelo;
use sistema\Modelo\CarrinhoModelo;

/**
 * Classe AdminUsuarios
 *
 * @author Fernando A
 */
class AdminUsuarios extends AdminControlador
{

    /**
     * Lista usuários
     * @return void
     */
    public function listar(): void
    {
        $usuario = new UsuarioModelo();
        $tipousuario = new TipoUsuarioModelo();

        echo $this->template->renderizar('usuarios/listar.html', [
            'tipousuario' => $tipousuario->busca(),
            'usuarios' => $usuario->busca()->ordem('fktipousuario DESC, status ASC')->resultado(true),
            'total' => [
                'usuarios' => $usuario->busca('fktipousuario = 1')->total(),
                'usuariosAtivo' => $usuario->busca('status = 1 AND fktipousuario = 1')->total(),
                'usuariosInativo' => $usuario->busca('status = 0 AND fktipousuario = 1')->total(),
                'admin' => $usuario->busca('fktipousuario = 3')->total(),
                'adminAtivo' => $usuario->busca('status = 1 AND fktipousuario = 3')->total(),
                'adminInativo' => $usuario->busca('status = 0 AND fktipousuario = 3')->total(),
                'gerente' => $usuario->busca('fktipousuario = 2')->total(),
                'gerenteAtivo' => $usuario->busca('status = 1 AND fktipousuario = 2')->total(),
                'gerenteInativo' => $usuario->busca('status = 0 AND fktipousuario = 2')->total()
            ]
        ]);
    }

    /**
     * Cadastra usuário
     * @return void
     */
    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            //checa os dados 
            if ($this->validarDados($dados)) {

                if (empty($dados['senha'])) {
                    $this->mensagem->alerta('Informe uma senha para o usuário')->flash();
                } else {
                    $usuario = new UsuarioModelo();

                    $usuario->nome = $dados['nome'];
                    $usuario->email = $dados['email'];
                    $usuario->telefone = $dados['telefone'];
                    $usuario->cpf = $dados['cpf'];
                    $usuario->senha = Helpers::gerarSenha($dados['senha']);
                    $usuario->fktipousuario = $dados['fktipousuario'];
                    $usuario->status = $dados['status'];

                    if ($usuario->salvar()) {
                        $this->mensagem->sucesso('Usuário cadastrado com sucesso')->flash();
                        Helpers::redirecionar('admin/usuarios/listar');
                    } else {
                        $usuario->mensagem()->flash();
                    }
                }
            }
        }
        $tipousuario = (new TipoUsuarioModelo())->busca();
        echo $this->template->renderizar('usuarios/formulario.html', [
            'usuario' => $dados,
            'tipousuario' => $tipousuario->resultado(true)
        ]);
    }

    /**
     * Edita os dados do usuário por ID
     * @param int $id
     * @return void
     */
    public function editar(int $id): void
    {
        $usuario = (new UsuarioModelo())->buscaPorId($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if ($this->validarDados($dados)) {
                $usuario = (new UsuarioModelo())->buscaPorId($id);

                $usuario->nome = $dados['nome'];
                $usuario->email = $dados['email'];
                $usuario->telefone = $dados['telefone'];
                $usuario->cpf = $dados['cpf'];
                $usuario->senha = Helpers::gerarSenha($dados['senha']);
                $usuario->fktipousuario = $dados['fktipousuario'];
                $usuario->status = $dados['status'];
                //$usuario->atualizado_em = date('Y-m-d H:i:s');

                if ($usuario->salvar()) {
                    $this->mensagem->sucesso('Usuário atualizado com sucesso')->flash();
                    Helpers::redirecionar('admin/usuarios/listar');
                } else {
                    $usuario->mensagem()->flash();
                }
            }
        }
        $tipousuario = (new TipoUsuarioModelo())->busca();
        echo $this->template->renderizar('usuarios/formulario.html', [
            'usuario' => $usuario,
            'tipousuario' => $tipousuario->resultado(true)
        ]);
    }

    /**
     * Checa os dados do formulário
     * @param array $dados
     * @return bool
     */
    public function validarDados(array $dados): bool
    {
        if (empty($dados['nome'])) {
            $this->mensagem->alerta('Informe o nome do usuário')->flash();
            return false;
        }
        if (empty($dados['cpf'])) {
            $this->mensagem->alerta('Informe o cpf do usuário')->flash();
            return false;
        }
        if (empty($dados['email'])) {
            $this->mensagem->alerta('Informe o e-mail do usuário')->flash();
            return false;
        }
        if (!Helpers::validarEmail($dados['email'])) {
            $this->mensagem->alerta('Informe um e-mail válido!')->flash();
            return false;
        }

        if (!empty($dados['senha'])) {
            if (!Helpers::validarSenha($dados['senha'])) {
                $this->mensagem->alerta('A senha deve ter 6 caracteres')->flash();
            }
        }

        return true;
    }

    /**
     * Deletar um usuário por ID
     * @param int $id
     * @return void
     */
    public function deletar(int $id): void
    {
        if (is_int($id)) {
            $usuario = (new UsuarioModelo())->buscaPorId($id);
            if (!$usuario) {
                $this->mensagem->alerta('O usuário que você está tentando deletar não existe!')->flash();
                Helpers::redirecionar('admin/usuarios/listar');
            }


            $venda = (new VendaModelo())->busca("fkusuario  = {$id}")->resultado(true);

            if ($venda) {
                $this->mensagem->alerta('A exclusão do usuário não é possível devido à presença de informações cruciais, incluindo histórico de compras e registros.')->flash();
                Helpers::redirecionar('admin/usuarios/listar');
            }

            $carrinho = (new CarrinhoModelo())->busca("fkusuario  = {$id}")->resultado(true);

            if ($carrinho) {
                $this->mensagem->alerta('A exclusão do usuário não é possível devido à presença de informações cruciais, incluindo histórico de compras e registros.')->flash();
                Helpers::redirecionar('admin/usuarios/listar');
            }

            $endereco = (new \sistema\Modelo\EnderecoModelo())->busca("fkusuario  = {$id}")->resultado(true);

            if ($endereco) {
                $this->mensagem->alerta('Existem endereços associados a este usuario! Exclusão não permitida')->flash();
                Helpers::redirecionar('admin/usuarios/listar');
            }


            if ($usuario->deletar()) {
                $this->mensagem->sucesso('Usuário deletado com sucesso!')->flash();
                Helpers::redirecionar('admin/usuarios/listar');
            } else {
                $this->mensagem->erro($usuario->erro())->flash();
                Helpers::redirecionar('admin/usuarios/listar');
            }
        }
    }
}
