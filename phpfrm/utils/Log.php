<?php

namespace corsica\framework\utils;


/**
 * Clase para guardar actividad principal.
 */
class Log extends \corsica\framework\utils\DbClientSimple
{

    public function tablename()
    {
        return "t_log";
    }
    public function primarykey()
    {
        return "id";
    }
    public function filtrar($filtro, $pagina)
    {
        return $this->filtrarSimple($filtro, $pagina, ["usuario"], "is desc");
    }
    public function guardar($nota,$idproyecto=null)
    {
        $row = (object)["nota" => $nota, "idusuario" => $this->env->idusuario, "usuario" => $this->env->username];
        $this->insertSimple($row);
    }
    public static function log($env, $conn, $nota)
    {
        $o = new Log($env, $conn);
        $o->guardar($nota);
    }
}
