<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class Producto extends \corsica\framework\utils\DbClientSimple
{
    protected  function tablename()
    {
        return "p_producto";
    }
    protected  function primarykey()
    {
        return "CodProd";
    }

    /**
     * Indica si cada producto de un proyecto tiene actividads y estas suman 100;
     */
    public function filtrar($filtro, $pagina)
    {

        $this->logger->info("filtrar",["filtro"=>$filtro,"pagina"=>$pagina]);
        $dbfiltro = (object)["CodProd"=>null,"DesProd"=>null,"CodGrupo"=>null,"CodSubGr"=>null,"Inactivo"=>null] ;

        $dbfiltro->CodProd = strlen($filtro->codigo)>0 ? $filtro->codigo:null;
        $dbfiltro->DesProd = strlen($filtro->descripcion)>0 ? $filtro->descripcion:null;
        $dbfiltro->CodGrupo = !is_null($filtro->idgrupo) ? $filtro->idgrupo:null;
        $dbfiltro->CodSubGr = !is_null($filtro->idsubgrupo) ? $filtro->idsubgrupo:null;
        $dbfiltro->Inactivo = $filtro->inactivo;
       
        $g = new LeftJoin("p_producto_grupo", $this->tablename(),["CodGrupo" => "CodGrupo"], ["DesGrupo" => "grupo"]);
        $sg = new LeftJoin("p_producto_subgrupo", $this->tablename(),["CodSubGr" => "CodSubGr"], ["DesSubGr" => "subgrupo"]);
        return $this->filtrarSimple($dbfiltro, $pagina,["CodProd","DesProd"],null,"CodProd asc",[$g,$sg]);
    }
    /**
     * Función para buscar 
     */
    public function buscar($texto, $pagina, $max = 30)
    {
        $offset = is_null($pagina) ? 0 : 1 * $pagina - 1;

        $sql  = "SELECT CodProd as value, DesProd as text, p_producto.* ";
        //$sql  = ", p_producto_grupo.DesGrupo ";
        //$sql  = ", p_producto_subgrupo.DesSubGr ";

        $fromwhere = "  FROM " . $this->tablename();
        //$fromwhere = "  LEFT JOIN p_producto_grupo on p_producto_grupo.CodGrupo = p_producto.CodGrupo";
        //$fromwhere = "  LEFT JOIN p_producto_subgrupo on p_producto_subgrupo.CodSubGr = p_producto.CodSubGr" ;
        
        $fromwhere .= "  WHERE  concat(CodProd, ' ', DesProd) LIKE " . $this->like($texto);
        $fromwhere .= "  AND  inactivo=0 " ;
        $sql .=$fromwhere;
        $sql .= " ORDER BY DesProd asc";
        $sql .= " LIMIT " . $offset . "," . $max;
        $items = $this->select($sql);

        $totalitems = $this->select1("SELECT COUNT(CodProd) as x ".$fromwhere);

        return (object)["items"=>$items,"totalitems"=>$totalitems];
    }
}
