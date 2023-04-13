<?php

namespace corsica\framework\smartsheets;

use corsica\framework\config\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Exception;
use ReflectionClass;

abstract class AbstractReport

{
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_GET = 'GET';
    public const METHOD_DELETE = 'DELETE';

    private $map = null;



    protected const URL = "https://api.smartsheet.com/2.0/reports";
    protected $logger = null;

    public function __construct()
    {
        $this->logger  = new Logger(static::class);
        $path = dirname(dirname(dirname(__FILE__))) . '/log/smartsheets.log';
        $this->logger->pushHandler(new StreamHandler($path, Config::getLogLevel('smartsheets')));
        $this->map = $this->getMap();
    }
    /**
     * Entrega el número identificador único del report
     */
    abstract public function getReportId();
    /**
     * Entrega un mapa de datos para parsear un registro
     * en el formato
     * ['campo']=>{id:123,title:'',type''}
     */
    private static function getMap()
    {
        return json_decode(file_get_contents(self::getJsonFile()), TRUE);
    }
    /**
     * Entrega un row con la estructura del json
     * definida
     * @param $row de Smartsheets que debo limpiar
     * @param array DTO
     */
    public function parseRow($row)
    {

        //primero mapeo las celdas y id
        $cellmap = [];
        foreach ($row->cells as $cell) {
            $cellmap[$cell->virtualColumnId] = $cell->value;
        }
        //echo json_encode($cellmap);
        //echo "poto",PHP_EOL;
        //echo json_encode($this->map);
        $out = [];
        foreach ($this->map as $field => $value) {
            ///echo "Buscando ".$field.' '.$value['id'],PHP_EOL;
            $out[$field] = $cellmap[$value['id']];
        }
        return $out;
    }
    public function buscar($params)
    {
        $result = $this->_buscar($params);
        if (is_null($result)) {
            return (object)["error" => "El registro no fue encontado"];
        } else {
            return (object)["data" => $result];
        }
    }

    /**
     * Entrega un record según el 'parametro:>valor' de
     * @params Array $params, ej ["hapId"="27898-01"]
     */
    private function _buscar($params)
    {
        $PAGE_SIZE = 1000;
        $page = 1;
        $maxpage = 1;
        while ($maxpage >= $page) {

            $report = $this->getReport($PAGE_SIZE, $page);
            if ($page == 1) {
                $maxpage = floor($report->totalRowCount / $PAGE_SIZE + 1);
                //echo "maxpage ", $maxpage, PHP_EOL;
            }
            $page++;
            foreach ($report->rows as $row) {
                $item = $this->parseRow($row);
                $match = true;
                foreach ($params as $fieldname => $fieldvalue) {


                    if (!($item[$fieldname] == $fieldvalue)) {
                        $match = false;
                        break;
                    }
                }
                if ($match == true) {
                    return $item;
                }
            }
        }
        return null;
    }



    /**
     * Devuelve la ruta de archivo del json que tenga el mismo nombre de la clase
     */
    private static function getJsonFile()
    {
        $rc = new ReflectionClass(static::class);
        $file = $rc->getFileName();
        return substr($file, 0, strlen($file) - 4) . ".json";
    }


    public function getSheetDescription()
    {
        ///7676341781849988?pageSize=1&page=1
        $urlextension = '/' . $this->getReportId() . '?include=objectValue&pageSize=1&page=1';
        $rawsheet = $this->call($urlextension, $this::METHOD_GET);
        $sheet = array();
        $sheet['name'] = $rawsheet->name;
        $sheet['totalRowCount'] = $rawsheet->totalRowCount;
        $sheet['columns'] = $this->getReportColumns($rawsheet->columns);
        return (object)$sheet;
    }
    /**
     * Obtiene la hoja completa con todos sus datos
     * Probablemente sólo se utilize una vez para mapear lo que está
     * publicado
     * debe retornar un conjunto de datos de formato
     * [{referencia:valor,cantidad:valor}]
     */
    public function getReport($pageSize = 1, $page = 1)
    {
        ///7676341781849988?pageSize=1&page=1
        $urlextension = '/' . $this->getReportId() . '?pageSize=' . $pageSize . '&page=' . $page;
        $rawsheet = $this->call($urlextension, $this::METHOD_GET);
        return $rawsheet;
    }

