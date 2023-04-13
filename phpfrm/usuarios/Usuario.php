<?php

namespace corsica\framework\usuarios;

/**
 * Mantiene los datos de usuario dentro del ambiente de cliente
 * Para loguear lo hace en la tabla x_user de la DB master
 */
class Usuario extends \corsica\framework\utils\DbClient
{
    protected  function tablename() {
        return "t_despacho_op_op";
    }
    protected  function primarykey() {
        return "id";
    }
    public function listar($idperfil = 0)
    {
        $sql = "select a.id , a.idperfil, a.nombre, a.apellido, a.email, concat(a.nombre,' ',a.apellido) as nombreusuario, b.nombre as perfil ";
        $sql .= " from t_usuario a, t_perfil b ";
        $sql .= " where a.idperfil = b.id";

        if ($idperfil > 0) {
            $sql .= " and a.idperfil = " . $idperfil;
        }
        $sql .= " order by a.idperfil, a.nombre";
        return $this->select($sql);
    }
    public function filtrar($filtro)
    {
        $sql = "select a.id , a.idperfil, a.nombre, a.username, a.apellido,a.email, concat(a.nombre,' ',a.apellido) as nombreusuario, b.nombre  as perfil ";
        $sql .= " from t_usuario a, t_perfil b ";
        $sql .= " where a.idperfil = b.id";
        $sql .= " and a.idperfil <> 1 ";
        if ($filtro->idperfil > 0) {
       //     $sql .= " and a.idperfil = " . $filtro->idperfil;
        }
        $sql .= " order by  a.idperfil, a.nombre";
        return $this->select($sql);
    }
    //ok
    /**
     * Evalua si el código existe para otro producto
     */
    /*  public function usernameExiste($id, $username)
    {
        $sql = "select count(id) as x from t_usuario ";
        $sql .= " where username=" . $this->texto($username);
        $sql .= " and id <> " . $this->numero($id);
        $result = 1 * $this->select1($sql);
        return $result;
    }
    public function login($username, $password)
    {

        $sql = " select id , idperfil , nombre , username , email , codigovendedor, password ";
        $sql .= "  from t_usuario ";
        $sql .= " where username = '" . $username . "' ";

        $users = $this->select($sql);
        if (count($users) == 1) {
            $user = $users[0];
            if (password_verify($password, $user->password)) {
                return $user;
            }
        }
        return (object)array('error' => 'Usuario o password incorrecto ');
    }*/
    public function cargar($id)
    {
        $sql = " select * from t_usuario where id = " . $id;
        $user = $this->select($sql);
        if (count($user) == 1) {
            $user = $user[0];
        } else {
            $user = null;
        }
        return $user;
    }




