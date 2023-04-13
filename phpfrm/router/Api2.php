<?php

namespace corsica\framework\router;

use Exception;

/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class Api2
{
    const POST = 'POST';
    const GET = 'GET';
    const PUT = 'PUT';
    const DELETE = 'DELETE';


    private static $localapikey = "a3c139f7-19bd-49e1-890a-c04ccc594919";


    /**
     * Ejecuta una funcion de una clase
     * @param object/array 'entidad','metodo','arg1','arg2'...'argn'
     */
    public static function execute($apikey, $resource, $id, $method, $data)
    {
        try {
            $env = self::checkApiKey($apikey);
            return self::run($resource, $env, $id, $method, $data);
        } catch (Exception $e) {
            return (object)["error" => $e->getMessage()];
        }
    }
    /**
     * @return object
     */
    private static function getResource($resource, $env)
    {
        $path = realpath(dirname(__FILE__) . "/api2resources.json");
        $resources = json_decode(file_get_contents($path), true);
        $classname = $resources[$resource];
        if (is_null($classname)) {
            throw new Exception("resource '" . $resource . "' not found");
        } else {
            $classname = str_replace("/", "\\", $classname);
            $object = new $classname($env);
            return $object;
        }
    }
    private static function run($resource, $env, $id, $method, $data)
    {
        $iresource = self::getResource($resource, $env);

        if ($id == null) {
            switch ($method) {
                case self::POST:
                    return $iresource->add($data);
                case self::GET:
                    return $iresource->list();
            }
        } else {
            switch ($method) {
                case self::PUT:
                    $data['id'] = $id;
                    return $iresource->update($data);
                case self::GET:
                    return $iresource->get($id);
                case self::DELETE:
                    return $iresource->delete($id);
            }
        }
        throw new Exception("Method '" . $method . "' for resource /" . $resource . "/" . $id . " not allowed");
    }

    private static function checkApiKey($apikey)
    {
        if (is_null($apikey))
            throw new Exception("APIKEY not provided");

        if (self::$localapikey == $apikey) {
            $env = (object)["env" => "winko", "idusuario" => 1];
            return $env;
        }
        throw new Exception("APIKEY does not match");
    }
}
