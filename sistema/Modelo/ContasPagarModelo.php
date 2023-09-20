<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe de Contas Para Pagar
 *
 * @author FernandoA
 */
class ContasPagarModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('contaspagar');
    }

    /**
     * Busca a nota de entrada pelo ID
     * @return NotaEntradaModelo|null
     */
    public function notaEntrada(): ?NotaEntradaModelo
    {
        if ($this->fknotaentrada) {
            return (new NotaEntradaModelo())->buscaPorId($this->fknotaentrada);
        }
        return null;
    }
}
