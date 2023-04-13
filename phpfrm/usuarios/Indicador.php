<?php

namespace corsica\framework\usuarios;



class Indicador extends \corsica\framework\utils\DbClient
{
    const VENTA_MES = 1;
    const VENTA_MES_USUARIO = 2;
    const VENTA_SEMANA = 3;
    const VENTA_SEMANA_USUARIO = 4;
    const ENTREGA_SEMANA = 10;
    const ENTREGA_SEMANA_SIGUIENTE = 11;
    const ENTREGA_LISTA_SEMANA = 20;
    const ENTREGA_LISTA_SEMANA_SIGUIENTE = 21;

    const PEDIDOS_EN_PRODUCCION = 100;
    const PEDIDOS_EN_DESPACHO = 101;
    const ATRASADOS_EN_FABRICA = 102;
    const ATRASADOS_EN_RETIRO = 103;
    const ATRASADOS_EN_DESPACHO = 104;
    const TIEMPOS_DE_ENTREGA = 105;

    const CALENDARIO_CONSUMOS = 200;
    const CALENDARIO_CIRUGIAS = 201;







    public function lista($idusuario)
    {
        $sql = " SELECT *, '' as valor from t_indicador";
        $sql .= " where orden > 0 ";
        $sql .= " order by orden ";
        $rows =  $this->select($sql);
        $out = array();
        foreach ($rows as $row) {
            $row->valor = $this->cargarIndicador($row->id, $idusuario);
            array_push($out, $row);
        }
        return $out;
    }
    private function cargarIndicador($id, $idusuario)
    {

        switch (1 * $id) {
            case Indicador::VENTA_MES:
                return $this->ventaMes(0); //ok
                break;
            case Indicador::VENTA_MES_USUARIO:
                return $this->ventaMes($idusuario);
                break;
            case Indicador::VENTA_SEMANA:
                return $this->ventaSemana(0);
                break;
            case Indicador::VENTA_SEMANA_USUARIO:
                return $this->ventaSemana($idusuario);
                break;
            case Indicador::ENTREGA_SEMANA:
                return $this->entregaSemana(0);
                break;
            case Indicador::ENTREGA_SEMANA_SIGUIENTE:
                return $this->entregaSemana(1);
                break;
            case Indicador::ENTREGA_LISTA_SEMANA:
                return $this->entregaListaSemana(0);
                break;
            case Indicador::ENTREGA_LISTA_SEMANA_SIGUIENTE:
                return $this->entregaListaSemana(1);
                break;
            case Indicador::PEDIDOS_EN_PRODUCCION:
            case Indicador::PEDIDOS_EN_DESPACHO:
                return $this->indicadoresDeEstado($id);
                break;
            case Indicador::ATRASADOS_EN_FABRICA:
            case Indicador::ATRASADOS_EN_RETIRO:
            case Indicador::ATRASADOS_EN_DESPACHO:
                return $this->indicadoresDeAtraso($id);
                break;
            case Indicador::TIEMPOS_DE_ENTREGA:
                return $this->entregaTiemposDeEntrega(1);
                break;
            case Indicador::CALENDARIO_CONSUMOS:
                return $this->entregaCalendario(Indicador::CALENDARIO_CONSUMOS);
                break;
            case Indicador::CALENDARIO_CIRUGIAS:
                return $this->entregaCalendario(Indicador::CALENDARIO_CIRUGIAS);
                break;
            default:
                return 6924;
        }
    }
    public function indicadoresDeAtraso($tipo)
    {

        $sql = "select count(id) as x from t_pedido ";
        switch ($tipo) {
            case Indicador::ATRASADOS_EN_FABRICA:
                $sql .= "where  idestado = 1 and datediff(CURRENT_DATE,fchcompromiso) > 0 ";
                break;
            case Indicador::ATRASADOS_EN_RETIRO:
                $sql .= "where  idestado = 5 and despacho = 0 and datediff(CURRENT_DATE,fchcompromiso) >0 ";
                break;
            case Indicador::ATRASADOS_EN_DESPACHO:
                $sql .= "where  idestado = 5 and despacho = 1 and datediff(CURRENT_DATE,fchcompromiso) >0 ";
                break;
        }
        return $this->select1($sql);
    }
    public function indicadoresDeEstado($tipo)
    {

        $sql = "select count(id) as x from t_pedido ";
        switch ($tipo) {
            case Indicador::PEDIDOS_EN_PRODUCCION:
                $sql .= "where  idestado = 1 ";
                break;
            case Indicador::PEDIDOS_EN_DESPACHO:
                $sql .= "where  idestado = 5 and despacho = 1 ";
                break;
        }
        return $this->select1($sql);
    }
    public function entregaTiemposDeEntrega()
    {
        $sql = "SELECT concat(llave,' ',format(valor,0),' días') as x FROM t_param where grupo='produccion'";
        return $this->select($sql);
    }
    /**
     * Venta mensual
     * Sólo la del usuario o todas
     */
    private function ventaMes($idusuario)
    {
        $sql = " SELECT ";
        $sql .= " sum( a.neto * (61 <> a.tipo) -  a.neto * (61 = a.tipo)      ) as valor";
        $sql .= " FROM t_doc a";
        $sql .= " WHeRE a.tipo <> 52 ";
        $sql .= " and month(fchemis)=month(now())";
        $sql .= " and year(fchemis)=year(now())";
        if ($idusuario * 1 > 0)
            $sql .= " and idusuario=" . $idusuario;
        $out =  $this->select1($sql);
        return ($out == null) ? 0 : $out;
    }
    private function ventaSemana($idusuario)
    {
        $sql = " SELECT ";
        $sql .= " sum( a.neto * (61 <> a.tipo) -  a.neto * (61 = a.tipo)      ) as valor";
        $sql .= " FROM t_doc a";
        $sql .= " WHeRE a.tipo <> 52 ";
        $sql .= " and week(fchemis)=week(now())";
        $sql .= " and year(fchemis)=year(now())";
        if ($idusuario * 1 > 0)
            $sql .= " and idusuario=" . $idusuario;
        $out =  $this->select1($sql);
        return ($out == null) ? 0 : $out;
    }
    private function entregaSemana($semana)
    {
        $sql = " SELECT count(*) as x from t_pedido ";
        $sql .= " where year(fchcompromiso)=year(date_add(now(), INTERVAL " . $this->numero($semana) . " WEEK))";
        $sql .= " and week(fchcompromiso)=week(date_add(now(), INTERVAL " . $this->numero($semana) . " WEEK))";

        $out =  $this->select1($sql);
        return ($out == null) ? 0 : $out;
    }
    private function entregaCalendario($tipo)
    {
        $valor = array();
        switch($tipo) {
            case $this::CALENDARIO_CONSUMOS;
            $valor["tipo"]="pedido.fchcompromiso";
            break;
            case $this::CALENDARIO_CIRUGIAS;
            $valor["tipo"]="cirugia.fchcompromiso";
            break;
        }
        return (object)$valor;
    }
    private function pedidosPorEstado($idestado, $idusuario)
    {
        $sql = " SELECT count(*) as x from t_pedido ";
        $sql .= " where idestado=" . $idestado;

        if ($idusuario * 1 > 0)
            $sql .= " and idusuario=" . $idusuario;
        $out =  $this->select1($sql);
        return ($out == null) ? 0 : $out;
    }
    /**
     * Semana 0 = esta
     * Semana 1, la siguiente
     * ENTREGA UN ARRAY
     */
    private function entregaListaSemana($semana)
    {
        $this->execute("SET lc_time_names = 'es_ES'");
        $sql = "SELECT concat (date_format(fchcompromiso,'%a,%d de %b, %H:%i'),' > ',id,' ',nota2) as x from t_pedido ";
        $sql .= " where year(fchcompromiso)=year(date_add(now(), INTERVAL " . $this->numero($semana) . " WEEK))";
        $sql .= " and week(fchcompromiso)=week(date_add(now(), INTERVAL " . $this->numero($semana) . " WEEK))";
        $sql .= " order by fchcompromiso";
        return $this->select($sql);
    }
}
