<?php

$require_path = dirname(__FILE__)."/index.php";

require_once($require_path);

$cache = new phpMarshaling();
$cache->connect();

$cache->insert("DefaultStats", "1");
$cache->insertColumns("ProStats", [["dataId", "1"], ["os", "Windows"], ["browser", "Chrome"], ["country", "Republic of Korea"]]);

?>
