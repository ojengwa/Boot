<?php
/**
 * Database class takes care of all database connections, creating of database objects, etc.
 */
class Database {

    public static $pdo;


    public static $db_mysqli = null;

    public static function connect_mysqli() {
        global $BOOTSITE_DATABASE_CONFIG;

        if (Database::$db_mysqli)
            return true;

        Database::$db_mysqli = new mysqli($BOOTSITE_DATABASE_CONFIG['host'], $BOOTSITE_DATABASE_CONFIG['user'], $BOOTSITE_DATABASE_CONFIG['password'], $BOOTSITE_DATABASE_CONFIG['database']);
        if (Database::$db_mysqli->connect_errno > 0) {
    		die('Unable to connect to database [' . Database::$db_mysqli->connect_error . ']');
        }
    }

    public static function run_query($query_string) {
        $result = Database::$db_mysqli->query($query_string);

        if (!$result) {
            debug_backtrace();
    		die('Query Error [' . Database::$db_mysqli->error . ']');
        }
        return $result;
    }

    public static function fetch_assoc($result) {
        if ($result)
            return $result->fetch_assoc();

        return null;
    }

    public static function fetch_array($result) {
        return $result->fetch_array();
    }

    public static function num_rows($result) {
        return $result->num_rows;
    }

    public static function escape_string($str) {
        if (Database::$db_mysqli)
            return Database::$db_mysqli->real_escape_string($str);
        else
            die("Database not connected");
    }
/**
 * Creates a database connection and sets it as a PDO global variable.
 *
 * @todo  Set PDO to class variable rather than global.
 * @return bool
 */
    public static function connect() {
        global $BOOTSITE_DATABASE_CONFIG;

        if (Database::$pdo)
            return true;

        try {
            $dsn = $BOOTSITE_DATABASE_CONFIG['dsn'] . ":dbname=" . $BOOTSITE_DATABASE_CONFIG['database'] . ";host=" . $BOOTSITE_DATABASE_CONFIG['host'];

            Database::$pdo = new PDO ($dsn, $BOOTSITE_DATABASE_CONFIG['user'], $BOOTSITE_DATABASE_CONFIG['password']);
            Database::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return true;
        } catch (PDOException $e) {
            echo "Database connection failure: " . $e->getMessage() . "\n";
    		exit;
        }
    }


}
?>
