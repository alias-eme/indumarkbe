<?php


namespace corsica\indumark\pdf;

class DeclaracionFinancieraPdf extends MyPdf
{
    private $data = null;
    private $apoderados = null;

    /**
     * @param Object $data (conjunto de objetos de corte)
     */
    function __construct($data, $apoderados)
    {
        $this->data = $data;
        $this->apoderados = $apoderados;

        parent::__construct('P', 'mm', 'Letter');
        $this->SetMargins(10, 10);
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
        $this->escribe($this->data);
        $this->Output($type, $filename, $isUTF8);
    }
    private function escribe()
    {
        $this->AddPage();
        $this->SetFont('Arial', '', 10);


        $this->seccion1();
        $this->seccion2();
        $this->seccion3();
    }
    function seccion1()
    {
        $height = 8;
        $width = [120, 30];
        $this->Cell($width[0] + $width[1], $height, 'SERVICIO NACIONAL DE ADUANA', "LTR", 0, "C");
        $this->MultiCell(0, $height, 'DESPACHO', 'LTR', 'C');

        $this->SetFont('Arial', '', 12);
        $height = 10;
        $this->Cell($width[0] + $width[1], $height, 'DECLARACION JURADA DE ANTECEDENTES FINANCIEROS', "LR", 0, "C");
        $this->MultiCell(0, $height, '', 'LR', 'C');

        $this->SetFont('Arial', '', 10);
        $height = 6;
        $this->Cell($width[0], $height, 'DESPACHADOR AL QUE REPRESENTA', "LTR", 0, "L");
        $this->Cell($width[1], $height, '', "LTR", 0, "C");
        $this->MultiCell(0, $height, '', 'LR', 'C');

        $agencia = $this->data->agencia;
        $this->Cell($width[0], $height, $agencia, "LBR", 0, "C");
        $this->Cell($width[1], $height, 'C-49', "LBR", 0, "C");
        $this->MultiCell(0, $height, $this->fixtext('NÚMERO Y FECHA'), 'LBR', 'C');
        $height = 10;
        $this->SetFont('Arial', 'b', 12);
        $this->Cell($width[0] + $width[1], $height, 'INDUMARK S.A.', "LBR", 0, "C");
        $this->MultiCell(0, $height, '87.828.600-6', 'LBR', 'C');
    }
    function seccion2()
    {
        $this->SetY($this->getY() + 5);
        $this->SetFont('Arial', '', 12);
        $height = 8;
        $this->MultiCell(0, $height, 'ANTECEDENTES FINANCIEROS', 'LBTR', 'C');


        $this->seccion2Row("NOMBRE DEL PROVEEDOR EXTERNO", $this->data->proveedor);
        $this->seccion2Row("FACTURA", $this->data->docfolio);
        $this->seccion2Row("FECHA DE FACTURA", $this->data->docfecha);
        $this->seccion2Row("RÉGIMEN DE EXPORTACIÓN", $this->data->regimen);
        $this->seccion2Row("MONEDA", $this->data->docmoneda);
        $this->seccion2Row("CLAÚSULA DE COMPRA", $this->data->clausula);
        $this->seccion2Row("FORMA DE PAGO", $this->data->condventa);
        $this->MultiCell(0, 1, '', 'LBR', 'C');
    }
    function seccion2Row($label, $value, $label2 = null, $value2 = null)
    {
        $this->SetFont('Arial', '', 10);
        $height = 8;
        $width = [10, 70, 5];

        $this->Cell($width[0], $height, '', "L", 0, "");
        $this->Cell($width[1], $height, $this->fixtext($label), "", 0, "");
        $this->Cell($width[2], $height, ':', "C", 0, "");
        $this->MultiCell(0, $height, $this->fixtext($value), 'R', '');
    }

