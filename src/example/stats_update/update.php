<?php

$require_path = dirname(__FILE__)."/index.php";

require_once($require_path);

$dataSet = [
	["ClickStats", 236643],
	["UserStats", 236643, 5321],
	["CountryStats", 236643, 'Korea']
]

phpMarshaling::insertTransaction($dataSet);

?>