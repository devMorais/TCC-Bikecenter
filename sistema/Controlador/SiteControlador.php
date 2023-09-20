<?php

namespace sistema\Controlador;

use sistema\Nucleo\Controlador;
use sistema\Modelo\ProdutoModelo;
use sistema\Modelo\CategoriaModelo;
use sistema\Nucleo\Helpers;
use sistema\Suporte\Email;
use sistema\Modelo\MarcaModelo;

class SiteControlador extends Controlador
{

    public function __construct()
    {
        parent::__construct('templates/site/views');
    }

    public function categorias(): array
    {
        $categoriaModelo = new CategoriaModelo();
        $categorias = $categoriaModelo->busca("status = 1")->resultado(true);

        if ($categorias) {
            return $categorias;
        } else {
            return [];
        }
    }

    public function categoria(int $id): void
    {
        $produtos = (new CategoriaModelo())->produtos($id);

        if (!$produtos) {
            $this->mensagem->alerta('Não há nenhum produto cadastrado para esta categoria!')->flash();
            Helpers::redirecionar();
        }

        $todosProdutosComEstoqueZerado = true;

        foreach ($produtos as $item) {
            $idsProdutos = $item->id;
            $quantidadeEstoque = VendasControlador::qtdProduto($idsProdutos);

            // Verifique se a quantidade em estoque é diferente de zero
            if ($quantidadeEstoque !== 0) {
                $todosProdutosComEstoqueZerado = false;
                break;
            }
        }

        if ($todosProdutosComEstoqueZerado) {

            $this->mensagem->alerta('Todos os produtos para esta categoria estão com estoque zerado.')->flash();
            Helpers::redirecionar();
        } else {
            $marcas = (new MarcaModelo())->busca()->resultado(true);

            echo $this->template->renderizar('categorias.html', [
                'produtos' => $produtos,
                'categorias' => $this->categorias(),
                'marcas' => $marcas
            ]);
        }
    }

    public function buscar(): void
    {
        $busca = filter_input(INPUT_POST, 'busca', FILTER_DEFAULT);
        if (isset($busca)) {
            $produtos = (new ProdutoModelo())->busca("status = 1 AND desc_produto LIKE '%{$busca}%' OR detalhes_produtos LIKE '%{$busca}%' ")->resultado(true);
            if ($produtos) {
                foreach ($produtos as $produto) {
                    echo "<li class='list-group-item fw-bold'><a href=" . Helpers::url('produto/') . $produto->id . ">$produto->desc_produto</a></li>";
                }
            }
        }
    }

    public function marca(int $id): void
    {
        $produtos = (new MarcaModelo())->produtos($id);

        if (!$produtos) {
            $this->mensagem->alerta('Não há nenhum produto cadastrado para esta marca!')->flash();
            Helpers::redirecionar();
        }

        $todosProdutosComEstoqueZerado = true;

        foreach ($produtos as $item) {
            $idsProdutos = $item->id;
            $quantidadeEstoque = VendasControlador::qtdProduto($idsProdutos);

            // Verifique se a quantidade em estoque é diferente de zero
            if ($quantidadeEstoque !== 0) {
                $todosProdutosComEstoqueZerado = false;
                break;
            }
        }

        if ($todosProdutosComEstoqueZerado) {

            $this->mensagem->alerta('Todos os produtos para esta categoria estão com estoque zerado.')->flash();
            Helpers::redirecionar();
        } else {
            $marcas = (new MarcaModelo())->busca()->resultado(true);

            echo $this->template->renderizar('listmarcas.html', [
                'produtos' => $produtos,
                'categorias' => $this->categorias(),
                'marcas' => $marcas
            ]);
        }
    }

    public function index(): void
    {

        $produtos = (new ProdutoModelo())->busca("status = 1 ")->ordem("id DESC")->limite(30);
        $categorias = (new CategoriaModelo())->busca("status = 1 ")->resultado(true);

        $marcas = (new MarcaModelo())->busca("status = 1 ")->resultado(true);

        echo $this->template->renderizar('index.html', [
            'slides' => $produtos->ordem("id DESC")->limite(5)->resultado(true),
            'produtos' => $produtos->ordem("id DESC")->limite(20)->offset(5)->resultado(true),
            'categorias' => $categorias,
            'marcas' => $marcas
        ]);
    }

    public function produto(int $id): void
    {

        $qtdProdutoEstoue = VendasControlador::qtdProduto($id);

        if (empty($qtdProdutoEstoue)) {
            $this->mensagem->alerta('Produto zerado em estoque!')->flash();
            Helpers::redirecionar();
        }

        $produto = (new ProdutoModelo())->buscaPorID($id);

        $categorias = (new CategoriaModelo())->busca("status = 1 ")->resultado(true);
        $marcas = (new MarcaModelo())->busca();
        if (!$produto) {
            Helpers::redirecionar('404');
        }

        $produto->visitas += 1;
        $produto->ultima_visita_em = date('Y-m-d H:i:s');
        $produto->salvar();

        echo $this->template->renderizar('detalheproduto.html', [
            'produto' => $produto,
            'categorias' => $categorias,
            'marcas' => $marcas
        ]);
    }

    public function sobre(): void
    {
        $categorias = (new CategoriaModelo())->busca("status = 1 ")->resultado(true);
        echo $this->template->renderizar('sobre.html', [
            'categorias' => $categorias
        ]);
    }

    public function erro404(): void
    {
        echo $this->template->renderizar('404.html', [
            'titulo' => 'Página não econtrada'
        ]);
    }

    /**
     * Contato
     * @return void
     */
    public function contato(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        if (isset($dados)) {
            if (in_array('', $dados)) {
                Helpers::json('erro', 'Preencha todos os campos!');
            } elseif (!Helpers::validarEmail($dados['email'])) {
                Helpers::json('erro', 'E-mail inválido!');
            } else {
                try {
                    $email = new Email();

                    $view = $this->template->renderizar('emails/contato.html', [
                        'dados' => $dados,
                    ]);

                    $email->criar(
                            'Contato via Site - ' . SITE_NOME,
                            $view,
                            EMAIL_REMETENTE['email'],
                            EMAIL_REMETENTE['nome'],
                            $dados['email'],
                            $dados['nome']
                    );

                    $anexos = $_FILES['anexos'];

                    foreach ($anexos['tmp_name'] as $indice => $anexo) {
                        if (!$anexo == UPLOAD_ERR_OK) {
                            $email->anexar($anexo, $anexos['name'][$indice]);
                        }
                    }
                    $email->enviar(EMAIL_REMETENTE['email'], EMAIL_REMETENTE['nome']);

                    Helpers::json('successo', 'E-mail enviado com sucesso!');
                } catch (\PHPMailer\PHPMailer\Exception $ex) {
                    $this->mensagem->alerta($ex->getMessage())->flash();
                }
            }
        }

        echo $this->template->renderizar('contato.html', [
            'categorias' => $this->categorias(),
        ]);
    }
    
    public function privacidade()
    {
         echo $this->template->renderizar('privacidade.html', [
            'categorias' => $this->categorias(),
        ]);
        
    }
}
