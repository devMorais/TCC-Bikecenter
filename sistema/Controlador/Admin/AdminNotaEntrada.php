<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\NotaEntradaModelo;
use sistema\Modelo\FornecedorModelo;
use sistema\Modelo\FormaPagamentoModelo;
use sistema\Nucleo\Helpers;
use sistema\Modelo\ProdutoModelo;
use sistema\Modelo\ItensNotaEntradaModelo;
use sistema\Modelo\EstoqueModelo;
use sistema\Modelo\ContasPagarModelo;
use DateTime;

/**
 * Classe de entrada de produtos no sistema
 *
 * @author FernandoA
 */
class AdminNotaEntrada extends AdminControlador
{

    public function listar(): void
    {
        $notaentrada = new NotaEntradaModelo();

        echo $this->template->renderizar('notaentrada/listar.html', [
            'notasentrada' => $notaentrada->busca()->ordem('id ASC')->resultado(true)
        ]);
    }

    public function cadastrar(): void
    {
        echo $this->template->renderizar('notaentrada/formulario.html', [
            'fornecedores' => (new FornecedorModelo())->busca()->resultado(true),
            'formaspagamento' => (new FormaPagamentoModelo())->busca("id = 1")->resultado(true),
            'produtos' => (new ProdutoModelo())->busca()->ordem("id DESC")->resultado(true)
        ]);
    }

    public function pagar()
    {



        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $dataEmissao = new DateTime($dados['dataemissao']);
        if (isset($dados)) {

            if (empty($dados['serie'])) {
                $this->mensagem->alerta('Informe a série da NF')->flash();
                Helpers::redirecionar('admin/nf/cadastrar');
            }

            if (empty($dados['numeronota'])) {
                $this->mensagem->alerta('Informe o número da NF')->flash();
                Helpers::redirecionar('admin/nf/cadastrar');
            }
            if (empty($dados['qtd'])) {
                $this->mensagem->alerta('Informe a quantidade da NF')->flash();
                Helpers::redirecionar('admin/nf/cadastrar');
            }
            if (empty($dados['vlrunitario'])) {
                $this->mensagem->alerta('Informe o valor unitário da NF')->flash();
                Helpers::redirecionar('admin/nf/cadastrar');
            }


            $notaentrada = new NotaEntradaModelo();

            $notaentrada->fkfornecedor = $dados['fkfornecedor'];

            $notaentrada->dataemissao = $dataEmissao->format('Y-m-d H:i:s');

            $notaentrada->serie = $dados['serie'];
            $notaentrada->numeronota = $dados['numeronota'];
            $notaentrada->fkformapagamento = $dados['fkformapagamento'];
            if ($notaentrada->salvar()) {
                //ULTIMO ID DA NOTA DE ENTRADA
                $ultimoIdNotaEntrada = $notaentrada->id;

                $itensNfEntrada = new ItensNotaEntradaModelo();

                $itensNfEntrada->fkproduto = $dados['fkproduto'];

                $itensNfEntrada->fknotaentrada = $ultimoIdNotaEntrada;

                $itensNfEntrada->qtd = $dados['qtd'];
                $itensNfEntrada->vlrunitario = $dados['vlrunitario'];
                $itensNfEntrada->valortotal = $dados['valortotal'];

                $itensNfEntrada->salvar();

                $idProduto = $dados['fkproduto'];
                $qtdProduto = $dados['qtd'];

                $estoque = (new EstoqueModelo())->busca("fkproduto = {$idProduto}")->resultado(true);

                if (!empty($estoque)) {
                    foreach ($estoque as $item) {
                        $id_estoque = $item->id;
                        $quantidade = $item->quantidade;
                    }

                    $estoque = (new EstoqueModelo())->buscaPorId($id_estoque);

                    $estoque->quantidade = $quantidade + $qtdProduto;

                    $estoque->salvar();
                } else {
                    $estoque = new EstoqueModelo();
                    $estoque->fkproduto = $idProduto;
                    $estoque->quantidade = $qtdProduto;

                    $estoque->salvar();
                }
                $contaPagar = new ContasPagarModelo();

                $dataVencimento = $dataEmissao->modify('+30 days')->format('Y-m-d');

                $contaPagar->fknotaentrada = $ultimoIdNotaEntrada;
                $contaPagar->parcela = 1;
                $contaPagar->valor = $dados['valortotal'];
                $contaPagar->datavencimento = $dataVencimento;
                $contaPagar->databaixa = null;

                $contaPagar->salvar();
                $this->mensagem->sucesso("NF {$dados['numeronota']} Cadastrada com sucesso! Veja seus produtos no estoque!")->flash();

                Helpers::redirecionar('admin/consulta/estoque');
            }
        }
    }
}
