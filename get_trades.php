<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']!==true){ http_response_code(403); exit; }

$tradesFile = 'trades.json';
if(!file_exists($tradesFile)) file_put_contents($tradesFile,'[]');

header('Content-Type: application/json');
echo file_get_contents($tradesFile);
?>