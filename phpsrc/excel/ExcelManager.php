<?php

namespace corsica\indumark\excel;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Border;

use corsica\indumark\importacion\CarpetaProducto;

/**
 * Convierte la data de pagos en un excel
 */
class ExcelManager extends \corsica\framework\utils\Manager
{
    public function documentos($filtro)
    {
        $x = new Documentos2Excel($this->env);
        return $x->stream();
    }
    public function pagos()
    {
        $x = new Pagos2Excel($this->env);
        return $x->stream();
    }
    public function carpetaProductos($idcarpeta)
    {
        $model = new CarpetaProducto($this->env);
        $columns = $model->columns();
        $data = $model->cargar($idcarpeta);
        $x = new Table2Excel($this->env);
        return $x->stream($columns, $data);
    }
    public function cargaCarpetaProductos($idcarpeta, $productos)
    {
        $x = new Pagos2Excel($this->env);
        return $x->stream();
    }
    public function carpetaProductosUpload($idcarpeta, $archivo)
    {
        $this->logger->info("carpetaProductosUpload", ["idcarpeta" => $idcarpeta, "archivo" => $archivo]);
        if (1 * $idcarpeta == 0) {
            throw new \Exception("Debe estar asociado a una carpeta");
        }
        if ($archivo["type"] != "application/vnd.ms-excel") {
            throw new \Exception("Tipo de archivo debe ser 'application/vnd.ms-excel' y es ".$archivo["type"]);
        }
        $et = new Excel2Table($this->env);
        $rows = $et->parse($archivo,['idcarpeta'=>$idcarpeta]);
        $cp = new CarpetaProducto($this->env);
        foreach ($rows as $row) {
            $cp->guardar($row);
        }

        return "ok";
    }
}