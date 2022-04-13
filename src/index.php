<?php
/**
 * @property SQlite3 $DB
*/
class phpMarshaling{ 
    public static $dbPath = "/usr/share/phpMarshaling";
    public static $max_db = 20;
    public static $connection_timeout = self::$max_db*0.5+30;

    public function __construct()
    {
    }
    
    public function connect($db_number = NULL){
        if(!isset($db_number)) { 
            $db_number = rand(0, self::$max_db-1);
            $this->DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite".$db_number);
            $this->DB->busyTimeout(self::$connection_timeout);
            return;
        }

        $this->DB = new SQLite3(self::$dbPath."phpMarshaling.sqlite".$db_number);
        $this->DB->busyTimeout(self::$max_db*10);
        return;
    }

    public static function init($initFlag="") {
        $file = fopen(self::$dbPath."/.$initFlag.phpMarshalingInit", "w");
        fwrite($file, " ");
        fclose($file);

        for($i=0; $i<self::$max_db; $i++){
            chmod(self::$dbPath."/.$initFlag.phpMarshaling.sqlite".$i, 0666);

            if(isset($username)) {
                chown($username, $username);
                chmod(self::$dbPath."/.$initFlag.phpMarshaling.sqlite".$i, 0666);
            }
        }
    }

    public function clearInit($initFlag="") {
        unlink(self::$dbPath."/.$initFlag.phpMarshalingInit");
    }

    public static function isInit($initFlag="default") {
        return file_exists(self::$dbPath."/.$initFlag.phpMarshalingInit");
    }

