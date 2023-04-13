<?php

namespace corsica\framework\utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use corsica\framework\config\Config;
use Exception;

/**
 * Para administrar la base de datos
 */
abstract class DbClient
{
    const PAGINACION = 30;
    /**
     * La conexión a BD, útil para manejar transacciones
     */
    protected $conn = null; // para mantener viva la conexión
    /**
     * El environment para
     */
    public $env = null; // para mantener viva la conexión
    /**
     * Logger
     */
    protected $logger = null;
    /**
     * para cuando se abran transacciones almacenará las sentencias y entregará la pila al momento de ROLLBACK
     */
    private $stack = null;

    /**
     * Construye la clase de acceso a un BD MySql junto con un logger
     * @param Object $env Objeto environment con 3 parametros env, idusuario, idperfil
     * @param Object $conn es la conexión externa, aveces necesario apra transacciones
     */

    public function __construct($env, $conn = null)
    {
        $this->env = $env;
        $this->conn = $conn;

        $this->logger  = new Logger(static::class);

        $this->logger->pushHandler(new StreamHandler(Config::getLogPath("sql"), Config::getLogLevel("sql")));
        if ($env != null && $env->idusuario != null) {
            //do nothing
        } else {
            //$this->logger->error("Error de environment!",array("env"=>$this->env));
        }
    }





    //?
    private $cantidadDeRegistros = 0;
    //?
    protected function getCantRegistros()
    {
        return $this->cantidadDeRegistros;
    }

    //recorta un string al máximo indicado para que no se caiga el insert/update
    protected function fixmax($str, $max)
    {
        $str = trim($str);
        $l = strlen($str);
        if ($l > $max) {
            $str = substr($str, 0, $max);
        }
        return $str;
    }
    /***********************************************************************
     * evalua si un dato viene nulo para poner un cero
     */
    protected function numero($value)
    {
        $out = (is_numeric($value)) ? $value : 0;
        return $out;
    }
    protected function texto($valor)
    {

        return "'" . mysqli_real_escape_string($this->getConexion(), $valor) . "'";
    }
    protected function like($valor)
    {
        return "'%" .  mysqli_real_escape_string($this->getConexion(), $valor) . "%'";
    }
    protected function fecha($fecha)
    {
        $out = $fecha;
        if (strpos($fecha, '/')) {
            $d = strtok($fecha, "/"); //busca el dd/MM/yy
            $m = strtok("/");
            $y = strtok("/");
            if (strlen($y) == 2)
                $y = '20' . $y;
            $out =  $y . '-' . $m . '-' . $d;
        }
        $out = "'" . $out . "'";
        return $out;
    }
    protected function fechahora($fecha, $hora = '00:00')
    {
        //yyyy5mm8dd
        $fecha = substr($fecha, 0, 10);
        $out = $this->fecha($fecha);
        $out = substr($out, 0, strlen($out) - 1);
        $out .=  ' ' . $hora . "'";
        return $out;
    }
    /**
     * Evalua si la conexión es nula y la crea
     */
    public function getConexion()
    {

        if ($this->conn == null) {

            // Create connection
            $config =  new Config($this->env);
            $conn = mysqli_connect(
                $config->getParam("database", "servername"),
                $config->getParam("database", "username"),
                $config->getParam("database", "password"),
                $config->getParam("database", "database")
            );
            if (!$conn) {
                $mensajeError = "ERROR DE CONEXION EN DbClient - HOLA!! ";
                $mensajeError .= "[servername : " .  json_encode($config->getParam("database", "servername")) . "]";
                $mensajeError .= "[username : " .  $config->getParam("database", "username") . "]";
                //$mensajeError .= "[password : " .  $config->getParam("database", "password") . "]";
                $mensajeError .= "[database : " .  $config->getParam("database", "database") . "]";
                $mensajeError .= "[error no : " . mysqli_connect_errno() . "]";
                $mensajeError .= "[error : " . mysqli_connect_error() . "]";
                $this->logger->error($mensajeError);


                exit($mensajeError);
            }
            mysqli_set_charset($conn, "utf8");
            $this->conn = $conn;
        }
        return $this->conn;
    }
    /**
     * Para recibir la conexión desde otra clase cuando se realizan transacciones
     */
    public function setConexion($conn)
    {
        $this->conn = $conn;
    }
    /**
     * obtiene el id de un insert
     */
    protected function lastId()
    {
        $id = mysqli_insert_id($this->conn);
        $this->logger->info($id);
        return $id;
    }
    /**
     * Ejecuta sentencias update/insert/delete
     */
    public function execute($sql)
    {
        $out = null;
        $conn = $this->getConexion();
        $this->logger->info($sql);

        if ($conn->query($sql) === TRUE) {
            $out = $conn->affected_rows;
        } else {
            $out = (object)["error" => $conn->error];
            $this->logger->error($conn->error, array("sql" => $sql));
        }
        return $out;
    }


