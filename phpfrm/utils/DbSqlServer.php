<?php

namespace corsica\framework\utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use corsica\framework\config\Config;
use Exception;

/**
 * Para administrar la base de datos
 */
abstract class DbSqlServer
{

    /**
     * La conexión a BD, útil para manejar transacciones
     */
    private static $conn = null; // para mantener viva la conexión

    /**
     * Logger
     */
    private static $logger = null;

    /**todos piden la conexión a SQL SERVER */
    public static function getConexion()
    {
        if (DbSqlServer::$conn === null) {
            DbSqlServer::$conn = DbSqlServer::conecta();
        }
        return    DbSqlServer::$conn;
    }
    public static function getLogger()
    {
        if (DbSqlServer::$logger === null) {
            DbSqlServer::$logger  = new Logger(static::class);
            DbSqlServer::$logger->pushHandler(new StreamHandler(Config::getLogPath("sqlserver"), Config::getLogLevel("sqlserver")));
        }
        return    DbSqlServer::$logger;
    }


    private static function conecta()
    {
        $conf = Config::getSqlServer();
        $connectioninfo = array("Database" => $conf->database, "UID" => $conf->username, "PWD" => $conf->password, "CharacterSet" => "UTF-8");
        //$connectioninfo = array("Database" => $conf->database, "UID" => $conf->username, "PWD" => $conf->password);
        DbSqlServer::$conn = sqlsrv_connect($conf->servername, $connectioninfo);

        if (DbSqlServer::$conn) {
            DbSqlServer::getLogger()->debug("Conexión establecida.");
            return DbSqlServer::$conn;
        } else {
            DbSqlServer::getLogger()->error("Error de conexión con", array("conf" => $conf));
            $msg = "No se pudo conectar a la base de datos SQL Server '".$conf->database."'. ";
            $errs = sqlsrv_errors();
            if (is_array($errs)&&count($errs)>0) {
                 $msg .= $errs[0]['message'];
            }
            throw new Exception($msg);
            exit;
        }
    }

    public function select($query)
    {
        $conn = DbSqlServer::getConexion();
        DbSqlServer::getLogger()->debug($query);
        $stmt = sqlsrv_query($conn, $query);
        if ($stmt === false) {
            DbSqlServer::getLogger()->error($query, array("errors" => sqlsrv_errors()));
            die(print_r(sqlsrv_errors(), true));
        }
        $out = [];
        while ($obj = sqlsrv_fetch_object($stmt)) {
            array_push($out, $obj);
        }
        return $out;
    }
    protected function numero($value)
    {
        $out = (is_numeric($value)) ? $value : 0;
        return $out;
    }
    protected function texto($valor)
    {
        return "'"  .  $valor. "'";
    }
    public function select1($query)
    {
        $conn = DbSqlServer::getConexion();
        DbSqlServer::getLogger()->debug($query);
        $stmt = sqlsrv_query($conn, $query);
        if ($stmt === false) {
            DbSqlServer::getLogger()->error($query, array("errors" => sqlsrv_errors()));
            die(print_r(sqlsrv_errors(), true));
        }
        $out = null;
        if (sqlsrv_fetch($stmt) !== false) {
            $out = sqlsrv_get_field($stmt, 0);
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
}
