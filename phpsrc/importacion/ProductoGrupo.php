<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class ProductoGrupo extends \corsica\framework\utils\DbClientSimple
{
    protected  function tablename()
    {
        return "p_producto_grupo";
    }
    protected  function primarykey()
    {
        return "CodGrupo";
    }
    public function listar()
    {
        $sql = "SELECT * FROM ".$this->tablename()." ORDER BY DesGrupo ASC";
        return $this->select($sql);
    }
    /**
     * Indica si cada producto de un proyecto tiene actividads y estas suman 100;
     */
    public function filtrar($filtro, $pagina)
    {

        $this->logger->info("filtrar",["filtro"=>$filtro,"pagina"=>$pagina]);
        $dbfiltro = (object)["DesGrupo"=>null] ;

        $dbfiltro->DesGrupo = strlen($filtro->descripcion)>0 ? $filtro->descripcion:null;

        return $this->filtrarSimple($dbfiltro, $pagina,["DesGrupo"],null,"DesGrupo asc");
    }

}
