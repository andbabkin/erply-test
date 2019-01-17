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

        while($row = $stmt->fetch()){
            if(!array_key_exists($row[$this->stock], $data)){
                $data[$this->stock] = [];
            }
            $data[ $row[$this->stock] ][ $row[$this->id] ] = $row[$this->qty];
        }

        return $data;
    }

    /**
     * @return int number of rows deleted
     */
    public function deleteAll()
    {
        $sql = "DELETE FROM `{$this->table}`";
        return DBConnection::getConn()->exec($sql);
    }

    /**
     * @param array $data - [ [warehouseID] => [ ['productID' => integer,'amountInStock' => decimal(,6)], ... ], ... ]
     * @return int number of added rows
     */
    public function insertFromExternalSource($data)
    {
        $first = true;
        $sql = "INSERT INTO `{$this->table}` (`{$this->id}`,`{$this->qty}`,`{$this->stock}`) VALUES ";
        foreach ($data as $stock => $items){
            foreach ($items as $record){
                if($first){
                    $first = false;
                } else {
                    $sql .= ', ';
                }
                $p = (int)$record['productID'];
                $a = $record['amountInStock'] + 0; // make number if type is string
                $sql .= "($p, $a, $stock)";
            }
        }
        return DBConnection::getConn()->exec($sql);
    }

    public function insert($product_id, $amount, $stock_id)
    {
        $sql = "INSERT INTO `{$this->table}` (`{$this->id}`,`{$this->qty}`,`{$this->stock}`) VALUES "
            ."($product_id,$amount,$stock_id)";
        return DBConnection::getConn()->exec($sql);
    }

    public function update($product_id, $amount, $stock_id)
    {
        $sql = "UPDATE `{$this->table}` SET `{$this->qty}`=$amount "
            ."WHERE `{$this->id}`=$product_id AND `{$this->stock}`=$stock_id";
        return DBConnection::getConn()->exec($sql);
    }
}
