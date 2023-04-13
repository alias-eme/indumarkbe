<?php

namespace corsica\mto;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
//header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//////////////////////////////////////////////////////////////////
// recibe las transacciones desde la barraca

require(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

use corsica\mto\router\Api4Print;


/*
//los headers
$headers = getallheaders();
$key = $headers["key"];

if ($key == null) {
  $response = array("error" => "No tiene llave para realizar esta operacion");
  echo json_encode($response);
  exit;
} else {
  $localKey = "qwertyuiopasdfghjklzxcvbnm";
  if ($key != $localKey) {
    $response = array("error" => "La llave no corresponde");
    echo json_encode($response);
    exit;    
  } else {*/
    //la data del llamado
    //ahora puedo parsear el contenido
    $env = (object)array("env"=>"maderas","idperfil"=>1,"idusuario"=>1);
    $data = json_decode(file_get_contents("php://input"));
    $clase = new Api4Print($env);
    $deletedocs = ($data->deletedocs==1) ? true : false;
    $response = $clase->getDocumentos($data->idtipo,$deletedocs);
    echo json_encode($response);
    exit;
//  }
//}




