<?php

namespace sistema\Controlador;

use sistema\Nucleo\Controlador;
use sistema\Modelo\BoletoModelo;
use Dompdf\Dompdf;
use Dompdf\Options;
use sistema\Nucleo\Helpers;

class BoletoControlador extends Controlador
{

    public function __construct()
    {
        parent::__construct('templates/site/views');
    }

    public function gerar()
    {

        $id_usuario = UsuarioControlador::usuario()->id;

        $boleto = (new BoletoModelo())->busca("fkusuario = {$id_usuario}")->resultado(true);

        if (empty($boleto)) {
            $this->mensagem->alerta('Finalize uma venda para gerar seu boleto!')->flash();
            Helpers::redirecionar();
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        foreach ($boleto as $item) {
            $dompdf = new Dompdf($options);
            $html = $item->boleto;
            $dompdf->loadHtml($html);
            $dompdf->render();
            $pdfFilename = 'boleto_' . time() . '.pdf';
            $dompdf->stream();
        }

        $boleto = (new BoletoModelo())->busca("fkusuario = {$id_usuario}")->resultado(true);

        foreach ($boleto as $item) {
            $ultimo_id_boleto = $item->id;
        }

        $boleto = (new BoletoModelo())->buscaPorId($ultimo_id_boleto);
        $boleto->deletar();
    }
}
