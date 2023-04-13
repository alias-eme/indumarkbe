<?php

namespace corsica\indumark\excel;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Border;

use corsica\indumark\importacion\CarpetaDocumento;

/**
 * Convierte la data de pagos en un excel
 */
class Documentos2Excel extends \corsica\framework\utils\DbClient
{
    const FORMATO_NUMERO = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
    const FORMATO_MONEDA = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
    const FORMATO_PORCENTAJE = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_0;
    const FORMATO_FECHA = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY;
   // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME
    
    private $sheet = null;

    /**
     * Devuelve un stream EXCEL para bajar
     */
    public function stream()
    {
        $this->generar('php://output');
    }

    /**
     * $filenam
     */
    public function generar($filename)
    {
        $cp = new CarpetaDocumento($this->env, $this->getConexion());
        $filtrado = $cp->filtrar((object)[],0);
        $items = $filtrado->items;

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
            $vencimiento = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($row->vencimiento));
            $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($row->fecha));




            $this->sheet->setCellValue('H4', 'Banco');
            $this->sheet->setCellValue('I4', 'Tipo de Pago');
            $this->sheet->setCellValue('J4', 'Días');//moneda
            $this->sheet->setCellValue('K4', 'Monto');
            $this->sheet->setCellValue('L4', 'Moneda');


            $this->sheet->setCellValue('A' . $y, $vencimiento);
            $this->setFormato('A' . $y, self::FORMATO_FECHA);

            $this->sheet->setCellValue('B' . $y, $row->carpeta);
            $this->sheet->setCellValue('C' . $y, $row->idestado);
            $this->sheet->setCellValue('D' . $y, $row->tipo);
            $this->sheet->setCellValue('E' . $y, $row->folio); 

            $this->sheet->setCellValue('F' . $y, $row->fecha); 
            $this->setFormato('F' . $y, self::FORMATO_FECHA);

            $this->sheet->setCellValue('G' . $y, $row->proveedor); 

            $this->sheet->setCellValue('H' . $y, $row->fininstitucion); 
            $this->sheet->setCellValue('I' . $y, $row->fininstrumento); 
            $this->sheet->setCellValue('J' . $y, $row->findias); 
            $this->sheet->setCellValue('K' . $y, $row->monto); 
            $this->setFormato('K' . $y, self::FORMATO_MONEDA);            
            $this->sheet->setCellValue('L' . $y, $row->moneda);

            $correlativo++;
            $y++;
        }

        return $y;
    }




    private function buildTemplate()
    {
        $this->sheet->setCellValue('A1', 'INDUMARK');
        $this->sheet->setCellValue('A2', 'www.indumark.cl');
        //listado

        $this->sheet->setCellValue('A4', 'Vencimiento');//fecha
        $this->sheet->setCellValue('B4', 'Carpeta');
        $this->sheet->setCellValue('C4', 'Estado');
        $this->sheet->setCellValue('D4', 'Tipo');
        $this->sheet->setCellValue('E4', 'Folio');//fecha
        $this->sheet->setCellValue('F4', 'Fecha');
        $this->sheet->setCellValue('G4', 'Proveedor');
        $this->sheet->setCellValue('H4', 'Banco');
        $this->sheet->setCellValue('I4', 'Tipo de Pago');
        $this->sheet->setCellValue('J4', 'Días');//moneda
        $this->sheet->setCellValue('K4', 'Monto');
        $this->sheet->setCellValue('L4', 'Moneda');

    }
    private function formatTemplate()
    {
        //el header
        $this->sheet->getStyle('A1')->getFont()->setSize(20);
        $this->sheet->getStyle('A1')->getFont()->setBold(true);
    
        $listLabelStyle = $this->getStyleListLabel();
       // $labelStyle = $this->getStyleLabel();
       // $boxStyle = $this->getStyleBox();

        $this->sheet->getStyle('A4:L4')->applyFromArray($listLabelStyle);
        $this->sheet->getStyle('A4:L4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
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
