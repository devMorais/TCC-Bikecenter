<?php

namespace sistema\Controlador;

use sistema\Nucleo\Controlador;
use sistema\Modelo\CarrinhoModelo;
use sistema\Modelo\ProdutoModelo;
use sistema\Nucleo\Helpers;
use sistema\Modelo\EstoqueModelo;

class CarrinhoControlador extends Controlador
{

    public function __construct()
    {
        parent::__construct('templates/site/views');
    }

    public function inserir()
    {
        $usuario = UsuarioControlador::usuario();

        if ($usuario) {
            $id_cliente = UsuarioControlador::usuario()->id;
            $idproduto = filter_input(INPUT_POST, 'idproduto', FILTER_VALIDATE_INT);

           

            
                $carrinho = new CarrinhoModelo();

                $carrinho->fkusuario = $id_cliente;
                $carrinho->fkproduto = $idproduto;
                $carrinho->quantidade = 1;
                $carrinho->fkvenda = 0;
                $carrinho->data = date('Y-m-d');

                if ($carrinho->salvar()) {
                    echo 'Inserido com Sucesso!!';
                    $this->mensagem->sucesso("Adicionado ao carrinho com sucesso!")->flash();
                } else {
                    $this->mensagem->erro($carrinho->erro())->flash();
                }
            
        }
    }

    public function listar(): void
    {
        $id_usuario = UsuarioControlador::usuario()->id;
        $cartCss = Helpers::url("templates/site/assets/css/carrinho.css");

        // Inclui o CSS do carrinho
        echo '<link rel="stylesheet" href="' . $cartCss . '">';

        // Inicia a tabela HTML
        echo '<div class="cart-inline-header">
            <div class="shoping__cart__table">
                <table>';

        $carrinhoModelo = new CarrinhoModelo();
        $dados = $carrinhoModelo->busca("fkusuario = {$id_usuario} AND fkvenda = 0 ")->ordem("id ASC")->resultado(true);

        if (!empty($dados)) {
            $totalValorCarrinho = 0;
            $quantidadeTotal = 0;

            foreach ($dados as $item) {
                $id_produto = $item->fkproduto;
                $quantidade = $item->quantidade;
                $id_carrinho = $item->id;
                $quantidadeTotal += $quantidade;

                $estoque = (new EstoqueModelo())->busca("fkproduto = {$id_produto} AND quantidade > 0")->resultado(true);

                foreach ($estoque as $item) {
                    $quantidadeCarrinho = $item->quantidade;
                }

                $detalhesProdutoPorId = (new ProdutoModelo())->buscaPorId($id_produto);
                $imagem = $detalhesProdutoPorId->imagem;

                $imagemUrl = Helpers::url('uploads/imagens/thumbs/' . $imagem);
                $cart = Helpers::url("templates/assets/js/cart.js");

                $nome_produto = $detalhesProdutoPorId->desc_produto;
                $valorItem = $detalhesProdutoPorId->valor;
                $valorTotalItem = $valorItem * $quantidade;
                $totalValorCarrinho += $valorTotalItem;

                $valorItemFormatado = number_format($valorItem, 2, ',', '.');
                $valorTotalItemFormatado = number_format($valorTotalItem, 2, ',', '.');

                // Exibe cada item do carrinho
                echo '<tr>
                    <td>
                        <div class="shoping__cart__item">
                            <img src="' . $imagemUrl . '" alt="" class="capa" style="max-width: 80px">
                            <h5>' . $nome_produto . '</h5>
                        </div>
                    </td>
                    <td class="shoping__cart__price">
                        R$ ' . $valorTotalItemFormatado . '
                    </td>
                    <td class="shoping__cart__quantity">
                        <div class="quantity">
                            <div class="pro-qty">
                                <input onchange="editarCarrinho(' . $id_carrinho . ')" type="text" data-zeros="true" value="' . $quantidade . '" min="1" max=' . $quantidadeCarrinho . ' id="quantidade">
                            </div>
                        </div>
                    </td>
                    <td class="shoping__cart__item__close">
                        <a onclick="deletarCarrinho(' . $id_carrinho . ')" id="btn-deletar" class="ml-2" tooltip="tooltip" title="Remover item do carrinho">
                            <span><i class="fa-solid fa-trash"></i></span>
                        </a>
                    </td>
                </tr>';
            }

            // Fecha a tabela HTML e adiciona os totais
            echo '</table>
            </div>
        </div>
        <script src="' . $cart . '"></script>
        <script type="text/javascript">
            var itens = "' . Helpers::formatarNumero($quantidadeTotal) . '";
            var total = "' . Helpers::formatarValor($totalValorCarrinho) . '";
            $("#total_itens").text(itens);
            $("#valor_total").text(total);
        </script>';
        }
    }

    function deletar()
    {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $carrinho = (new CarrinhoModelo())->buscaPorId($id);
        if ($carrinho->deletar()) {

            echo 'Excluido com Sucesso!!';
        } else {
            $this->mensagem->erro($carrinho->erro())->flash();
        }
    }

    /**
     * Edita a quantidade e os valores do carrinho de compras
     * @param type $id
     * @return void
     */
    public function editar($id): void
    {
        $id_usuario = UsuarioControlador::usuario()->id;

        $carrinho = (new CarrinhoModelo())->buscaPorId($id);

        $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
        $carrinho = (new CarrinhoModelo())->buscaPorID($carrinho->id);

        $carrinho->fkusuario = $id_usuario;
        $carrinho->quantidade = $quantidade;
        $carrinho->fkvenda = 0;
        $carrinho->data = date('Y-m-d');

        if ($carrinho->salvar()) {
            echo 'Editado com Sucesso!!';
        }
    }

    /**
     * metodo para retornar a quantidade dos produtos no carrinho quando o usuario estiver logado para ser usado em qualquer template atráves do twig 
     * @return int
     */
    public static function QtdCarrinho(): int
    {
        $id_usuario = UsuarioControlador::usuario()->id;
        $carrinho = (new CarrinhoModelo())->busca("fkusuario = {$id_usuario} AND fkvenda = 0 ")->resultado(true);
        $quantidadeTotal = 0;
        if ($carrinho) {
            foreach ($carrinho as $item) {
                $quantidade = $item->quantidade;
                $quantidadeTotal += $quantidade;
            }

            return $quantidadeProdutosFormatada = Helpers::formatarNumero($quantidadeTotal);
        }
        return 0;
    }

    /**
     * Metodo para retorna o valor total do carrinho quando o usuario estiver logado para ser usado em qualquer template atráves do twig 
     * @return float
     * 
     */
    public static function valorTotalCarrinho()
    {
        $id_usuario = UsuarioControlador::usuario()->id;
        $carrinho = (new CarrinhoModelo())->busca("fkusuario = {$id_usuario} AND fkvenda = 0 ")->resultado(true);
        $totalValorCarrinho = 0;

        if ($carrinho) {
            foreach ($carrinho as $item) {
                $id_produto = $item->fkproduto;
                $quantidade = $item->quantidade;
                $detalhesProdutoPorId = (new ProdutoModelo())->buscaPorId($id_produto);
                $valorItem = $detalhesProdutoPorId->valor;
                $totalValorCarrinho_item = $valorItem * $quantidade;
                $totalValorCarrinho += $totalValorCarrinho_item;
            }

            return $totalValorCarrinho;
        }

        return 0;
    }
}
