<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Description of EnderecoModelo
 *
 * @author FernandoA
 */
class EnderecoModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('endereco');
    }

    public function tipoendereco(): ?EnderecoModelo
    {
        if ($this->fktipoendereco) {
            return (new EnderecoModelo())->buscaPorId($this->fktipoendereco);
        }
        return null;
    }
    
    
}
