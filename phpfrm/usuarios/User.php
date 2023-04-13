<?php

namespace corsica\framework\usuarios;
use corsica\framework\utils\Email;
use corsica\framework\config\Config;
use corsica\framework\utils\EmailTemplate;

/**
 * Esta es para conectar como usuario global y acceder a el entorno empresa
 */
class User extends \corsica\framework\utils\DbMaster
{
    /**
     * Debe crear en el master y  en la empresa 
     * @return Object ubjeto usuario o objeto error->mensaje
     */
    public function login($username, $password)
    {
        $db = Config::getMasterDatabase();
        $this->logger->debug("Login",array("username"=>$username,"database"=>$db->database));
        $sql = " select a.*,b.rut, b.name as company, b.env, b.fchexpiracion, b.fchsuspension from x_user a";
        $sql .= " join x_company b on a.idcompany = b.id ";
        $sql .= " where a.username = " . $this->texto($username);
        $users = $this->select($sql);
        //$this->logger->debug("Login select ",array("users"=>$users));
   
        if (count($users) == 1) {
            $user = $users[0];
            $this->logger->debug("Login result",array("user"=>$user));
            $suspendida = $this->licenciaSuspendida($user);
            if (!is_null($suspendida)) {
                return $suspendida;
            }

            if (password_verify($password, $user->password)) {
                return $user;
            } else {
                return (object)array('error' => 'Usuario o password incorrecto', 'code' => '-1');
            }
        } else {
            return (object)array('error' => 'Usuario o password incorrecto', 'code' => '-1');
        }
    }

    /**
     * login por id
     */
    public function logininduweb($idinduweb)
    {
        // $this->convertPassword($username);
        $sql = " select a.*,b.env from x_user a";
        $sql .= " join x_company b on a.idcompany = b.id ";
        $sql .= " where a.idinduweb = " . $this->texto($idinduweb);

        $users = $this->select($sql);
        if (count($users) == 1) {
            $user = $users[0];
            return $user;

        } else {
            return (object)array('error' => 'ID de usuario induweb no se encuentra en sistema de importaciones', 'code' => '-1');
        }
    }

    private function licenciaExpirada($user) {
    }
    private function licenciaSuspendida($user) {
       if (!is_null($user->fchsuspension)) {
           $fechaactual = strtotime(date("Y-m-d"));
           $fchsuspension =  strtotime($user->fchsuspension);
           if ($fchsuspension<$fechaactual) {
               $msg = 'Su licencia está momentáneamente suspendida desde el '.$user->fchsuspension.'.';
               $msg .= " Por favor comuníquese con Córsica Ltda. para regularizar su situación.";
            return (object)array('error' => $msg, 'code' => '-1');
           }
       }
       return null;
 
    }



    public function cargar($id)
    {
        // $this->convertPassword($username);
        $sql = " select a.*,b.rut, b.name as company, b.env from x_user a";
        $sql .= " join x_company b on a.idcompany = b.id ";
        $sql .= " where a.id = " . $this->numero($id);
        $users = $this->select($sql);
        return $users[0];
    }
    /**sólo aqui */

