<?php
/**
 * API request. Takes warehouse Id and collection of item amounts as
 * input parameters. Sends positive response if all items can be
 * issued from stock, and negative response if at least one item
 * doesn't have enough quantity in the warehouse.
 */

// Includes
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type:application/json;charset=UTF-8');
header('Cache-Control:no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
header('Pragma:no-cache');

// Blank response
$response = [
    'status' => 'error',
    'message' => '',
    'records' => []
];


// Get input: request('getStockAmounts'), warehouseID(int), productIDs(string:integers separated by comma)
//
// Check request name
if(!isset($_GET['request']) || $_GET['request'] !== 'getStockAmounts'){
    http_response_code(400);
    $response['message'] = "Not supported request";
    echo json_encode($response);
    exit;
}

// Process warehouseID
if(!isset($_GET['warehouseID']) || !is_numeric($_GET['warehouseID'])){
    http_response_code(400);
    $response['message'] = "No valid warehouse code provided";
    echo json_encode($response);
    exit;
}
$warehouseID = (int)$_GET['warehouseID'];

// Process productIDs
if(!isset($_GET['productIDs'])){
    http_response_code(400);
    $response['message'] = "Product IDs should be provided";
    echo json_encode($response);
    exit;
}
$productIDs_raw = explode(',', $_GET['productIDs']);
$productIDs = [];
foreach ($productIDs_raw as $id_raw){
    if(is_numeric($id_raw) && (int)$id_raw > 0){
        $productIDs[] = (int)$id_raw;
    } else {
        http_response_code(400);
        $response['message'] = "Not valid Product ID";
        echo json_encode($response);
        exit;
    }
}


// Prepare output
$dao = new \App\DB\StockDAO();
try{
    $records = $dao->getStockAmountsByIDs($warehouseID, $productIDs);
} catch (\Exception $e){
    http_response_code(500);
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}
$response['status'] = 'ok';
$response['records'] = $records;

echo json_encode($response);
