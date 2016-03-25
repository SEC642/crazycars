<?php
require_once '../../library/config.php';
require_once '../library/functions.php';

$_SESSION['login_return_url'] = $_SERVER['REQUEST_URI'];
$uid=checkUser();

/* If UID is not 1, they don't get this page. */
if ($uid != 1) {
	doLogout();
}

$view = (isset($_GET['view']) && $_GET['view'] != '') ? $_GET['view'] : '';

switch ($view) {
	case 'list' :
		$content 	= 'list.php';		
		$pageTitle 	= 'Shop Admin Control Panel - View Category';
		break;

	case 'add' :
		$content 	= 'add.php';		
		$pageTitle 	= 'Shop Admin Control Panel - Add Category';
		break;

	case 'modify' :
		$content 	= 'modify.php';		
		$pageTitle 	= 'Shop Admin Control Panel - Modify Category';
		break;

	default :
		$content 	= 'list.php';		
		$pageTitle 	= 'Shop Admin Control Panel - View Category';
}


$script    = array('category.js');

require_once '../include/template.php';
?>
