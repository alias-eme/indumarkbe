<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class CarpetaProducto extends \corsica\framework\utils\DbClientSimple
{
    protected  function tablename()
    {
        return "i_carpeta_producto";
    }
    protected  function primarykey()
    {
        return "id";
    }

    /**
     * Indica si cada producto de un proyecto tiene actividads y estas suman 100;
     */
    public function cargar($idcarpeta)
    {
        $this->logger->info("cargar", ["idcarpeta" => $idcarpeta]);
        //$p = new LeftJoin("p_producto", $this->tablename(),["CodProd" => "idproducto"], ["CodProd" => "sku","DesProd" => "nombre"]);  
        return $this->selectSimple(["idcarpeta" => $idcarpeta],null,null);
    }
    public function guardar($carpetaproducto)
    {
        $this->logger->info("guardar", ["carpetaproducto" => $carpetaproducto]);
        $fields="";

        if (is_null($carpetaproducto->id)) {
            $this->insertSimple($carpetaproducto);
        } else {
            $this->updateSimple($carpetaproducto);
        }
        return "ok";
    }
    public function eliminar($id)
    {
        $this->logger->info("eliminar", ["id" => $id]);
        return $this->deleteSimple($id);
        return "ok";
    }

    public function database() {
        return $this->select1("SELECT DATABASE() as x ");
    }

    public function columns() {
        $database = $this->database();
        $sql = "SELECT COLUMN_NAME, DATA_TYPE FROM `INFORMATION_SCHEMA`.`COLUMNS` ";
        $sql .= " WHERE TABLE_SCHEMA = ".$this->texto($database)." AND TABLE_NAME=".$this->texto($this->tablename());
        $sql .= " ORDER BY ORDINAL_POSITION ";
        return $this->select($sql);
    }
  



}
