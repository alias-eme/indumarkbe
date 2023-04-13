<?php

namespace corsica\framework\smartsheets;

use corsica\framework\config\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Exception;
use ReflectionClass;

abstract class AbstractSheet

{
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_GET = 'GET';
    public const METHOD_DELETE = 'DELETE';



    protected const URL = "https://api.smartsheet.com/2.0/sheets";
    protected $logger = null;

    public function __construct()
    {
        $this->logger  = new Logger(static::class);
        $path = dirname(dirname(dirname(__FILE__))) . '/log/smartsheets.log';
        $this->logger->pushHandler(new StreamHandler($path, Config::getLogLevel('smartsheets')));
    }
    /**
     * Entrega el número identificador único de la sheet
     */
    abstract public function getSheetId();
    /**
     * Entrega un mapa de datos para parsear un registro
     * en el formato
     * ['campo']=>{id:123,title:'',type''}
     */
    public static function getMap()
    {
        return json_decode(file_get_contents(self::getJsonFile()), TRUE);
    }
    /**
     * Devuelve la ruta de archivo del json que tenga el mismo nombre de la clase
     */
    public static function getJsonFile()
    {
        $rc = new ReflectionClass(static::class);
        $file = $rc->getFileName();
        return substr($file, 0, strlen($file) - 4) . ".json";
    }

    /**
     * Recibe un conjunto de datos únicos, permite agregar
     * valores constantes, compuestos o duplicados
     * nota que recibe un objeto y debe devolver tb un objeto
     * @return Object el objeto de datos modificado, o null si no se modifica nada.
     */
    protected abstract function addExtraData($inputdata);
    /**
     * Recibe data, la mapea a los campos según id y realiza un post
     */
    public function addRow($inputdata, $files = null)
    {

        $xdata = $this->addExtraData($inputdata);
        $inputdata = $xdata ? $xdata : $inputdata;

        $cm = new ColumnMapper($this->getMap());
        $ssdata = $cm->convert($inputdata);
        $ssdata = [$ssdata];
        //print_r($ssdata);
        //echo json_encode($ssdata);
        $urlextension = '/' . $this->getSheetId() . '/rows';
        $response = $this->call($urlextension, $this::METHOD_POST, json_encode($ssdata));
        return $this->responseParser($response);
    }
    /**
     * Recibe un set de datos y los postea en SS como un set.
     */
    public function addRows($rows)
    {

        $cm = new ColumnMapper($this->getMap());
        $ssrows = [];
        foreach ($rows as $row) {
            $ssrow = $cm->convert($row);
            array_push($ssrows, $ssrow);
        }
        $urlextension = '/' . $this->getSheetId() . '/rows';
        $response = $this->call($urlextension, $this::METHOD_POST, json_encode($ssrows));
        return $this->responseParser($response);
    }
    /**
     * Este debe ser el formato de actualización
     * 
     *  [{"id": "6572427401553796", "cells": [
     * {"columnId": 7518312134403972,"value": "new value"}]
     */
    public function updateRows($rows, $fields)
    {
        //$this->logger->info("updateRows", ["rows" => $rows, "fields" => $fields]);
        $cm = new ColumnMapper($this->getMap());
        $ssrows = [];
        foreach ($rows as $row) {
            $ssrow = $cm->convert4Update($row, $fields);
            array_push($ssrows, $ssrow);
        }
        //echo json_encode($ssrows);
        $urlextension = '/' . $this->getSheetId() . '/rows';
        $response = $this->call($urlextension, $this::METHOD_PUT, json_encode($ssrows));
        return $this->responseParser($response);
    }



    /**
     * Devuelve un objeto status:0,response:id si es correcto
     * o -1/code-message si no
     */
    private function responseParser($response)
    {
        $result = (object)array("status" => -1, "response" => "RESPUESTA NULA", "errorCode" => null);
        if (!is_null($response)) {

            if (is_null($response->resultCode)) {
                if (!is_null($response->errorCode)) {
                    $result->response = $response->errorCode . '-' . $response->message;
                    $result->errorCode = $response->errorCode;
                } else {
                    $result->response = json_encode($response);
                }
            } else {
                //respuesta correcta

                if (is_array($response->result)) {
                    $result->status = 1;
                    if (count($response->result) == 1) {
                        $result->response = $response->result[0]->id;
                    } else {
                        $result->response = [];
                        foreach ($response->result as $res) {
                            array_push($result->response, $res->id);
                        }
                    }
                } else {
                    $result->status = 1;
                    $result->response = $response->result->id;
                }
            }
        }
        return $result;
    }

