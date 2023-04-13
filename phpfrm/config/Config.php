<?php

namespace corsica\framework\config;

use Exception;
use Monolog\Logger;

/**
 * Lee un archivo de configuración json global y un segundo según se identifique 
 * el usuario como miembro de alguna empresa
 * primero lee la variable "env" y según eso carga el resto
 */
class Config
{

    /**
     * FOLDER STRUCTURE
     * All must be under common_client_path
     */
    public const COMMON_CLIENT_PATH = "cli";
    public const UPLOAD_PATH = "uploads";
    public const ASSETS_PATH = "assets";


    private $params = null;
    private $env = null;



    /**
     * Al crear el objeto config con $env=[env,idusuario,idperfil] 
     * Busca el archivo de configuración /env/[env].json y entrega ese entorno
     * con us correspondientes variables
     * el token
     * @param $env 
     */
    public function __construct($env)
    {
        if ($env != null) {
            $this->env = $env->env;
            $envfile = realpath(dirname(__FILE__) . "/env/" . $this->env . ".json");
            if (file_exists($envfile)) {
                $envdata = json_decode(file_get_contents($envfile), true);
                $this->params = $envdata;
            } else {
                throw new Exception("No se pudo cargar entorno " . $this->env);
            }
        } else {
            throw new Exception("ENV es NULL ");
        }
    }
    /**
     * Devuelve un parámetro segun el nombre de grupo y prametro
     * Obtiene un valor desde el archivo de configuración de cada entorno
     */
    public function getParam($groupname, $paramname)
    {
        if ($this->params) {
            $group = $this->params[$groupname];
            $param = null;
            if ($group != null)
                $param = $group[$paramname];
            return $param;
        } else {
            return "No se cargaron las variables de entorno para " . $groupname;
        }
    }
    /**
     * Entrega los parametros de configuración master
     * del archivo de configuración principal
     */
    public static function getMasterDatabase()
    {
        $configdata = Config::getConfigData();
        $data =  $configdata['database'];
        return (object)$data;
    }
    /**
     * Entrega los parametros de configuración de SQLSERVER
     * del archivo de configuración principal
     */
    public static function getSqlServer()
    {
        $configdata = Config::getConfigData();
        $data =  $configdata['sqlserver'];
        return (object)$data;
    }
    /**
     * Entrega los parametros de configuración master
     * del archivo de configuración principal
     */
    public static function getMail()
    {
        $configdata = Config::getConfigData();
        $data =  $configdata['mail'];
        return (object)$data;
    }
    /**
     * Entrega la llave para generar tokens
     * del archivo de configuración principal
     * @return string la llave en la data de configuracion
     */

    public static function getSecretKey()
    {
        $configdata = Config::getConfigData();
        return $configdata['secretkey'];
    }
    public static function getSmartSheetsKey()
    {
        $configdata = Config::getConfigData();
        return $configdata['smartsheetskey'];
    }
    /**
     * Obtiene la ruta correspondiente de archivos que son
     * propios de cada cliente
     * @param Array of strings names of folders
     */
    public  function getUrl(array $folders, String $filename = "")
    {
        $baseurl = Config::getConfigData()['url'];
        return $baseurl . $this->path($folders,  $filename);
    }
    private function path(array $folders, String $filename = "")
    {
        $path = Config::COMMON_CLIENT_PATH . '/';
        $path .= $this->env . '/';
        $path .= implode('/', $folders) . '/';
        $path .=  $filename;
        $path = str_replace(array('//', '///', "\\", "\\\\"), '/', $path);
        return $path;
    }

    /**
     * Obtiene la ruta FULL correspondiente a partir del archivo de configuración
     * desde la ruta principal [ruta definida en config]/cli/[ambiente]/[path]/nombrearchivo
     * s
     * @param String $path ej. Config::QUOTES_PATH
     */
    public function getPath(array $folders, String $filename = "")
    {

        $basepath = Config::getConfigData()['path'];
        return $basepath . $this->path($folders,  $filename);
    }

    /**
     * Recibe un ámbito de logger (sql/file (ver config.json)) y
     * 
     * @param String $ambito String (Ej. SQL)
     * @return int log level de monoloh
     */
    public static function getLogLevel($ambito)
    {
        $configdata = Config::getConfigData();
        $stringLevel = $configdata['log-level'][$ambito];
        $levels = array(
            "DEBUG" => Logger::DEBUG,
            "INFO" => Logger::INFO,
            "NOTICE" => Logger::NOTICE,
            "WARNING" => Logger::WARNING,
            "ERROR" => Logger::ERROR,
            "CRITICAL" => Logger::CRITICAL
        );
        $level = $levels[strtoupper($stringLevel)];
        if (!$level) {
            $level = Logger::ERROR;
        }
        return $level;
    }
    /**
     * Entrega la ruta de log
     * @return String c://..../log/ambito.log
     */
    public static function getLogPath($ambito)
    {
        return dirname(dirname(dirname(__FILE__))) . "/log/" . $ambito . ".log";
    }
    /**
     * Entrega la data de configuración principal
     * @param String $ambito String (Ej. SQL)
     */
    public static function getConfigData()
    {
        $configfile = realpath(dirname(__FILE__) . "/config.json");
        return json_decode(file_get_contents($configfile), true);
    }


    /**
     * Retorna un grupo de parámetros
     */
    public function getGroup($groupname)
    {
        return $this->params[$groupname];
    }
    /**
     * Retorna un grupo de parámetros como objeto
     */
    public function getGroupObject($groupname)
    {
        return (object)$this->params[$groupname];
    }
    public function show()
    {
        return json_encode($this->params);
    }
}
