<?php

namespace sistema\Modelo;

use sistema\Nucleo\Modelo;

/**
 * Classe PostModelo
 *
 * @author Fernando Aguiar
 */
class ProdutoModelo extends Modelo
{

    public function __construct()
    {
        parent::__construct('produto');
//        parent::__construct('produto_fake');
    }

    /**
     * Busca a categoria pelo ID
     * @return CategoriaModelo|null
     */
    public function categoria(): ?CategoriaModelo
    {
        if ($this->fkcategoria) {
            return (new CategoriaModelo())->buscaPorId($this->fkcategoria);
        }
        return null;
    }

    /**
     * Busca o usuário pelo ID
     * @return UsuarioModelo|null
     */
    public function usuario(): ?UsuarioModelo
    {
        if ($this->fkusuario) {
            return (new UsuarioModelo())->buscaPorId($this->fkusuario);
        }
        return null;
    }
}
