<?php
	$currency = "JPY";
	//$currency = $_POST["currency"];
	
	//Initialize cURL.
	$ch = curl_init();

	//Set the URL that you want to GET by using the CURLOPT_URL option.
	curl_setopt($ch, CURLOPT_URL, 'https://tw.rter.info/capi.php');

	//Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	//Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	//Execute the request.
	$data = curl_exec($ch);

	//Close the cURL handle.
	curl_close($ch);
	
	$real_time_rate = json_decode($data,true);
	
	$rate = 0.0;
	$jpy_rate = $real_time_rate["USDJPY"]["Exrate"];
	$twd_rate = $real_time_rate["USDTWD"]["Exrate"];
	$record_time = $real_time_rate["USDTWD"]["UTC"];
	
	switch ($currency) {
	   case "USD":
		 $rate = $twd_rate;
		 break;
	   case "JPY":
		 $rate = $twd_rate/$jpy_rate;
		 break;
	   case "TWD":
		 $rate = 1.0;
		 break;
	   default:
	}
	
	$response = array(
        "rate" => $rate
    );
	
	echo json_encode($response);
	
?>
