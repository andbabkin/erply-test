<?php

namespace DB;


final class DBConnection
{
    static private $conn = null;

    private function __construct() {}

    static public function getConn()
    {
        if(self::$conn == null){
            $host = '127.0.0.1';
            $db = 'erply';
            $user = 'service';
            $pass = 'password';

            try
            {
                $options = [
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ];
                self::$conn = new \PDO("mysql:host=$host;dbname=$db;charset=utf8;", $user, $pass, $options);
            }
            catch (\PDOException $e)
            {
                // Logging
                echo "Error!: " . $e->getMessage(). "\n";
                exit;
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
