<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Sessao;
use sistema\Nucleo\Helpers;
use sistema\Modelo\ProdutoModelo;
use sistema\Modelo\UsuarioModelo;
use sistema\Modelo\CategoriaModelo;
use sistema\Modelo\VendaModelo;
use sistema\Modelo\ItensVendaModelo;
use sistema\Modelo\ContasReceberModelo;

/**
 * Classe AdminDashboard
 *
 * @author Fernando Aguiar
 */
class AdminDashboard extends AdminControlador
{

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
        <div class='product-icon'>
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
     * Home do admin
     * @return void
     */
    public function dashboard(): void
    {
        
        $contasReceber = (new ContasReceberModelo())->busca()->resultado(true);

        $total = 0;

        foreach ($contasReceber as $item) {
            $valor = $item->valor;
            $total += $valor;
        }
        
        $produtos = new ProdutoModelo();
        $usuarios = new UsuarioModelo();
        $categorias = new CategoriaModelo();
        $vendas = new VendaModelo();
        $entregas = new \sistema\Modelo\EntregaModelo();
        echo $this->template->renderizar('dashboard.html', [
            'produtos' => $produtos->busca()->ordem('visitas DESC, id DESC')->limite(5)->resultado(true),
            'total' => [
                'produtos' => $produtos->busca(null, 'COUNT(id)', 'id')->ordem('visitas DESC')->total(),
                'produtosAtivo' => $produtos->busca('status = 1')->total(),
                'produtosInativo' => $produtos->busca('status = 0')->total()
            ],
            'categorias' => $categorias->busca()->ordem('id DESC')->limite(5)->resultado(true),
            'totalcat' => [
                'total' => $categorias->busca()->total(),
                'ativo' => $categorias->busca('status = 1')->total(),
                'inativo' => $categorias->busca('status = 0')->total(),
            ],
            'usuarios' => [
                'logins' => $usuarios->busca()->ordem('ultimo_login DESC')->limite(5)->resultado(true),
                'usuarios' => $usuarios->busca('fktipousuario = 1')->total(),
                'usuariosAtivo' => $usuarios->busca('status = 1 AND fktipousuario = 1')->total(),
                'usuariosInativo' => $usuarios->busca('status = 0 AND fktipousuario = 1')->total(),
                'admin' => $usuarios->busca('fktipousuario = 3')->total(),
                'adminAtivo' => $usuarios->busca('status = 1 AND fktipousuario = 3')->total(),
                'adminInativo' => $usuarios->busca('status = 0 AND fktipousuario = 3')->total(),
                'gerente' => $usuarios->busca('fktipousuario = 2')->total(),
                'gerenteAtivo' => $usuarios->busca('status = 1 AND fktipousuario = 2')->total(),
                'gerenteInativo' => $usuarios->busca('status = 0 AND fktipousuario = 2')->total()
            ],
            'vendas' => [
                'ultimas' => $vendas->busca()->ordem('id DESC')->limite(5)->resultado(true),
                'total' => [
                    'venda' => $vendas->busca(null, 'COUNT(id)', 'id')->ordem('id ASC')->total(),
                    'soma' => $total

                ],
            ],
            'entregas' => [
                'ultimas' => $entregas->busca()->ordem('id DESC')->limite(5)->resultado(true),
                'total' => [
                    'entregas' => $entregas->busca(null, 'COUNT(id)', 'id')->ordem('id ASC')->total(),
                    'entregasAtivo' => $entregas->busca('fkstatusentrega = 2 OR fkstatusentrega = 3 ')->total(),
                    'entregasInativo' => $entregas->busca('fkstatusentrega = 1')->total()
                ]
            ]
        ]);
    }

    /**
     * Faz logout do usuário
     * @return void
     */
    public function sair(): void
    {
        $sessao = new Sessao();
        $sessao->limpar('usuarioId');

        $this->mensagem->informa('Você saiu do painel de controle!')->flash();
        Helpers::redirecionar('admin/login');
    }
}
