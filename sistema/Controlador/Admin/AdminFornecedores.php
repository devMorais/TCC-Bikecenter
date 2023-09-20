<?php

namespace sistema\Controlador\Admin;

use sistema\Nucleo\Helpers;
use sistema\Modelo\FornecedorModelo;
use sistema\Modelo\NotaEntradaModelo;

/**
 * Description of AdminProdutos
 *
 * @author FernandoA
 */
class AdminFornecedores extends AdminControlador
{

    public function listar(): void
    {
        $fornecedor = new FornecedorModelo();
        echo $this->template->renderizar('fornecedores/listar.html', [
            'fornecedores' => $fornecedor->busca()->ordem('ID ASC')->resultado(true),
            'total' => [
                'total' => $fornecedor->total(),
                'ativo' => $fornecedor->total('status = 1'),
                'inativo' => $fornecedor->total('status = 0')
            ]
        ]);
    }

    public function cadastrar(): void
    {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {


            $fornecedor = new FornecedorModelo();

            $fornecedor->desc_fornecedor = $dados['desc_fornecedor'];

            $fornecedor->cnpj = $dados['cnpj'];
            $fornecedor->telefone = $dados['telefone'];
            $fornecedor->email = $dados['email'];
            $fornecedor->status = $dados['status'];

            if ($fornecedor->salvar()) {
                $this->mensagem->sucesso('Fornecedor cadastrado com sucesso')->flash();
                Helpers::redirecionar('admin/fornecedores/listar');
            } else {
                $this->mensagem->erro($fornecedor->erro())->flash();
                Helpers::redirecionar('admin/fornecedores/listar');
            }
        }

        echo $this->template->renderizar('fornecedores/formulario.html', []);
    }

    public function editar(int $id): void
    {

        $fornecedor = (new FornecedorModelo())->buscaPorID($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            $fornecedor = (new FornecedorModelo())->buscaPorID($fornecedor->id);

            $fornecedor->desc_fornecedor = $dados['desc_fornecedor'];

            $fornecedor->cnpj = $dados['cnpj'];
            $fornecedor->telefone = $dados['telefone'];
            $fornecedor->email = $dados['email'];
            $fornecedor->status = $dados['status'];

            if ($fornecedor->salvar()) {
                $this->mensagem->sucesso('Fornecedor atualizada com sucesso')->flash();
                Helpers::redirecionar('admin/fornecedores/listar');
            } else {
                $this->mensagem->erro($fornecedor->erro())->flash();
                Helpers::redirecionar('admin/fornecedores/listar');
            }
        }
        echo $this->template->renderizar('fornecedores/formulario.html', [
            'fornecedor' => $fornecedor
        ]);
    }

    /**
     * Deletar um forncedor por ID
     * @param int $id
     * @return void
     */
    public function deletar(int $id): void
    {
        if (is_int($id)) {
            $fornecedor = (new FornecedorModelo())->buscaPorId($id);
            if (!$fornecedor) {
                $this->mensagem->alerta('O fornecedor que você está tentando deletar não existe!')->flash();
                Helpers::redirecionar('admin/fornecedores/listar');
            }


            $nfEntrada = (new NotaEntradaModelo())->busca("fkfornecedor  = {$id}")->resultado(true);

            if ($nfEntrada) {
                $this->mensagem->alerta('A exclusão deste fornecedor não é permitida devido à presença de informações cruciais, incluindo histórico de notas de entrada e registros.')->flash();
                Helpers::redirecionar('admin/fornecedores/listar');
            }
            if ($fornecedor->deletar()) {
                $this->mensagem->sucesso('Fornecedor deletado com sucesso!')->flash();
                Helpers::redirecionar('admin/fornecedores/listar');
            } else {
                $this->mensagem->erro($fornecedor->erro())->flash();
                Helpers::redirecionar('admin/fornecedores/listar');
            }
        }
    }
}
