<?php

namespace corsica\framework\usuarios;



class Perfil extends \corsica\framework\utils\DbClient
{

    /**
     * Lista todos los perfiles menos el de master que lo toma desde ENV
     */
    public function listar()
    {
        $sql = " select id, nombre,'' as usuarios ";
        $sql .= " from t_perfil ";
        if ($this->env->idperfil != 1)
            $sql .= " where id != 1 ";
        $sql .= " order by id";

        $items = $this->select($sql);

        return $items;
    }
    public function filtrar($filtro, $conusuarios = 0)
    {
        $sql = " select id, nombre,'' as usuarios ";
        $sql .= " from t_perfil ";
        if ($this->env->idperfil != 1)
            $sql .= " where id != 1 ";
        $sql .= " order by id";
        $items = $this->select($sql);
        if ($conusuarios == 1) {
            foreach ($items as $item) {
                $item->usuarios = $this->select("select id, nombre,apellido from t_usuario where idperfil=" . $item->id);
            }
        }
        $totalitems = count($items);
        $out = array("items" => $items, "totalitems" => $totalitems);
        return (object)$out;
    }
    public function guardar($data)
    {
        if ($data->id == 0) {
            $sql = "insert into t_perfil ( nombre) values (" . $this->texto($data->nombre) . ")";
        } else {
            $sql = "update t_perfil set nombre =" . $this->texto($data->nombre) . " where id = " . $this->texto($data->id);
        }
        $this->execute($sql);
        return 0;
    }
    public function eliminar($id)
    {
        $sql = "delete from  t_perfil where id = " . $this->texto($id);
        $this->execute($sql);
        return 0;
    }
}
