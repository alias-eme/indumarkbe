<?php

namespace corsica\indumark\pdf;

use corsica\indumark\importacion\Carpeta;
use corsica\indumark\importacion\CarpetaApoderado;
use corsica\indumark\importacion\CarpetaPago;
use corsica\framework\config\Config;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class PdfManager extends \corsica\framework\utils\Manager
{
    /**
     * @param string $formato
     * @param array $get
     * @param object $env
     */
    public static function print($formato, $get, $env)
    {
        self::info("print", ["formato" => $formato, "get" => $get]);



        switch ($formato) {
            case "aduana":
                $id = $get['id'];
                $c = new Carpeta($env);
                $data = $c->cargar($id);
                $a = new CarpetaApoderado($env, $c->getConexion());
                $apoderados = $a->cargar($id, true);
                self::info("aduana", ["data" => $data, "apoderados" => $apoderados]);

                $c = new Config($env);
                $logopath = $c->getPath([Config::ASSETS_PATH], 'logo.jpg');
                self::info("aduana", ["data" => $data, "logopath" => $logopath]);

                $pdf = new InformeAduanaPdf($data, $apoderados, $logopath);
                $pdf->print();
                break;
            case "declFinanciera":
                $id = $get['id'];
                $c = new Carpeta($env);
                $data = $c->cargar($id);
                $a = new CarpetaApoderado($env, $c->getConexion());
                $apoderados = $a->cargar($id, true);
                $pdf = new DeclaracionFinancieraPdf($data, $apoderados);
                $pdf->print();
                break;

            case "declValores":
                $id = $get['id'];
                $c = new Carpeta($env);
                $data = $c->cargar($id);
                $a = new CarpetaApoderado($env, $c->getConexion());
                $apoderados = $a->cargar($id, true);
                $pdf = new DeclaracionValoresPdf($data, $apoderados);
                $pdf->print();
                break;
            case "costeo":
                $id = $get['id'];
                $c = new Carpeta($env);
                $data = $c->cargar($id);
                $a = new CarpetaPago($env, $c->getConexion());
                $apoderados = $a->cargar($id);
                $c = new Config($env);
                $logopath = $c->getPath([Config::ASSETS_PATH], 'logo.jpg');
                $pdf = new CosteoPdf($data, $apoderados, $logopath);
                $pdf->print();
                break;
            case "instructivo":
                $id = $get['id'];
                $c = new Carpeta($env);
                $data = $c->cargar($id);
                $a = new CarpetaApoderado($env, $c->getConexion());
                $apoderados = $a->cargar($id, true);
                $pdf = new InstructivoPdf($data, $apoderados);
                $pdf->print();
                break;
            case "importaciones":






            default:
                self::error("print", ["formato" => $formato, "get" => $get]);
        }
    }
}