<?php

namespace corsica\framework\router;

use Exception;

/**
 * métodos estándares de los recursos que implementen la api de lectura
 */
interface Api2Interface
{
    /**
     * adds a data transfer object
     */
    public function add($dto);
    public function list();
    public function get($id);
    public function update( $dto);
    public function delete($id);
}
