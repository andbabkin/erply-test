<?php

namespace Services;

use DB\ParametersDAO;
use DB\StockDAO;
use Utils\EAPI;

class UpdateStockService
{
    private $eapi;
    private $parametersDAO;
    private $stockDAO;
    private $changed_since;

    public function __construct(EAPI $eapi, ParametersDAO $parametersDAO, StockDAO $stockDAO)
    {
        $this->eapi = $eapi;
        $this->parametersDAO = $parametersDAO;
        $this->stockDAO = $stockDAO;
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        // Get the timestamp of last update
        $this->changed_since = $this->parametersDAO->get('changed_since');

        // Get data from api
        $received_data = $this->getDataFromApi();
        $new_changed_since = $received_data['status']['requestUnixTime'];
        $processed_data = $this->processData($received_data);

        // Save data to DB
        if(empty($this->changed_since)){
            $this->insertData($processed_data);
        } else {
            $this->updateData($processed_data);
        }

        // Update timestamp
        $this->parametersDAO->set('changed_since', $new_changed_since);
    }

    /**
     * Get stock amounts from ERPLY API.
     * @throws \Exception
     */
    private function getDataFromApi()
    {
        // Input parameters
        $params = [
            'requests' => json_encode($this->prepareRequests())
        ];

        $result = $this->eapi->sendRequest(false, $params);
        return json_decode($result, true);
    }

    /**
     * @return array
     */
    private function prepareRequests()
    {
        $requests = [];
        foreach ($this->getWarehouseIds() as $wid){
            $request = ['requestName' => 'getProductStock', 'warehouseID' => $wid];
            if(!empty($this->changed_since)){
                $request['changedSince'] = $this->changed_since;
            }
            $requests[] = $request;
        }
        return $requests;
    }

    /**
     * It's hardcoded in present. When this service will be taken in use
     * the warehouses data should be located in DB.
     * The warehouses data update must be performed by another service.
     * @return array
     */
    private function getWarehouseIds()
    {
        return [1,2];
    }

    /**
     * Connect received data with warehouse ids.
     * @param array $data
     * @return array - [ [warehouseID] => [ ['productID' => integer,'amountInStock' => decimal(,6)], ... ], ... ]
     */
    private function processData($data)
    {
        $processed = [];
        $i=0;
        foreach ($this->getWarehouseIds() as $wid){
            $processed[$wid] = $data['requests'][$i]['records'];
            $i++;
        }
        return $processed;
    }

    /**
     * @param array $data - [ [warehouseID] => [ ['productID' => integer,'amountInStock' => decimal(,6)], ... ], ... ]
     */
    private function insertData($data)
    {
        // Delete all stock records
        $this->stockDAO->deleteAll();

        // Insert all received data
        $inserted = $this->stockDAO->insertFromExternalSource($data);

        // Logging
        if($inserted !== false){
            echo "Inserted $inserted row(s)\n";
        }
    }

    /**
     * @param array $data - [ [warehouseID] => [ ['productID' => integer,'amountInStock' => decimal(,6)], ... ], ... ]
     */
    private function updateData($data)
    {
        // Get all local records
        // [ [stock] => [ id => qty, ... ], ... ]
        $local = $this->stockDAO->getAll();

        // For each record from external source
        // do INSERT if product doesn't exist locally
        // do UPDATE amount if it exists
        $inserted = 0;
        $updated = 0;
        foreach ($data as $stock => $items){
            foreach ($items as $record){
                $p = (int)$record['productID'];
                $a = $record['amountInStock'] + 0; // make number if type is string
                if(array_key_exists($p, $local[$stock])){
                    $rows = $this->stockDAO->update($p, $a, $stock);
                    if($rows !== false) $updated++;
                } else {
                    $rows = $this->stockDAO->insert($p, $a, $stock);
                    if($rows !== false) $inserted++;
                }
            }
        }

        // Logging
        echo "Inserted $inserted row(s)\n";
        echo "Updated $updated row(s)\n";
    }
}
