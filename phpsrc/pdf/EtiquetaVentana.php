<?php


namespace corsica\indumark\pdf;

use corsica\mto\docs\Doc;
use SimpleXMLElement;
use Fpdf\Fpdf;
use Exception;

class EtiquetaVentana extends MyPdf
{
    private $data = null;
    /**
     * @param Array $data (conjunto de objetose)
     */
    function __construct($data)
    {
        $this->data = $data;
        parent::__construct('L', 'mm', array(40, 100));
        $this->SetMargins(2, 2);
        $this->SetAutoPageBreak(true, 0);
    }

    /**
     * I: envía el fichero al navegador de forma que se usa la extensión (plug in) si está disponible.
     * D: envía el fichero al navegador y fuerza la descarga del fichero con el nombre especificado por name.
     * F: guarda el fichero en un fichero local de nombre name.
     * S: devuelve el documento como una cadena.
     */
    function print($type = 'I', $filename = 'doc.pdf', $isUTF8 = false)
    {
        foreach ($this->data as $data) {
            $data = (array)$data;
            $this->escribeEtiqueta($data);
        }


        $this->Output($type, $filename, $isUTF8);
    }
    private function escribeEtiqueta($data)
    {
        $this->AddPage();
        $this->codigo($data);
        $this->bigbox("Ancho", $data['ancho'],  55, 3, 20);
        $this->bigbox("Alto", $data['alto'], 75, 3, 20);

        $this->setXY(2, 20);
        $max = 50;
        $this->dato("Proyecto", $this->fixlargo($data['proyecto'], $max));
        $this->dato("Cliente", $this->fixlargo($data['cliente'], $max));
        //$this->dato("Serie", $this->fixlargo($data['serie'], $max));
        //$this->dato("Herrajes", $this->fixlargo($data['herrajes'], $max));
        $this->dato("Vidrios", $this->fixlargo($data['vidrios'], $max));
    }
    private function fixlargo($texto, $max)
    {
        $largo = strlen($texto);
        if ($largo > $max) {
            return substr($texto, 0, $max);
        }
        return $texto;
    }
    private function bigbox($label, $value, $x, $y, $w)
    {
        $this->SetXY($x, $y);
        $this->SetFont('Arial', '', 8);
        $this->Cell($w, 5, $this->fixtext($label), 'LRT');
        $this->SetXY($x, $y + 5);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell($w, 7, $this->fixtext($value), 'LRB', 0, 'R');
    }
    private function dato($label, $value)
    {

        $height = 4;
        $labelWidth = 15;
        $this->SetFont('Arial', '', 8);
        $this->Cell($labelWidth, $height, $this->fixtext($label));
        $this->Cell(2, $height, ':');
        $this->SetFont('Arial', 'B', 12);
        $this->MultiCell(0, $height, $this->fixtext($value));
    }
    private function codigo($data)
    {
        $x = 3;
        $y = 3;
        $w = 50;
        $h = 10;
        $code = $data['nombre'];
        $this->setXY($x, $y);
        $this->code128($x, $y, $code, $w, $h);
        $z = $this->GetY();

        $this->setXY($x, $y + 10);
        $this->SetFont('Arial', '', 14);
        //$this->SetFillColor(255);
        $this->Cell($w, 5, $code, 0, 0, 'C');
        //$this->setXY($x, $y+15);
        //$this->Cell($w, 5, $data['posicion'], 0, 0, 'C');
    }
}
