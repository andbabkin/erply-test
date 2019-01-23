<?php

namespace App\DB;

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
    public function getAll(): array
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
     * @param int $stock_id
     * @param array $product_ids - array of integer values
     * @return array - [ ['productID' => integer,'amountInStock' => decimal], ... ]
     */
    public function getStockAmountsByIDs(int $stock_id, array $product_ids): array
    {
        $plcs = implode(',', array_fill(0, count($product_ids), '?'));
        $sql = "SELECT `{$this->id}`, `{$this->qty}` FROM `{$this->table}` "
            ."WHERE `{$this->stock}`=? AND `{$this->id}` IN ($plcs)";
        $stmt = DBConnection::executeStatement($sql, array_merge([$stock_id], $product_ids));
        $data = [];
        while($row = $stmt->fetch()){
            $data[] = [
                'productID' => $row[$this->id],
                'amountInStock' => $row[$this->qty]
            ];
        }
        return $data;
    }

    /**
     * @return int number of rows deleted
     */
    public function deleteAll(): int
    {
        $sql = "DELETE FROM `{$this->table}`";
        return DBConnection::getConn()->exec($sql);
    }

    /**
     * @param array $data - [ [warehouseID] => [ ['productID' => integer,'amountInStock' => decimal], ... ], ... ]
     * @return int number of added rows
     */
    public function insertFromExternalSource(array $data): int
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

    public function insert(int $product_id, float $amount, int $stock_id): int
    {
        $sql = "INSERT INTO `{$this->table}` (`{$this->id}`,`{$this->qty}`,`{$this->stock}`) VALUES (?,?,?)";
        $stmt = DBConnection::executeStatement($sql, [$product_id, $amount, $stock_id]);
        return $stmt->rowCount();
    }

    public function update(int $product_id, float $amount, int $stock_id): int
    {
        $sql = "UPDATE `{$this->table}` SET `{$this->qty}`=? "
            ."WHERE `{$this->id}`=? AND `{$this->stock}`=?";
        $stmt = DBConnection::executeStatement($sql, [$amount, $product_id, $stock_id]);
        return $stmt->rowCount();
    }
}