    /**
     * Se utilizó para encriptar las passwords.
     */
    /* private function convertAll()
    {
        $lista = $this->listar();
        foreach ($lista as $u) {
            if (strlen($u->password) < 10) {
                $sql = " update t_usuario set password = '" . password_hash($u->password, PASSWORD_DEFAULT) . "'";
                $sql .= " where id = " . $u->id; // . "--".$u['password']. "/".$u->password. "--".$u['id']. "/".$u->id;
                $this->execute($sql);
            }
        }
    }*/
    /**
     * Para encriptar password
     */
    /* private function convertPassword($username)
    {
        $pwd = $this->select1("select password from t_usuario where username = ".$this->texto($username));  
        if (strlen($pwd) < 10) {
            $sql = " update t_usuario set password = '" . password_hash($pwd, PASSWORD_DEFAULT) . "'";
            $sql .= " where username = " .$this->texto($username);
            $this->execute($sql);
        }
        
    }*/
    public function guardar($data)
    {
        $x = 1 * $this->select1("select count(id) from t_usuario as x where id = " . $this->numero($data->id));
        if ($x == 0) {
            return $this->insertUsuario($data);
        } else {
            return  $this->updateUsuario($data);
        }
    }
    private function insertUsuario($data)
    {
        $sql = "insert into t_usuario ( id,username, nombre,apellido,email, idperfil) values ( ";
        $sql .= $this->numero($data->id) . "," . $this->texto($data->username) . "," . $this->texto($data->nombre) . ",";
        $sql .= $this->texto($data->apellido) . "," . $this->texto($data->email) . "," . $this->numero($data->idperfil) . ")";
        $this->execute($sql);
    }
    private function updateUsuario($data)
    {
        $sql = "update t_usuario set ";
        $sql .= "   username = " . $this->texto($data->username);
        $sql .= " , nombre = " . $this->texto($data->nombre);
        $sql .= " , apellido = " . $this->texto($data->apellido);
        $sql .= " , email = " . $this->texto($data->email);
        $sql .= " , idperfil = " . $this->numero($data->idperfil);
        $sql .= " where id = " . $this->numero($data->id);
        $this->execute($sql);
    }
    /**
     * TODO si se elimina el id de usuario se pierden las referencias de
     * docs y otros, sólo se debe eliminar de x_user
     */
    public function eliminar($id,$idusuariotraspaso)
    {

        $out = $this->esPrincipal($id);
        if ($out==0) {
            $this->traspasar($id,$idusuariotraspaso);
            $sql = "delete from t_usuario where id = " . $this->numero($id);
            $this->execute($sql);
        }
        return $out;
    }
    /**
     * TODO: donde se usa esto
     */
    public function autocompletar($texto, $max = 50)
    {
        $sql  = "select concat(nombre, ' ', apellido, ' ', email) as value, concat(nombre, ' ', apellido, ' ', email) as label, a.id ";
        $sql .= "  from t_usuario a ";
        $sql .= "  where  concat( nombre, ' ', apellido, ' ', email) like " . $this->like($texto);
        // $sql .= " or apellidos like " . $this->like($texto) . " )";
        $sql .= " order by nombre asc";
        $sql .= " limit " . $max;
        return $this->select($sql);
    }
    /**
     * Traspasa todos los pedidos, cotizaciones documentos etc entre un usuario y otro.
     */
    public function traspasar($idold, $idnew)
    {
        $new = $this->numero($idnew);
        $old = $this->numero($idold);
        $this->execute("UPDATE t_cliente          set idusuario = " . $new . " where idusuario = " . $old);
        $this->execute("UPDATE t_evento_usuario   set idusuario = " . $new . " where idusuario = " . $old);
        $this->execute("UPDATE t_bodega_mvto      set idusuario = " . $new . " where idusuario = " . $old);
        $this->execute("UPDATE t_cirugia          set idusuario = " . $new . " where idusuario = " . $old);
        $this->execute("UPDATE t_cotiza           set idusuario = " . $new . " where idusuario = " . $old);
        $this->execute("UPDATE t_doc              set idusuario = " . $new . " where idusuario = " . $old);
        $this->execute("UPDATE t_evento_noleido   set idusuario = " . $new . " where idusuario = " . $old);
        $this->execute("UPDATE t_pedido           set idusuario = " . $new . " where idusuario = " . $old);
        $this->execute("UPDATE t_pedido_imagen    set idusuario = " . $new . " where idusuario = " . $old);
        $this->execute("UPDATE t_pedido_nota      set idusuario = " . $new . " where idusuario = " . $old);
        return 0;
    }
        /**
     * Evalua que el usuario no esté activo en ninguna tabla
     * @return 0 si ok, mensaje de error(object) si no
     */
    public function esPrincipal($id)
    {
        //es usuario principal
        $principal=1*$this->select1("select principal from t_usuario where id=". $this->numero($id));
        if ($principal==1) {
            return (object)array("error"=>"no es posible eliminar el usuario principal");
        }
        return 0;
    }
    /**
     * Evalua que el usuario no esté activo en ninguna tabla
     * @return 0 si ok, mensaje de error(object) si no
     */
    /*public function eliminable($id)
    {
        //es usuario principal
        $principal=1*$this->select1("select principal from t_usuario where id=". $this->numero($id));
        if ($principal==1) {
            return (object)array("error"=>"no es posible eliminar el usuario principal");
        }

        $tables = [
            "t_cliente", "t_evento_usuario", "t_bodega_mvto", "t_cirugia", "t_cotiza", "t_doc", "t_evento_noleido", "t_pedido", "t_pedido_imagen", "t_pedido_nota"
        ];
        $begin = "select count(idusuario) as x from ";
        $end = " where idusuario=" . $this->numero($id);
        $out = true;
        foreach ($tables as $table) {
            $q = $this->select1($begin . $table . $end);
            if ($q != null) {
                return (object)array("error"=>"el usuario tiene objetos asociados");
            }
        }
        return 0;
    }*/
}
