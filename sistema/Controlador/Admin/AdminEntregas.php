<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Helpers;
use sistema\Modelo\EntregaModelo;
use sistema\Modelo\StatusEntregaModelo;
use sistema\Modelo\ProdutoModelo;
use sistema\Modelo\ItensVendaModelo;
use sistema\Modelo\VendaModelo;
use sistema\Modelo\EnderecoModelo;

/**
 * Classe Entregas
 *
 * @author Fernando A
 */
class AdminEntregas extends AdminControlador
{

    /**
     * Lista usuários
     * @return void
     */
    public function listar(): void
    {
        $entregas = new EntregaModelo();
        $statusentrega = new StatusEntregaModelo();

        echo $this->template->renderizar('entregas/listar.html', [
            'tipoentrega' => $statusentrega->busca(),
            'entregas' => $entregas->busca()->ordem('id ASC')->resultado(true),
            'total' => [
                'processo' => $entregas->busca("fkstatusentrega = 1")->total(),
                'preparacao' => $entregas->busca("fkstatusentrega = 2")->total(),
                'envio' => $entregas->busca("fkstatusentrega = 3")->total(),
                'concluido' => $entregas->busca("fkstatusentrega = 4")->total()
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

    public function editar(int $id): void
    {


        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            $entrega = (new EntregaModelo())->buscaPorId($id);

            $entrega->desc_entrega = $dados['desc_entrega'];
            $entrega->fkstatusentrega = $dados['fkstatusentrega'];
            if ($entrega->salvar()) {
                $this->mensagem->sucesso('Status atualizado com sucesso')->flash();
                Helpers::redirecionar('admin/entregas/listar');
            } else {
                $entrega->mensagem()->flash();
            }
        }
        $fkstatusentrega = (new StatusEntregaModelo())->busca();
        $fkvenda = (new VendaModelo())->busca();
        $fkendereco = (new EnderecoModelo())->busca();
        $entrega = (new EntregaModelo())->buscaPorId($id);
        echo $this->template->renderizar('entregas/formulario.html', [
            'statusentregas' => $fkstatusentrega->resultado(true),
            'fkvenda' => $fkvenda->resultado(true),
            'fkendereco' => $fkendereco->resultado(true),
            'entrega' => $entrega
        ]);
    }
}
