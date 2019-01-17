<?php

namespace Services;

use DB\ParametersDAO;
use Utils\EAPI;

class UpdateStockService
{
    private $eapi;
    private $parametersDAO;

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
        /*$received_data = $this->getDataFromApi();
        $processed_data = $this->processData($received_data);*/
        $val = $this->parametersDAO->get('test');
        if(empty($val)){
            echo 'Value is empty';
        } else {
            echo 'Value='.$val;
        }

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
            $requests[] = ['requestName' => 'getProductStock', 'warehouseID' => $wid];
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
}
