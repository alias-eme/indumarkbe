<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class ProductoSubgrupo extends \corsica\framework\utils\DbClientSimple
{
    protected  function tablename()
    {
        return "p_producto_subgrupo";
    }
    protected  function primarykey()
    {
        return "CodSubGr";
    }
    /**
     * Si el id grupo no viene nulo, buscará en la tabla de productos on select distinct
     */
    public function listar($idgrupo = null)
    {
        if (is_null($idgrupo)) {
            $sql = "SELECT * FROM " . $this->tablename();
            $sql .= " ORDER BY DesSubGr ASC";
        } else {
            $sql = " SELECT ps.CodSubGr,ps.DesSubGr";
            $sql .= " FROM (SELECT distinct p_producto.CodSubGr ";
            $sql .= "         FROM p_producto WHERE p_producto.CodGrupo=" . $this->texto($idgrupo) . ") p";
            $sql .= " JOIN p_producto_subgrupo ps on p.CodSubGr = ps.CodSubGr";
            $sql .= " ORDER BY ps.DesSubGr ASC";
        }
        return $this->select($sql);
    }
    /**
     * Indica si cada producto de un proyecto tiene actividads y estas suman 100;
     */
    public function filtrar($filtro, $pagina)
    {

        $this->logger->info("filtrar", ["filtro" => $filtro, "pagina" => $pagina]);
        $dbfiltro = (object)["DesSubGr" => null];

        $dbfiltro->DesSubGr = strlen($filtro->descripcion) > 0 ? $filtro->descripcion : null;

        return $this->filtrarSimple($dbfiltro, $pagina, ["DesSubGr"], null, "DesSubGr asc");
    }
}