    public function getInsertedId()
    {
        $conn = $this->getConexion();
        return $conn->insert_id;
    }

    public function autocommit($onoff)
    {
        $conn = $this->getConexion();
        $conn->autocommit($onoff);
        $this->logger->info("autocommit=" . json_encode($onoff));
    }
    public function commit()
    {
        $conn = $this->getConexion();
        $conn->commit();
    }
    public function rollback($message = "")
    {
        $conn = $this->getConexion();
        $this->logger->error("ROLLBACK", array("message" => $message));
        $conn->rollback();
    }


    /**
     * Retorna un dato escalar o null si no lo encuentra
     * @param sql la sentencia
     * @return String,null valor escalar o null si no lo encuentra
     */
    public function select1($sql)
    {
        $conn = $this->getConexion();
        $this->logger->debug($sql);
        $result = mysqli_query($conn, $sql);
        if ($result !== false) {

            $row = $result->fetch_array(MYSQLI_NUM);
            if (is_array($row) && count($row) == 1) {
                return $row[0];
            } else {
                return null;
            }
        } else {
            $this->logger->error($sql);
            return null;
        }
    }

    //lo mismo pero devuelve un objeto en vez de un array
    public function select($sql)
    {
        //aqui había un problema cuando haca varias queries seguidas
        //error_reporting(E_ERROR | E_PARSE);
        $out = array();
        $conn = $this->getConexion();
        $this->logger->debug($sql);
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $this->cantidadDeRegistros = mysqli_num_rows($result);
            $row = mysqli_fetch_object($result);
            while ($row != null) {
                array_push($out, $row);
                $row = mysqli_fetch_object($result);
            }
        } else {
            $this->logger->error($conn->error, array("sql" => $sql));
        }

        return $out;
    }

    /**
     * 
     */
    public function getFechaDb()
    {
        return $this->select1("select current_date as x");
    }
    /**
     * 
     */
    public function getFechaHoraDb()
    {
        return $this->select1("select now as x");
    }
    public function selectAsArray($sql)
    {
        //aqui había un problema cuando haca varias queries seguidas
        //error_reporting(E_ERROR | E_PARSE);
        $out = array();
        $conn = $this->getConexion();
        $this->logger->debug($sql);
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $this->cantidadDeRegistros = mysqli_num_rows($result);
            $row = mysqli_fetch_assoc($result);
            while ($row != null) {
                array_push($out, $row);
                $row = mysqli_fetch_assoc($result);
            }
        } else {
            $this->logger->error($conn->error, array("sql" => $sql));
        }
        return $out;
    }
    protected function dividirTexto($texto)
    {
        /** por defecto cuando no hay descripcion */
        $out = array("''", "''", "''", "''");
        $largo = strlen($texto);
        $i = 0;
        while ($largo > 0) {
            $corte = $largo > 250 ? 250 : $largo;
            $out[$i] = $this->texto(substr($texto, 0, $corte));
            $largo = $largo - $corte;
            $i++;
            if ($largo > 0) {
                $texto = substr($texto, $corte, strlen($texto) - $corte);
            }
        }
        return $out;
    }
    protected function turnBoolean($rows, $fieldName)
    {
        for ($i = 0; $i < count($rows); $i++) {
            $rows[$i]->$fieldName = ($rows[$i]->$fieldName == 0) ? false : true;
        }
    }
    /**
     * Create an update sentence
     * @param String $tablename
     * @param array $sets field-value array
     * @param array $where fieldvalue array
     * @param array $setfilter field array to update onli certain fields
     */
    protected function xupdate($tablename, $sets, $where, $setfilter = null)
    {
        $sql = "UPDATE " . $tablename;
        $sep = " SET ";
        if (is_null($setfilter)) {
            //se actualizan todos los datos
            foreach ($sets as $fieldname => $fieldvalue) {
                $sql .= $sep . $fieldname . '=' . $this->texto($fieldvalue);
                $sep = " , ";
            }
        } else {
            //se actualizan sólo los datos "$fields"
            foreach ($setfilter as $fieldname) {
                $fieldvalue = is_null($sets[$fieldname]) ? "NULL" : $this->texto($sets[$fieldname]);
                $sql .= $sep . $fieldname . '=' . $fieldvalue;
                $sep = " , ";
            }
        }
        if ($where) {
            $sep = " WHERE ";
            foreach ($where as $fieldname => $fieldvalue) {
                $sql .= $sep . $fieldname . '=' . $fieldvalue;
                $sep = " AND ";
            }
        }
        $this->execute($sql);
    }
}
