<?php

namespace sistema\Controlador\Admin;

use sistema\Modelo\ContasPagarModelo;
use sistema\Nucleo\Helpers;

/**
 * Classe AdminUsuarios
 *
 * @author Fernando A
 */
class AdminContasPagar extends AdminControlador
{

    /**
     * Lista usuários
     * @return void
     */
    public function listar(): void
    {
        $contaspagar = new ContasPagarModelo();

        echo $this->template->renderizar('contaspagar/listar.html', [
            //'formapagamento' => $formapagamento->busca()->resultado(true),
            'contaspagar' => $contaspagar->busca()->resultado(true),
            'total' => [
                'receber' => $contaspagar->busca()->total(),
            ]
        ]);
    }

    public function editar(int $id): void
    {

        $pagar = (new ContasPagarModelo())->buscaPorId($id);

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            
            if(empty($dados['databaixa'])){
                $this->mensagem->alerta('Você deve preencher a data do pagamento da NF!')->flash();
                Helpers::redirecionar('admin/contas/pagar');
            }
            
            $pagar = (new ContasPagarModelo())->buscaPorID($pagar->id);

            $pagar->databaixa = $dados['databaixa'];

            if ($pagar->salvar()) {
                $this->mensagem->sucesso('Data de pagamento atualizada com sucesso')->flash();
                //Helpers::redirecionar('admin/consulta/estoque');
            } else {
                $this->mensagem->erro($pagar->erro())->flash();
                //Helpers::redirecionar('admin/consulta/estoque');
            }
        }
        echo $this->template->renderizar('contaspagar/formulario.html', [
            'pagar' => $pagar
        ]);
    }
}
