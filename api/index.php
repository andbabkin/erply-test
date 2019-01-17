<?php
/**
 * API request. Takes warehouse Id and collection of item amounts as
 * input parameters. Sends positive response if all items can be
 * issued from stock, and negative response if at least one item
 * doesn't have enough quantity in the warehouse.
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type:application/json;charset=UTF-8');
header('Cache-Control:no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
header('Pragma:no-cache');

$data = ['param1'=>'12345','param2'=>'Hi!','param3'=>12345];
echo json_encode($data);
