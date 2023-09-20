<?php

namespace sistema\Controlador;

use sistema\Nucleo\Controlador;
use sistema\Nucleo\Helpers;
use sistema\Nucleo\Sessao;
use sistema\Modelo\UsuarioModelo;
use sistema\Modelo\TipoUsuarioModelo;
use sistema\Modelo\EnderecoModelo;
use sistema\Modelo\TipoEnderecoModelo;
use sistema\Modelo\EntregaModelo;
use sistema\Modelo\ItensVendaModelo;
use sistema\Modelo\ProdutoModelo;

class UsuarioControlador extends Controlador
{

    public function __construct()
    {
        parent::__construct('templates/usuario/views');
    }

    public static function usuario()
    {
        $sessao = new Sessao();
        if (!$sessao->checar('usuarioId')) {
            return null;
        }

        return (new UsuarioModelo())->buscaPorId($sessao->usuarioId);
    }

    public function login(): void
    {
        $usuario = UsuarioControlador::usuario();
        //USUARIO NÍVEL CLIENTE
        if ($usuario && $usuario->fktipousuario == 1) {
            Helpers::redirecionar();
        }

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if (in_array('', $dados)) {
                $this->mensagem->alerta('Todos os campos são obrigatórios!')->flash();
            } else {
                $usuario = (new UsuarioModelo())->login($dados);
                if ($usuario) {
                    Helpers::redirecionar();
                }
            }
        }

