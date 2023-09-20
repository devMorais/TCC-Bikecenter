<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\CategoriaModelo;
use sistema\Nucleo\Helpers;

/**
 * Description of AdminCategorias
 *
 * @author FernandoA
 */
class AdminCategorias extends AdminControlador
{

    public function listar(): void
    {
        $categoria = new CategoriaModelo();
        echo $this->template->renderizar('categorias/listar.html', [
            'categorias' => $categoria->busca()->ordem('desc_categoria ASC')->resultado(true),
            'total' => [
                'total' => $categoria->total(),
                'ativo' => $categoria->busca('status = 1')->total(),
                'inativo' => $categoria->busca('status = 0')->total(),
            ]
        ]);
    }

    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            if ($this->validarDados($dados)) {
                $categorias = new CategoriaModelo();

                $categorias->desc_categoria = $dados['desc_categoria'];

                $categorias->status = $dados['status'];

                if ($categorias->salvar()) {
                    $this->mensagem->sucesso('Categoria cadastrado com sucesso')->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                } else {
                    $this->mensagem->erro($categorias->erro())->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                }
            }
        }

        echo $this->template->renderizar('categorias/formulario.html', [
            'categoria' => $dados
        ]);
    }

    public function editar(int $id): void
    {

        $categoria = (new CategoriaModelo())->buscaPorID($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            if ($this->validarDados($dados)) {

                $categoria = (new CategoriaModelo())->buscaPorID($categoria->id);

                $categoria->desc_categoria = $dados['desc_categoria'];

                $categoria->status = $dados['status'];

                if ($categoria->salvar()) {
                    $this->mensagem->sucesso('Categoria atualizada com sucesso')->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                } else {
                    $this->mensagem->erro($categoria->erro())->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                }
            }
        }



        echo $this->template->renderizar('categorias/formulario.html', [
            'categoria' => $categoria
        ]);
    }

    private function validarDados(array $dados): bool
    {
        if (empty($dados['desc_categoria'])) {
            $this->mensagem->alerta('Escreva uma descrição para a categoria!')->flash();
            return false;
        }

        return true;
    }

    public function deletar(int $id): void
    {
        if (is_int($id)) {
            $categorias = (new CategoriaModelo())->buscaPorId($id);

            $produtos = (new \sistema\Modelo\ProdutoModelo())->busca("fkcategoria = {$categorias->id}")->resultado(true);

            if (!$categorias) {
                $this->mensagem->alerta('a Categoria que você está tentando deletar não existe!')->flash();
                Helpers::redirecionar('admin/categorias/listar');
            } elseif ($produtos) {
                $this->mensagem->alerta("Na categoria {$categorias->desc_categoria}, há produtos cadastrados. Antes de realizar a exclusão, por favor, altere ou remova os produtos existentes.")->flash();
                Helpers::redirecionar('admin/categorias/listar');
            } else {
                if ($categorias->deletar()) {

                    $this->mensagem->sucesso('Categoria deletada com sucesso!')->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                } else {
                    $this->mensagem->erro($categorias->erro())->flash();
                    Helpers::redirecionar('admin/categorias/listar');
                }
            }
        }
    }
}
