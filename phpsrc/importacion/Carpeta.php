<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class Carpeta extends \corsica\framework\utils\DbClientSimple
{
    protected function tablename()
    {
        return "i_carpeta";
    }
    protected function primarykey()
    {
        return "id";
    }

    /**
     * Indica si cada producto de un proyecto tiene actividads y estas suman 100;
     */
    public function cargar($idcarpeta)
    {
        $this->logger->info("cargar", ["idcarpeta" => $idcarpeta]);
        return $this->cargarSimple($idcarpeta);
    }


    public function crear($carpeta)
    {
        $this->logger->info("crear", ["carpeta" => $carpeta]);

        $folio = $this->crearFolio();
        $sql = "INSERT INTO i_carpeta ";
        $sql .= "( id, folio, descripcion ";
        $sql .= ", idproveedor, proveedor, idestado ";

        $sql .= ", fechaoc, docfecha, fwdfecha ";
        $sql .= ", fwdvencimiento, finfecha, finvencimiento ";
        $sql .= ", deliveryfecha, blfecha, fechallegada, fechabodega ";
        $sql .= ", segurofecha ";
        $sql .= ") VALUES ( ";
        $sql .= " NULL, " . $this->texto($folio) . ", " . $this->texto($carpeta->descripcion);
        $sql .= "," . $this->texto($carpeta->idproveedor) . "," . $this->texto($carpeta->proveedor) . "," . $this->texto($carpeta->idestado);

        $sql .= ",CURRENT_DATE,CURRENT_DATE,CURRENT_DATE";
        $sql .= ",CURRENT_DATE,CURRENT_DATE,CURRENT_DATE";
        $sql .= ",CURRENT_DATE,CURRENT_DATE,CURRENT_DATE,CURRENT_DATE";
        $sql .= ",CURRENT_DATE)";
        $this->execute($sql);
        $id = $this->lastId();

        return $this->cargar($id);
    }
    private function crearFolio()
    {
        $sql = "SELECT concat(LPAD(count(id)+1,3,0),'/',year(current_date)) as x ";
        $sql .= "FROM i_carpeta WHERE year(i_carpeta.fecha)=year(current_date)";
        return $this->select1($sql);
    }



    public function guardar($carpeta)
    {
        $this->logger->info("guardar", ["carpeta" => $carpeta]);
        $this->updateSimple($carpeta);
        return "ok";
    }
    public function filtrar($filtro, $pagina, $sort)
    {
        $sort = is_null($sort) ? "id desc" : $sort;
        $this->logger->info("filtrar", ["filtro" => $filtro, "pagina" => $pagina]);

        //$g = new LeftJoin("p_producto_grupo", $this->tablename(),["CodGrupo" => "CodGrupo"], ["DesGrupo" => "grupo"]);
        //$sg = new LeftJoin("p_producto_subgrupo", $this->tablename(),["CodSubGr" => "CodSubGr"], ["DesSubGr" => "subgrupo"]);
        return $this->filtrarSimple($filtro, $pagina, ["folio", "proveedor", "descripcion", "blcrt"], null, $sort, );
    }



    public function cambiarFecha($id, $fecha, $estado, $fieldname)
    {
        $sql = "UPDATE " . $this->tablename();
        $sql .= " SET " . $fieldname . " = " . $this->texto($fecha);
        $sql .= " , " . $fieldname . "status = " . $this->texto($estado);
        $sql .= " WHERE id = " . $this->texto($id);

        $this->execute($sql);
        return "ok";
    }



}