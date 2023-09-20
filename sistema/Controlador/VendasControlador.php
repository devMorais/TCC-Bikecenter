<?php

namespace sistema\Controlador;

use sistema\Nucleo\Controlador;
use sistema\Modelo\CarrinhoModelo;
use sistema\Modelo\ProdutoModelo;
use sistema\Nucleo\Helpers;
use sistema\Modelo\FormaPagamentoModelo;
use sistema\Modelo\VendaModelo;
use OpenBoleto\Banco\BancoDoBrasil;
use OpenBoleto\Agente;
use DateTime;
use DateInterval;
use sistema\Modelo\EnderecoModelo;
use sistema\Modelo\ItensVendaModelo;
use sistema\Modelo\ContasReceberModelo;
use sistema\Modelo\EntregaModelo;
use sistema\Modelo\BoletoModelo;
use sistema\Modelo\EstoqueModelo;

/**
 * Description of VendasControlador
 *
 * @author FernandoA
 */
class VendasControlador extends Controlador
{

    public function __construct()
    {
        parent::__construct('templates/site/views');
    }

    public function finalizar()
    {
        $id_usuario = UsuarioControlador::usuario()->id;

        $endereco = (new EnderecoModelo())->busca("fkusuario = {$id_usuario}")->total();

        if ($endereco == 0) {
            $this->mensagem->alerta('Cadastre um endereço de entrega!')->flash();
            Helpers::redirecionar('enderecos/cadastrar');
        }

        $carrinho = (new CarrinhoModelo())->busca("fkusuario = {$id_usuario} AND fkvenda = 0 ")->resultado(true);
        if (empty($carrinho)) {
            $this->mensagem->alerta('Seu carrinho está vazio! Adicione algum produto para finalizar!')->flash();
            Helpers::redirecionar();
        } else {
            $totalValorCarrinho = 0;
            $quantidadeTotal = 0;
            foreach ($carrinho as $item) {
                $id_produto = $item->fkproduto;
                $quantidade = $item->quantidade;
                $quantidadeTotal += $quantidade;

                $detalhesProdutoPorId = (new ProdutoModelo())->buscaPorId($id_produto);

                $valorItem = $quantidade * $detalhesProdutoPorId->valor;

                $totalValorCarrinho_item = $valorItem * $quantidade;
                $totalValorCarrinho += $totalValorCarrinho_item;
            }
            $totalValorCarrinhoFormatado = Helpers::formatarValor($totalValorCarrinho);
            $quantidadeProdutosFormatada = Helpers::formatarNumero($quantidadeTotal);
        }

        $forma_pagamento = (new FormaPagamentoModelo())->busca()->resultado(true);

        $endereco = (new EnderecoModelo())->busca("fkusuario = {$id_usuario} ")->resultado(true);

        echo $this->template->renderizar('finalizar/checkout.html', [
            'formasdepagamento' => $forma_pagamento,
            'valorCarrinho' => $totalValorCarrinhoFormatado,
            'QtdCarrinho' => $quantidadeProdutosFormatada,
            'enderecos' => $endereco
        ]);
    }

