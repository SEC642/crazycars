<?php
require_once 'config.php';
require_once 'utils.php';

/*********************************************************
*                 SHOPPING CART FUNCTIONS 
*********************************************************/

function updateCookie()
{
    /* Former code for getCartContent() used here to build and set cookie */
	$sid = session_id();
	$sql = "SELECT ct_id, ct.pd_id, ct_qty, pd_name, pd_price, pd_thumbnail, pd.cat_id
			FROM tbl_cart ct, tbl_product pd, tbl_category cat
			WHERE ct_session_id = '$sid' AND ct.pd_id = pd.pd_id AND cat.cat_id = pd.cat_id";
	
	$result = dbQuery($sql);
	
	while ($row = dbFetchAssoc($result)) {
		if ($row['pd_thumbnail']) {
			$row['pd_thumbnail'] = WEB_ROOT . 'images/product/' . $row['pd_thumbnail'];
		} else {
			$row['pd_thumbnail'] = WEB_ROOT . 'images/no-image-small.png';
		}
		$cartContent[] = $row;
	}


    /* Iterate on the array, when pd_price isset, changed it to padded string */
    for($i=0; $i < sizeof($cartContent); ++$i) {
        if(isset($cartContent[$i]["pd_price"])) {
            $cartContent[$i]["pd_price"] = str_pad(strval($cartContent[$i]["pd_price"]), 32, "0", STR_PAD_LEFT);
            $cartContent[$i]["ts"] = microtime_float();
            $cartContent[$i]["rand"] = random(30);
		}
	}

	$cookieplain = myserialize($cartContent);
	$td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '/usr/lib/mcrypt-modes');
	$ks = mcrypt_enc_get_key_size($td);
	$key = substr(md5('password'),0, $ks);
	$iv = "\0\0\0\0\0\0\0\0"; // ECB doesn't use an IV, but mcrypt requires it
	mcrypt_generic_init($td, $key, $iv);
	
	$cookieenc = mcrypt_generic($td, $cookieplain);
	mcrypt_generic_deinit($td);
	setrawcookie("state",bin2hex($cookieenc));
}
 
function addToCart()
{
	// make sure the product id exist
	if (isset($_GET['p']) && (int)$_GET['p'] > 0) {
		$productId = (int)$_GET['p'];
	} else {
		header('Location: index.php');
	}
	
	// does the product exist ?
	$sql = "SELECT pd_id, pd_qty
	        FROM tbl_product
			WHERE pd_id = $productId";
	$result = dbQuery($sql);
	
	if (dbNumRows($result) != 1) {
		// the product doesn't exist
		header('Location: cart.php');
	} else {
		// how many of this product we
		// have in stock
		$row = dbFetchAssoc($result);
		$currentStock = $row['pd_qty'];

		if ($currentStock == 0) {
			// we no longer have this product in stock
			// show the error message
			setError('The product you requested is no longer in stock');
			header('Location: cart.php');

		}

	}		
	
	// current session id
	$sid = session_id();
	
	// check if the product is already
	// in cart table for this session
	$sql = "SELECT pd_id
	        FROM tbl_cart
			WHERE pd_id = $productId AND ct_session_id = '$sid'";
	$result = dbQuery($sql);
	
	if (dbNumRows($result) == 0) {
		// put the product in cart table
		$sql = "INSERT INTO tbl_cart (pd_id, ct_qty, ct_session_id, ct_date)
				VALUES ($productId, 1, '$sid', NOW())";
		$result = dbQuery($sql);
	} else {
		// update product quantity in cart table
		$sql = "UPDATE tbl_cart 
		        SET ct_qty = ct_qty + 1
				WHERE ct_session_id = '$sid' AND pd_id = $productId";		
				
		$result = dbQuery($sql);		
	}	
	
	// an extra job for us here is to remove abandoned carts.
	// right now the best option is to call this function here
	deleteAbandonedCart();

    updateCookie();

	
	header('Location: ' . $_SESSION['shop_return_url']);				
}

