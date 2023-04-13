<?php


namespace corsica\indumark\pdf;

class CosteoPdf extends MyPdf
{
    private $data = null;
    private $costos = null;
    private $logopath = null;

    /**
    * @param object $data (conjunto de objetos de corte)
    * @param array $costos (conjunto de objetos de corte)
    */
    function __construct($data, $costos, $logopath)
    {
        $this->data = $data;
        $this->costos = $costos;
        $this->logopath = $logopath;
        parent::__construct('L', 'mm', 'Letter');
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
    private function escribe()
    {
        $this->AddPage();
        $y = $this->getY();
        $this->linea("IMPORTACIÓN", $this->data->folio);
        $this->linea("INF. IMPORTACIÓN", $this->data->descripcion);
        $this->linea("BANCO", $this->data->fininstitucion);
        $this->linea("VALOR FACTURA", number_format($this->data->finmonto));
        $this->linea("VENCIMIENTO", $this->data->finvencimiento);

        $this->setY($y);
        $this->MultiCell(150, 5*5, '', 1, '');
        $this->setY($this->getY()+5);
        $this->lineaTabla(null,true);
        $total = 0;
        $seleccion = 0;//para calcular factor de internación
        foreach ($this->costos as $costo) {
            $this->lineaTabla($costo);
            $total +=1*$costo->neto;
            $seleccion +=($costo->fi=='1') ? 1*$costo->neto:0;

        }
        $this->MultiCell(0, 5, '', 'T', '');

        //FIN TABLA
        $this->setY($this->getY()+5);
        $this->SetFont('Arial', 'B', 14);
        $width = [70,10,80];
        $height = 10;
        $this->Cell($width[0], $height, "COSTO TOTAL");
        $this->Cell($width[1], $height, ':');
        $this->MultiCell($width[2], $height, number_format($total), 1, 'C');


    
        $factorDeInternacion = '-';
        if ($seleccion>0) {
            $factorDeInternacion = number_format($total/$seleccion,4);
        }
        $this->setY($this->getY()+2);
        $this->Cell($width[0], $height, $this->fixtext("FACTOR DE INTERNACIÓN"));
        $this->Cell($width[1], $height, ':');
        $this->MultiCell($width[2], $height, $factorDeInternacion, 1, 'C');
        



    }
    function lineaTabla($data, $titulo = false)
    {
        $height = 5;
        $border ='';
        $this->SetFont('Arial', '', 8);


        if ($titulo) {
            $fecha = "FECHA";
            $doctipo = "DCTO";
            $concepto = "DETALLE";
            $monto = "MONTO";
            //$idmoneda = "MONEDA";
            $tasacambio = "TC";
            $neto = "MONTO PESOS";
            //$iva = "IVA";
            $border="TB";
            $this->SetFont('Arial', 'B', 8);

        } else {
            $fecha = date("d/m/Y",strtotime($data->docfecha));
            $doctipo = $this->fixtext($data->doctipo . $data->docfolio);
            $concepto = $this->fixtext($data->concepto);
            $monto = number_format($data->monto,2).' '.$this->fixtext($data->idmoneda);
            //$idmoneda = $this->fixtext($data->idmoneda);
            $tasacambio = $data->tasacambio=='1' ? '-' :number_format($data->tasacambio,2);
            $neto = number_format($data->neto);
            //$iva = number_format($data->iva);

        }


        $w = 25;
        $width = [20, 50, 80, 30, -1, 20, $w, $w];
        $this->Cell($width[0], $height, $fecha, $border);
        $this->Cell($width[1], $height, $doctipo, $border);
        $this->Cell($width[2], $height, $concepto, $border);

        $this->Cell($width[3], $height, $monto, $border, 0, 'R');

        //$this->Cell($width[4], $height, $idmoneda, $border, 0, 'R');
        $this->Cell($width[5], $height, $tasacambio, $border, 0, 'R');
        //$this->Cell($width[6], $height, $neto, $border, 0, 'R');
        //$this->Cell($width[7], $height, $iva, $border, 0, 'R');
        $this->MultiCell(0, $height, $neto, $border, 'R');
    }




    function Header()
    {
        $this->Image($this->logopath, 220, 5, 40);
        // Select Arial bold 15
        $this->SetFont('Arial', 'b', 14);
        $height = 7;
        $this->setY(15);
        $this->Cell(80, $height, "Indumark S.A. - Pagos de las importaciones", 'B');
        // Framed title
        $this->SetFont('Arial', 'b', 10);
        $this->MultiCell(0, $height, $this->fixtext(date("Y-m-d")), 'B', 'R');
        // Line break
        $this->Ln(10);
    }
    function Footer()
    {
        $this->SetY(-40);
        $this->SetFont('Arial', '', 8);
        $height = 4;
        $this->MultiCell(0, $height, $this->fixtext('OBSERVACIONES'), '', '');
        $this->MultiCell(0, 30, $this->fixtext(' '), 1, 'C');
    }

    private function linea(string $texto, string $valor = null, bool $checked = null)
    {
        $fontSize = 10;
        $height = 5;
        $width = 50;

        if (!is_null($checked)) {
            if ($checked) {
                $this->SetFont('ZapfDingbats', 'B', 8);
                $this->Cell($height, $height, '4', 1, 0, 'C');
            } else {
                $this->Cell($height, $height, '', 1);
            }
            $width -= $height;
        }



        $this->SetFont('Arial', 'B', $fontSize);
        $this->Cell($width, $height, $this->fixtext($texto));
        if (is_null($valor)) {
            $this->MultiCell(0, $height, " ", "");

        } else {
            $this->MultiCell(0, $height, $this->fixtext($valor), "");
        }
    }
}