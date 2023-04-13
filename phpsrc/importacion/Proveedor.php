<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class Proveedor extends \corsica\framework\utils\DbClientSimple
{
    protected  function tablename()
    {
        return "i_proveedor";
    }
    protected  function primarykey()
    {
        return "id";
    }

    /**
     * Indica si cada producto de un proyecto tiene actividads y estas suman 100;
     */
    public function filtrar($filtro, $pagina)
    {

        $this->logger->info("filtrar", ["filtro" => $filtro, "pagina" => $pagina]);

        //$dbfiltro = (object)[];
        return $this->filtrarSimple($filtro, $pagina, ["nombre"], null, "nombre asc");
    }
    public function guardar($proveedor)
    {
        if (!is_null($proveedor)) {
            if (is_null($proveedor->id)) {
                $this->insertSimple($proveedor);
            } else {
                $this->updateSimple($proveedor);
            }
        }
        return "ok";
    }
    public function eliminar($id)
    {
        $this->deleteSimple($id);
        return "ok";
    }
    public function autocompletar($query, $max = 30)
    {
        $sql  = "select nombre as value, concat(nombre, ', ', pais) as label, i_proveedor.* ";
        $sql .= "  from " . $this->tablename();
        $sql .= "  where  concat(nombre, ', ', pais) LIKE " . $this->like($query);
        $sql .= " order by nombre asc";
        $sql .= " limit " . $max;
        return $this->select($sql);
    }
    /**
     * Función para buscar 
     */
    public function buscar($texto, $pagina, $max = 30)
    {
        $offset = is_null($pagina) ? 0 : $max * ($pagina - 1);

        $sql  = "select id as value, concat(nombre, ', ', pais) as text, i_proveedor.* ";

        $fromwhere = "  from " . $this->tablename();
        $fromwhere .= "  where  concat(nombre, ', ', pais) LIKE " . $this->like($texto);
        $sql .=$fromwhere;
        $sql .= " order by nombre asc";
        $sql .= " limit " . $offset . "," . $max;
        $items = $this->select($sql);

        $totalitems = $this->select1("SELECT COUNT(id) as x ".$fromwhere);


        return (object)["items"=>$items,"totalitems"=>$totalitems];
    }
}