    /* respuesta correcta
    {
        "message": "SUCCESS",
        "resultCode": 0,
        "result": [
            {
                "id": 5855532697446276,
                "sheetId": 4321886424328068,
                "rowNumber": 33,
                "siblingId": 7155915650688900,
                "expanded": true,
                "createdAt": "2021-11-29T22:38:07Z",
                "modifiedAt": "2021-11-29T22:38:07Z",
            }
        ],

        respuera fallada

        {   "errorCode" : 1006,   "message" : "Not Found",   "refId" : "chg7rqbzl0yt" }
*/


    /**
     * 
     */
    //POST /sheets/{sheetId}/rows/{rowId}/attachments
    public function addFileToRow($rowid, $file)
    {
        $file = (object)$file;
        $urlextension = '/' . $this->getSheetId() . '/rows/' . $rowid . "/attachments";
        $url = self::URL;
        if ($urlextension != null) {
            $url .= $urlextension;
        }

        $headers = array(
            'Authorization: Bearer ' . Config::getSmartSheetsKey(), "Content-Type:multipart/form-data"
        );
        $curlfile = curl_file_create($file->tmp_name, $file->type, $file->name);
        $this->logger->debug("file", array("tmp_name" => $file->tmp_name, "name" => $file->name));

        $this->logger->debug("curlfile", array("antes" => $file, "despues" => $curlfile));

        $postfields = array("file" => $curlfile);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //esto es para escribir un log de errores
        //$patherrores = dirname(dirname(dirname(__FILE__))) . '/log/curl.log';
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        //curl_setopt($ch, CURLOPT_STDERR, fopen($patherrores, 'a+')); 


        //$this->logger->info("Subiendo archivo '" . $file->name . "' a " . $url);
        $json_result = curl_exec($ch);
        $response = null;
        if (!$json_result) {
            $this->logger->error("ERROR al subir archivo", array("archivo" => $file->name, "curl_error" => curl_error($ch)));
            // throw new Exception("ERROR AL SUBIR ARCHIVO '" . $file->name . "'");
        } else {
            $response = json_decode($json_result);
            $this->logger->info("EXITO al subir archivo", array("response" => $response, "archivo" => $file->name, "message" => $response->message, "id" => $response->result->id));
        }
        return $this->responseParser($response);
    }


    public function deleteRow($rowid)
    {
        $urlextension = '/' . $this->getSheetId() . '/rows?ids=' . $rowid;
        $response = $this->call($urlextension, $this::METHOD_DELETE);
        $this->logger->debug("deleteRow", array("input" => $rowid, "response" => $response));
        return $this->responseParser($response);
    }
    /**
     * DELETE /sheets/{sheetId}/attachments/{attachmentId}
     */

    public function deleteFile($attachmentId)
    {
        $urlextension = '/' . $this->getSheetId() . '/attachments/' . $attachmentId;
        $response = $this->call($urlextension, $this::METHOD_DELETE);
        $this->logger->debug("deleteFile", array("input" => $attachmentId, "response" => $response));
        return $this->responseParser($response);
    }


    public function getSheetDescription()
    {
        ///7676341781849988?pageSize=1&page=1
        $urlextension = '/' . $this->getSheetId() . '?include=objectValue&pageSize=1&page=1';
        $rawsheet = $this->call($urlextension, $this::METHOD_GET);
        $sheet = array();
        $sheet['name'] = $rawsheet->name;
        $sheet['totalRowCount'] = $rawsheet->totalRowCount;
        $sheet['columns'] = $this->getSheetColumns($rawsheet->columns);
        return (object)$sheet;
    }
    /**
     * Obtiene la hoja completa con todos sus datos
     * Probablemente sólo se utilize una vez para mapear lo que está
     * publicado
     * debe retornar un conjunto de datos de formato
     * [{referencia:valor,cantidad:valor}]
     */
    public function getSheet()
    {
        ///7676341781849988?pageSize=1&page=1
        $urlextension = '/' . $this->getSheetId();
        $rawsheet = $this->call($urlextension, $this::METHOD_GET);
        return $rawsheet;
    }
    /**
     * Desde el mapa json, genera un mapa inverso 
     * map[idcolumn]=fieldname;
     */
    private function getReverseMap()
    {
        $map = $this->getMap();
        $reversemap = [];
        foreach ($map as $key => $value) {
            $reversemap[$value->id] = $key;
        }
    }
    public function getRow($rowid)
    {
        $urlextension = '/' . $this->getSheetId() . '/rows/' . $rowid;
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
    private function getSheetColumns($columns)
    {
        $out = array();
        foreach ($columns as $f) {
            $field = array();
            $field['id'] = $f->id;
            $field['index'] = $f->index;
            $field['title'] = $f->title;
            $field['type'] = $f->type;
            $field['options'] = $f->options;
            array_push($out, (object)$field);
        }
        return $out;
    }

    protected function call($urlextension = null, $method, $jsonstring = null)
    {
        $TIME_LIMIT=300;
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
            curl_setopt($ch, CURLOPT_TIMEOUT, $TIME_LIMIT);//tiempo máximo de ejecución
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
