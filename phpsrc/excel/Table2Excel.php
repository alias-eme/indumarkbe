<?php

namespace corsica\indumark\excel;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Border;
use corsica\framework\config\Config;
use corsica\indumark\importacion\CarpetaProducto;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Convierte la data de pagos en un excel
 */
class Table2Excel
{
    const FORMATO_NUMERO = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
    const FORMATO_MONEDA = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
    const FORMATO_PORCENTAJE = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_0;
    const FORMATO_FECHA = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY;
    // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME

    private $sheet = null;
    private $logger = null;



    public function __construct($env, $conn = null)
    {


        $this->logger  = new Logger(static::class);
        $this->logger->pushHandler(new StreamHandler(Config::getLogPath("manager"), Config::getLogLevel("manager")));
    }

    /**
     * Devuelve un stream EXCEL para bajar
     */
    public function stream($columns, $data)
    {
        $this->generar($columns, $data, 'php://output');
    }

    /**
     * $filenam
     */
    public function generar($columns, $data, $filename)
    {

        $spreadsheet = new Spreadsheet();
        $this->sheet = $spreadsheet->getActiveSheet();
        $this->sheet->getDefaultColumnDimension()->setWidth(16);
        $this->sheet->getSheetView()->setZoomScale(80);

        $this->buildTemplate($columns);
        $this->formatTemplate($columns);

        $this->setData($columns, $data);

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
    private function setData($columns, $data)
    {
        $this->logger->info("setData", ["columns" => $columns, "data" => $data]);
        $y = 2;
        foreach ($data as $row) {
            $this->logger->info("row", ["row" => $row]);
            $row = (array)$row;
            $x = 0;
            foreach ($columns as $col) {
                $this->sheet->setCellValue($this->num2alpha($x) . $y, $row[$col->COLUMN_NAME]);
                switch ($col->DATA_TYPE) {
                    case 'date':
                    case 'timestamp':
                        $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($row[$col->COLUMN_NAME]));
                        $this->sheet->setCellValue('A' . $y, $fecha);
                        $this->setFormato('A' . $y, self::FORMATO_FECHA);
                        break;
                    case 'int':
                    case 'decimal':
                        $this->sheet->setCellValue($this->num2alpha($x) . $y, $row[$col->COLUMN_NAME]);
                        $this->setFormato('E' . $y, self::FORMATO_NUMERO);
                        break;
                    default:
                        $this->sheet->setCellValue($this->num2alpha($x) . $y, $row[$col->COLUMN_NAME]);
                }
                $x++;
            }
            $y++;
        }

        return $y;
    }




    private function buildTemplate($columns)
    {
        $x = 0;
        $y = 1;
        foreach ($columns as $col) {
            $this->sheet->setCellValue($this->num2alpha($x) . $y, $col->COLUMN_NAME);
            $x++;
        }
    }
    private function formatTemplate($columns)
    {
        $q = count($columns);
        $letter = $this->num2alpha($q-1);

        $listLabelStyle = $this->getStyleListLabel();

        $this->sheet->getStyle('A1:' . $letter . '1')->applyFromArray($listLabelStyle);
        $this->sheet->getStyle('A1:' . $letter . '1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    private static function getStyleLabel()
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
    private static function getStyleListLabel()
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
    private static function getStyleBox()
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
        $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'B'];
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