    function seccion3()
    {
        $this->SetY($this->getY() + 5);
        $this->SetFont('Arial', 'B', 10);
        $height = 6;

        $txt = "DECLARO BAJO JURAMENTO QUE LOS DATOS CONTENIDOS EN ESTE DOCUMENTO SON EXACTOS Y ME";
        $txt .= " RESPONZALIBILIZO DE SU EFECTIVIDAD, COMO ASI DECLARO CONOCER LAS DISPOSICIONES DE LOS";
        $txt .= " ARTICULOS 176 Y 187 DE LA ORDENANZA DE ADUANAS.";
        $this->MultiCell(0, $height, $txt, 'LTR', '');
        $this->MultiCell(0, $height, '', 'LR', '');

        $this->MultiCell(0, 6, "INDUMARK S.A", 'LR', 'C');
        $y = $this->getY();
        $mid = round($this->GetPageWidth() / 2);
        $w = 50;
        $this->Line($mid - $w, $y, $mid + $w, $y);

        $this->MultiCell(0, 6, "NOMBRE DEL IMPORTADOR", 'LR', 'C');
        $this->MultiCell(0, $height, '', 'LR', '');


        $this->MultiCell(0, 10, "", 'LR', 'C');
        $this->SetFont('Arial', 'I', 10);

        $q = count($this->apoderados);
        switch ($q) {
            case 1:
                $this->firma($mid, $this->apoderados[0], null);
                break;
            case 2:
                $this->firma($mid, $this->apoderados[0], $this->apoderados[1]);
                break;
            case 3:
                $this->firma($mid, $this->apoderados[0], $this->apoderados[1]);
                $this->MultiCell(0, 10, "", 'LR', 'C');
                $this->firma($mid, $this->apoderados[2], null);
                break;
            default:
                $this->firma($mid, $this->apoderados[0], $this->apoderados[1]);
                $this->MultiCell(0, 10, "", 'LR', 'C');
                $this->firma($mid, $this->apoderados[2], $this->apoderados[3]);
                break;
        }


        $this->MultiCell(0, $height, '', 'LR', '');
        $y = $this->getY();
        $w = 40;
        //$this->MultiCell(0, 10, "", 'LR', 'C');
        //$this->MultiCell(0, 6, "FIRMA - FECHA", 'LR', 'C');
        $this->MultiCell(0, 6, $this->fixtext("Documento impreso a las " . date("Y/m/d h:i")) , 'LR', 'C');
        $this->MultiCell(0, $height, "", 'LBR', '');

    }
    function firma($mid, $apoderado, $apoderado2 = null)
    {
        $width = $mid - 20;
        $height = 5;
        $y = $this->getY();
        $x = 30;
        if (!is_null($apoderado)) {
            if (!is_null($apoderado2)) {
                $this->Cell(5, $height, '', "L", 0, '');
                $this->Cell($width - 10, $height, self::fixtext($apoderado->nombre), "T", 0, 'C');
                $this->Cell(30, $height, '', "", 0, 'C');
                $this->Cell($width - 10, $height, self::fixtext($apoderado2->nombre), "T", 0, 'C');
                $this->MultiCell(0, $height, '', "R", '');

                $this->Cell(5, $height, '', "L", 0, '');
                $this->Cell($width - 10, $height, self::fixtext($apoderado->rut), 0, 0, 'C');
                $this->Cell(30, $height, '', "", 0, 'C');
                $this->Cell($width - 10, $height, self::fixtext($apoderado2->rut), "", 0, 'C');
                $this->MultiCell(0, $height, '', "R", '');
            } else {
                $this->Cell(10, $height, '', "L", 0, '');
                $this->Cell(2 * $width, $height, self::fixtext($apoderado->nombre), "T", 0, 'C');
                $this->MultiCell(0, $height, '', "R", '');
                $this->Cell(5, $height, '', "L", 0, '');
                $this->Cell(2 * $width, $height, self::fixtext($apoderado->rut), 0, 0, 'C');
                $this->MultiCell(0, $height, '', "R", '');
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





    private function dato($label, $value, $labelWidth = 35)
    {
        $fontSize = 10;
        $height = 5;
        $this->SetFont('Arial', '', $fontSize);
        $this->Cell($labelWidth, $height, $this->fixtext($label));
        $this->Cell(2, $height, ':');
        $this->SetFont('Arial', 'B', $fontSize);
        $this->MultiCell(0, $height, $this->fixtext($value));
    }
    private function bigbox($label, $value, $x, $y, $w)
    {
        $this->SetXY($x, $y);
        $this->SetFont('Arial', '', 8);
        $this->Cell($w, 5, $this->fixtext($label), 'LRT');
        $this->SetXY($x, $y + 5);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell($w, 10, $this->fixtext($value), 'LRB', 0, 'R');
    }
    private function box($label, $value, $x, $y, $w)
    {
        $this->SetXY($x, $y);
        $this->SetFont('Arial', '', 8);
        $this->Cell($w, 3, $this->fixtext($label), '');
        $this->SetXY($x, $y + 3);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell($w, 3, $this->fixtext($value), '', 0, 'R');
    }
}