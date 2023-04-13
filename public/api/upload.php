<?php

namespace corsica\mto;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use corsica\framework\config\Config;
use corsica\framework\utils\Token;

require(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

use corsica\mto\pedidos\PedidoImagen;

$method = $_SERVER['REQUEST_METHOD'];

if ($method != 'OPTIONS') {
  $request = $_POST['request'];

  if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
    header('HTTP/1.0 403 Forbidden');
    //echo '403 No hay Token';
    exit;
  } else {
    //header('HTTP/1.0 200 OK');
    $jwt = $matches[1];
    

    $secretKey  = Config::getSecretKey();
    try {
      $t = new Token();
      $env = $t->getEnv($jwt);
    } catch (ExpiredException $e) {
      header('HTTP/1.0 403 Forbidden');
      exit;
    }
  }



  //return json_encode($_POST);
  if ($request == 'archivo') {

    $pi = new PedidoImagen($env);

    $idpedido = $_POST['idpedido'];
    $idusuario = $_POST['idusuario'];
    $archivo = $_FILES['archivo'];
    if (1 * $idpedido > 0) {
      $response = $pi->guardar($idpedido, $archivo, $idusuario);

      if (array_key_exists("error", $response)) {
        echo json_encode($response);
        exit;
      } else {
        echo json_encode($pi->cargarImagenes($idpedido));
        exit;
      }
    } else {
      echo "error idpedido";
    }
  }
}
