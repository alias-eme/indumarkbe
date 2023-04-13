<?php

namespace corsica\framework\utils;

use Exception;

/**
 * Para crear un join
 */
class LeftJoin
{
    private $mastertable = null;
    private $jointable = null;
    private $on = null;
    private $fields = null;

    /**
     * @param array $on array map jointablefield-mastertablefield
     * @param array $fields array map field=>name
     * 
     */
    public function __construct($jointable, $mastertable, $on, $fields)
    {
        $this->mastertable = $mastertable;
        $this->jointable = $jointable;
        $this->on = $on;
        $this->fields = $fields;
    }
    public function join()
    {
        $sql = " LEFT JOIN " . $this->jointable . " ON ";
        foreach ($this->on as $j => $m) {
            $sql .= " " . $this->jointable . "." . $j . " = " . $this->mastertable . "." . $m;
        }
        return $sql;
    }
    public function joinfields() {
        $sql="";
        foreach ($this->fields as $field => $name) {

            $sql .= ", " . $this->jointable . "." . $field;
            if ($name)
            $sql .= " AS " . $name;
      
        }
        return $sql;
    }
}
