<?php


namespace corsica\indumark\pdf;

class DeclaracionValoresAntiguaPdf extends MyPdf
{
    private $data = null;
    private $w6 = 0;
    private $w12 = 0;
    private $height = 6;

    /**
     * @param Object $data (conjunto de objetos de corte)
     */
    function __construct($data)
    {
        $this->data = $data;

        parent::__construct('P', 'mm', 'Legal');
        $marginleft = 5;
        $margintop = 10;
        $this->SetMargins($marginleft, $margintop);
        $this->SetAutoPageBreak(true, 0);
        $this->w6 = round(($this->GetPageWidth() - $marginleft * 2) / 6, 0);
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

    private function titulo1($label,$align=null) {
        $this->SetY($this->getY() + 1);
        $this->SetFont('Arial', 'B', 10);
        $this->SetDrawColor(100);
        $this->MultiCell(0, $this->height, $label, 'TBLR', $align);
        $this->SetY($this->getY() + 1);
    }


    private function escribe()
    {
        $this->SetDrawColor(20);
        $this->AddPage();
        $this->SetFont('Arial', 'B', 10);
        $height = 10;
        $this->MultiCell(0, $height, 'ANEXO 12', '', 'C');
        $height = $this->height;
        $this->titulo1( 'DECLARACION JURADA DEL VALOR Y SUS ELEMENTOS','C');

        $this->titulo1( 'I IDENTIFICACION DE LAS PARTES');

        $this->SetY($this->getY() + 1);
        $this->SetFont('Arial', 'B', 8);
        $this->SetDrawColor(200);
        $this->MultiCell(0, $height, '1 Despachador responsable', 'TBLR', '');
        $this->MultiCell(0, $height, '', 'TBLR', '');

        $width = round(($this->GetPageWidth() - 10) / 6, 0); //ese es el margen

        $this->Cell($width * 3, $height, '2 Importador o Comprador', 'TBLR', '');
        $this->MultiCell(0, $height, '3 Vendedor o Proveedor', 'TBLR', '');
        $this->SetFont('Arial', '', 8);
        $this->Cell($width, $height, $this->fixtext('Nombre / Razón Social'), 'TBLR', '');
        $this->Cell($width, $height, 'R.U.T.', 'TBLR', 0, 'R');
        $this->Cell($width, $height, '87.828.600-6', 'TBLR', '');
        $this->MultiCell(0, $height, $this->fixtext('Nombre / Razón Social'), 'TBLR', '');

        $this->Cell($width * 3, $height, 'INDUMARK S.A.', 'TBLR', '');
        $this->MultiCell(0, $height, $this->fixtext('ESBELT S.A.'), 'TBLR', '');

        $this->Cell($width * 3, $height, $this->fixtext('Dirección: CERRO SOMBRERO 670-B, MAIPU - SANTIAGO'), 'TBLR', '');
        $this->MultiCell(0, $height, $this->fixtext('Dirección: PROVENZA 385-08025 BARCELONA'), 'TBLR', '');

        $this->Cell($width * 3, $height, $this->fixtext('Marqhe con una X la alternativa'), 'TBLR', '');
        $this->MultiCell(0, $height, $this->fixtext(''), 'TLR', '');
        $this->Cell($width, $height, $this->fixtext('Mayorista'), 'TBLR', '');
        $this->Cell($width, $height, $this->fixtext('Minorista'), 'TBLR', '');
        $this->Cell($width, $height, $this->fixtext('Usuario'), 'TBLR', '');
        $this->MultiCell(0, $height, $this->fixtext(''), 'LR', '');
        $this->Cell($width, $height, $this->fixtext('X'), 'TBLR', '');
        $this->Cell($width, $height, $this->fixtext(''), 'TBLR', '');
        $this->Cell($width, $height, $this->fixtext(''), 'TBLR', '');
        $this->MultiCell(0, $height, $this->fixtext(''), 'BLR', '');

        $this->SetDrawColor(0);
        $this->MultiCell(0, 1, '', '', '');
        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0, $height, 'II INFORMACION DE LA COMPRAVENTA', 'TBLR', '');
        $this->SetY($this->getY() + 1);
        $this->SetFont('Arial', 'B', 8);
        $this->SetDrawColor(200);
        $this->Cell(5 * $width, $height, '4 Representante en Chile del vendedor extranjero', 'TBLR');
        $this->Cell(round($width / 2, 0), $height, 'SI', 'TBLR');
        $this->MultiCell(0, $height, 'NO  X ', 'TBLR', '');

        $this->SetFont('Arial', '', 8);
        $this->MultiCell(0, $height, 'Nombre del representante', 'TBLR', '');
        $this->MultiCell(0, $height, $this->fixtext('Tipo de representación (Marque con una "X" alternativa)'), 'TBLR', '');

        $this->Cell($width, $height, 'Filial', 'TBLR');
        $this->Cell($width, $height, 'Sucursal', 'TBLR');
        $this->Cell($width * 2, $height, 'Representante Exclusivo', 'TBLR');
        $this->MultiCell(0, $height, $this->fixtext('Otro (especificar en observaciones)'), 'TBLR', '');

        $this->lineaSiONo('5 Información factura',true,false);
        $this->SetFont('Arial', '', 8);
        $this->Cell($width, $height, 'Factura', 'TBLR');
        $this->Cell($width, $height, 'Fecha', 'TBLR');
        $this->Cell($width, $height, 'Clausula', 'TBLR');
        $this->Cell($width, $height, 'Factura', 'TBLR');
        $this->Cell($width, $height, 'Fecha', 'TBLR');
        $this->MultiCell($width, $height, 'Clausula', 'TBLR');
        $this->Cell($width, $height, 'Factura', 'TBLR');
        $this->Cell($width, $height, 'Fecha', 'TBLR');
        $this->Cell($width, $height, 'Clausula', 'TBLR');
        $this->Cell($width, $height, 'Factura', 'TBLR');
        $this->Cell($width, $height, 'Fecha', 'TBLR');
        $this->MultiCell($width, $height, 'Clausula', 'TBLR');
        $this->Cell($width, $height, 'Factura', 'TBLR');
        $this->Cell($width, $height, 'Fecha', 'TBLR');
        $this->Cell($width, $height, 'Clausula', 'TBLR');
        $this->Cell($width, $height, 'Factura', 'TBLR');
        $this->Cell($width, $height, 'Fecha', 'TBLR');
        $this->MultiCell($width, $height, 'Clausula', 'TBLR');

        $this->SetDrawColor(0);
        $this->MultiCell(0, 1, '', '', '');
        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0, $height, 'III ELEMENTOS DE VALOR', 'TBLR', '');
        $this->SetY($this->getY() + 1);

