<?php

namespace corsica\framework\router;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use corsica\framework\utils\Token;
use corsica\indumark\pdf\PdfManager;
use Exception;

/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class ApiGet
{
    /** para cargar el archivo json 
     * tiene 3 datos env, idusuario e idperfil
     */

    private static $routes = null;
    /**
     * @param object/array with env, idusuario e idperfil
     */


    /**
     * Para cargar los archivos de mapa de funciones
     * @param Array $get _GET
     */
    public static function route($get)
    {
        $token = $get['token'];
        $t = new Token();
        $env = $t->getEnv($token);
        $formato = $get['formato'];
        PdfManager::print($formato, $get, $env);
    }
}
