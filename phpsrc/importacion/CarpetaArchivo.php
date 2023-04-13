<?php

namespace corsica\indumark\importacion;

use corsica\framework\utils\LeftJoin;
use corsica\framework\utils\FileManager;
use Exception;


/**
 * Lee un archivo de configuración json
 * primero lee la variable "env" y según eso carga el resto
 */
class CarpetaArchivo extends \corsica\framework\utils\DbClientSimple
{
    protected  function tablename()
    {
        return "i_carpeta_archivo";
    }
    protected  function primarykey()
    {
        return "id";
    }
    public function test($archivo = null)
    {
        $this->logger->info("test");
        $fm = new FileManager($this->env);
        return $fm->test($archivo, []);
    }

    /**
     */
    public function cargar($id)
    {
        $this->logger->info("cargar", ["id" => $id]);
        return $this->cargarSimple($id);
    }
    /**
     * 
     * 1-crea el registro en la BD
     * 2-sube el archivo
     * 3- Solicita guardar (si el archivo existe debe guardar con otro nombre y entregar este nombre)
     * 
     * 
     */
    public function subir($idcarpeta, $archivos)
    {
        $this->logger->info("subir", ["idcarpeta" => $idcarpeta, "archivos" => $archivos]);
        $fm = new FileManager($this->env);
        //return is_null($archivos) ? "fallo":"ok";
        $subcarpeta = $this->getFolder($idcarpeta);
        $uploaded = $fm->subirArchivos($archivos, [$subcarpeta]);
        $toinsert = [];
        foreach ($uploaded as $u) {
            $dto = (object)[
                "id" => null,
                "idcarpeta" => $idcarpeta,
                "nombre" => $u->name,
                "extension" => $u->type,
                "path" => $u->path,
                "url" => $u->url,
            ];
            array_push($toinsert, $dto);
        }
        $this->insertSimple($toinsert);

        return $uploaded;
    }
    private function getFolder($idcarpeta)
    {
        $sql = "SELECT folio FROM i_carpeta where id = " . $this->texto($idcarpeta);
        $folio = $this->select1($sql);
        $parts = explode('/', $folio);
        return ($parts[1] . '_' . $parts[0]);
    }



    public function listar($idcarpeta)
    {
        $this->logger->info("listar", ["idcarpeta" => $idcarpeta]);
        return $this->selectSimple(["idcarpeta" => $idcarpeta]);
    }

    public function eliminar($id)
    {
        $this->logger->info("eliminar", ["id" => $id]);
        $row = $this->cargar($id);
        $path = $row->path;
        $fm = new FileManager($this->env);

        $fm->eliminarArchivo($path);
        $this->deleteSimple($id);

        return "ok";
    }
}
