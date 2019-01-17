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
            $db = 'erply';
            $user = 'service';
            $pass = 'password';

            try
            {
                $options = [
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ];
                self::$conn = new \PDO("mysql:host=$host;dbname=$db;charset=utf8;", $user, $pass, $options);
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
     * Executes query with parameters
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
