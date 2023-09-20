<?php

use Pecee\SimpleRouter\SimpleRouter;
use sistema\Nucleo\Helpers;

try {
//ROTAS DO SITE
    SimpleRouter::setDefaultNamespace('sistema\Controlador');
    SimpleRouter::get(URL_SITE, 'SiteControlador@index');
    SimpleRouter::get(URL_SITE . 'sobre', 'SiteControlador@sobre');
    SimpleRouter::get(URL_SITE . 'privacidade', 'SiteControlador@privacidade');
    SimpleRouter::get(URL_SITE . 'produto/{id}', 'SiteControlador@produto');
    SimpleRouter::get(URL_SITE . 'categoria/{id}/{pagina?}', 'SiteControlador@categoria');
    SimpleRouter::get(URL_SITE . 'marca/{id}', 'SiteControlador@marca');
    SimpleRouter::post(URL_SITE . 'buscar', 'SiteControlador@buscar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'contato', 'SiteControlador@contato');

    SimpleRouter::get(URL_SITE . 'boleto', 'VendasControlador@efetuada');

   SimpleRouter::get(URL_SITE . '404', 'SiteControlador@erro404');

//ROTAS DO USUARIO LOGADO E ROTAS DE CADASTRO DO USUARIO CLIENTE
    SimpleRouter::match(['get', 'post'], URL_SITE . 'cadastro', 'UsuarioControlador@cadastro');

    SimpleRouter::match(['get', 'post'], URL_SITE . 'esqueceu/senha', 'UsuarioControlador@senha');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'confirmar', 'UsuarioControlador@confirmar');

    SimpleRouter::match(['get', 'post'], URL_SITE . 'enderecos/cadastrar', 'UsuarioControlador@cadastrar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'login', 'UsuarioControlador@login');
    SimpleRouter::get(URL_SITE . 'sair', 'UsuarioControlador@sair');
    SimpleRouter::get(URL_SITE . 'conta', 'UsuarioControlador@conta');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'enderecos/editar{id}', 'UsuarioControlador@editar');
    SimpleRouter::get(URL_SITE . 'endereco', 'UsuarioControlador@endereco');
    SimpleRouter::get(URL_SITE . 'enderecos/deletar/{id}', 'UsuarioControlador@deletar');
    SimpleRouter::get(URL_SITE . 'perfil', 'UsuarioControlador@listarPerfil');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'usuarios/editar{id}', 'UsuarioControlador@editarPerfil');
    SimpleRouter::get(URL_SITE . 'pedidos', 'UsuarioControlador@pedidos');

    SimpleRouter::get(URL_SITE . 'boletos', 'BoletoControlador@gerar');

    SimpleRouter::match(['get', 'post'], URL_SITE . 'listar/itens', 'UsuarioControlador@itens');

