<?php

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use corsica\framework\config\Config;
require(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

$method = $_SERVER['REQUEST_METHOD'];
if ($method=="POST") {
  $data = json_decode(file_get_contents("php://input"));
} else if ($method=="GET") {
  $data = (object)$_GET;
}
///////////////////////////////////////////////////////////
$token = $data->token;
if (!preg_match('/Bearer\s(\S+)/', $token, $matches)) {
  //header('HTTP/1.0 403 Forbidden');
  echo '403 No hay Token';
  exit;
} else {
  //header('HTTP/1.0 200 OK');
  $jwt = $matches[1];
  $secretKey  = Config::getSecretKey();
  try {
    //$token = JWT::decode($jwt, $secretKey, ['HS512']);
    $token = JWT::decode($token, new Key($secretKey, 'HS256'));
       
    $env = array("env"=>$token->env,"idusuario"=>$token->idusuario,"idperfil"=>$token->idperfil);
    $env = (object)$env;
  } catch (ExpiredException $e) {
    //header('HTTP/1.0 403 Forbidden');
    echo '403 Token Expiró';
    exit;
  }
}




//////////////////////////////////////////////////////////////
$response = \corsica\framework\router\ApiRouter::execute($env,$data);
echo json_encode($response);

exit;


