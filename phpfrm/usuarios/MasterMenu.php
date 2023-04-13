<?php

namespace corsica\framework\usuarios;

/**
 * Esta es para conectar como usuario global y acceder a el entorno empresa
 */
class MasterMenu extends \corsica\framework\utils\DbMaster
{
    /**
     * Para copiar//pegar el menu MASTER
     * checked luego puede ser 1, 0 o -1 en caso de no corresponder al estandar.
     * Es para la administración 
     */
 public function listar () {
    $sql = "SELECT *, 0 as checked, NULL as notas from x_menu ORDER BY id";
    return $this->select($sql);
 }
}
