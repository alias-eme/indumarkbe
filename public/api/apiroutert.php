<?php
require(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use corsica\framework\config\Config;
use corsica\framework\utils\Token;

$method = $_SERVER['REQUEST_METHOD'];

if ($method != 'OPTIONS') {

  if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
    header('HTTP/1.0 403 Forbidden');
    echo '403 No hay Token';
    exit;
  } else {
    //header('HTTP/1.0 200 OK');
    $jwt = $matches[1];
    $secretKey  = Config::getSecretKey();
    try {
      $t = new Token();
      $env = $t->getEnv($jwt);
      //$token = JWT::decode($jwt, $secretKey, ['HS512']);
      //$env = array("env"=>$token->env,"idusuario"=>$token->idusuario,"idperfil"=>$token->idperfil);
      //$env = (object)$env;
    } catch (ExpiredException $e) {
      header('HTTP/1.0 403 Forbidden');
      echo '403 Token Expiró';
      exit;
    }
  }

  if ($method == "POST") {

    $data = json_decode(file_get_contents("php://input"));
    if (!$data) {
      //este es el caso de multipart/form-data
      $data = array();
      $keys = array_keys($_POST);
      foreach ($keys as $key) {
        $obj = json_decode($_POST[$key]);
        $data[$key] = is_null($obj) ? $_POST[$key] : $obj;
      }
      if ($_FILES) {
        foreach ($_FILES as $param => $file) {
          $data[$param] = $file;
        }
      }
      $data = (object)$data;
    }
  } else if ($method == "GET") {
    $data = (object)$_GET;
  }
  try {
    $response = \corsica\framework\router\ApiRouter::execute($env, $data);
    echo json_encode($response);
  } catch (Exception $e) {
    $errormsg = (object) ["error"=> $e->getMessage()];
    echo json_encode($errormsg);
  }
}
