<?php

namespace corsica\framework\utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use corsica\framework\config\Config;
use Exception;

/**
 * Para contar con templates de correo. Para usar hacer lo siguiente
 * crear un public const con el nombre del template ej TEST = 'test'
 * crear en la carpeta template test.html para el cuerpo y test.txt para el subject
 * crear un subtemplate (ej. test.detalles.html) en caso de que sea una lista
 * los campos se reemplazan con {{field}}, y en el caso de un subtemplate {{field | subtemplate}}
 */
class EmailTemplate
{
    public const NVA_CLAVE = "nvaclave";
    public const NVA_CIRUGIA = 'nvacirugia';
    public const NVO_PEDIDO = 'nvopedido';

    private const TAG_BEGIN = '{{';
    private const TAG_END = '}}';

    private $subject = null;
    private $body = null;
    private $data = null;

    /**
     * Crea un EmailTemplate con el nombre del template y la data correspondiente
     */
    public function __construct($template, $data)
    {
        $this->subject = file_get_contents(dirname(__FILE__) . '/templates/' . $template . '.txt');
        if ($this->subject === false) {
            throw new Exception("No se encuentra el archivo " . dirname(__FILE__) . 'templates/' . $template . '.txt');
        }
        $this->body = file_get_contents(dirname(__FILE__) . '/templates/' . $template . '.html');
        if ($this->body === false) {
            throw new Exception("No se encuentra el archivo " . dirname(__FILE__) . 'templates/' . $template . '.html');
        }
        $this->data = $data;
    }
    /** 
     * Entrega el subject correspondiente
     */
    public function subject()
    {
        return $this->completar($this->subject, $this->data);
    }
    /**
     * Entrega el cuerpo del mensaje
     */
    public function body()
    {
        return $this->completar($this->body, $this->data);
    }
    /**
     * Reemplaza los tags por valores 
     */
    private function completar($template, $data)
    {
        $data = (array)$data;
        $tag_start = strpos($template, $this::TAG_BEGIN);
        $tag_end = strpos($template, $this::TAG_END);
        if ($tag_start === false || $tag_end === false) {
            return $template;
        } else {
            $tag_end += 2;
            $tag = substr($template, $tag_start, $tag_end - $tag_start);

            $tag_name = trim(substr($tag, 2, strlen($tag) - 4));

            $palitopos = strpos($tag_name, '|');
            if ($palitopos === false) {
                $value = $data[$tag_name];
            } else {
                $subtemplate = trim(substr($tag_name, $palitopos + 1, strlen($tag_name) - $palitopos - 1));
                $tag_name = trim(substr($tag_name, 0, $palitopos));
                $value = $this->completarLista($subtemplate, $data[$tag_name]);
            }

            $template = str_replace($tag, $value, $template);
            return $this->completar($template, $data, $tag_end);
        }
    }
    /**
     * Cuando el dato es una lista de elementos
     */
    private function completarLista($subtemplate, $data)
    {
        $itemtemplate = file_get_contents(dirname(__FILE__) . '/templates/' . $subtemplate . '.html');
        if ($this->body === false) {
            throw new Exception("No se encuentra el archivo " . dirname(__FILE__) . 'templates/' . $subtemplate . '.html');
        }
        $value = "";
        if (is_array($data)) {
            foreach ($data as $item) {
                $value .= $this->completar($itemtemplate, $item) . "\n";
            }
        }

        return $value;
    }
}
