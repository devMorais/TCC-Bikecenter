<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe de Categoria
 *
 * @author FernandoA
 */
class CategoriaModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('categoria');
    }

    public function produtos(int $id): ?array
    {
        $busca = (new ProdutoModelo())->busca("fkcategoria = {$id}");
        return $busca->resultado(true);
    }
}
