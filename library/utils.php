<?php

function myserialize($data) {
	$cook = "";
    /* Iterate on the array of data, converting contents to a string */
    for($i=0; $i < sizeof($data); ++$i) {
		/* Process elements into a string */
		$cook = $cook . $data[$i]["ct_id"] . ",";
		$cook = $cook . $data[$i]["pd_id"] . ",";
		$cook = $cook . $data[$i]["ct_qty"] . ",";
		$cook = $cook . $data[$i]["pd_name"] . ",";
		$cook = $cook . $data[$i]["pd_price"] . ",";
		$cook = $cook . $data[$i]["pd_thumbnail"] . ",";
		$cook = $cook . $data[$i]["cat_id"] . ",";
		$cook = $cook . $data[$i]["ts"] . ",";
		$cook = $cook . $data[$i]["rand"];
        /* Append a next-record indicator "|" if there are more entries */
        if ($i+1<sizeof($data)) {
			$cook = $cook . "|";
		} 
	}
	//print("myserialize:<br><br>".$cook."<br><br>\n");
	return $cook;
}

function myunserialize($data) {
    $cook = array();
	$i=0;

	/* Take string and split it into fields by element */
    $items = explode("|",$data,10);
    
    /* Create the new array to return */
    foreach($items as $item) {
		$entries = explode(",", $item, 9);
		$cook[$i]["ct_id"] = $entries[0];
		$cook[$i]["pd_id"] = $entries[1];
		$cook[$i]["ct_qty"] = $entries[2];
		$cook[$i]["pd_name"] = $entries[3];
		$cook[$i]["pd_price"] = $entries[4];
		$cook[$i]["pd_thumbnail"] = $entries[5];
		$cook[$i]["cat_id"] = $entries[6];
		$cook[$i]["ts"] = $entries[7];
		$cook[$i]["rand"] = $entries[8];
        $i++;
	}

	//print("<br><br>myunserialize:</br><br><pre>");
    //print_r($cook);
    //print("</pre>\n");
    return($cook);
}

function hex2bin($hexstr) {
	return pack("H*", $hexstr);
}

function random($len) {
    if (@is_readable('/dev/urandom')) {
        $f=fopen('/dev/urandom', 'r');
        $urandom=fread($f, $len);
        fclose($f);
    }

    $return='';
    for ($i=0;$i<$len;++$i) {
        if (!isset($urandom)) {
            if ($i%2==0) mt_srand(time()%2147 * 1000000 + (double)microtime() * 1000000);
            $rand=48+mt_rand()%64;
        } else $rand=48+ord($urandom[$i])%64;

        if ($rand>57)
            $rand+=7;
        if ($rand>90)
            $rand+=6;

        if ($rand==123) $rand=45;
        if ($rand==124) $rand=46;
        $return.=chr($rand);
    }
    return $return;
} 

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
?>
