<?php
require(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

use corsica\framework\config\Config;
use corsica\framework\utils\Token;

$method = $_SERVER['REQUEST_METHOD'];
$env = (object)["env"=>"maderas-dev","idusuario"=>"1","idperfil"=>"1"];
if ($method != 'OPTIONS') {

  if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"));
  } else if ($method == "GET") {
    $data = (object)$_GET;
  }
  $response = \corsica\framework\router\ApiRouter::execute($env,$data);

  echo json_encode($response);
}
