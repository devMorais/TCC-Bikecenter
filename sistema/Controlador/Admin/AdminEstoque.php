<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\EstoqueModelo;
use sistema\Nucleo\Helpers;

/**
 * Classe AdminUsuarios
 *
 * @author Fernando A
 */
class AdminEstoque extends AdminControlador
{

    /**
     * Lista produtos
     * @return void
     */
    public function listar(): void
    {
        $estoque = new EstoqueModelo();
        echo $this->template->renderizar('estoque/listar.html', [
            'estoques' => $estoque->busca()->resultado(true),
        ]);
    }

    public function editar(int $id): void
    {

        $estoque = (new EstoqueModelo())->buscaPorID($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            $estoque = (new EstoqueModelo())->buscaPorID($estoque->id);

            $estoque->quantidade = $dados['quantidade'];

            if ($estoque->salvar()) {
                $this->mensagem->sucesso('Estoque atualizado com sucesso')->flash();
                Helpers::redirecionar('admin/consulta/estoque');
            } else {
                $this->mensagem->erro($estoque->erro())->flash();
                Helpers::redirecionar('admin/consulta/estoque');
            }
        }
        echo $this->template->renderizar('estoque/formulario.html', [
            'estoque' => $estoque
        ]);
    }
}
