<?php

namespace corsica\framework\router;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use corsica\framework\config\Config;
use Exception;

/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class ApiRouter
{
    /** para cargar el archivo json 
     * tiene 3 datos env, idusuario e idperfil
     */

    private static $routes = null;
    /**
     * @param object/array with env, idusuario e idperfil
     */
    public final function __construct($env = null)
    {
        $this->env = $env;

        //cargo la configuración
        $arr = explode('\\', self::class);
        $name = $arr[count($arr) - 1];
        $path = realpath(dirname(__FILE__) . "/" . $name . ".json");
        $configarray = json_decode(file_get_contents($path), true);
        $this->params = $configarray;
    }

    /**
     * Para cargar los archivos de mapa de funciones
     * 
     */
    public static function getRouteMapFiles()
    {
        $routes = [
            "usuarios.json",
            "importacion.json"
        ];
        return $routes;
    }
    private static function loadRoutes()
    {
        if (!self::$routes) {
            $routeMapFiles = self::getRouteMapFiles();
            $basepath = dirname(__FILE__) . "/";
            self::$routes = [];
            foreach ($routeMapFiles as $routeMapFile) {
                $path = realpath($basepath . $routeMapFile);
                $configarray = json_decode(file_get_contents($path), true);
                self::$routes = array_merge(self::$routes, $configarray);
            }
        }
        return self::$routes;
    }
    /**
     * Ejecuta una funcion de una clase
     * @param object/array 'entidad','metodo','arg1','arg2'...'argn'
     */

    public static function execute($env, $data)
    {
        //return "pico";
        $entidades = ApiRouter::loadRoutes();
        //Verifica la entidad
        $entidad = $data->entidad;
        if (!array_key_exists($entidad, $entidades)) {
            throw new Exception("El nombre de entidad no se encuentra en json: [" . $entidad . "]");
        }
        //Verifica el método
        $metodo = $data->metodo;
        if (!array_key_exists($metodo, $entidades[$entidad]['metodos'])) {
            throw new Exception("El nombre de metodo no se encuentra en json: [" . $metodo . "] para la entidad [" . $entidad . "]");
        }

        //cargo el método y la clase

        $clase = $entidades[$entidad]['clase'];
        $clase = str_replace("/", "\\", $clase);

        $argnames = $entidades[$entidad]['metodos'][$metodo];

        $argumentos = null;
        $qargs = count($argnames);

        for ($i = 0; $i < $qargs; $i++) {
            if ($argumentos == null) {
                $argumentos = array();
            }
            $argumento = $argnames[$i];

            array_push($argumentos, $data->$argumento);
        }

        $object = new $clase($env);
        switch ($qargs) {
            case 0:
                $out = $object->$metodo();
                break;
            case 1:
                $out = $object->$metodo($argumentos[0]);
                break;
            case 2:
                $out = $object->$metodo($argumentos[0], $argumentos[1]);
                break;
            case 3:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2]);
                break;
            case 4:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3]);
                break;
            case 5:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3], $argumentos[4]);
                break;
            case 6:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3], $argumentos[4], $argumentos[5]);
                break;
            case 7:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3], $argumentos[4], $argumentos[5], $argumentos[6]);
                break;
            case 8:
                $out = $object->$metodo($argumentos[0], $argumentos[1], $argumentos[2], $argumentos[3], $argumentos[4], $argumentos[5], $argumentos[6], $argumentos[7]);
                break;
            default:
                throw new Exception("El numero de argumentos es mayor a 8");
                break;
        }
        return $out;
    }
}
