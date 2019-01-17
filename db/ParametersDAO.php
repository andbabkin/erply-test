<?php
namespace DB;


class ParametersDAO
{
    private $table = 'parameters';

    public function get($name, $create_if_not_exist = true)
    {
        $sql = "SELECT `value` FROM `{$this->table}` WHERE `name` = ?";
        $stmt = DBConnection::executeStatement($sql, [$name]);
        $param = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($param === false){
            if($create_if_not_exist){
                $this->create($name);
            }
            return null;
        } else {
            return $param['value'];
        }
    }

    public function create($name)
    {
        $sql = "INSERT INTO {$this->table} (`name`) VALUES (?)";
        DBConnection::executeStatement($sql, [$name]);
    }

    public function set($name, $value)
    {
        $sql = "UPDATE {$this->table} SET `value` = ? WHERE `name` = ?";
        DBConnection::executeStatement($sql, [$value, $name]);
    }
}
