<?php


require(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

use Firebase\JWT\JWT;
use corsica\framework\usuarios\User;
use corsica\framework\usuarios\UsuarioMgr;
use corsica\framework\config\Config;
use corsica\framework\utils\Token;

$data = json_decode(file_get_contents("php://input"));
$method = $_SERVER['REQUEST_METHOD'];
//esto es para que chrome que envía un preload y falla
if ($method != 'OPTIONS') {
  $metodo = $data->metodo;
  if ($metodo == "login") {
    //sólo USER tiene la password

    $u = new User(null);
    $user = $u->login($data->username, $data->password);

    if (!$user->error) {

      $env = (object) array("env" => $user->env, "idusuario" => $user->id, "username" => $data->username, "idperfil" => "0");

      $us = new UsuarioMgr($env);
      $response = $us->getLoginData($user);
      $env->idperfil = $response->usuario->idperfil;

      $t = new Token();
      $token = $t->getToken($env->idusuario, $env->username, $env->idperfil, $env->env);

      $response->token = $token;
      echo json_encode($response);


    } else {
      //header('HTTP/1.0 401 Unauthorized');
      echo json_encode($user); //este "user" es un mensaje de error
    }
  } else if ($metodo == "logininduweb") {
    $u = new User(null);
    $user = $u->logininduweb($data->idinduweb);
    if (!$user->error) {

      $env = (object) array("env" => $user->env, "idusuario" => $user->id, "username" => $data->username, "idperfil" => "0");

      $us = new UsuarioMgr($env);
      $response = $us->getLoginData($user);
      $env->idperfil = $response->usuario->idperfil;

      $t = new Token();
      $token = $t->getToken($env->idusuario, $env->username, $env->idperfil, $env->env);

      $response->token = $token;
      echo json_encode($response);
    } else {
      //header('HTTP/1.0 401 Unauthorized');
      echo json_encode($user); //este "user" es un mensaje de error
    }
  } else if ($metodo == "logout") {
    //el logout se hace en el frontend
  } else if ($metodo == "enviarCorreoClave") {
    $u = new User(null);
    $u->enviarCorreoClave($data->username);
    //el logout se hace en el frontend

  } else {
    header('HTTP/1.0 400 Bad Request');
    echo json_encode("400 método no corresponde"); //este "user" es un mensaje de error

  }
}





$loginsuccess = true;
if ($loginsuccess) {

  //return $token;
} else {
  $response = array("error" => "Usuario o contraseña incorrecta.");
  echo json_encode($response);
  //return $response;
  //header('HTTP/1.0 400 Bad Request');
  //echo 'Token not found in request';
  //exit;
}
//print_r("fin");
//$api = new \corsica\mto\ApiRouter();
//$response = $api->execute($data);

//echo json_encode($response);


exit;