/*
	Get all item in current session
	from shopping cart table
*/
function getCartContent()
{
	$cartContent = array();

    //print_r($_COOKIE);
    if(!isset($_COOKIE['state'])) {
		return $cartContent;
    }

	$cookieenc = hex2bin($_COOKIE['state']);
	$td = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_ECB, '/usr/lib/mcrypt-modes');
	$ks = mcrypt_enc_get_key_size($td);
	$key = substr(md5('password'),0, $ks);
	$iv = "\0\0\0\0\0\0\0\0"; // ECB doesn't use an IV, but mcrypt requires it
	mcrypt_generic_init($td, $key, $iv);
	$cookieplain = mdecrypt_generic($td, $cookieenc);
	mcrypt_generic_deinit($td);

	//print("<pre>");
    //print_r($cookieplain);
    //print("</pre>");
    $cartContent = myunserialize($cookieplain);

    /* Iterate on the array, when pd_price isset, changed it to int */
    if(!empty($cartContent)) {
	    foreach($cartContent as $cartItem) {
	        if(isset($cartItem["pd_price"])) {
	            //print("<br>Converted string to int<br>");
	            $cartItem["pd_price"] = intval($cartItem["pd_price"]);
			}
		}
	}
    return $cartContent;
}

/*
	Remove an item from the cart
*/
function deleteFromCart($cartId = 0)
{
	if (!$cartId && isset($_GET['cid']) && (int)$_GET['cid'] > 0) {
		$cartId = (int)$_GET['cid'];
	}

	if ($cartId) {	
		$sql  = "DELETE FROM tbl_cart
				 WHERE ct_id = $cartId";

		$result = dbQuery($sql);
	}
	
    updateCookie();
	header('Location: cart.php');	
}

/*
	Update item quantity in shopping cart
*/
function updateCart()
{

    print "Updating cart<br><br><br>";
	$cartId     = $_POST['hidCartId'];
	$productId  = $_POST['hidProductId'];
	$itemQty    = $_POST['txtQty'];
	$numItem    = count($itemQty);
	$numDeleted = 0;
	$notice     = '';
	
	for ($i = 0; $i < $numItem; $i++) {
		$newQty = (int)$itemQty[$i];
		if ($newQty < 1) {
			// remove this item from shopping cart
			deleteFromCart($cartId[$i]);	
			$numDeleted += 1;
		} else {
			// check current stock
			$sql = "SELECT pd_name, pd_qty
			        FROM tbl_product 
					WHERE pd_id = {$productId[$i]}";
			$result = dbQuery($sql);
			$row    = dbFetchAssoc($result);
			
			if ($newQty > $row['pd_qty']) {
				// we only have this much in stock
				$newQty = $row['pd_qty'];

				// if the customer put more than
				// we have in stock, give a notice
				if ($row['pd_qty'] > 0) {
					setError('The quantity you have requested is more than we currently have in stock. The number available is indicated in the &quot;Quantity&quot; box. ');
				} else {
					// the product is no longer in stock
					setError('Sorry, but the product you want (' . $row['pd_name'] . ') is no longer in stock');

					// remove this item from shopping cart
					deleteFromCart($cartId[$i]);	
					$numDeleted += 1;					
				}
			} 
							
			// update product quantity
			$sql = "UPDATE tbl_cart
					SET ct_qty = $newQty
					WHERE ct_id = {$cartId[$i]}";
				
			dbQuery($sql);
		}
	}
    print "Deleted $numDeleted<br><br>";

    updateCookie();
	
	if ($numDeleted == $numItem) {
		// if all item deleted return to the last page that
		// the customer visited before going to shopping cart
		header("Location: $returnUrl" . $_SESSION['shop_return_url']);
	} else {
		header('Location: cart.php');	
	}
	
	exit;
}

function isCartEmpty()
{
	$isEmpty = false;
	
	$sid = session_id();
	$sql = "SELECT ct_id
			FROM tbl_cart ct
			WHERE ct_session_id = '$sid'";
	
	$result = dbQuery($sql);
	
	if (dbNumRows($result) == 0) {
		$isEmpty = true;
	}	
	
	return $isEmpty;
}

/*
	Delete all cart entries older than one day
*/
function deleteAbandonedCart()
{
	$yesterday = date('Y-m-d H:i:s', mktime(0,0,0, date('m'), date('d') - 1, date('Y')));
	$sql = "DELETE FROM tbl_cart
	        WHERE ct_date < '$yesterday'";
	dbQuery($sql);		
}

?>
