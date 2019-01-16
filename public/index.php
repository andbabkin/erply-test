<?php
echo "<!DOCTYPE html><html><head><title>Test site</title></head><body>";
echo "<h1>Hi all!</h1>";
echo "<p>Remote IP: {$_SERVER['REMOTE_ADDR']}<br>";
echo "Request: {$_SERVER['REQUEST_URI']}<br>";
echo "Query: {$_SERVER['QUERY_STRING']}<br>";
if(isset($_GET['name'])){
	echo "Name: {$_GET['name']}";
}
echo "</p>";
echo "<h2>Route</h2><p>";
$path = trim($_SERVER['REQUEST_URI'],'/');
$q_start = strpos($path, '?');
if($q_start !== false){
	$path = substr($path, 0, $q_start);
}
$segments = explode('/',$path);
foreach($segments as $index => $segment){
	echo "$index ($segment)<br>";
}
echo "</p>";
echo "</body></html>";
