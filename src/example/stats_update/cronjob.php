<?php

$debug = TRUE;
// $global_time = new HRTime\StopWatch;
// $global_time->start();

// $time_1 = new HRTime\StopWatch;
// $time_1->start();
//**********//

$require_path = dirname(__FILE__)."../index.php";
require_once($require_path);

if(!phpMarshaling::isInit()){
	for($i=0; $i<phpMarshaling::$max_db; $i++){
		$cache = new phpMarshaling();
		$cache->connect($i);
		$cache->beginTransaction();
		$cache->createTable("DefaultStats");
		$cache->createTableMultipleColumn("ProStats", ["os", "browser", "country"]);
		$cache->endTransaction();
	}

	phpMarshaling::init();
}

for($i=0; $i<phpMarshaling::$max_db; $i++){
	$cache = new phpMarshaling();
	$cache->connect($i);

	$cache->beginTransaction();
	$default_stats = $cache->getAll("DefaultStats");
	$pro_stats = $cache->getAll2("ProStats", ["os", "browser", "country"]);
	$cache->endTransaction();

	$updateItemNumber = count($default_stats) + count($pro_stats);

	if($debug) {
		echo("Connected to $i\n");
		echo("There are update items $isUpdate \n");
	}

	if($updateItemNumber < 1) continue;

	if(!isset($db)){
		$db = new PDO("mysql=domain;dbname='something';charset=utf8", "username", "password");
	}

	foreach($default_stats as $stats) {
		$id = $stats['dataId'];
		$sum = $stats['sum'];

		$query = $db->prepare("call update_default_stats(?, ?)");
		$query->execute([$id, $sum]);
	}

	foreach($pro_stats as $stats) {
		$id = $stats['dataId'];
		$os = $stats['os'];
		$browser = $stats['browser'];
		$country = $stats['country'];
		$sum = $stats['sum'];

		$query = $db->prepare("call update_pro_stats(?, ?, ?, ?, ?)");
		$query->execute([$id, $os, $browser, $country, $sum]);
	}
}

$cache->cleanAll("DefaultStats");
$cache->cleanAll("ProStats");
$cache->endTransaction();

//**********//
// $time_1->stop();
// $elapsed0 = $time_1->getLastElapsedTime(HRTime\Unit::MILLISECOND);
// echo("This script take time $elapsed0 ms");

?>