    public function getRow($rowid)
    {
        $urlextension = '/' . $this->getReportId() . '/rows/' . $rowid;
        $response = $this->call($urlextension, $this::METHOD_GET);
        return $response;
    }
    /**
     * Entrega el mapa de campos en la consola
     * para facilitar la creación del mapa de campos
     */
    public function printMap()
    {
        $sheet = $this->getSheetDescription();
        echo '$map = [', PHP_EOL;
        $sep = '';
        foreach ($sheet->columns as $c) {
            $str = $sep . PHP_EOL . "'" . $c->title . "' => ['index' => '" . $c->index . "', 'id' => '" . $c->id . "', 'title' => '" . $c->title . "', 'type' => '" . $c->type . "']";
            echo $str;
            $sep = ',';
        }
        echo "];", PHP_EOL;
    }
    /**
     * Para imprimir el mapa por consola
     */
    public function printJsonMap()
    {
        $sheet = $this->getSheetDescription();
        //echo json_encode($sheet);
        echo '{', PHP_EOL;
        $sep = '';
        foreach ($sheet->columns as $c) {
            $str = $sep . PHP_EOL . '"' . $c->title . '" : {"index":"' . $c->index . '"';
            $str .= ', "id" : "' . $c->id . '", "title" : "' . $c->title . '", "type" : "' . $c->type . '"';
            $str .=  $this->printOptions($c->options);
            $str .=  '}';
            echo $str;
            $sep = ',';
        }
        echo "}", PHP_EOL;
    }
    public function printJsonMapOptions()
    {
        $sheet = $this->getSheetDescription();
        echo '{', PHP_EOL;
        $sep = '';
        foreach ($sheet->columns as $c) {
            if ($c->options) {
                $str = $sep . PHP_EOL . '"' . $c->title . '" : {"index":"' . $c->index . '"';
                $str .= ', "id" : "' . $c->id . '", "title" : "' . $c->title . '", "type" : "' . $c->type . '"';
                $str .=  $this->printOptions($c->options);
                $str .=  '}';
                echo $str;
                $sep = ',';
            }
        }
        echo "}", PHP_EOL;
    }
    private function printOptions($options)
    {
        $str = "";
        if ($options) {
            $str = ', "options" : [';
            $sep = "";

            foreach ($options as $o) {
                $str .= $sep . '"' . $o . '"';
                $sep = ",";
            }
            $str .= ']';
        }
        return $str;
    }
    /**
     * Obtiene los datos de una columna
     */
    private function getReportColumns($columns)
    {
        $out = array();
        foreach ($columns as $f) {
            $field = array();
            $field['id'] = $f->virtualId;
            $field['index'] = $f->index;
            $field['title'] = $f->title;
            $field['type'] = $f->type;
            //echo json_encode($f), PHP_EOL;
            array_push($out, (object)$field);
        }
        return $out;
    }

    protected function call($urlextension = null, $method, $jsonstring = null)
    {
        $TIME_LIMIT = 300;
        set_time_limit($TIME_LIMIT);


        $url = self::URL;
        if ($urlextension != null) {
            $url .= $urlextension;
        }

        $ch = curl_init($url);
        //curl_setopt($ch, CURLOPT_ENCODING, "ISO-8859-1");
        //curl_setopt($ch, CURLOPT_URL,  self::URL);
        //set the content type to application/json
        $headers = array(
            'Authorization: Bearer ' . Config::getSmartSheetsKey(),
            'Content-Type:application/json'
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //attach encoded JSON string to the POST fields
        if ($method == AbstractSheet::METHOD_POST) {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($jsonstring) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstring);
            }
            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, $TIME_LIMIT); //tiempo máximo de ejecución
        } else if ($method == AbstractSheet::METHOD_PUT) {
            if ($jsonstring) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstring);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            }
        } else {
            curl_setopt($ch, CURLOPT_POST, false);
        }

        if ($method == AbstractSheet::METHOD_DELETE) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($method == AbstractSheet::METHOD_POST || $method == AbstractSheet::METHOD_PUT) {
            $this->logger->info("INPUT:" . $jsonstring);
        }

        //$this->logger->debug("INPUT:" . json_encode($jsonstring));

        $json_result = curl_exec($ch);
        //si falla devuelve false
        if (!$json_result) {
            //ERROR DE LLAMADO
            $msg = "Problema de conexión: " . curl_error($ch);
            $this->logger->error("Errores CURL=" . $msg);

            $result = (object)["code" => "-1", "error" => $msg];
            //throw new Exception(curl_error($ch));
        } else {
            //TRANSFORMO EL JSON A DATOS
            //$this->logger->debug("Respuesta OK CURL=" . $json_result);
            $result = json_decode($json_result, FALSE);
            if (is_null($result->errorCode)) {
                $this->logger->debug("OUTPUT:" . $json_result);
                return $result;
            } else {
                $this->error = $result->error;
                $this->logger->error("ERROR", array("output" => $json_result, "input" => $jsonstring));
                return $result;
            }
        }
    }
}
