<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class Lista extends \corsica\framework\utils\DbClientSimple
{
    protected  function tablename()
    {
        return "i_lista";
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
        return $this->filtrarSimple($filtro, $pagina,null,null,"nombre asc");
    }

    public function listar($grupo)
    {
        $sql = "SELECT * from ". $this->tablename()." WHERE grupo = ".$this->texto($grupo);
        $sql .= " ORDER BY nombre ASC ";
        return $this->select($sql);
    }
    public function listarNombres($grupo)
    {
        $rows = $this->listar($grupo);
        $out = [];
        foreach($rows as $row) {
            array_push($out,$row->nombre);
        }
        return $out;
    }
    public function listarGrupos()
    {
        $sql = "SELECT DISTINCT grupo from " . $this->tablename();
        return $this->select($sql);
    }
}