    public function createTable($tableName) {
        $escaped_tableName = Sqlite3::escapeString(($tableName));

        return $this->DB->exec("
            CREATE TABLE $escaped_tableName(
                dataId TEXT,
                value TEXT
            )
        ");
    }

    public function createTableMultipleColumn($tableName, $columns){
        $escaped_tableName = Sqlite3::escapeString($tableName);

        $query = "			CREATE TABLE $escaped_tableName(
            dataId TEXT";

        foreach($columns as $column) {
            $escaped_columnName = Sqlite3::escapeString($column);
            $query .= ", ".$escaped_columnName." TEXT";
        }

        $query .=");";

        return $this->DB->exec($query);
    }

    public function insert($tableName, $dataId, $value=0) {
        $escaped_tableName = Sqlite3::escapeString($tableName);
        $escaped_dataId = Sqlite3::escapeString($dataId);
        $escaped_value = Sqlite3::escapeString($value);

        $insert_query = "INSERT INTO $escaped_tableName(dataId, value) 
            VALUES('$escaped_dataId', '$escaped_value')
        ";

        return $this->DB->exec($insert_query);
    }

    public function insertColumns($tableName, $dataSet) {
        $escaped_tableName = Sqlite3::escapeString($tableName);
        $columns = array();
        $values = array();

        foreach($dataSet as $data) {
            $escaped_columnName = Sqlite3::escapeString($data[0]);
            $escaped_columnValue = Sqlite3::escapeString($data[1]);
            array_push($columns, $escaped_columnName);
            array_push($values, $escaped_columnValue);
        }

        $insert_query = "INSERT INTO $escaped_tableName($columns[0]";

        for($i=1; $i<count($columns); $i++) {
            $insert_query .= ", $columns[$i]";
        }

        $insert_query .= ") VALUES('$values[0]'";

        for($i=1; $i<count($values); $i++) {
            $insert_query .= ", '$values[$i]'";
        }

        $insert_query .= ");";

        return $this->DB->exec($insert_query);
    }

    /**
     * @param [int](string, string, string) $dataSet 
     * 		array  of tuple ($tableName, $dataId, $value)
    */
    public function insertTransaction($dataSet) {
        $query = 'BEGIN;';

        foreach($dataSet as $data) {

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

        $this->DB->exec($query);

        return ;
    }

    public function updateTransaction($tableName, $rowId, $column, $value) {
        $escaped_tableName = Sqlite3::escapeString(($tableName));
        $escaped_rowId = Sqlite3::escapeString(($rowId));
        $escaped_column = Sqlite3::escapeString(($column));
        $escaped_value = Sqlite3::escapeString(($value));

        $delete_query = "UPDATE $escaped_tableName SET $escaped_column=$escaped_value WHERE rowid=$escaped_rowId";

        return $this->DB->exec($delete_query);
    }

    public function clean($tableName, $dataId) {
        $escaped_tableName = Sqlite3::escapeString(($tableName));
        $escaped_dataId = Sqlite3::escapeString(($dataId));

        $delete_query = "DELETE FROM $escaped_tableName WHERE dataId=$escaped_dataId";

        return $this->DB->exec($delete_query);
    }

    public function clean2($tableName, $dataSet) {
        $escaped_tableName = Sqlite3::escapeString(($tableName));

        $column = $dataSet[0][0];
        $value = $dataSet[0][1];
        $delete_query = "DELETE FROM $escaped_tableName WHERE $column='$value'";

        for($i=1; $i<count($dataSet); $i++) {
            $column = $dataSet[$i][0];
            $value = $dataSet[$i][1];
            $delete_query .= " AND $column='$value'";
        }

        return $this->DB->exec($delete_query);

    }

    public function clean3($tableName, $rowId) {
        $escaped_tableName = Sqlite3::escapeString(($tableName));
        $escaped_rowId = Sqlite3::escapeString(($rowId));

        $delete_query = "DELETE FROM $escaped_tableName WHERE rowid=$escaped_rowId";

        return $this->DB->exec($delete_query);
    }

    public function cleanAll($tableName) {
        $escaped_tableName = Sqlite3::escapeString(($tableName));
        $delete_query = "DELETE FROM $escaped_tableName";

        return $this->DB->exec($delete_query);
    }

    public function get($tableName, $dataId) {
        $escaped_tableName = Sqlite3::escapeString(($tableName));

        $select_query = "SELECT * FROM $escaped_tableName";

        return $this->DB->exec($select_query);
    }
    
    public function getAll($tableName) {
        $escaped_tableName = Sqlite3::escapeString(($tableName));

        $get_query = "SELECT dataId, value, COUNT(dataId) AS sum FROM $escaped_tableName GROUP BY dataId";

        $queryResult = $this->DB->query($get_query);
        $rows = array();

        while($row = $queryResult->fetchArray()) {
            array_push($rows, $row);
        }

        return $rows;
    }

    public function getAll2($tableName, $columns) {
        $escaped_tableName = Sqlite3::escapeString($tableName);

        $get_query = " 
            SELECT dataId, COUNT(dataId) AS sum";

        foreach($columns as $column){
            $get_query .= ", $column";
        }

        $get_query .= " FROM $escaped_tableName GROUP BY dataId";
        
        foreach($columns as $column) {
            $escaped_columnName = Sqlite3::escapeString($column);
            $get_query .=", $escaped_columnName";
        }

        $queryResult = $this->DB->query($get_query);
        $rows = array();

        while($row = $queryResult->fetchArray()) {
            array_push($rows, $row);
        }

        return $rows;
    }

    public function getAllWithClean($tableName) {
        $escaped_tableName = Sqlite3::escapeString(($tableName));

        self::$DB->exec("BEGIN;");
        $query_result = self::$DB->query("
        BEGIN;
            SELECT dataId, value, COUNT(dataId) AS sum FROM $escaped_tableName GROUP BY dataId;
            DELETE FROM $escaped_tableName;
        END;
        ");

        $rows = array();

        while($row = $query_result->fetchArray()) {
            array_push($rows, $row);
        }

        return $rows;
    }

    public function beginTransaction(){
        $this->DB->exec("BEGIN EXCLUSIVE;");
    }

    public function endTransaction(){
        $this->DB->exec("END;");
    }

    public function commit(){
        $this->DB->exec("COMMIT;");
    }

}
?>
