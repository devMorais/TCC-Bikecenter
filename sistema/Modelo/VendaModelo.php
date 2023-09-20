<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe de vendas
 *
 * @author FernandoA
 */
class VendaModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('venda');
    }

    public function usuario(): ?UsuarioModelo
    {
        if ($this->fkusuario) {
            return (new UsuarioModelo())->buscaPorId($this->fkusuario);
        }
        return null;
    }

    public function pagamento(): ?FormaPagamentoModelo
    {
        if ($this->fkformapagamento) {
            return (new FormaPagamentoModelo())->buscaPorId($this->fkformapagamento);
        }
        return null;
    }
}
