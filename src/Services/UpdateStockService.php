<?php

namespace App\Services;

use App\DB\ParametersDAO;
use App\DB\StockDAO;
use App\Utils\EAPI;

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
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        // Get the timestamp of last update
        $this->changed_since = $this->parametersDAO->get('changed_since');

        // Get data from api
        $received_data = $this->getDataFromApi();
        if($received_data['status']['responseStatus'] === 'error'){
            throw new \Exception('Request Error: '.$received_data['status']['errorCode']);
        }

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
     * @return array
     * @throws \Exception
     */
    private function getDataFromApi(): array
    {
        // Input parameters
        $params = [
            'requests' => json_encode($this->prepareRequests()),
            'responseType' => 'CSV'
        ];

        $result = $this->eapi->sendRequest('', $params);
        $decoded = json_decode($result, true);
        if(is_array($decoded)){
            return $decoded;
        } else {
            throw new \Exception("Non-JSON object or empty response returned from API.");
        }
    }

    /**
     * @return array
     */
    private function prepareRequests(): array
    {
        $requests = [];
        foreach ($this->getWarehouseIds() as $wid){
            $request = ['requestName' => 'getProductStock', 'warehouseID' => $wid, 'getAmountReserved' => 1];
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
    private function getWarehouseIds(): array
    {
        return [1,2];
    }

    /**
     * Get data in CSV format and convert it into array.
     * @param array $received_data
     * @return array - [ [warehouseID] => [ ['productID' => integer,'amountInStock' => decimal(,6)], ... ], ... ]
     */
    private function processData(array $received_data): array
    {
        $stocks = [];
        if(isset($received_data['requests'])){
            $i=0;
            foreach ($this->getWarehouseIds() as $wid){
                if(isset($received_data['requests'][$i]['records'][0]['reportLink'])){
                    // URL address of CSV file with stock data
                    $link = $received_data['requests'][$i]['records'][0]['reportLink'];
                    // Get content of the CSV file as string
                    $content = file_get_contents($link);
                    if($content !== false){
                        // Split string to rows
                        $content_data = explode("\n", $content);
                        // Prepare array for products
                        $stocks[$wid] = [];
                        // Get product IDs and stock amounts from the rows
                        foreach ($content_data as $row){
                            $row_data = explode(',', $row);
                            if(count($row_data) > 2) {
                                $prid = trim($row_data[0], " \t\n\r\0\x0B\"");
                                $amnt = trim($row_data[1], " \t\n\r\0\x0B\"");
                                $rsrv = trim($row_data[2], " \t\n\r\0\x0B\"");
                                if(is_numeric($prid) && is_numeric($amnt) && is_numeric($rsrv)){
                                    $stocks[$wid][] = [
                                        'productID' => (int)$prid,
                                        'amountInStock' => round((float)$amnt - (float)$rsrv, 6)
                                    ];
                                }
                            }
                        }
                    } else {
                        // Logging
                        echo "Failed to get CSV content from $link".PHP_EOL;
                    }
                } else if(isset($received_data['requests'][$i]['status']['errorCode'])){
                    // Logging
                    echo "Request error: ".$received_data['requests'][$i]['status']['errorCode'].PHP_EOL;
                }
                $i++;
            }
        }
        return $stocks;
    }

    /**
     * @param array $data - [ [warehouseID] => [ ['productID' => integer,'amountInStock' => decimal(,6)], ... ], ... ]
     * @return void
     */
    private function insertData(array $data)
    {
        // Delete all stock records
        $this->stockDAO->deleteAll();

        // Insert all received data
        $inserted = $this->stockDAO->insertFromExternalSource($data);

        // Logging
        if($inserted !== false){
            echo "Inserted $inserted row(s)".PHP_EOL;
        }
    }

    /**
     * @param array $data - [ [warehouseID] => [ ['productID' => integer,'amountInStock' => decimal(,6)], ... ], ... ]
     * @return void
     */
    private function updateData(array $data)
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
                    $updated += $this->stockDAO->update($p, $a, $stock);
                } else {
                    $inserted += $this->stockDAO->insert($p, $a, $stock);
                }
            }
        }

        // Logging
        echo "Inserted $inserted row(s)".PHP_EOL;
        echo "Updated $updated row(s)".PHP_EOL;
    }
}
