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
class Excel2Table
{
    const FORMATO_NUMERO = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
    const FORMATO_MONEDA = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
    const FORMATO_PORCENTAJE = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_0;
    const FORMATO_FECHA = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY;
    // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME

    private $logger = null;





    public function __construct($env, $conn = null)
    {
        $this->logger = new Logger(static::class);
        $this->logger->pushHandler(new StreamHandler(Config::getLogPath("manager"), Config::getLogLevel("manager")));
    }
    /**
     * @param fiedfields an associative array like ["masterid"=>10]
     */
    public function parse($file, $fixedfields)
    {
        $this->logger->info("parse", ["file" => $file, "fixedfields" => $fixedfields]);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file["tmp_name"]);
        $sheet = $spreadsheet->getActiveSheet();

        $columnNames = $this->readColumnNames($sheet);
        $rows = $this->readColumnValues($sheet, $columnNames, $fixedfields);
        return $rows;
    }
    private function readColumnNames($sheet)
    {
        $out = [];
        $x = 0;
        $adr = self::num2alpha($x) . '1';
        $val = $sheet->getCell($adr)->getValue();
        while (!is_null($val) && $val != '') {
            array_push($out, $val);
            $x++;
            $adr = self::num2alpha($x) . '1';
            $val = $sheet->getCell($adr)->getValue();
        }
        return $out;
    }
    private function readColumnValues($sheet, $columnNames, $fixedfields)
    {
        $qcol = count($columnNames);

        $rows = [];

        for ($y = 2; ; $y++) {
            $row = [];
            for ($x = 0; $x < $qcol; $x++) {
                $adr = self::num2alpha($x) . $y;
                $row[$columnNames[$x]] = $sheet->getCell($adr)->getValue();
                if (array_key_exists($columnNames[$x], $fixedfields)) {
                    $row[$columnNames[$x]] = $fixedfields[$columnNames[$x]];
                }
 
            }

            if (self::emptyRow($row, $fixedfields)) {
                break;
            }
            array_push($rows, (object) $row);
        }
        return $rows;
    }
    /**
     * @param $num 0,1,2,3
     * @return 'A','B'...
     */
    private static function num2alpha($num)
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
    private static function emptyRow($row, $fixedfields)
    {
        foreach ($row as $name => $value) {
            if (!is_null($value) && $value != '') {
                if (!array_key_exists($name, $fixedfields)) {
                    return false;
                }
            }
        }
        return true;
    }
}