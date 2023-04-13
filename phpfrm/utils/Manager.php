<?php

namespace corsica\framework\utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use corsica\framework\config\Config;

/**
 * Clase que funciona como "Manager" al realizar varias operaciones
 * con o sin transaccionalidad. El objetivo es centralizar métodos ne negocio
 * de forma que sean entendibles y administrables
 */
abstract class Manager
{
  /**
   * La conexión a BD, útil para manejar transacciones
   */
  public $env = null; // para mantener la data de conexión
  private $conn = null; // para mantener viva la conexión
  /**
   * Logger
   */
  protected $logger = null;

  private static $staticlogger = null;
  /**
   * 
   */
  public function __construct($env, $conn = null)
  {
    $this->env = $env;
    $this->conn = $conn;

    $this->logger  = new Logger(static::class);
    $this->logger->pushHandler(new StreamHandler(Config::getLogPath("manager"), Config::getLogLevel("manager")));
  }
  private static function getStaticLogger()
  {
    if (is_null(self::$staticlogger)) {
      self::$staticlogger = new Logger(static::class);
      self::$staticlogger->pushHandler(new StreamHandler(Config::getLogPath("manager"), Config::getLogLevel("manager")));
    }
    return self::$staticlogger;
  }
  public static function info($message, $data)
  {
    $logger = self::getStaticLogger();
    $logger->info($message, $data);
  }
  public static function error($message, $data)
  {
    $logger = self::getStaticLogger();
    $logger->error($message, $data);
  }
  protected function getConexion()
  {
    return $this->conn;
  }
  protected function tieneConexion()
  {
    return $this->conn == null ? false : true;
  }
}
