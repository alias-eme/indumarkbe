<?php


namespace corsica\indumark\pdf;

class DeclaracionValoresPdf extends MyPdf
{
    private $data = null;
    private $apoderados = null;
    private $w6 = 0;
    private $w12 = 0;
    private $height = 6;

    private $marginleft = 5;
    private $margintop = 10;

    /**
     * @param Object $data (conjunto de objetos de corte)
     */
    function __construct($data,$apoderados)
    {
        $this->data = $data;
        $this->apoderados = $apoderados;

        parent::__construct('P', 'mm', 'Letter');

        $this->SetMargins($this->marginleft, $this->margintop);
        $this->SetAutoPageBreak(true, 0);
        $this->w6 = round(($this->GetPageWidth() - $this->marginleft * 2) / 6, 0);
        $this->w12 = round($this->w6 / 2, 0);
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

    private function titulo1($label, $align = null)
    {
        $this->SetY($this->getY() + 1);
        $this->SetFont('Arial', 'B', 10);
        $this->SetDrawColor(100);
        $this->MultiCell(0, $this->height, $label, 'TBLR', $align);
        $this->SetY($this->getY() + 1);
    }


    private function step(int $height)
    {
        $this->setY($this->getY() + $height);
    }
    private function escribe()
    {
        $this->SetDrawColor(20);
        $this->AddPage();
        $this->SetFont('Arial', 'B', 10);
        $height = 5;
        $this->MultiCell(0, $height, $this->fixtext('ANEXO N°12: DECLARACIÓN JURADA DEL VALOR Y SUS ELEMENTOS'), '', 'C');
        $this->MultiCell(0, $height, $this->fixtext('(Para completar el presente formulario, ver instrucciones de llenado)'), '', 'C');
        $this->step(3);
        $width = 150;
        $height = 7;
        $this->Cell($width, $height, $this->fixtext('I NÚMERO DE IDENTIFICACIÓN DE LA DECLARACIÓN DE INGRESO'), '', '');
        $this->SetDrawColor(180);
        $this->MultiCell(0, $height, 'C-49', 'TBLR', 'C');

        $this->MultiCell(0, $height, $this->fixtext('II NIVEL COMERCIAL DEL DUEÑO / CONSIGNATARIO / IMPORTADOR / COMPRADOR'), '', '');

        $this->SetFont('Arial', '', 8);
        $width = 30;

        $this->Cell($width, $height, $this->fixtext('Mayorista'), 'TBLR', '');
        $this->Cell($width, $height, $this->fixtext('Minorista'), 'TBLR', '');
        $this->Cell($width, $height, $this->fixtext('Usuario'), 'TBLR', '');
        $this->MultiCell(0, $height, $this->fixtext(''), '', '');
        $this->Cell($width, $height, $this->fixtext('X'), 'TBLR', 0, 'C');
        $this->Cell($width, $height, $this->fixtext(''), 'TBLR', '');
        $this->Cell($width, $height, $this->fixtext(''), 'TBLR', '');
        $this->MultiCell(0, $height, $this->fixtext(''), '', '');

        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0, $height, $this->fixtext('III MÉTODO DE VALORACIÓN'), '', '');
        $width = 100;
        $height = 7;
        $this->Cell($width, $height, $this->fixtext('Señale el método de valoración empleado'), '', '');
        $this->MultiCell(0, $height, $this->fixtext('VALOR DE TRANSACCIÓN (ART. 1°) 01'), 'TBLR', 'C');

        $this->MultiCell(0, $height, $this->fixtext('IV ELEMENTOS DE VALOR'), '', '');
        $this->MultiCell(0, $height, $this->fixtext('1.- Vinculación'), '', '');

        $this->lineaSiONo('a) Existe vinculación entre comprador y vendedor.', false);
        $this->lineaSiONo('b) Tipo de vinculación: indique la letra que corresponda. (Marque si la respuesta anterior es afirmativa).', false);
        $this->lineaSiONo('c) La vinculación entre las partes ha influido en el precio. (Marque si la respuesta de la letra a) anterior es afirmativa).', false);
        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0, $height, $this->fixtext('2.- Adiciones al valor'), '', '');

        $this->lineaSiONo('a) Existen comisiones y gastos de corretaje que corran a cargo del comprador, que no estén incluidos en el valor aduanero declarado en la importación.', false);
        $this->lineaSiONo('b) Existen bienes y/o servicios suministrados por el comprador gratuitamente o a precios reducidos, que se utilizaron en la producción y venta para la exportación de las mercancías importadas, que no estén incluidos en el valor aduanero declarado en la importación.');
        $this->lineaSiONo('c) Existen cánones y derechos de licencia que el comprador tenga que pagar directa o indirectamente como condición de venta, que no estén incluidos en el valor aduanero declarado en la importación.', false);
        $this->lineaSiONo('d) Existe algún importe o pago que se revierta al vendedor producto de la reventa, cesión o utilización posterior de la mercancía, que no este incluido en el valor aduanero declarado en la importación.', false);
        $this->lineaSiONo('e) Existen gastos de transporte que no estén incluidos en el valor aduanero declarado en la importación.', false);
        $this->lineaSiONo('f) Existen gastos de carga, descarga y manipulación ocasionados por el transporte, que no estén incluidos en el valor aduanero declarado en la importación.', false);
        $this->lineaSiONo('g) Existe algún costo de seguro que no este incluido en el valor aduanero declarado en la importación.', false);
        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0, $height, $this->fixtext('3.- Descuentos'), '', '');

        $this->lineaSiONo('Existen descuentos incluidos en el valor aduanero declarado en la importación. Si la respuesta es afirmativa, indique de qué tipo de descuento setrata, en el recuadro V Observaciones.', false);
        $this->step(5);
        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0, $height, $this->fixtext('OBSERVACIONES'), 'TLR', '');
        $this->MultiCell(0, 25, '', 'BLR', '');
        $this->step(5);
        $this->MultiCell(0, $height, $this->fixtext('NOMBRE Y FIRMA'), 'TLR', '');
        $this->MultiCell(0, 30, '', 'BLR', '');

        $this->step(-5);
        $this->SetFont('Arial', '', 8);
        $this->Cell($this->w12, $height, '', '', '');
        $this->MultiCell($this->w12 * 10, 5, $this->fixtext('NOMBRE Y FIRMA DEL DUEÑO / CONSIGNATARIO / IMPORTADOR / REPRESENTANTE LEGAL'), 'T', 'C');
        
        $this->step(-20);
        $mid = round($this->GetPageWidth() / 2);
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

    }

    function lineaSiONo($texto, $siono = null)
    {
        $height = 5;
        $w = [100, 20, 20];

        $this->SetFont('Arial', '', 8);

        if (is_null($siono)) {
            $this->MultiCell(0, $height, self::fixtext($texto), 'TBLR');
        } else {
            $y = $this->getY();
            $this->MultiCell($this->w12 * 10, $height, self::fixtext($texto), 'TBLR');
            $height = $this->getY() - $y;
            $this->setY($y);
            $this->setX($this->w12 * 10 + $this->marginleft);
            $si = ($siono) ? "SI X" : "SI";
            $no = (!$siono) ? "NO X" : "NO";

            $this->Cell($this->w12, $height, $si, 'TBLR', 0, 'C');
            $this->MultiCell(0, $height, $no, "TBLR", 'C');
        }
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


}