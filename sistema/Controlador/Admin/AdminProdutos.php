<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\ProdutoModelo;
use sistema\Modelo\CategoriaModelo;
use sistema\Nucleo\Helpers;
use sistema\Modelo\MarcaModelo;
use Verot\Upload\Upload;
use sistema\Controlador\VendasControlador;
use sistema\Modelo\EstoqueModelo;
use sistema\Modelo\ItensNotaEntradaModelo;

/**
 * Classe AdminPosts
 *
 * @author Fernando Aguiar
 */
class AdminProdutos extends AdminControlador
{

    private string $imagem;

//    public function datatable(): void
//    {
//
//        $datatable = $_REQUEST;
//
//        $datatable = filter_var_array($datatable, FILTER_SANITIZE_STRING);
//
//        $limite = $datatable['length'];
//        $offset = $datatable['start'];
//        $busca = $datatable['search']['value'];
//
//        $colunas = [
//            0 => 'id',
//            2 => 'desc_produto',
//            3 => 'fkcategoria',
//            4 => 'valor',
//            5 => 'status',
//        ];
//
//        $ordem = "  " . $colunas[$datatable['order'][0]['column']] . "  ";
//        $ordem .= "  " . $datatable['order'][0]['dir'] . "  ";
//
//        $produtos = new ProdutoModelo();
//
//        if (empty($busca)) {
//            $produtos->busca()->ordem($ordem)->limite($limite)->offset($offset);
//            $total = (new ProdutoModelo())->busca(null, 'COUNT(id)', 'id')->total();
//        } else {
//            $produtos->busca("id LIKE '%{$busca}%' OR desc_produto LIKE '%{$busca}%' ")->limite($limite)->offset($offset);
//            $total = $produtos->total();
//        }
//
//
//
//
//        $dados = [];
//
//        foreach ($produtos->resultado(true) as $produto) {
//            $dados[] = [
//                $produto->id,
//                $produto->imagem,
//                $produto->desc_produto,
//                $produto->categoria()->desc_categoria ?? '-----',
//                Helpers::formatarValor($produto->valor),
//                $produto->status
//            ];
//        }
//
//        $retorno = [
//            "draw" => $datatable['draw'],
//            "recordsTotal" => $total,
//            "recordsFiltered" => $total,
//            "data" => $dados
//        ];
//
//        echo json_encode($retorno);
//    }

    /**
     * Lista produtos
     * @return void
     */
    public function listar(): void
    {
        $produtos = new ProdutoModelo();

        echo $this->template->renderizar('produtos/listar_1.html', [
            'produtos' => $produtos->busca()->ordem('status ASC, id DESC')->resultado(true),
            'total' => [
                'produtos' => $produtos->total(),
                'produtosAtivo' => $produtos->busca('status = 1')->total(),
                'produtosInativo' => $produtos->busca('status = 0')->total()
            ]
        ]);
    }

    /**
     * Cadastra Produtos
     * @return void
     */
    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (isset($dados)) {
            if ($this->validarDados($dados)) {
                if (empty($dados['valor'])) {
                    $this->mensagem->alerta("O campo valor não pode estar em branco")->flash();
                    Helpers::redirecionar('admin/produtos/cadastrar');
                }

                $produtos = new ProdutoModelo();
                $produtos->fkmarca = $dados['fkmarca'];
                $produtos->desc_produto = $dados['desc_produto'];
                $produtos->fkcategoria = $dados['fkcategoria'];
                $produtos->detalhes_produtos = $dados['detalhes_produtos'];
                $produtos->valor = $dados['valor'];
                $produtos->status = $dados['status'];
                $produtos->imagem = $this->imagem;

                if ($produtos->salvar()) {
                    $this->mensagem->sucesso('Produto cadastrado com sucesso! Entre com ele no estoque!')->flash();
                    Helpers::redirecionar('admin/nf/cadastrar');
                } else {
                    $this->mensagem->erro($produtos->erro())->flash();
                    Helpers::redirecionar('admin/produtos/listar');
                }
            }
        }