    public function efetuada()
    {

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            $venda = new VendaModelo();  //inserir na tabela venda

            $venda->fkusuario = $dados['id_usuario'];
            $venda->datavenda = date('Y-m-d');
            $venda->fkformapagamento = $dados['fkformadepagamento'];

            if ($venda->salvar()) {  // se foi inserido recuperar o id para inserir nas outras tabelas
                $ultimoIdInserido = $venda->id;

                $id_usuario = UsuarioControlador::usuario()->id;
                $carrinho = (new CarrinhoModelo())->busca("fkusuario = {$id_usuario}")->resultado(true);

                foreach ($carrinho as $item) {
                    $id_produto = $item->fkproduto;
                    $quantidade = $item->quantidade;
                    $detalhesProdutoPorId = (new ProdutoModelo())->buscaPorId($id_produto);

                    $valorItem = $detalhesProdutoPorId->valor;
                    $valorTotalItem = $valorItem * $quantidade;

                    //SALVA NA TABELA DE ITENS VENDIDOS
                    $itemVendido = new ItensVendaModelo(); //tabela de itens vendidos

                    $itemVendido->fkproduto = $id_produto;
                    $itemVendido->fkvenda = $ultimoIdInserido;
                    $itemVendido->quantidade = $quantidade;
                    $itemVendido->salvar();

                    $estoque = (new EstoqueModelo())->busca("fkproduto = {$id_produto} AND quantidade > 0")->resultado(true);

                    if (empty($estoque)) {

                        $id_usuario = UsuarioControlador::usuario()->id;
                        $carrinho = (new CarrinhoModelo())->busca("fkusuario = {$id_usuario}")->resultado(true);
                        foreach ($carrinho as $item) {

                            $idsCarrinho = (new CarrinhoModelo())->buscaPorId($id_carrinho = $item->id);

                            $idsCarrinho->deletar();
                        }


                        $itemVendido = (new ItensVendaModelo())->busca("fkvenda = {$ultimoIdInserido}")->resultado(true);

                        foreach ($itemVendido as $item) {
                            $id_item = $item->id;

                            $idsItens = (new ItensVendaModelo())->buscaPorId($id_item = $item->id);

                            $idsItens->deletar();
                        }


                        $venda = (new VendaModelo())->busca("id = {$ultimoIdInserido}")->resultado(true);

                        foreach ($venda as $item) {
                            $id_item = $item->id;

                            $idsItens = (new VendaModelo())->buscaPorId($id_item = $item->id);

                            $idsItens->deletar();
                        }

                        $this->mensagem->erro('Produto zerado em estoque!')->flash();
                        Helpers::redirecionar();
                    }
                    foreach ($estoque as $item) {
                        $id = $item->id;
                        $quantidadeEstoque = $item->quantidade;
                    }

                    $estoque = (new EstoqueModelo())->buscaPorId($id);

                    $estoque->fkproduto = $id_produto;

                    $estoque->quantidade = $quantidadeEstoque - $quantidade;

                    $estoque->salvar();
                }


                //SALVA NA TABELA CONTAS A RECEBER 
                $contaReceber = new ContasReceberModelo();

                $contaReceber->fkvenda = $ultimoIdInserido;
                //SOMENTE BOLETO UMA VEZ
                $contaReceber->parcela = 1;
                $contaReceber->valor = CarrinhoControlador::valorTotalCarrinho();

                $contaReceber->salvar();

                //SALVA NA TABELA DE ENTREGAS COM STATUS 1  -* Processamento do Pedido *-

                $entrega = new EntregaModelo(); // salvo na tabela de entreegas com -* Processamento do Pedido *-

                $entrega->desc_entrega = 'Em processamento';
                $entrega->fkstatusentrega = 1;
                $entrega->fkvenda = $ultimoIdInserido;
                $entrega->fkendereco = $dados['fkendereco'];
                $entrega->fkusuario = UsuarioControlador::usuario()->id;

                $entrega->salvar();

                //GERAR O BOLETO  AQUI VAI GERAR O BOLETO de acordo com as informaçõe do usuario
                //DADOS DO USUARIO
                $nome = UsuarioControlador::usuario()->nome;
                $cpf = UsuarioControlador::usuario()->cpf;
                $complemento = UsuarioControlador::usuario()->complemento;
                $cep = UsuarioControlador::usuario()->cep;

                $sacado = new Agente($nome, $cpf, $complemento, $cep, 'Brasília', 'DF');
                $cedente = new Agente(SITE_DESCRICAO, '02.123.123/0001-11', 'CLS 403 Lj 23', '71000-000', 'Brasília', 'DF');

                $dataVencimento = new DateTime();
                $dataVencimento->add(new DateInterval('P3D')); // Adiciona 3 dias

                $boleto = new BancoDoBrasil(array(
                    // Parâmetros obrigatórios
                    'dataVencimento' => $dataVencimento, // Usar a data modificada
                    'valor' => CarrinhoControlador::valorTotalCarrinho(),
                    'sequencial' => 1234567, // Para gerar o nosso número
                    'sacado' => $sacado,
                    'cedente' => $cedente,
                    'agencia' => 1724, // Até 4 dígitos
                    'carteira' => 18,
                    'conta' => 10403005, // Até 8 dígitos
                    'convenio' => 1234, // 4, 6 ou 7 dígitos
                ));

                $boletogerado = $boleto->getOutput();

                $tblBoleto = new BoletoModelo();

                $tblBoleto->fkvenda = $ultimoIdInserido;
                $tblBoleto->fkusuario = UsuarioControlador::usuario()->id;
                $tblBoleto->boleto = $boletogerado;

                $tblBoleto->salvar();

                //VAI EXCLUIR TODOS OS REGISTRO DO CARRINHO QUANDO A VENDA FOR EFETUADA        
                $id_usuario = UsuarioControlador::usuario()->id;
                $carrinho = (new CarrinhoModelo())->busca("fkusuario = {$id_usuario}")->resultado(true);
                foreach ($carrinho as $item) {
                    $id_carrinho = $item->id;
                    $id_produto = $item->fkproduto;
                    $quantidade = $item->quantidade;

                    $idsCarrinho = (new CarrinhoModelo())->buscaPorId($id_carrinho = $item->id);

                    $idsCarrinho->deletar();
                }

                // POR FIM FINALIZAR A VENDA

                $this->mensagem->sucesso("Venda efetuada! Clique em gerar boleto!")->flash();
                Helpers::redirecionar('conta');
            } else {
                $this->mensagem->erro($venda->erro())->flash();
            }
        }
    }

    public static function qtdProduto($idProduto): int
    {
        $quantidade = 0;

        $estoque = (new EstoqueModelo())->busca("fkproduto = {$idProduto}")->resultado(true);

        if (!empty($estoque)) {
            foreach ($estoque as $item) {
                $quantidade += $item->quantidade;
            }
        }

        return $quantidade;
    }
}
