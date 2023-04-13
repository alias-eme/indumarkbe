<?php

namespace corsica\framework\usuarios;

/**
 * Toma los parámetros por defecto de toda la aplicación para ponerlos en la sesión
 * según perfil.
 * NOTA CON LOS TIEMPODE CEPILLADO Y OTROS QUE NO DEBERÍAN IR EN LA SESIÓN.
 */

class Param extends \corsica\framework\utils\DbClientSimple
{
    protected  function tablename()
    {
        return "t_param";
    }
    protected  function primarykey()
    {
        return "id";
    }

    private $params = null;

    /**
     * Carga los parametros para la sesión
     * Primero la base desde JSON
     * Luego desde la BD de forma genérica para todos los usuarios (con idperfil=0)
     * Finalmente desde la BD por perfil
     * @param idperfil perfil del usuario
     */
    public function getParams($idperfil)
    {
        $sql = " SELECT ";
        $sql .= "  p.id";
        $sql .= " ,coalesce(pp.valor,p.valor) as valor ";
        $sql .= " FROM t_param p ";
        $sql .= " LEFT JOIN t_param_perfil pp on p.id = pp.idparam ";
        $sql .= " AND pp.idperfil=" . $idperfil;
        $rows = $this->select($sql);
        $out = [];
        foreach ($rows as $row) {
            $out[$row->id] = 1 * $row->valor;
        }
        return $out;
    }

    public function guardar($param)
    {
        $this->updateSimple($param, ['valor', 'descripcion']);
        return "ok";
    }
    /**
     * Devuelve el valor de la tasa de iva como entero
     * @return float Ej 19.00 
     */
    public function getTasaIva()
    {
        $row = $this->cargarSimple('TAX_IVA');
        return 1 * $row->valor;
    }
    /**
     * obtiene los parametros de sesión para un perfil como un array
     */

    /**
     * consulta los parámetros de la Bd según perfil, en caso de ser cero corresponde al por defecto
     * Nota: ja debe estar cargado el objeto json.
     * @param idperfil
     */
    public function listar($idperfil = null)
    {
        $sql = " SELECT ";
        $sql .= "  p.id";
        $sql .= " ,p.grupo ";
        $sql .= " ,coalesce(pp.valor,p.valor) as valor ";
        $sql .= " ,p.descripcion ";
        $sql .= " ,p.perfilable";
        $sql .= " ,p.vf";
        $sql .= " ,p.master";
        $sql .= " FROM t_param p ";
        $sql .= " LEFT JOIN t_param_perfil pp on p.id = pp.idparam ";
        if ($idperfil) {
            $sql .= " AND pp.idperfil=" . $idperfil;
        }

        if ($this->env->idperfil != 1) {
            $sql .= " WHERE p.master = 0";
        }


        $sql .= " ORDER BY p.grupo,p.id";
        $rows = $this->select($sql);
        foreach ($rows as &$row) {
            $row->perfilable = ($row->perfilable == "1");
            $row->vf = ($row->vf == "1");
            $row->valor = 1 * $row->valor;
        }
        return $rows;
    }
    /**
     * Entrega todos los perfiles
     * @param String $id del parametro
     */
    public function cargarParamPerfiles($id)
    {
        $sql = " SELECT ";
        $sql .= "  t_param.id as idparam ";
        $sql .= " ,t_perfil.id  as idperfil";
        $sql .= " ,t_perfil.nombre ";

        $sql .= " ,isnull(t_param_perfil.valor) as general ";
        $sql .= " ,t_param.valor as valorgeneral ";
        $sql .= " ,coalesce(t_param_perfil.valor,t_param.valor) as valor ";

        $sql .= " FROM t_perfil ";
        $sql .= " LEFT JOIN t_param on t_param.id =" . $this->texto($id);
        $sql .= " LEFT JOIN t_param_perfil on t_param_perfil.idparam =" . $this->texto($id);
        $sql .= " AND t_param_perfil.idperfil = t_perfil.id";
        if ($this->env->idperfil != 1) {
            $sql .= " AND t_perfil <> 1";
        }
        $rows = $this->select($sql);
        foreach ($rows as &$row) {
            $row->general = 1 * $row->general;
            $row->valorgeneral = 1 * $row->valorgeneral;
            $row->valor = 1 * $row->valor;
        }
        return $rows;
    }

    public function cambiarParamPerfil($idparam, $idperfil, $general, $valor)
    {
        $this->logger->info("cambiarParamPerfil", ["idparam" => $idparam, "idperfil" => $idperfil, "general" => $general, "valor" => $valor]);
        if ($general == 1) {
            $sql = "DELETE FROM t_param_perfil ";
            $sql .= " WHERE idparam=" . $this->texto($idparam);
            $sql .= " AND idperfil=" . $this->texto($idperfil);
            $this->execute($sql);
            return "Eliminado de t_param_perfil";
        } else {
            $sql = "INSERT INTO t_param_perfil (idparam,idperfil,valor) VALUES ";
            $sql .= " (" . $this->texto($idparam);
            $sql .= " ," . $this->texto($idperfil);
            $sql .= " ," . $this->texto($valor) . ") ";
            $sql .= " ON DUPLICATE KEY UPDATE valor=" . $this->texto($valor);
            //$sql = "UPDATE t_param_perfil ";
            //$sql .= " SET valor=".$this->texto($valor);
            //$sql .= " WHERE idparam=".$this->texto($idparam);
            //$sql .= " AND idperfil=".$this->texto($idperfil);
            $this->execute($sql);
            return "Actualizado de t_param_perfil";
        }
    }
    public function cargarParamsProduccion()
    {

        $rows = $this->select("select id,valor from t_param where grupo='produccion'");
        $out = [];
        foreach ($rows as $row) {
            $out[$row->id] = $row->valor * 1;
        }
        return $out;
    }


}
