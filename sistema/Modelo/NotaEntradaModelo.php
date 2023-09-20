<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe de Boletos
 *
 * @author FernandoA
 */
class NotaEntradaModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('notaentrada');
    }

    /**
     * Busca o fornecedor pelo ID
     * @return FornecedorModelo|null
     */
    public function fornecedor(): ?FornecedorModelo
    {
        if ($this->fkfornecedor) {
            return (new FornecedorModelo())->buscaPorID($this->fkfornecedor);
        }
        return null;
    }

    /**
     * Busca a forma de pagamento pelo ID
     * @return FormaPagamentoModelo|null
     */
    public function formapagamento(): ?FormaPagamentoModelo
    {
        if ($this->fkformapagamento) {
            return (new FormaPagamentoModelo())->buscaPorId($this->fkformapagamento);
        }
        return null;
    }
}
