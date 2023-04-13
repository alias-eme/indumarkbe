<?php

namespace corsica\framework\usuarios;

/**
 * Esta es para conectar como usuario global y acceder a el entorno company
 */
class Company extends \corsica\framework\utils\DbMaster
{
    /**Obtiene el id por la variable env */
    public function getIdByEnv($env)
    {
        $sql = "select id from x_company where env = " . $this->texto($env);
        return $this->select1($sql);
    }
    public function filtrar($filtro)
    {
        $sql = "select * from x_company ";
        $nombre = trim($filtro->nombre);
        $where = " where 1=1 ";
        if (strlen($nombre)>0) {
           $where .=" and nombre like " . $this->like($nombre);
        }
        $sql .= $where. " limit " . self::PAGINACION * ($filtro->pagina - 1) . ", " . self::PAGINACION;
       $items = $this->select($sql);
       $totalitems = $this->select1("select count(*) from x_company ".$where);
       
        $out = array("items" => $items, "totalitems" => $totalitems);
        return (object)$out;
    }
    public function listar() {
        $filtro = array("pagina"=>1,"nombre"=>"");
        $out= $this->filtrar((object)$filtro);
        return $out->items;
    }


    public function guardar($company)
    {
        if($company->id==0) {
            return $this->insertCompany($company);
        } else {
            return $this->updateCompany($company);          
        }
    }
    private function insertCompany($company)
    {
        $sql = "INSERT INTO x_company( rutno, rutdv, rut, name, env) VALUES ";
        $sql .= "(" . $this->numero($company->rutno);
        $sql .= "," . $this->numero($company->rutdv);
        $sql .= "," . $this->texto($company->rut);
        $sql .= "," . $this->texto($company->name);
        $sql .= "," . $this->texto($company->env) . ")";
        $this->execute($sql);
        return $this->lastId();
    }
    private function updateCompany($company)
    {
        $sql = "UPDATE x_company SET ";
        $sql .= " rutno=" . $this->numero($company->rutno);
        $sql .= " ,rutdv=" . $this->numero($company->rutdv);
        $sql .= " ,rut=" . $this->texto($company->rut);
        $sql .= " ,name=" . $this->texto($company->name);
        $sql .= " ,env=" . $this->texto($company->env);
        $sql .= " WHERE id=" . $this->numero($company->id);
        $this->execute($sql);
        return $company->id;
       
    }
}
