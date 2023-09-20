<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe de Itens de Entrada Modelo
 *
 * @author FernandoA
 */
class ItensNotaEntradaModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('itens_nota_entrada');
    }

    /**
     * Busca o produto pelo ID
     * @return ProdutoModelo|null
     */
    public function produto(): ?ProdutoModelo
    {
        if ($this->fkproduto) {
            return (new ProdutoModelo())->buscaPorId($this->fkproduto);
        }
        return null;
    }

    /**
     * Busca a forma a NF de entrada pelo ID
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
