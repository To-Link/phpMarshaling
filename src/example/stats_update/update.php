<?php

$require_path = dirname(__FILE__)."/index.php";

require_once($require_path);

phpMarshaling::insert("ClickStats", "1");
phpMarshaling::insert("ClickStats", "1");
phpMarshaling::insert("ClickStats", "2");
phpMarshaling::insert("ClickStats", "2");
phpMarshaling::insert("ClickStats", "3");
phpMarshaling::insert("ClickStats", "3");
phpMarshaling::insert("ClickStats", "3");
phpMarshaling::insert("ClickStats", "4");
phpMarshaling::insert("ClickStats", "4");
phpMarshaling::insert("ClickStats", "4");
phpMarshaling::insert("ClickStats", "4");
phpMarshaling::insert("ClickStats", "4");
phpMarshaling::insert("ClickStats", "4");
phpMarshaling::insert("ClickStats", "4");

$rows = phpMarshaling::getAllWithClean("ClickStats");

foreach( $rows as $row) {
	print_r($row);
	echo("<br>");
}

?>