//CARRINHO
    SimpleRouter::match(['get', 'post'], URL_SITE . 'carrinho/inserir', 'CarrinhoControlador@inserir');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'produto/carrinho/inserir', 'CarrinhoControlador@inserir');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'marca/carrinho/inserir', 'CarrinhoControlador@inserir');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'categoria/carrinho/inserir', 'CarrinhoControlador@inserir');

    SimpleRouter::match(['get', 'post'], URL_SITE . 'carrinho/listar', 'CarrinhoControlador@listar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'produto/carrinho/listar', 'CarrinhoControlador@listar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'marca/carrinho/listar', 'CarrinhoControlador@listar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'categoria/carrinho/listar', 'CarrinhoControlador@listar');

    SimpleRouter::match(['get', 'post'], URL_SITE . 'carrinho/excluir', 'CarrinhoControlador@deletar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'produto/carrinho/excluir', 'CarrinhoControlador@deletar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'marca/carrinho/excluir', 'CarrinhoControlador@deletar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'categoria/carrinho/excluir', 'CarrinhoControlador@deletar');

    SimpleRouter::match(['get', 'post'], URL_SITE . 'carrinho/editar{id}', 'CarrinhoControlador@editar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'produto/carrinho/editar{id}', 'CarrinhoControlador@editar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'marca/carrinho/editar{id}', 'CarrinhoControlador@editar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'categoria/carrinho/editar{id}', 'CarrinhoControlador@editar');

    SimpleRouter::match(['get', 'post'], URL_SITE . 'finalizar', 'VendasControlador@finalizar');
    SimpleRouter::match(['get', 'post'], URL_SITE . 'compra/efetuada', 'VendasControlador@efetuada');

    //ROTAS DO PAINEL ADMINISTRATIVO
    SimpleRouter::group(['namespace' => 'Admin'], function () {

        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'login', 'AdminLogin@login');

        //DASHBOARD
        SimpleRouter::get(URL_ADMIN . 'dashboard', 'AdminDashboard@dashboard');
        SimpleRouter::get(URL_ADMIN . 'sair', 'AdminDashboard@sair');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'listar/itens', 'AdminDashboard@itens');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'dashboard/listar/itens', 'AdminDashboard@itens');

//ADMIN PRODUTOS
        SimpleRouter::get(URL_ADMIN . 'produtos/listar', 'AdminProdutos@listar');
        //CADASTRAR/ATUALIZAR
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'produtos/cadastrar', 'AdminProdutos@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'produtos/editar/{id}', 'AdminProdutos@editar');
        SimpleRouter::get(URL_ADMIN . 'produtos/deletar/{id}', 'AdminProdutos@deletar');
        

//ADMIN CATEGORIAS
        SimpleRouter::get(URL_ADMIN . 'categorias/listar', 'AdminCategorias@listar');
        //CADASTRAR/ATUALIZAR
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'categorias/cadastrar', 'AdminCategorias@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'categorias/editar/{id}', 'AdminCategorias@editar');
        SimpleRouter::get(URL_ADMIN . 'categorias/deletar/{id}', 'AdminCategorias@deletar');

        //ADMIN FORNECEDOR
        SimpleRouter::get(URL_ADMIN . 'fornecedores/listar', 'AdminFornecedores@listar');
        //CADASTRAR/ATUALIZAR
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'fornecedores/cadastrar', 'AdminFornecedores@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'fornecedores/editar/{id}', 'AdminFornecedores@editar');
        SimpleRouter::get(URL_ADMIN . 'fornecedores/deletar/{id}', 'AdminFornecedores@deletar');

//ADMIN MARCAS
        SimpleRouter::get(URL_ADMIN . 'marcas/listar', 'AdminMarcas@listar');
        //CADASTRAR/ATUALIZAR
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'marcas/cadastrar', 'AdminMarcas@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'marcas/editar/{id}', 'AdminMarcas@editar');
        SimpleRouter::get(URL_ADMIN . 'marcas/deletar/{id}', 'AdminMarcas@deletar');

//ADMIN USUARIOS
        SimpleRouter::get(URL_ADMIN . 'usuarios/listar', 'AdminUsuarios@listar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'usuarios/cadastrar', 'AdminUsuarios@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'usuarios/editar/{id}', 'AdminUsuarios@editar');
        SimpleRouter::get(URL_ADMIN . 'usuarios/deletar/{id}', 'AdminUsuarios@deletar');

//CADASTRAR/ATUALIZAR
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'usuarios/cadastrar', 'AdminUsuarios@cadastrar');

        //ADMIN ENTREGAS
        SimpleRouter::get(URL_ADMIN . 'entregas/listar', 'AdminEntregas@listar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'entregas/listar/itens', 'AdminEntregas@itens');

        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'entregas/editar/{id}', 'AdminEntregas@editar');

        //ADMIN VENDAS
        SimpleRouter::get(URL_ADMIN . 'vendas/listar', 'AdminVendas@listar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'vendas/listar/itens', 'AdminVendas@itens');

        //CONTAS À RECEBER
        SimpleRouter::get(URL_ADMIN . 'contasreceber/listar', 'AdminContasReceber@listar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'contasreceber/listar/itens', 'AdminContasReceber@itens');

        //NOTA FISCAL DE ENTRADA

        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'nf/cadastrar', 'AdminNotaEntrada@cadastrar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'nf/pagar', 'AdminNotaEntrada@pagar');

        //ESTOQUE
        SimpleRouter::get(URL_ADMIN . 'consulta/estoque', 'AdminEstoque@listar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'estoque/editar/{id}', 'AdminEstoque@editar');

        //CONTAS PAGAR
        SimpleRouter::get(URL_ADMIN . 'contas/pagar', 'AdminContasPagar@listar');
        SimpleRouter::match(['get', 'post'], URL_ADMIN . 'contaspagar/editar/{id}', 'AdminContasPagar@editar');
    });

    SimpleRouter::start();
} catch (Pecee\SimpleRouter\Exceptions\NotFoundHttpException $ex) {

    if (Helpers::localhost()) {
        echo $ex->getMessage();
    } else {
        Helpers::redirecionar('404');
    }
}
