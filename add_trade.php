<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']!==true){ http_response_code(403); exit; }

$tradesFile = 'trades.json';
$data = file_exists($tradesFile)?json_decode(file_get_contents($tradesFile),true):[];

$json = file_get_contents('php://input');
$trade = json_decode($json,true);

if($trade && isset($trade['date'],$trade['coin'],$trade['qty'])){
    if(!isset($trade['type'])) $trade['type']="buy";
    if($trade['type']==="withdraw" && !isset($trade['usd'])) $trade['usd']=0;
    $data[] = $trade;
    file_put_contents($tradesFile,json_encode($data, JSON_PRETTY_PRINT|LOCK_EX));
    echo json_encode(['status'=>'ok']);
} else {
    http_response_code(400);
    echo json_encode(['status'=>'error']);
}
?>