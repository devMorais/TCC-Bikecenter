<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe de Entregas
 *
 * @author FernandoA
 */
class EntregaModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('entrega');
    }

    public function statusEntrega(): ?StatusEntregaModelo
    {
        if ($this->fkstatusentrega) {
            return (new StatusEntregaModelo())->buscaPorId($this->fkstatusentrega);
        }
        return null;
    }

    public function venda(): ?VendaModelo
    {
        if ($this->fkvenda) {
            return (new VendaModelo())->buscaPorId($this->fkvenda);
        }
        return null;
    }

    public function endereco(): ?EnderecoModelo
    {
        if ($this->fkendereco) {
            return (new EnderecoModelo())->buscaPorId($this->fkendereco);
        }
        return null;
    }

    public function usuario(): ?UsuarioModelo
    {
        if ($this->fkusuario) {
            return (new UsuarioModelo())->buscaPorId($this->fkusuario);
        }
        return null;
    }
}
