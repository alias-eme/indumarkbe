<?php

namespace corsica\framework\utils;

use Exception;

/**
 * Para administrar la base de datos
 */
abstract class DbClientSimple extends DbClient
{
    protected abstract function tablename();
    protected abstract function primarykey();
    /**
     * Hace un select de la tabla
     * @param array $where - array asocitivo campo valor
     * @param array $nullfields - campos adicionales que van en null
     * @return array con los registros
     */
    /**
     * Hace un select de la tabla
     * @param array $where - array asocitivo campo valor
     * @param array $nullfields - campos adicionales que van en null
     * @param string $orderby - Ej "nombre,apellido desc"
     * @param array $leftjoin - Ej "nombre,apellido desc"
     * @return array con los registros
     */
    public function selectSimple($where = null, $nullfields = null, $orderby = null, $leftjoins = null)
    {
        $sql = "SELECT " . $this->tablename() . ".*";

        if ($leftjoins) {
            foreach ($leftjoins as $leftjoin) {
                if ($leftjoin) {
                    $sql .= $leftjoin->joinfields();
                }
            }
        }
        if ($nullfields && count($nullfields) > 0) {
            foreach ($nullfields as $field) {
                $sql .= ", NULL AS " . $field;
            }
        }
        $sql .= " FROM " . $this->tablename();
        if ($leftjoins) {
            foreach ($leftjoins as $leftjoin) {
                if ($leftjoin) {
                    $sql .= $leftjoin->join();
                }
            }
        }

        if ($where) {
            $sep = " WHERE ";
            foreach ($where as $fieldname => $fieldvalue) {
                $sql .= $sep . $this->tablename() . '.' . $fieldname . '=' . $this->texto($fieldvalue);
                $sep = " AND ";
            }
        }
        if ($orderby) {
            $sql .= " ORDER BY " . $orderby;
        }

        return $this->select($sql);
    }

