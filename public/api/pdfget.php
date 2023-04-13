<?php
namespace corsica\mto;

require(dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php');

use corsica\framework\router\ApiGet;

ApiGet::route($_GET);


