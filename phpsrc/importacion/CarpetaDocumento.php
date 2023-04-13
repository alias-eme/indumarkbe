<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class CarpetaDocumento extends \corsica\framework\utils\DbClientSimple
{
    protected function tablename()
    {
        return "i_carpeta_documento";
    }
    protected function primarykey()
    {
        return "id";
    }

    /**
     * Indica si cada producto de un proyecto tiene actividads y estas suman 100;
     */
    public function cargar($idcarpeta)
    {
        $this->logger->info("cargar", ["idcarpeta" => $idcarpeta]);
        return $this->selectSimple(["idcarpeta" => $idcarpeta]);
    }
    public function guardar($carpetadocumento)
    {
        $this->logger->info("guardar", ["carpetadocumento" => $carpetadocumento]);

        if (is_null($carpetadocumento->id)) {
            $this->insertSimple($carpetadocumento);
        } else {
            $this->updateSimple($carpetadocumento);
        }
        return "ok";
    }
    public function eliminar($id)
    {
        $this->logger->info("eliminar", ["id" => $id]);
        return $this->deleteSimple($id);
        return "ok";
    }
    /**
     * Para filtrar
     */
    public function filtrar($filtro, $pagina, $sort = null)
    {
        $this->logger->info("filtrar", ["filtro" => $filtro, "pagina" => $pagina, "sort" => $sort]);
        $t = $this->tablename();
        $sql = "SELECT i_carpeta_documento.* ";
        $sql .= ", i_carpeta.folio as carpeta";
        $sql .= ", i_carpeta.idestado as idestado";
        $sql .= ", i_carpeta.proveedor ";
        $sql .= ", i_carpeta.fininstitucion ";
        $sql .= ", i_carpeta.fininstrumento ";
        $sql .= ", i_carpeta.findias ";

        $sql .= "FROM i_carpeta_documento";
        $sql .= " JOIN i_carpeta on i_carpeta.id = i_carpeta_documento.idcarpeta ";

        $where = " WHERE 1=1";
        if ($filtro->pagado == '1') {
            $where .= " AND i_carpeta_documento.pagado = 1";
        } else {
            $where .= " AND i_carpeta_documento.pagado = 0";
        }

        if (!is_null($filtro->idestado)) {
            if (is_array($filtro->idestado)) {
                if (count($filtro->idestado) > 0)
                    $where .= " AND i_carpeta.idestado IN ";
                $sep = "(";
                foreach ($filtro->idestado as $e) {
                    $where .= $sep . $this->texto($e);
                    $sep = ",";
                }
                $where .= ")";


            } else {
                $where .= " AND i_carpeta.idestado = " . $this->texto($filtro->idestado);
            }

        }



        if (strlen($filtro->proveedor) > 1)
            $where .= " AND i_carpeta.proveedor like " . $this->like($filtro->proveedor);
        if (!is_null($filtro->institucion))
            $where .= " AND i_carpeta_documento.institucion = " . $this->texto($filtro->institucion);
        if (!is_null($filtro->instrumento))
            $where .= " AND i_carpeta_documento.instrumento = " . $this->texto($filtro->instrumento);
        if (is_null($sort)) {
            $orderby = "";
        } else {
            $orderby = " ORDER BY " . $sort;
        }


        $sql .= $where;
        $sql .= $orderby;

        if ($pagina && $pagina > 0) {
            $sql .= "   LIMIT " . self::PAGINACION * ($pagina - 1) . ", " . self::PAGINACION;
        }
        $items = $this->select($sql);
        $this->logger->info("filtrar", ["sql" => $sql]);
      
        $totalitems = $this->select1("SELECT COUNT(*) as x FROM i_carpeta_documento JOIN i_carpeta on i_carpeta.id = i_carpeta_documento.idcarpeta " . $where);
        $out = array("items" => $items, "totalitems" => $totalitems);
        return (object) $out;
    }
}