<?php

namespace corsica\framework\usuarios;

use corsica\framework\config\Config;
/*
<li>dsantoni 2323 </li>
<li>jruiz 5055 </li>
<li>ccespedes 2138 </li>
<li>jvilches 1812 </li>
<li>colivares 3232 </li>
<li>priquelme 4466 </li>
<li>phidalgo 7733 </li>
<li>rsantos 4545 </li>
<li>afranco 1111 </li
*/

class UsuarioMgr extends \corsica\framework\utils\Manager
{
  /**
   * Acceso por usuario, password
   * no se está utilizando ... ver auth.php
   */
  public function login($username, $password)
  {

    $ux = new User($this->env);
    //$userx = $ux->login($username, $password);
    //$ux = new Usuario($this->env);
    $userx = $ux->login($username, $password);

    $this->logger->info("Respuesta de User ",array("userx"=>$userx));
 
    if ($userx->error != null) {
      return $userx;
    } else {
      return $this->getLoginData($userx);
    }
  }

    /**
   * Este login no tiene seguridad y simplemente le pasa un id de induweb y si lo encuentra lo trae
   */
  public function logininduweb($idinduweb)
  {
    $ux = new User($this->env);
    $userx = $ux->logininduweb($idinduweb);
    return $this->getLoginData($userx->id);
  }
  /**
   * Retorna los datos de usuarios para poner en la sessión del cliente
   */
  public function getLoginData($userx)
  {
    $idusuario = $userx->id;
    $this->logger->info("Obteniendo data de usuario",array("userx"=>$userx,"idusuario"=>$idusuario));
    //$this->setSession($userx->id, $userx->idcompany, $userx->env);//con esto carga bien
    //$u = new Usuario();
    $u = new Usuario($this->env);
    $conn = $u->getConexion();
    $m = new Menu($this->env,$conn);
    //$p = new Param($this->env,$conn);
    $c = new Config($this->env,$conn);
  

    $response = array();
    $usuario = $u->cargar($idusuario);
    if (is_null($usuario))
    throw new \Exception("Usuario  existe en x_user pero no en t_usuario");
    //agrego los datos del usuario master
    $usuario = (array)$usuario;
    $usuario['env'] = $userx->env;
    $usuario['company'] = $userx->company;
    $usuario['fchexpiracion'] = $userx->fchexpiracion;
    $usuario['fchsuspension'] = $userx->fchsuspension;
    $usuario = (object)$usuario;

    $response['usuario']  = $usuario; //menu de la app
    $response['menu']  = $m->menu($usuario->idperfil); //menu de la app
    $response['menutop']  = $m->menutop($usuario->idperfil); //menu de la app
    //$response["param"] = $p->getParams($usuario->idperfil);
    $response["frontend"] = $c->getGroup("frontend");
    $response["token"] = ""; //acciones permitidas
    return (object)$response;
  }
  /**
   * Verifica si existe o no el username
   */
  public function usernameExiste($id, $username)
  {
    $ux = new User($this->env);
    return $ux->usernameExiste($id, $username);
  }
  /**
   * guarda un usuario ya sea insert o update
   * WARNING : SÓLO DESDE EL CLIENTE
   */
  public function guardar($usuario)
  {
    $ux = new User($this->env);
    $u = new Usuario($this->env);
    $id = $ux->guardar($usuario, $this->env);
    $usuario->id = $id;
    $u->guardar($usuario);
    $usuario = $u->cargar($id);
    $userx = $ux->cargar($id);
    $usuario = (array)$usuario;
    $usuario['env'] = $userx->env;
    $usuario['company'] = $userx->company;
    $usuario = (object)$usuario;

    return $usuario;
  }
  /**permite guardar desde la cuenta master */
  public function guardarMaster($usuario,$env)
  {
    $this->logger->info("guardarMaster",array("usuario"=>$usuario,"env"=>$env));
    $env = array("env"=>$env,"idusuario"=>1,"idperfil"=>1);
    $env = (object)$env;
    //guardo en la base principal
    $ux = new User($this->env);    
    $id = $ux->guardar($usuario, $this->env);

    //entrega el env desde la capa de presentación
    $u = new Usuario($this->env);
    $usuario->id = $id;
    $u->guardar($usuario);
     
  }
  /**
   *  no se debe eliminar el usuario hasta eliminarlo de todas las TX
   * WARNING: SÓLO DESDE EL CLIENTE
   * @param id a eliminar
   * @param idotro a quien transferir la propiedad
   */
  public function eliminar($id,$idotro)
  {

    if ($id==$idotro) {
      $resultado= (object)array("error"=>"El usuario no puede ser el mismo que el que hereda");
    } else if($id==$this->env->idusuario) {
      $resultado= (object)array("error"=>"El usuario no puede eliminarse a si mismo");
    } else {
      $ux = new User($this->env);
      $u = new Usuario($this->env);

      //este si
      $resultado = $u->eliminar($id,$idotro);
      if ($resultado==0) {
        $ux->eliminar($id);
      } 
    }

    return $resultado;
  }
  /**
   * Permite eliminar desde el master
   */
  public function eliminarMaster($id,$idotro,$env)
  {
    $env = array("env"=>$env,"idusuario"=>1,"idperfil"=>1);
    $env = (object)$env;
    $ux = new User($this->env);
    $u = new Usuario($this->env);
    //este si
    $resultado = $u->eliminar($id,$idotro);
    if ($resultado==0) {
      $ux->eliminar($id);
    } 
    return $resultado;
  }
  /**Cambia la clave */
  public function cambiarClave($id, $old, $new, $new2)
  {
    $ux = new User($this->env);
    return $ux->cambiarClave($id, $old, $new, $new2);
  }
  public function nuevaClave($id, $password)
  {
    $ux = new User($this->env);
    return $ux->nuevaClave($id, $password);
  }
  /**
   * Genera una clave de 8 caracteres de largo
   */
  public function generarClave()
  {
    $characters = "1234567890.QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm";
    $largo = 8;
    $out = "";
    
    for($i=0;$i<$largo;$i++) {
      $indice = rand(0,62);
      $c= substr($characters,$indice,1);
      $out += $c;
    }
    return $out;
  }
}