    public function cargarSimple($id, $nullfields = null, $leftjoins = null)
    {

        $rows = $this->selectSimple([$this->primarykey() => $id], $nullfields, null, $leftjoins);
        return $rows[0];
    }
    /**
     * Realiza un update en la base de datos
     * @param object/array $data array asociativo de campos a insertar
     * @param array $fields campos a actualizar, si va null actualiza todo lo que venga en param1
     * @param object/array $where array asociativo con los campos where. Si es null usará la Primary key
     */
    public function updateSimple($data, $fields = null, $where = null)
    {
        $pk = $this->primarykey();
        $data = (array)$data;



        $sql = "UPDATE " . $this->tablename();
        $sep = " SET ";
        if (is_null($fields)) {

            //se actualizan todos los datos
            foreach ($data as $fieldname => $fieldvalue) {
                $sql .= $sep . $fieldname . '=' . $this->texto($fieldvalue);
                $sep = " , ";
            }
        } else {
            //se actualizan sólo los datos "$fields"
            foreach ($fields as $fieldname) {
                $fieldvalue = is_null($data[$fieldname]) ? "NULL" : $this->texto($data[$fieldname]);
                $sql .= $sep . $fieldname . '=' . $fieldvalue;
                $sep = " , ";
            }
        }
        $sep = " WHERE ";
        if ($where) {
            foreach ($where as $fieldname => $fieldvalue) {
                $sql .= $sep . $fieldname . '=' . $this->texto($fieldvalue);
                $sep = " AND ";
            }
        } else {
            $sql .= " WHERE " . $pk . "=" . $this->texto($data[$pk]);
        }

        $this->execute($sql);
    }
    private function has_string_keys(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
    /**
     * 
     * @param object/array dataset uno o múltiple
     * @param array $includefields para hacer un insert sólo con los campos seleccionados, si es nul son todos menos la pk
     * @param boolean $ignore true para hacer INSERT IGNORE
     * @param array $fixedfields 
     */

    public function insertSimple($data, $includefields = null, $ignore = false, $fixedfields = null)
    {
        // $this->logger->info("insertSimple", ["data" => $data, "includefields" => $includefields]);
        $ignore = ($ignore) ? " IGNORE " : "";

        $sql = "INSERT " . $ignore . " INTO " . $this->tablename() . " (";


        //preparo el header
        //la data siempre será array (es decir como su fueran varios inserts)
        $data = (array)$data;
        $multiinsert = !$this->has_string_keys($data);

        if (is_null($includefields)) {
            if (!$multiinsert) {
                //caso una fila
                $sql .= implode(',', array_keys($data));
                $this->logger->info("monofila", ["data" => $data, "sql" => $sql]);
            } else {
                //caso varias filas
                $sql .= implode(',', array_keys((array)$data[0]));
                $this->logger->info("multifila", ["data" => $data[0], "sql" => $sql]);
            }
        } else {
            $sql .= implode(',', $includefields);
        }

        if (!is_null($fixedfields)) {
            $sql .= ',' . implode(',', array_keys($fixedfields));
        }
        $sql .= ") VALUES ";
        //aqui comienza la data 
        $rowsep = "";
        if (!$multiinsert) {
            $data = [$data];
        }
        //por cada row
        foreach ($data as $row) {
            $sep = "";
            $sql .= $rowsep . '(';
            //cada campo 
            if (is_null($includefields)) {
                foreach ($row as $key => $value) {
                    $value = is_null($value) ?  'NULL' : $this->texto($value);
                    $sql .= $sep . $value;
                    $sep = ",";
                }
            } else {
                $row = (array)$row;
                foreach ($includefields as $key) {
                    $value = is_null($row[$key]) ?  'NULL' : $this->texto($row[$key]);
                    $sql .= $sep . $value;
                    $sep = ",";
                }
            }

            if (!is_null($fixedfields)) {
                foreach ($fixedfields as $key => $value) {
                    $sql .= $sep . $value;
                    $sep = ",";
                }
            }
            $sql .=  ')';
            $rowsep = ",";
        }
        $this->execute($sql);
        return $this->lastId();
    }

    public function deleteSimple($id)
    {
        if (is_array($id)) {
            $this->deleteSimple2($id);
        } else {
            $sql = "DELETE FROM " . $this->tablename();
            $sql .= " WHERE " . $this->primarykey() . " = " . $this->texto($id);
            $this->execute($sql);
        }



        return "ok";
    }



    /** 
     * Elecuta un filtro con paginación
     * Contruye y ejecuta un select paginado
     * @param array/object $filtro
     * @param int $pagina
     * @param array $nullfields - campos extra a agregar con valor null
     * @param string $orderby orderby
     * @param corsica\framework\utils\LeftJoin $leftjoin 
     * @return object (items, totalitems)
     */
    protected function filtrarSimple(
        $filtro,
        $pagina = 1,
        $likes = null,
        $nullfields = null,
        $orderby = null,
        $leftjoin = null
    ) {
        if ($leftjoin && !is_array($leftjoin)) {
            $leftjoin = [$leftjoin];
        }
        //valudacion


        $sql = "SELECT " . $this->tablename() . ".*";

        if ($leftjoin) {
            foreach ($leftjoin as $lf) {
                $sql .= $lf->joinfields();
            }
        }




        if ($nullfields && is_array($nullfields)) {
            foreach ($nullfields as $nf) {
                $sql .= ", NULL AS " . $nf;
            }
        }


        $sql .= " FROM " . $this->tablename();
        if ($leftjoin) {
            foreach ($leftjoin as $lf) {
                $sql .= $lf->join();
            }
        }
        $where = " WHERE 1=1";
        foreach ($filtro as $fname => $fvalue) {
            if (!is_null($fvalue)) {
                if (is_array($fvalue)) {
                    if (count($fvalue) > 0) {
                        $sep = "";
                        $where .= " AND " . $this->tablename() . "." . $fname . " IN ( ";
                        foreach ($fvalue as $x) {
                            $where .= $sep . $this->texto($x);
                            $sep = ",";
                        }
                        $where .= " ) ";
                    }
                } else if (trim($fvalue) != '') {
                    if ($likes && in_array($fname, $likes)) {
                        $where .= " AND " . $this->tablename() . "." . $fname . " LIKE " . $this->like($fvalue);
                    } else {
                        $where .= " AND " . $this->tablename() . "." . $fname . " = " . $this->texto($fvalue);
                    }
                }
            }
        }


        $sql .= $where;
        if ($orderby) {
            $orderby = " ORDER BY " . $orderby;
            $sql .= $orderby;
        }
        if ($pagina && $pagina > 0) {
            $sql .= "   LIMIT " . self::PAGINACION * ($pagina - 1) . ", " . self::PAGINACION;
        }
        $items = $this->select($sql);

        $totalitems = $this->select1("SELECT COUNT(*) as x FROM " . $this->tablename() . " " . $where);
        $out = array("items" => $items, "totalitems" => $totalitems);
        return (object)$out;
    }
    /**
     * @return string nombre de la table, si returna null las funciones que dependen arrojaran una excepcion
     */
    private function deleteSimple2(array $fieldvalue)
    {
        $sql = "DELETE FROM " . $this->tablename();
        $operator = " WHERE ";
        foreach ($fieldvalue as $field => $value) {
            $sql .= $operator . $field . "=" . $value;

            $operator = " AND ";
        }
        $this->execute($sql);
    }
}
