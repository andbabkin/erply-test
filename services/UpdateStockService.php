<?php

namespace Services;

use DB\ParametersDAO;
use Utils\EAPI;

class UpdateStockService
{
    private $eapi;
    private $parametersDAO;
    private $changed_since;

    public function __construct(EAPI $eapi, ParametersDAO $parametersDAO)
    {
        $this->eapi = $eapi;
        $this->parametersDAO = $parametersDAO;
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

        // Logging
        //print_r($processed_data);
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
     * @param $data
     * @return array - [ [warehouse_id] => [ ['productID','amountInStock'], ... ], ... ];
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

    private function insertData($data)
    {
        // Delete all stock records

        // Insert all received data
    }

    private function updateData($data)
    {
        // Get all local records

        // For each record from external source
        // do INSERT if product doesn't exist locally
        // do UPDATE amount if it exists
    }
}
