<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\MarcaModelo;
use sistema\Nucleo\Helpers;

/**
 * Description of AdminProdutos
 *
 * @author FernandoA
 */
class AdminMarcas extends AdminControlador
{

    public function listar(): void
    {
        $marcas = new MarcaModelo();
        echo $this->template->renderizar('marcas/listar.html', [
            'marcas' => $marcas->busca()->ordem('desc_marca ASC')->resultado(true),
            'total' => [
                'total' => $marcas->total(),
                'ativo' => $marcas->busca('status = 1')->total(),
                'inativo' => $marcas->busca('status = 0')->total(),
            ]
        ]);
    }

    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            if ($this->validarDados($dados)) {
                $marcas = new MarcaModelo();

                $marcas->desc_marca = $dados['desc_marca'];

                $marcas->status = $dados['status'];

                if ($marcas->salvar()) {
                    $this->mensagem->sucesso('Marca cadastrado com sucesso')->flash();
                    Helpers::redirecionar('admin/marcas/listar');
                } else {
                    $this->mensagem->erro($marcas->erro())->flash();
                    Helpers::redirecionar('admin/marcas/listar');
                }
            }
        }

        echo $this->template->renderizar('marcas/formulario.html', [
            'marca' => $dados
        ]);
    }
    
     private function validarDados(array $dados): bool
    {
        if (empty($dados['desc_marca'])) {
            $this->mensagem->alerta('Escreva uma descrição para a marca!')->flash();
            return false;
        }

        return true;
    }
    

    public function editar(int $id): void
    {

        $marcas = (new MarcaModelo())->buscaPorID($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            if ($this->validarDados($dados)) {

                $marcas = (new MarcaModelo())->buscaPorID($marcas->id);

                $marcas->desc_marca = $dados['desc_marca'];

                $marcas->status = $dados['status'];

                if ($marcas->salvar()) {
                    $this->mensagem->sucesso('Marca atualizada com sucesso')->flash();
                    Helpers::redirecionar('admin/marcas/listar');
                } else {
                    $this->mensagem->erro($marcas->erro())->flash();
                    Helpers::redirecionar('admin/marcas/listar');
                }
            }
        }



        echo $this->template->renderizar('marcas/formulario.html', [
            'marca' => $marcas
        ]);
    }

    public function deletar(int $id): void
    {
        if (is_int($id)) {
            $marcas = (new MarcaModelo())->buscaPorId($id);

            $produtos = (new \sistema\Modelo\ProdutoModelo())->busca("fkmarca = {$marcas->id}")->resultado(true);

            if (!$marcas) {
                $this->mensagem->alerta('a Categoria que você está tentando deletar não existe!')->flash();
                Helpers::redirecionar('admin/categorias/listar');
            } elseif ($produtos) {
                $this->mensagem->alerta("Na marca {$marcas->desc_marca}, há produtos cadastrados. Antes de realizar a exclusão, por favor, altere ou remova os produtos existentes.")->flash();
                Helpers::redirecionar('admin/marcas/listar');
            } else {
                if ($marcas->deletar()) {

                    $this->mensagem->sucesso('Categoria deletada com sucesso!')->flash();
                    Helpers::redirecionar('admin/marcas/listar');
                } else {
                    $this->mensagem->erro($marcas->erro())->flash();
                    Helpers::redirecionar('admin/marcas/listar');
                }
            }
        }
    }
}
