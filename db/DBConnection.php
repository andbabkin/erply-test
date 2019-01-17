<?php

namespace DB;


final class DBConnection
{
    static private $conn = null;

    private function __construct() {}

    static public function getConn()
    {
        if(self::$conn == null){
            $host = 'localhost';
            $port = '3311'; // default 3306
            $db = 'erply';
            $user = 'service';
            $pass = 'password';

            try
            {
                self::$conn = new \PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8;", $user, $pass);
            }
            catch (\PDOException $e)
            {
                // Logging
                echo "Error!: " . $e->getMessage();
            }

        }
        return self::$conn;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    static public function executeStatement($sql, $params)
    {
        $stmt = self::getConn()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
