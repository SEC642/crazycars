<?php

if (!defined('WEB_ROOT')) {
	exit;
}
$self = WEB_ROOT . 'admin/index.php';
/* Get and decrypt the cookie "id" with the IV.  If they are not set, return to login page. */
if (!isset($_COOKIE['uid']) || !isset($_COOKIE['iv'])) {
    /* If these values are not set, reject login. */
	if (isset($_SESSION['plaincart_user_id'])) {
		unset($_SESSION['plaincart_user_id']);
		session_unregister('plaincart_user_id');
	}
	//print("Cookie not set");
	header('Location: /admin/login.php');
	exit;
}

$iv = hex2bin($_COOKIE['iv']);
$uidenc = hex2bin($_COOKIE['uid']);
$key = md5("drowssap");

$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128,'','cbc','');
mcrypt_generic_init($cipher, $key, $iv);
list($username, $uid) = explode("|", mdecrypt_generic($cipher, $uidenc));
mcrypt_generic_deinit($cipher);

?>

<html>
<head>
<title><?php echo $pageTitle; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="<?php echo WEB_ROOT;?>admin/include/admin.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript" src="<?php echo WEB_ROOT;?>library/common.js"></script>
<?php
$n = count($script);
for ($i = 0; $i < $n; $i++) {
	if ($script[$i] != '') {
		echo '<script language="JavaScript" type="text/javascript" src="' . WEB_ROOT. 'admin/library/' . $script[$i]. '"></script>';
	}
}
?>
</head>
<body>
<table width="750" border="0" align="center" cellpadding="0" cellspacing="1" class="graybox">
  <tr>
    <td colspan="2"><img src="<?php echo WEB_ROOT; ?>admin/include/banner-top.gif" width="750" height="75"></td>
  </tr>
  <tr>
    <td width="150" valign="top" class="navArea"><p>&nbsp;</p>
<?php
	if ($uid == 1) {
?>
      <a href="<?php echo WEB_ROOT; ?>admin/" class="leftnav">Home</a> 
	  <a href="<?php echo WEB_ROOT; ?>admin/category/" class="leftnav">Category</a>
	  <a href="<?php echo WEB_ROOT; ?>admin/product/" class="leftnav">Product</a> 
	  <a href="<?php echo WEB_ROOT; ?>admin/order/?status=Paid" class="leftnav">Order</a> 
	  <a href="<?php echo WEB_ROOT; ?>admin/config/" class="leftnav">Shop Config</a> 
	  <a href="<?php echo WEB_ROOT; ?>admin/user/" class="leftnav">User</a> 
	  <a href="<?php echo $self; ?>?logout" class="leftnav">Logout</a>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
<?php
	} else {
?>
      <a href="<?php echo WEB_ROOT; ?>admin/" class="leftnav">Home</a> 
	  <a href="<?php echo WEB_ROOT; ?>admin/order/?status=Paid" class="leftnav">Order</a> 
	  <a href="<?php echo $self; ?>?logout" class="leftnav">Logout</a>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
<?php
}
?>
    <td width="600" valign="top" class="contentArea"><table width="100%" border="0" cellspacing="0" cellpadding="20">
        <tr>
          <td>
<?php
require_once $content;	 
?>
          </td>
        </tr>
      </table></td>
  </tr>
</table>
  <p align="right"><?php echo "UID:".$uid; ?></p>
</body>
</html>
