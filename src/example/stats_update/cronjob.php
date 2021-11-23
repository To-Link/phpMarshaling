<?php

$require_path = dirname(__FILE__)."../index.php";
require_once($require_path);

$cache = new phpMarshaling();

// Initalized..
if(!$cache->isInit()) {
	$cache->createTable("ClickStats");

	$cache->init();
}

$rows = $cache->getAll("ClickStats");

$isUpdate = count($rows);

if($isUpdate < 1) {
	// Don't need to update.
	return;
}

// UPDATE TO DATABASE
$db_host = "your_host";
$db_dbname = "your_db";
$db_user = "your_user";
$db_password = "your_password";

$remote_database = new PDO("mysql:host=$db_host;dbname=$db_dbname", $db_user, $db_password);

foreach( $rows as $row) {
	$id = $row->dataId;
	$sum = $row->sum;

	$remote_database->exec("UPDATE click=click+$sum FROM Stats WHERE id=$id");
}

?>