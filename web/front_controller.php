<?php 
session_start();
require_once '../src/business.php';
require_once '../src/controllers.php';
require_once '../src/routing.php';
require_once '../src/dispatcher.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'library';
dispatch($routing, $action);
?>
