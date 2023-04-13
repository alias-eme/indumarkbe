<?php

require(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

$method = $_SERVER['REQUEST_METHOD'];
$apikey = $_SERVER['HTTP_APIKEY'];
$url = $_SERVER['REQUEST_URI'];
//echo json_encode($_SERVER);
//url is ...com/api2/resource/id but also ...com/subproject/api2
$urltoken = explode('/', $url);
$step = ($urltoken[1] == 'api2') ? 0 : 1;

$resource = ($urltoken[2 + $step] == '') ? null : $urltoken[2 + $step];
$id = ($urltoken[3 + $step] == '') ? null : $urltoken[3 + $step];
if ($method == 'GET') {
    $data = $_GET;
} else {
    $data = json_decode(file_get_contents("php://input"));
}

$response = \corsica\framework\router\Api2::execute($apikey, $resource, $id, $method, $data);
echo json_encode($response);
