<?php

namespace DB;

class StockDAO
{
    // table name
    private $table = 'stock';

    // column names
    private $id = 'itemid';
    private $qty = 'qty';
    private $stock = 'stockid';

    /**
     * @return array - [ [stock] => [ id => qty, ... ], ... ]
     */
    public function getAll()
    {
        $data = [];

        $sql = "SELECT * FROM `{$this->table}`";
        $stmt = DBConnection::getConn()->query($sql);

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            if(!array_key_exists($row[$this->stock], $data)){
                $data[$this->stock] = [];
            }
            $data[ $this->stock ][ $row[$this->id] ] = $row[$this->qty];
        }

        return $data;
    }
}
