<?php
namespace App\DB;


class ParametersDAO
{
    // table name
    private $table = 'parameters';

    // column names
    private $name = 'name';
    private $value = 'value';

    public function get(string $name, bool $create_if_not_exist = true): ?string
    {
        $sql = "SELECT `{$this->value}` FROM `{$this->table}` WHERE `{$this->name}` = ?";
        $stmt = DBConnection::executeStatement($sql, [$name]);
        $param = $stmt->fetch();
        if($param === false){
            if($create_if_not_exist){
                $this->create($name);
            }
            return null;
        } else {
            return $param[$this->value];
        }
    }

    /**
     * @param string $name
     * @return void
     */
    public function create(string $name)
    {
        $sql = "INSERT INTO {$this->table} (`{$this->name}`) VALUES (?)";
        DBConnection::executeStatement($sql, [$name]);
    }

    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    public function set(string $name, string $value)
    {
        $sql = "UPDATE {$this->table} SET `{$this->value}` = ? WHERE `{$this->name}` = ?";
        DBConnection::executeStatement($sql, [$value, $name]);
    }
}