        $this->SetDrawColor(200);
        $this->lineaSiONo('6 Vinculación entre comprador y vendedor (Art. 15 N°s. 4 y 5, Acuerdo OMC)',true,false);

        $this->lineaSiONo('Tipo de vinculación: señale letra que corresponda del Art. 15 N°4 o Art. 15 N°5, según las instrucciones de llenado.');
        $this->lineaSiONo('a.- Vinculación entre las partes ha influido en el precio (Art.. 1 N°s. 1 d) y 2 a) Acuerdo OMC). Ver instrucciones de llenado', false, false);
        $this->lineaSiONo('b.- Precio pagado se aproxima mucho a los criterios de valor determinados de conformidad al Art. 1 N° 2 b) OMC. Ver instrucciones', false, false);

        $this->lineaSiONo('7 Restricciones para la utilización de las mercaderías por el importador (Art. 1 N° 1 a) Acuerdo OMC)', true);
        $this->lineaSiONo('a.- Restricciones para la utilización de las mercancías por el importador (Art. 1 N° 1 a) Acuerdo OMC)',false,false);
        $this->lineaSiONo('b.- Condiciones y contraprestaciones no cuantificables (Art. 1 N° 1 b) Acuerdo OMC)',false,false);
        $this->lineaSiONo('8 Adicionales (Art. 8 Acuerdo OMC)',true,false);
        $this->lineaSiONo('a.- Comisiones (art.. 8.1 a)',false,false);
        $this->lineaSiONo('Incluidas en el precio',false,false);
        $this->lineaSiONo('b.- Gastos de corretaje (art. 8.1 a)',false,false);
        $this->lineaSiONo('Incluidas en el precio',false,false);
        $this->lineaSiONo('c.- Bienes y servicios suministrados por el comprador (art. 8.1 b)',false,false);
        $this->lineaSiONo('Objetivo y cuantificable',false,false);
        $this->lineaSiONo('d.- Cánones y derechos de licencia (art. 8.1 c)',false,false);
        $this->lineaSiONo('Objetivo y cuantificable',false,false);
        $this->lineaSiONo('e. Producto de la reventa, cesión o utilización posterior de la mercancía que revierta al vendedor (art. 8.1 d)',false,false);
        $this->lineaSiONo('Objetivo y cuantificable',false,false);
        $this->lineaSiONo('9 Descuentos retroactivos (Opinión consultiva 8.1 Acuerdo OMC)',true,false);

        $this->titulo1("IV METODO DE VALORACION EMPLEADO");
        $this->titulo1("V OBSERVACIONES");
        $this->SetDrawColor(200);
        $this->MultiCell(0, 10, '', 'TLBR', '');
        $this->titulo1("VI DECLARANTE");
        $this->SetDrawColor(200);
        $this->SetFont('Arial', '', 8);
        $this->MultiCell(0, $height, '1.- Declaro bajo juramento que los datos contenidos en este documento son exactos y me responsabilizo de su efectividad, como asimismo declaro conocer lasdisposiciones de los artículos 168 y 169 relativos al delito de contrabando de la Ordenanza de Aduanas', 'TLR', '');
        $this->MultiCell(0, $height, '2.- Las intrucciones de llenado del presente formulario se encuentran disponibles en el Anexo 12 de la Resolución Nº 1300 del 2 de Junio de 2006, Compendio deNormas Aduaneras', 'BLR', '');

    }

    function lineaSiONo($texto, $estitulo = false, $siono = null)
    {
        $height = 5;
        if ($estitulo) {
            $this->SetFont('Arial', 'B', 8);
        } else {
            $this->SetFont('Arial', '', 8);
        }
        if (is_null($siono)) {
            $this->MultiCell(0, $height, self::fixtext($texto), 'TBLR');
        } else {
            $this->Cell($this->w6 * 5, $height, self::fixtext($texto), 'TBLR');

            $si = ($siono) ? "SI X" : "SI";
            $no = (!$siono) ? "NO X" : "NO";

            $this->Cell($this->w12, $height, $si, 'TBLR');
            $this->MultiCell(0, $height, $no, "TBLR");
        }
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

}
