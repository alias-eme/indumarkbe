<?php


namespace corsica\indumark\pdf;

class CarpetaPdf extends MyPdf
{
    private $data = null;
    private $logopath = null;

    /**
     * @param Object $data (conjunto de objetos de corte)
     */
    function __construct($data,$logopath)
    {
        $this->data = $data;
        $this->logopath = $logopath;
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
        $this->escribe($this->data);
        $this->Output($type, $filename, $isUTF8);
    }
    private function escribe()
    {
        $this->AddPage();
        $this->SetFont('Arial', 'BU', 10);
        $height = 5;
        $this->MultiCell(0,$height, 'AGENTE DE ADUANA', '','');
        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0,$height, 'PRESENTE', '','');
        $this->SetFont('Arial', '', 10);

        $this->dato("En atención a", 'SR(A)');
        $this->dato("Referencia",'ESBELT S.A.');
        $this->dato("Carpeta", '72/2022');


        $this->SetFont('Arial', '', 10);



        //$this->dato("Carpeta", $this->data->fechaaprobacion);
        $height = 7;
        $this->MultiCell(0, $height, '', 'B');
        $this->MultiCell(0,$height, self::fixtext('De nuestra consideración:'), '','');
        $this->MultiCell(0,$height, self::fixtext('ENVÍO DE DOCUMENTOS PARA REALIZAR TRÁMITES DE IMPORTACION. LOS DOCUMENTOS SON LOS SIGUIENTES:'), '','');
 //       $this->MultiCell(0, $height, $this->fixtext(json_encode($data)));

 $this->escribeItem('ORIGINAL DE FACTURA N°72 DEL 24-11-2022');
 $this->escribeItem('ORIGINAL DE POLIZA DEFINITIVA N°0 -COMPROBANTE N°');
 $this->escribeItem('COPIA DE CODIGOS CIPS, QUE YA SE ENCUENTRAN EN SU PODER');
 $this->escribeItem('B/L ORIGINAL N° DE FECHA');
 $this->escribeItem('PACKING LIST N°');
 $this->escribeItem('INSTRUCTIVO SOBRE CONDICIONES DE IMPORTACION');
 $this->escribeItem('DECLARACION JURADA DEL VALOR DE LOS ELEMENTOS');
 $this->escribeItem('DECLARACION JURADA DE ANTECEDENTES FINANCIEROS');
 $this->escribeItem('CERTIFICADO DE ORIGEN');

   //     $this->SetY($this->getY()+10);
   //     $this->escribeItem("gello");


    }

    function escribeItem($item)
    {
        //l=item 4 boldcheck 6 boldtimes 3/5
        $dot = 'l';
        $width=5;
        $height = 5;  
        $this->Cell($width, $height, '');
      
        $this->SetFont('ZapfDingbats', 'B', 6);
        $this->Cell($width, $height, $dot);

        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0,$height, self::fixtext($item));

    }


    function Header()
    {
        $this->Image($this->logopath, 20, 5, 60);
        // Select Arial bold 15
        $this->SetFont('Arial', 'i', 10);
        $height = 7;
        $this->setY(15);
        //$this->Cell(80, $height, "Indumark S.A.", 'B');
        // Framed title
        $this->MultiCell(0, $height, $this->fixtext('Santiago, 23-Ene-2022'), 'B', 'R');
        // Line break
        $this->Ln(10);
    }
    function Footer()
    {
        $this->SetY(-30);
        $this->SetFont('Arial', '', 8);
        $height = 4;
        $this->MultiCell(0, $height, $this->fixtext('INDUMARK S.A.'), 'T', 'C');
        $this->MultiCell(0, $height, $this->fixtext('CERRO SOMBRERO 670-B'), '', 'C');
        $this->MultiCell(0, $height, $this->fixtext('PO: 9250000'), '', 'C');
        $this->MultiCell(0, $height, $this->fixtext('FONOS: 56 2 -29459900 - FAX: 56 2 29459904'), '', 'C');
        $this->MultiCell(0, $height, $this->fixtext('MAIL: CONTACTO@INDUMARK.CL - WEB: WWW.INDUMARK.CL'), '', 'C');
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
