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
class Productos2Excel extends \corsica\framework\utils\DbClient
{
    const FORMATO_NUMERO = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
    const FORMATO_MONEDA = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
    const FORMATO_PORCENTAJE = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_0;
    const FORMATO_FECHA = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY;
   // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
    
    private $sheet = null;
    public function __construct($env, $conn = null)
    {

  
      $this->logger  = new Logger(static::class);
      $this->logger->pushHandler(new StreamHandler(Config::getLogPath("manager"), Config::getLogLevel("manager")));
    }
    /**
     * Devuelve un stream EXCEL para bajar
     */
    public function stream($idcarpeta)
    {
        $this->generar($idcarpeta,'php://output');
    }

    /**
     * $filenam
     */
    public function generar($idcarpeta,$filename)
    {
        $cp = new CarpetaProducto($this->env, $this->getConexion());
        $items = $cp->cargar($idcarpeta);

        $spreadsheet = new Spreadsheet();
        $this->sheet = $spreadsheet->getActiveSheet();
        $this->sheet->getDefaultColumnDimension()->setWidth(16);
        $this->sheet->getSheetView()->setZoomScale(80);
        $this->buildTemplate();
        $this->formatTemplate();
        //$this->setHeaderData($proyecto, $totalPagado);
        $y = $this->setItemsData($items);
        //$this->setRawData($y + 10, $data, $hojadata);
        $writer = new Xls($spreadsheet);
        $writer->save($filename);
    }
    /**
     * Formatea una celda
     */
    private function setFormato(String $celda, String $formato)
    {
        $this->sheet->getStyle($celda)->getNumberFormat()->setFormatCode($formato);
    }

    /**
     * Imprime los datos de cada item
     */
    private function setItemsData($data)
    {
        $this->logger->info("setItemsData",["data"=>$data]);
        $y = 5;
        $correlativo = 1;
        foreach ($data as $row) {
            $this->logger->info("row",["row"=>$row]);
            //$calculados = $this->calculados($data, $row);
            //convertir a fecha
            $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($row->fecha));

            $this->sheet->setCellValue('A' . $y, $fecha);
            $this->setFormato('A' . $y, self::FORMATO_FECHA);

            $this->logger->info("folio",["folio"=>$row->folio]);
            $this->sheet->setCellValue('B' . $y, $row->folio);
            $this->sheet->setCellValue('C' . $y, $row->descripcion);
            $this->sheet->setCellValue('D' . $y, $row->detalle);
            $this->sheet->setCellValue('E' . $y, $row->monto); 
            $this->setFormato('E' . $y, self::FORMATO_MONEDA);
            $this->sheet->setCellValue('F' . $y, $row->idmoneda); 
            $this->sheet->setCellValue('G' . $y, $row->tasacambio); 
            $this->setFormato('G' . $y, self::FORMATO_MONEDA);
            $this->sheet->setCellValue('H' . $y, $row->doctipo); 
            $this->sheet->setCellValue('I' . $y, $row->docfolio); 
            $this->sheet->setCellValue('J' . $y, $row->neto); 
            $this->sheet->setCellValue('K' . $y, $row->iva); 
            $this->sheet->setCellValue('L' . $y, $row->total);
            $this->setFormato('J' . $y, self::FORMATO_MONEDA);
            $this->setFormato('K' . $y, self::FORMATO_MONEDA);
            $this->setFormato('L' . $y, self::FORMATO_MONEDA); 
            $correlativo++;
            $y++;
        }

        return $y;
    }




    private function buildTemplate()
    {

        $this->sheet->setCellValue('A1', 'id');
        $this->sheet->setCellValue('B1', 'idproducto');
        $this->sheet->setCellValue('C1', 'producto');
        $this->sheet->setCellValue('D1', 'descripcion');
        $this->sheet->setCellValue('E1', 'cantidad');
        $this->sheet->setCellValue('F1', 'ancho');
        $this->sheet->setCellValue('G1', 'largo');
        $this->sheet->setCellValue('H1', 'unidad');
        $this->sheet->setCellValue('I1', 'monto');
        $this->sheet->setCellValue('J1', 'cantidadtotal');


    }
    private function formatTemplate()
    {
   
        $listLabelStyle = $this->getStyleListLabel();

        $this->sheet->getStyle('A1:J1')->applyFromArray($listLabelStyle);
        $this->sheet->getStyle('A1:j1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    private function getStyleLabel()
    {
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFBBDEDF',
                ]
            ],
        ];
        return $styleArray;
    }
    private function getStyleListLabel()
    {
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFBBDEDF',
                ]
            ],
        ];
        return $styleArray;
    }
    private function getStyleBox()
    {
        $styleArray = [
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        return $styleArray;
    }

    private function setRawData($yaddress, $data, $hojadata)
    {
        /* $this->sheet->setCellValue('A' . $yaddress, 'DATOS PROVENIENTES DE LA HOJA');
        $this->sheet->getStyle('A' . $yaddress)->applyFromArray($this->getStyleListLabel());
        $yaddress++;



        $x = 0;
        $hojadata = (array)$hojadata;
        foreach ($hojadata as $key => $value) {
            $xaddress = $this->num2alpha($x);
            $this->sheet->setCellValue($xaddress . $yaddress, $key);
            $this->sheet->setCellValue($xaddress . ($yaddress + 1), $value);
            $x++;
        }
        $yaddress += 3;
        $this->sheet->setCellValue('A' . $yaddress, 'DATOS GENERADOS PARA TODAS LAS OPS');
        $this->sheet->getStyle('A' . $yaddress)->applyFromArray($this->getStyleListLabel());
        $yaddress++;
        $x = 0;
        $materiales = $data->materiales;
        $data = (array)$data;
        foreach ($data as $key => $value) {
            if ($key != 'materiales') {
                $xaddress = $this->num2alpha($x);
                $this->sheet->setCellValue($xaddress . $yaddress, $key);
                $this->sheet->setCellValue($xaddress . ($yaddress + 1), $value);
                $x++;
            }
        }
        $yaddress += 3;
        $this->sheet->setCellValue('A' . $yaddress, 'DATOS PARTICULARES DE CADA OP');
        $this->sheet->getStyle('A' . $yaddress)->applyFromArray($this->getStyleListLabel());
        $yaddress++;
        $x = 0;
        $first = true;
        foreach ($materiales as $op) {
            $op = (array)$op;

            $x = 0;
            foreach ($op as $key => $value) {

                $xaddress = $this->num2alpha($x);
                if ($first) {
                    $this->sheet->setCellValue($xaddress . $yaddress, $key);
                }
                $this->sheet->setCellValue($xaddress . ($yaddress + 1), $value);
                $x++;
            }
            if ($first) {

                $first = false;
            }
            $yaddress++;
        }*/
        $yaddress += 3;

        $this->sheet->setCellValue('A' . $yaddress, json_encode($data));
    }
    /**
     * 0>A
     */
    private function num2alpha($num)
    {
        $out = '';
        $alphabet = ['A', 'C', 'B', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'B'];
        $l = count($alphabet);
        if ($num >= $l) {
            $dec = ($num - $num % $l) / $l;
            $out = $alphabet[$dec];
            $num = $num % $l;
        }
        $out .= $alphabet[$num];
        return $out;
    }
}
