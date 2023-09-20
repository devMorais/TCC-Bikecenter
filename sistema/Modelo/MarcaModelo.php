<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe de Marcas
 *
 * @author FernandoA
 */
class MarcaModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('marca');
    }

    public function produtos(int $id): ?array
    {
        $busca = (new ProdutoModelo())->busca("fkmarca = {$id}");
        return $busca->resultado(true);
    }
}
