<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class CarpetaPago extends \corsica\framework\utils\DbClientSimple
{
    protected  function tablename()
    {
        return "i_carpeta_pago";
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
        return $this->selectSimple(["idcarpeta" => $idcarpeta]);
    }
    public function guardar($carpeta)
    {
        $this->logger->info("guardar", ["carpeta" => $carpeta]);
        if (is_null($carpeta->id)) {
            $this->insertSimple($carpeta);
        } else {
            $this->updateSimple($carpeta);
        }
        return "ok";
    }
    public function eliminar($id)
    {
        $this->logger->info("eliminar", ["id" => $id]);
        return $this->deleteSimple($id);
        return "ok";
    }
    public function filtrar($filtro, $pagina)
    {

        $this->logger->info("filtrar", ["filtro" => $filtro, "pagina" => $pagina]);

        $c = new LeftJoin("i_carpeta", $this->tablename(), ["id" => "idcarpeta"], ["folio" => "folio", "descripcion" => "descripcion"]);
        //$sg = new LeftJoin("p_producto_subgrupo", $this->tablename(),["CodSubGr" => "CodSubGr"], ["DesSubGr" => "subgrupo"]);
        return $this->filtrarSimple($filtro, $pagina, ["detalle"], null, "fecha asc", [$c]);
    }

    public function actualizaFi($id, $fi)
    {
        $row = (object)["id" => $id, "fi" => $fi];
        $this->updateSimple($row);
        return "ok";
    }
}
