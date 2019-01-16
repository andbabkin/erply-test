<?php
/**
 * Author: Andrei Babkin <andrei@malachiteden.com>
 * Date: 16.01.2019
 * Time: 10:18
 */

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type:application/json;charset=UTF-8');
header('Cache-Control:no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
header('Pragma:no-cache');

$data = ['param1'=>'12345','param2'=>'Hi!','param3'=>12345];
echo json_encode($data);
