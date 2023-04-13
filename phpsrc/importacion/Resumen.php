<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class Resumen extends \corsica\framework\utils\DbClient
{
    public function totalesPorBanco()
    {
        $sql = " SELECT t.fininstitucion ";
        $sql .= " ,t.finmoneda ";
        $sql .= " , sum(t.finmonto) as finmonto  ";
        $sql .= " FROM i_carpeta t ";
        $sql .= " WHERE t.finpagado=0 ";
        $sql .= " AND t.fininstitucion!='' ";
        $sql .= " AND t.tienefinanciamiento=1 ";
        $sql .= " GROUP BY t.fininstitucion, t.finmoneda ";
        return $this->select($sql);
    }
}