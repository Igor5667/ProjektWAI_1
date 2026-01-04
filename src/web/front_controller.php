<?php 
session_start();
require_once '../business.php';
require_once '../controllers.php';
require_once '../routing.php';
require_once '../dispatcher.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'library';
dispatch($routing, $action);
?>
