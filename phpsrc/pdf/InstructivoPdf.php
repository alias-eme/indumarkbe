<?php


namespace corsica\indumark\pdf;

class InstructivoPdf extends MyPdf
{
    private $data = null;
    private $apoderados = null;


    /**
     * @param object $data (conjunto de objetos de corte)
     * @param array $apoderados (conjunto de objetos de corte)
     * @param string $logopath (conjunto de objetos de corte)
     */
    function __construct($data, $apoderados)
    {
        $this->data = $data;
        $this->apoderados = $apoderados;
        parent::__construct('P', 'mm', 'Letter');
        $this->SetMargins(20, 10);
        $this->SetAutoPageBreak(true, 0);
    }

    /**
     * I: envía el fichero al navegador de forma que se usa la extensión (plug in) si está disponible.
     * D: envía el fichero al navegador y fuerza la descarga del fichero con el nombre especificado por name.
     * F: guarda el fichero en un fichero local de nombre name.
     * S: devuelve el documento como una cadena.
     */
    function print($type = 'I', $filename = 'pago.pdf', $isUTF8 = false)
    {
        $this->escribe();
        $this->Output($type, $filename, $isUTF8);
    }


    function step(int $y)
    {
        $this->setY($this->getY() + $y);
    }

    function seccion(int $y1, int $y2)
    {
        $y1 -= 2;
        $y2 += 2;
        $x1 = 18;
        $x2 = $this->GetPageWidth() - $x1;
        $this->Line($x1, $y1, $x2, $y1);
        $this->Line($x2, $y1, $x2, $y2);
        $this->Line($x1, $y1, $x1, $y2);
        $this->Line($x1, $y2, $x2, $y2);

    }
    function condventa(string $condventa)
    {
        $out = [false, false, false];

        if (strpos($condventa, 'CONTADO') !== false || strpos($condventa, 'COBRA') !== false) {
            $out[1] = true;
            return $out;
        }
        if (strpos($condventa, 'CARTA') !== false|| strpos($condventa, 'LETRA') !== false) {
            $out[0] = true;
            return $out;
        }
        $out[2] = true;
        return $out;
    }
    function origen(string $tienefin,string $instrumento)
    {
        $out = [false, false, false];

        if ($tienefin== '0') {
            $out[2] = true;
            return $out;
        }
        if (strpos($instrumento, 'CARTA') !== false) {
            $out[0] = true;
            return $out;
        }
        $out[1] = true;
        return $out;
    }
    function clausula(string $clausula)
    {
        $out = [false, false, false, false, false];

        if (strpos($clausula, 'EX') !== false) {
            $out[0] = true;
            return $out;
        }
        if (strpos($clausula, 'FOB') !== false) {
            $out[1] = true;
            return $out;
        }
        if (strpos($clausula, 'C&F') !== false) {
            $out[2] = true;
            return $out;
        }
        if (strpos($clausula, 'C&S') !== false) {
            $out[3] = true;
            return $out;
        }
        $out[4] = true;
        return $out;
    }
    private function escribe()
    {
        $this->AddPage();
        $height = 5;

        $y = $this->getY();
        $this->linea('IMPORTADOR', 'INDUMARK S.A.');
        $this->linea('PROVEEDOR', $this->data->proveedor);
        $this->linea('REFERENCIA', 'IMPORTACIÓN ' . $this->data->folio);
        $this->seccion($y, $this->getY());
        $this->step($height);
        $y = $this->getY();
        $this->linea('PAÍS DE ORIGEN', $this->data->origen);
        $this->linea('PAÍS DE ADQUISICIÓN', $this->data->regimen);
        $this->seccion($y, $this->getY());
        $this->step($height);
        $y = $this->getY();
        $this->linea('FORMA DE PAGO AL PROVEEDOR');
        $condventa = $this->condventa($this->data->condventa);
        $this->linea('COBRANZA', ' ', $condventa[0]);
        $this->linea('ACREDITIVO', ' ', $condventa[1]);
        $this->linea('CONTADO', null, $condventa[2]);
        $this->seccion($y, $this->getY());
        $this->step($height);
        $y = $this->getY();
        $this->linea('CLAÚSULA DE COMPRA');
        $clausula = $this->clausula($this->data->clausula);
        $this->linea('EX-FCA', null, $clausula[0]);
        $this->linea('FOB', null, $clausula[1]);
        $this->linea('C&F', null, $clausula[2]);
        $this->linea('C&S', null, $clausula[3]);
        $this->linea('CIF', null, $clausula[4]);
        $this->seccion($y, $this->getY());
        $this->step($height);
        $y = $this->getY();
        $this->linea('ORIGEN DE DIVISAS');
        $origen = $this->origen($this->data->tienefinanciamiento,$this->data->fininstrumento);
        $this->linea('MERCADO BANCARIO', null, $origen[0]);
        $this->linea('DISPONIBILIDADES PROPIAS', null, $origen[1]);
        $this->linea('OTROS (DESCRIBIR)', '', $origen[2]);

        $this->seccion($y, $this->getY());
        $this->step($height);
        $y = $this->getY();
        $this->linea('MERCADERÍA');
        $this->linea('N° DE ARTICULO DE PRODUCTO', $this->data->descripcion);
        $this->linea('PDA ARANCELARIA', '');
        $this->linea('MARCA', '');
        $this->linea('MODELO', '');
        $this->linea('PARA USO EN', '');
        $this->linea('COMPOSICIÓN', '');
        $this->linea('POTENCIA', '');
        $this->linea('VOLTAJE', '');
        $this->linea('TALLA', '');
        $this->linea('NUMEROS', '');
        $this->linea('DIMENSION', '');
        $this->linea('OTROS (DESCRIBIR)', '');
        $this->seccion($y, $this->getY());
        $this->step($height);



        $this->SetFont('Arial', 'I', 10);
        $y = $this->getY();
        $mid = round($this->GetPageWidth() / 2);
        $x = 50;

        $this->setY($this->getY() + 8);
        $q = count($this->apoderados);
        switch ($q) {
            case 0:
                break;
            case 1:
                $this->firma($mid, $this->apoderados[0], null);
                break;
            case 2:
                $this->firma($mid, $this->apoderados[0], $this->apoderados[1]);
                break;
            case 3:
                $this->firma($mid, $this->apoderados[0], $this->apoderados[1]);
                $this->setY($this->getY() + 8);
                $this->firma($mid, $this->apoderados[2], null);
                break;
            default:
                $this->firma($mid, $this->apoderados[0], $this->apoderados[1]);
                $this->setY($this->getY() + 8);
                $this->firma($mid, $this->apoderados[2], $this->apoderados[3]);
                break;
        }

        // $this->MultiCell(0, $height, self::fixtext("JAIME MANZANO TAGLE - MARCELO VASQUEZ TRINCADO"), 0, 'C');
        // $this->MultiCell(0, $height, self::fixtext("10.266.206.7 12.402.644-K"), 0, 'C');
    }
    function firma($mid, $apoderado, $apoderado2 = null)
    {
        $width = $mid - 20;
        $height = 5;
        $y = $this->getY();
        $x = 30;
        if (!is_null($apoderado)) {
            if (!is_null($apoderado2)) {

                $this->Cell($width - 10, $height, self::fixtext($apoderado->nombre), "T", 0, 'C');
                $this->Cell(20, $height, '', "", 0, 'C');
                $this->MultiCell(0, $height, self::fixtext($apoderado2->nombre), "T", 'C');


                $this->Cell($width - 10, $height, self::fixtext($apoderado->rut), 0, 0, 'C');
                $this->Cell(20, $height, '', "", 0, 'C');
                $this->MultiCell(0, $height, self::fixtext($apoderado2->rut), 0, 'C');
            } else {
                $this->line($mid - $x, $y, $mid + $x, $y);
                $this->MultiCell(0, $height, self::fixtext($apoderado->nombre), "T", 'C');
                $this->MultiCell(0, $height, self::fixtext($apoderado->rut), 0, 'C');
            }
        }
    }