        echo $this->template->renderizar('produtos/formulario.html', [
            'categorias' => (new CategoriaModelo())->busca()->resultado(true),
            'produto' => $dados,
            'marcas' => (new MarcaModelo())->busca()->resultado(true)
        ]);
    }

    /**
     * Edita produto pelo ID
     * @param int $id
     * @return void
     */
    public function editar(int $id): void
    {
        $produtos = (new ProdutoModelo())->buscaPorId($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            if ($this->validarDados($dados)) {
                $produtos = (new ProdutoModelo())->buscaPorId($id);

                $produtos->fkmarca = $dados['fkmarca'];
                $produtos->desc_produto = $dados['desc_produto'];

                $produtos->fkcategoria = $dados['fkcategoria'];
                $produtos->detalhes_produtos = $dados['detalhes_produtos'];
                $produtos->valor = $dados['valor'];
                $produtos->status = $dados['status'];

                if (!empty($_FILES['imagem'])) {
                    if ($produtos->imagem && file_exists("uploads/imagens/{$produtos->imagem}")) {
                        unlink("uploads/imagens/{$produtos->imagem}");
                        unlink("uploads/imagens/thumbs/{$produtos->imagem}");
                    }
                    $produtos->imagem = $this->imagem;
                }

                if ($produtos->salvar()) {
                    $this->mensagem->sucesso('Produto atualizado com sucesso')->flash();
                    Helpers::redirecionar('admin/produtos/listar');
                } else {
                    $this->mensagem->erro($produtos->erro())->flash();
                    Helpers::redirecionar('admin/produtos/listar');
                }
            }
        }

        echo $this->template->renderizar('produtos/formulario.html', [
            'produto' => $produtos,
            'categorias' => (new CategoriaModelo())->busca()->resultado(true),
            'marcas' => (new MarcaModelo())->busca()->resultado(true)
        ]);
    }

    /**
     * Valida os dados do formulário
     * @param array $dados
     * @return bool
     */
    public function validarDados(array $dados): bool
    {
        if (empty($_FILES['imagem']['tmp_name'])) {
            $this->mensagem->alerta("Por favor, escolha uma imagem")->flash();
            return false;
        }

        $upload = new Upload($_FILES['imagem'], 'pt_BR');

        if ($upload->uploaded) {
            $titulo = preg_replace('/[^a-zA-Z0-9]/', '', $dados['desc_produto']);
            $upload->file_new_name_body = $titulo;
            $upload->jpeg_quality = 90;
            $upload->image_convert = 'jpg';
            $upload->process('uploads/imagens/');

            if ($upload->processed) {
                $this->imagem = $upload->file_dst_name;
                $upload->file_new_name_body = $titulo;
                $upload->image_resize = true;
                $upload->image_x = 540;
                $upload->image_y = 304;
                $upload->jpeg_quality = 70;
                $upload->image_convert = 'jpg';
                $upload->process('uploads/imagens/thumbs/');
                $upload->clean();
            } else {
                $this->mensagem->alerta($upload->error)->flash();
                return false;
            }
        } else {
            $this->mensagem->alerta("Erro no upload da imagem")->flash();
            return false;
        }

        return true;
    }

    /**
     * Deleta posts por ID
     * @param int $id
     * @return void
     */
    public function deletar(int $id): void
    {
        if (is_int($id)) {
            $produtos = (new ProdutoModelo())->buscaPorId($id);
            if (!$produtos) {
                $this->mensagem->alerta('O Produto que você está tentando deletar não existe!')->flash();
                Helpers::redirecionar('admin/produtos/listar');
            } else {

                $quantidadeEmEstoque = VendasControlador::qtdProduto($id);

                if ($quantidadeEmEstoque) {
                    $this->mensagem->alerta("Este produto têm " . $quantidadeEmEstoque . " unidade(s) em estoque, não pode ser deletado.")->flash();
                    Helpers::redirecionar('admin/produtos/listar');
                } elseif (empty($quantidadeEmEstoque)) {

                    $estoque = (new EstoqueModelo())->busca("fkproduto = {$id}")->resultado(true);
                    if (!empty($estoque)) {
                        foreach ($estoque as $item) {
                            $idEstoque = $item->id;
                        }
                        $estoque = (new EstoqueModelo())->buscaPorId($idEstoque);

                        $estoque->deletar();
                    }
                }

                $entrada = (new ItensNotaEntradaModelo())->busca("fkproduto = {$id}")->resultado(true);

                if (!empty($entrada)) {

                    $this->mensagem->alerta("Este produto não pode ser excluído devido ao registro de movimentações associadas a ele. A exclusão resultaria na perda de informações importantes, como itens da nota, valor unitário e valor total.")->flash();
                    Helpers::redirecionar('admin/produtos/listar');
                } else {
                    if ($produtos->deletar()) {

                        if ($produtos->imagem && file_exists("uploads/imagens/{$produtos->imagem}")) {
                            unlink("uploads/imagens/{$produtos->imagem}");
                            unlink("uploads/imagens/thumbs/{$produtos->imagem}");
                        }

                        $this->mensagem->sucesso('Produto deletado com sucesso!')->flash();
                        Helpers::redirecionar('admin/produtos/listar');
                    } else {
                        $this->mensagem->erro($produtos->erro())->flash();
                        Helpers::redirecionar('admin/produtos/listar');
                    }
                }
            }
        }
    }
}