        Helpers::redirecionar();
    }

    public function listarPerfil(): void
    {
        echo $this->template->renderizar('perfil/listar.html', [
        ]);
    }

    public function editarPerfil(int $id): void
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
                $usuario->status = 1;
                //$usuario->atualizado_em = date('Y-m-d H:i:s');

                if ($usuario->salvar()) {
                    $this->mensagem->sucesso('Usuário atualizado com sucesso')->flash();
                    Helpers::redirecionar('perfil');
                } else {
                    $usuario->mensagem()->flash();
                }
            }
        }
        $tipousuario = (new TipoUsuarioModelo())->busca();
        echo $this->template->renderizar('perfil/formulario.html', [
            'tipousuario' => $tipousuario->resultado(true)
        ]);
    }

    public function sair(): void
    {
        $sessao = new Sessao();
        $sessao->limpar('usuarioId');

        $this->mensagem->informa('Você saiu!')->flash();
        Helpers::redirecionar();
    }

    /**
     * Realiza o cadastro dos clientes 
     * @return void
     */
    public function cadastro(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if ($this->validarDados($dados)) {

                if (empty($dados['senha'])) {
                    $this->mensagem->alerta('Informe uma senha para o usuário')->flash();
                } elseif (!Helpers::validarSenha($dados['senha'])) {
                    $this->mensagem->alerta('A senha deve ter pelo menos 6 caracteres, incluindo pelo menos uma letra maiúscula e um caractere especial.')->flash();
                } elseif (!Helpers::validarTelefone($dados['telefone'])) {
                    $this->mensagem->alerta('Insira um telefone válido!')->flash();
                } else {
                    $usuario = new UsuarioModelo();

                    $usuario->nome = $dados['nome'];
                    $usuario->email = $dados['email'];

                    $usuario->telefone = $dados['telefone'];
                    if (Helpers::validarCpf($dados['cpf'])) {
                        $usuario->cpf = $dados['cpf'];
                    }
                    $senha = Helpers::gerarSenha($dados['senha']);

                    $usuario->senha = $senha;
                    $usuario->fktipousuario = 3;
                    $usuario->status = 1;

                    if ($usuario->salvar()) {
                        $this->mensagem->sucesso('Parabéns! Você foi cadastrado com sucesso!')->flash();
                        Helpers::redirecionar();
                    } else {
                        $usuario->mensagem()->flash();
                    }
                }
            }
        }


        $tipousuario = (new TipoUsuarioModelo())->busca();
        echo $this->template->renderizar('cadastro.html', [
            'tipousuario' => $tipousuario->resultado(true)
        ]);
    }

    /**
     * Realiza o cadastro do endereco do cliente
     * @return void
     */
    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (isset($dados)) {
            if ($this->validarDadosEndereco($dados)) {

                $endereco = new EnderecoModelo();

                $endereco->complemento = $dados['complemento'];
                $endereco->cep = $dados['cep'];
                $endereco->fkusuario = UsuarioControlador::usuario()->id;
                $endereco->logradouro = $dados['logradouro'];
                $endereco->cidade = $dados['cidade'];
                $endereco->bairro = $dados['bairro'];
                $endereco->estado = $dados['estado'];
                $endereco->numero = $dados['numero'];
                $endereco->fktipoendereco = $dados['fktipoendereco'];

                if ($endereco->salvar()) {
                    $this->mensagem->sucesso('Endereço cadastrado com sucesso!')->flash();
                    Helpers::redirecionar('endereco');
                } else {
                    $this->mensagem->erro($endereco->erro())->flash();
                    Helpers::redirecionar('endereco');
                }
            }
        }
        $tipoendereco = (new TipoEnderecoModelo())->busca();
        echo $this->template->renderizar('enderecos/formulario.html', [
            'tipoenderecos' => $tipoendereco->resultado(true)
        ]);
    }

    public function editar(int $id): void
    {

        $endereco = (new EnderecoModelo())->buscaPorID($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            if ($this->validarDadosEndereco($dados)) {

                $endereco = (new EnderecoModelo())->buscaPorID($endereco->id);

                $endereco->complemento = $dados['complemento'];
                $endereco->cep = $dados['cep'];
                $endereco->fkusuario = UsuarioControlador::usuario()->id;
                $endereco->logradouro = $dados['logradouro'];
                $endereco->numero = $dados['numero'];
                $endereco->fktipoendereco = $dados['fktipoendereco'];

                if ($endereco->salvar()) {
                    $this->mensagem->sucesso('Endereço atualizado com sucesso!')->flash();
                    Helpers::redirecionar('endereco');
                } else {
                    $this->mensagem->erro($endereco->erro())->flash();
                    Helpers::redirecionar('endereco');
                }
            }
        }

        $tipoendereco = (new TipoEnderecoModelo())->busca();
        echo $this->template->renderizar('enderecos/formulario.html', [
            'tipoenderecos' => $tipoendereco->resultado(true),
            'endereco' => $endereco
        ]);
    }

    /**
     * Deleta enderecos por ID
     * @param int $id
     * @return void
     */
    public function deletar(int $id): void
    {
        if (is_int($id)) {
            $endereco = (new EnderecoModelo())->buscaPorId($id);
            if (!$endereco) {
                $this->mensagem->alerta('O Endereço que você está tentando deletar não existe!')->flash();
                Helpers::redirecionar('endereco');
            } else {
                if ($endereco->deletar()) {
                    $this->mensagem->sucesso('Endereço deletado com sucesso!')->flash();
                    Helpers::redirecionar('endereco');
                } else {
                    $this->mensagem->erro($endereco->erro())->flash();
                    Helpers::redirecionar('endereco');
                }
            }
        }
    }

    /**
     * Renderiza a página da conta do usuario logado
     * @return void
     * 
     */
    public function conta(): void
    {
        echo $this->template->renderizar('conta.html', [
        ]);
    }

    /**
     * lista os endereços do cliente logado
     * @param int $id
     */
    public function endereco(): void
    {

        $id = UsuarioControlador::usuario()->id;
        $endereco = (new EnderecoModelo())->busca("fkusuario = {$id} ");
        echo $this->template->renderizar('enderecos/listar.html', [
            'enderecos' => $endereco->resultado(true),
            'total' => [
                'enderecos' => $endereco->total(),
                'residencial' => $endereco->busca("fktipoendereco = 1 AND fkusuario = {$id}")->total(),
                'comercial' => $endereco->busca("fktipoendereco = 2 AND fkusuario = {$id}")->total()
            ]
        ]);
    }

    public function pedidos()
    {
        $id_usuario = UsuarioControlador::usuario()->id;

        $entregas = (new EntregaModelo())->busca("fkusuario = {$id_usuario}")->resultado(true);
        $entregasProcesso = (new EntregaModelo())->busca("fkusuario = {$id_usuario} AND fkstatusentrega = 1 ");
        $entregasPreparacao = (new EntregaModelo())->busca("fkusuario = {$id_usuario} AND fkstatusentrega = 2 ");
        $entregasEnvio = (new EntregaModelo())->busca("fkusuario = {$id_usuario} AND fkstatusentrega = 3 ");
        $entregasConluido = (new EntregaModelo())->busca("fkusuario = {$id_usuario} AND fkstatusentrega = 4 ");

        if (!$entregas) {
            $this->mensagem->alerta('Você não têm nenhum pedido!')->flash();
            Helpers::redirecionar();
        }
        echo $this->template->renderizar('pedidos/listar.html', [
            'entregas' => $entregas,
            'total' => [
                'processo' => $entregasProcesso->total(),
                'preparacao' => $entregasPreparacao->total(),
                'envio' => $entregasEnvio->total(),
                'concluido' => $entregasConluido->total(),
            ]
        ]);
    }

    public function displayProductDetails($product)
    {
        $quantidade = $product->quantidade;
        $id_produto = $product->fkproduto;
        $detalhesProdutoPorID = (new ProdutoModelo())->buscaPorId($product->fkproduto);
        $imagem = $detalhesProdutoPorID->imagem;
        $nome = $detalhesProdutoPorID->desc_produto;
        $valor = $detalhesProdutoPorID->valor;
        $valorTotal = $valor * $quantidade;
        $valorTotalFormatado = Helpers::formatarValor($valorTotal);
        $valorUnitarioFormatado = Helpers::formatarValor($valor);
        $imagemUrl = Helpers::url('uploads/imagens/thumbs/' . $imagem);

        echo "
    <div class='product-details'>
        <div class='product-icon my-4'>
            <img src='$imagemUrl' alt='Produto Ícone' class='capa' style='max-width: 150px'>
            <small>$nome</small>
        </div>
        <div class='product-info text-center'>
            <span> Código do produto:</span><strong> $id_produto</strong>
            <span> | Quantidade do produto:</span><strong text-danger> $quantidade</strong>
            <span> | Valor Unitário:</span><strong text-danger> R$ $valorUnitarioFormatado<br></strong>
            <span>Valor Total:</span><strong text-danger> R$ $valorTotalFormatado<br></strong>
        </div>
    </div>
    <hr style='border: 1px solid black;'>
    ";

        return $valorTotal; // Retorna o valor total do produto
    }

    public function itens()
    {
        $idvenda = filter_input(INPUT_POST, 'idvenda', FILTER_VALIDATE_INT);
        $itensVenda = (new ItensVendaModelo())->busca("fkvenda = {$idvenda} ")->resultado(true);
        $valorTotalGeral = 0;

        if (!empty($itensVenda)) {
            $valorTotalGeralFormatado = 0;

            foreach ($itensVenda as $item) {
                $valorTotalGeral += $this->displayProductDetails($item);
            }

            $valorTotalGeralFormatado = Helpers::formatarValor($valorTotalGeral);

            echo "<div class='total-geral float-end'>Valor Total da Venda:<strong> R$ $valorTotalGeralFormatado</strong></div>";
        } else {
            echo "Nenhum resultado encontrado.";
        }
    }

    /**
     * Checa os dados do formulário de cadastro de clientes
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

        return true;
    }

    /**
     * Checa os dados do formulário de cadastro de enderecos clientes
     * @param array $dados
     * @return bool
     */
    public function validarDadosEndereco(array $dados): bool
    {
        if (empty($dados['complemento'])) {
            $this->mensagem->alerta('Informe o complemento!')->flash();
            return false;
        }

        if (empty($dados['cep'])) {
            $this->mensagem->alerta('Informe o cep!')->flash();
            return false;
        }
        if (empty($dados['logradouro'])) {
            $this->mensagem->alerta('Informe o logradouro!')->flash();
            return false;
        }
        if (empty($dados['numero'])) {
            $this->mensagem->alerta('Informe o numero!')->flash();
            return false;
        }

        return true;
    }
}
