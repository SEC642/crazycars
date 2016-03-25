<?php
require_once '../../library/config.php';
require_once '../library/functions.php';

$uid = checkUser();

/* If UID is not 1, they don't get this page. */
if ($uid != 1) {
	doLogout();
}

$view = (isset($_GET['view']) && $_GET['view'] != '') ? $_GET['view'] : '';

switch ($view) {
	default :
		$content 	= 'main.php';		
		$pageTitle 	= 'Shop Admin Control Panel - Shop Configuration';
}

$script    = array('shop.js');

require_once '../include/template.php';
?>
