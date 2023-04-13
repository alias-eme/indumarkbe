<?php

namespace corsica\framework\smartsheets;



class ColumnMapper

{
    private $map = null;
    public function __construct($map)
    {
        $this->map = $map;
    }
    /**
     * Convierte los datos winko a los datos Smartsheet
     * 
     */
    public function convert($data)
    {
        $out = (object)array('toBottom' => true, 'cells' => array());

        $data = (array)$data;
        $keys = array_keys($data);
       
        foreach ($keys as $k) {
            $cellmap = $this->map[$k];
            if ($cellmap != null) {
                $cell = (object)array('columnId' => $cellmap['id'], 'value' => $data[$k]);
                array_push($out->cells, $cell);
            }
        }

        return $out;
    }
    /**entrega la estructura para update 
     * @param array $fields campos que se va a incluir en el update
    */
    public function convert4Update($data,$fields){
        
        $out = (object)["id"=>$data->ssrowid,"cells"=>[]];
        $data = (array)$data;
        
        foreach ($fields as $field) {
            $cellmap = $this->map[$field];
            if ($cellmap != null) {
                $cell = (object)array('columnId' => $cellmap['id'], 'value' => $data[$field]);
                array_push($out->cells, $cell);
            }
        }
        return $out;
    }
}