    function escribeItem($item)
    {
        //l=item 4 boldcheck 6 boldtimes 3/5
        $dot = 'l';
        $width = 5;
        $height = 5;
        $this->Cell($width, $height, '');

        $this->SetFont('ZapfDingbats', 'B', 6);
        $this->Cell($width, $height, $dot);

        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0, $height, self::fixtext($item));
    }


    function Header()
    {

        // Select Arial bold 15
        $this->SetFont('Arial', 'B', 12);
        $height = 7;
        $this->setY(15);
        $this->MultiCell(0, $height, $this->fixtext('INSTRUCTIVO SOBRE CONDICIONES DE IMPORTACIÓN'), 'B', 'C');
        // Line break
        $this->Ln(10);
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', '', 8);
        $height = 4;

        $this->MultiCell(0, $height, $this->fixtext('FECHA DE IMPRESIÓN: ' . date("Y-m-d H:i")), '', 'C');
    }

    /**
     * 
     */
    private function linea(string $texto, string $valor = null, bool $checked = null)
    {
        $fontSize = 8;
        $height = 5;
        $width = 60;

        if (!is_null($checked)) {
            if ($checked) {
                $this->SetFont('ZapfDingbats', 'B', 8);
                $this->Cell($height, $height, '4', 1, 0, 'C');
            } else {
                $this->Cell($height, $height, '', 1);
            }
            $width -= $height;
        }



        $this->SetFont('Arial', '', $fontSize);
        $this->Cell($width, $height, $this->fixtext($texto));
        if (is_null($valor)) {
            $this->MultiCell(0, $height, " ", "");

        } else {
            $this->MultiCell(0, $height, $this->fixtext($valor), "B");
        }
    }

}