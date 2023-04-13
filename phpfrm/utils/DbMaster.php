<?php

namespace corsica\framework\utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use corsica\framework\config\Config;
use Exception;

/**
 * Para administrar la base de datos SIEMPRE SE CONECTA A MASTER
 * PARA LOGIN Y OTROS
 */
abstract class DbMaster extends DbClient
{
    public function getConexion()
    {

        if ($this->conn == null) {

            // Create connection
            $config = Config::getMasterDatabase();
            $conn = mysqli_connect(
                $config->servername,
                $config->username,
                $config->password,
                $config->database
            );
            if (!$conn) {
                $this->logger->error("error de conexión", array("conf" => $config));
                exit("error de conexión");
            }
            mysqli_set_charset($conn, "utf8");
            $this->conn = $conn;
        }
        return $this->conn;
    }
}
