<?php
	class phpMarshaling{ 
		public static $dbPath = "/usr/share/phpMarshaling";

		public static function init($username=NULL) {
			$file = fopen(self::$dbPath.".phpMarshalingInit", "w");
			fwrite($file, " ");
			fclose($file);

			chmod(self::$dbPath."phpMarshaling.sqlite", 0666);

			if(isset($username)) {
				chown($username, $username);
				chmod(self::$dbPath."phpMarshaling.sqlite", 0666);
			}
		}

		public static function clearInit() {
			unlink(self::$dbPath.".phpMarshalingInit");
		}

		public static function isInit() {
			return file_exists(self::$dbPath.".phpMarshalingInit");
		}

		public static function createTable($tableName) {
			$DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite");
			$escaped_tableName = Sqlite3::escapeString(($tableName));

			return $DB->exec("
				CREATE TABLE $escaped_tableName(
					dataId TEXT,
					value TEXT
				)
			");
		}

		public static function insert($tableName, $dataId, $value=0) {
			$DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite");
			$DB->busyTimeout(2000);
			$escaped_tableName = Sqlite3::escapeString(($tableName));
			$escaped_dataId = Sqlite3::escapeString(($dataId));
			$escaped_value = Sqlite3::escapeString(($value));

			$insert_query = "INSERT INTO $escaped_tableName(dataId, value) 
				VALUES('$escaped_dataId', '$escaped_value')
			";

			return $DB->exec($insert_query);
		}

		/**
		 * @param [int](string, string, string) $dataSet 
		 * 		array  of tuple ($tableName, $dataId, $value)
		*/
		public static function insertTransaction($dataSet) {
			$query = 'BEGIN;';

			foreach($dataSet as $data) {

				$DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite");
				$DB->busyTimeout(2000);
				$escaped_tableName = Sqlite3::escapeString($data[0]);
				$escaped_dataId = Sqlite3::escapeString($data[1]);

				if(isset($data[2])) {
					$escaped_value = Sqlite3::escapeString($data[2]);
				} 
				else {
					$escaped_value = 0;
				}

				$query .= "INSERT INTO $escaped_tableName(dataId, value) 
					VALUES('$escaped_dataId', '$escaped_value');
				";
			}

			$query .= 'END;';

			$DB->exec($query);

			return ;
		}

		public static function clean($tableName, $dataId) {
			$DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite");
			$escaped_tableName = Sqlite3::escapeString(($tableName));
			$escaped_dataId = Sqlite3::escapeString(($dataId));

			$delete_query = "DELETE FROM $escaped_tableName WHERE dataId=$escaped_dataId";

			return $DB->exec($delete_query);
		}

		public static function cleanAll($tableName) {
			$DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite");
			$escaped_tableName = Sqlite3::escapeString(($tableName));
			$delete_query = "DELETE FROM $escaped_tableName";

			return $DB->exec($delete_query);
		}

		public static function get($tableName, $dataId) {
			$DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite");
			$escaped_tableName = Sqlite3::escapeString(($tableName));

			$select_query = "SELECT * FROM $escaped_tableName";

			return $DB->exec($select_query);
		}
		
		public static function getAll($tableName) {
			$DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite");
			$escaped_tableName = Sqlite3::escapeString(($tableName));

			$get_query = "SELECT dataId, value, COUNT(dataId) AS sum FROM $escaped_tableName GROUP BY dataId";

			$queryResult = $DB->query($get_query);
			$rows = array();

			while($row = $queryResult->fetchArray()) {
				array_push($rows, $row);
			}

			return $rows;
		}

		public static function getAllWithClean($tableName) {
			$DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite");
			$escaped_tableName = Sqlite3::escapeString(($tableName));

			$DB->exec("BEGIN");
			$query_result = $DB->query("SELECT dataId, value, COUNT(dataId) AS sum FROM $escaped_tableName GROUP BY dataId");

			$rows = array();

			while($row = $query_result->fetchArray()) {
				array_push($rows, $row);
			}

			$DB->exec("DELETE FROM $escaped_tableName");
			$DB->exec("COMMIT");

			return $rows;
		}
	}
?>