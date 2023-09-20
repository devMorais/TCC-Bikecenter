<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\VendaModelo;
use sistema\Modelo\FormaPagamentoModelo;
use sistema\Modelo\ProdutoModelo;
use sistema\Modelo\ItensVendaModelo;
use sistema\Nucleo\Helpers;

/**
 * Classe AdminUsuarios
 *
 * @author Fernando A
 */
class AdminVendas extends AdminControlador
{

    /**
     * Lista usuários
     * @return void
     */
    public function listar(): void
    {
        $vendas = new VendaModelo();
        $formapagamento = new FormaPagamentoModelo();

        echo $this->template->renderizar('vendas/listar.html', [
            'formapagamento' => $formapagamento->busca()->resultado(true),
            'vendas' => $vendas->busca()->resultado(true),
            'total' => [
                'vendas' => $vendas->busca()->total(),
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
}
