<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe de Estoque de produtos
 *
 * @author FernandoA
 */
class EstoqueModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('estoque');
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
}