    public function cambiarClave($id, $old, $new, $new2)
    {
        $response = array('codigo' => '-1', 'mensaje' => 'Nuevas passwords no coinciden');

        if ($new == $new2) {
            $sql  = "select password from x_user ";
            $sql .= " where id = " . $id;
            $olshash = (string)$this->select1($sql);
            if (password_verify($old, $olshash)) {
                $sql  = "update x_user set password = '" . password_hash($new, PASSWORD_DEFAULT) . "'";
                $sql .= " where id = '" . $id . "'";
                $this->execute($sql);
                $response = array('codigo' => '0', 'mensaje' => 'Se ha cambiado la clave con exito');
            } else {
                $response = array('codigo' => '-2', 'mensaje' => 'Clave actual no coincide');
            }
        }

        return $response;
    }
    /**
     * Para cambiar la clave, sólo para MASTER
     */
    public function nuevaClave($id, $password)
    {
        $sql  = "update x_user set password = " . $this->texto(password_hash($password, PASSWORD_DEFAULT)) ;
        $sql .= " where id = ". $this->numero($id);
        $this->execute($sql);
        return 0;
    }
    /**
     * Cambia la clave y envía un correo
     */
    public function enviarCorreoClave($username)
    {
        $out = array("codigo" => -1, "mensaje" => "usuario no existe");
        $users = $this->select("select * from x_user where username = " . $this->texto($username));
        if (count($users) == 1) {
            $out = array("codigo" => 0, "mensaje" => "Le hemos enviado un correo con su nueva password");
            $user = $users[0];
            $pwd = $this->randomPassword();
            $user->password = $pwd;
            $email = new Email();
            $to=$user->email;
            $et = new EmailTemplate(EmailTemplate::NVA_CLAVE,$user);

            $email->notificar($to, $et->subject(), $et->body());

            $sql = " update x_user set password = '" . password_hash($pwd, PASSWORD_DEFAULT) . "'";
            $sql .= " where id = " . $user->id; // . "--".$u['password']. "/".$u->password. "--".$u['id']. "/".$u->id;
            $this->execute($sql);
        }
        return $out;
    }
    public function filtrar($filtro)
    {
        $sql = "select a.*, b.name as nombreempresa from x_user a ";
        $sql .= " join x_company b on b.id=a.idcompany ";
        $nombre = trim($filtro->nombre);
        $where = " where 1=1 ";
        if (strlen($nombre) > 0) {
            $where .= " and a.nombre like " . $this->like($nombre);
        }
        $nombreempresa = trim($filtro->nombreempresa);
        if (strlen($nombreempresa) > 0) {
            $where .= " and b.name like " . $this->like($nombreempresa);
        }
        $sql .= $where . " limit " . self::PAGINACION * ($filtro->pagina - 1) . ", " . self::PAGINACION;
        $items = $this->select($sql);
        $totalitems = $this->select1("select count(*) from x_company a  join x_company b on b.id=a.idcompany " . $where);

        $out = array("items" => $items, "totalitems" => $totalitems);
        return $out;
    }
    /**
     * Verifica si el usuario existe o no
     * @param Number $id del usuario
     * @param String $username ingresado
     * @return int 0 si no existe, 1 si existe
     */
    public function usernameExiste($id, $username)
    {
        $sql = "select count(id) as x from t_usuario ";
        $sql .= " where username=" . $this->texto($username);
        $sql .= " and id <> " . $this->numero($id);
        $result = 1 * $this->select1($sql);
        return $result;
    }
    private function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = ""; //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass .= $alphabet[$n];
        }
        return $pass; //turn the array into a string
    }
    /**
     * Debe crear en el master y  en la empresa 
     */
    public function eliminar($id)
    {
        $sql = "delete from x_user where id = " . $id;
        $this->execute($sql);
    }
    /**
     * Almacena los datos de usuario
     * @param $usuario Object
     * @param $env Object env con idusuario,idperfil y env
     */
    public function guardar($usuario, $env)
    {

        if ($usuario->id == 0) {
            $c = new Company($env);
            $idcompany = $c->getIdByEnv($env->env);
            return $this->insertUser($usuario, $idcompany);
        } else {
            return  $this->updateUser($usuario);
        }
    }
    private function insertUser($data, $idcompany)
    {
        $pwd = password_hash($data->password, PASSWORD_DEFAULT);
        $sql = "insert into x_user ( idcompany, ";
        $sql .= " username,email,  ";
        $sql .= " nombre,apellido,  ";
        $sql .= " telefono,password) values ( ";
        $sql .= $this->numero($idcompany) . ",";
        $sql .= $this->texto($data->username) . "," . $this->texto($data->email) . ",";
        $sql .= $this->texto($data->nombre) . "," . $this->texto($data->apellido) . ",";
        $sql .= $this->texto($data->telefono) . "," . $this->texto($pwd) . ")";

        $this->execute($sql);
        return $this->lastId();
    }
    private function updateUser($data)
    {
        $sql = "update x_user set ";
        $sql .= "   username = " . $this->texto($data->username);
        $sql .= " , email = " . $this->texto($data->email);
        $sql .= " , nombre = " . $this->texto($data->nombre);
        $sql .= " , apellido = " . $this->texto($data->apellido);
         $sql .= " , telefono = " . $this->texto($data->telefono);
        // $sql .= " , password = " . $this->numero($data->idperfil);
        $sql .= " where id = " . $this->numero($data->id);
        $this->execute($sql);
        return $data->id;
    }
}
