<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class CarpetaApoderado extends \corsica\framework\utils\DbClientSimple
{
    protected function tablename()
    {
        return "i_carpeta_apoderado";
    }
    protected function primarykey()
    {
        return "id";
    }

    /**
     * Indica si cada producto de un proyecto tiene actividads y estas suman 100;
     * @return array of apoderados
     */
    public function cargar($idcarpeta, $forprint = false)
    {
        $this->logger->info("cargar", ["idcarpeta" => $idcarpeta]);

        $sql = " SELECT ia.id as idapoderado ";
        $sql .= " , " . $idcarpeta . " as idcarpeta";
        $sql .= " , ia.rut";
        $sql .= " , ia.nombre";
        $sql .= " , IF(ica.idapoderado IS NULL,0,1) as value ";
        $sql .= " FROM i_apoderado ia";
        $sql .= $forprint ? " " : " LEFT ";
        $sql .= " JOIN (SELECT idapoderado FROM i_carpeta_apoderado WHERE idcarpeta = ".$idcarpeta.") ica";
        $sql .= "  on ica.idapoderado = ia.id";

        return $this->select($sql);
    }



    public function guardar($apoderados)
    {
        $this->logger->info("apoderados", ["apoderados" => $apoderados]);
        foreach ($apoderados as $apo) {
            if ($apo->value == "1") {
                $this->insertar($apo->idcarpeta, $apo->idapoderado);
            } else {
                $this->eliminar($apo->idcarpeta, $apo->idapoderado);
            }
        }

    }
    private function eliminar($idcarpeta, $idapoderado)
    {
        $sql = "DELETE FROM " . $this->tablename();
        $sql .= " WHERE idcarpeta = " . $this->texto($idcarpeta);
        $sql .= " AND idapoderado = " . $this->texto($idapoderado);
        $this->execute($sql);
    }

    private function insertar($idcarpeta, $idapoderado)
    {
        $sql = "INSERT INTO " . $this->tablename();
        $sql .= " (idcarpeta,idapoderado) values (" . $this->texto($idcarpeta);
        $sql .= "," . $this->texto($idapoderado) . ")";
        $this->execute($sql);
    }
}