<?php
	function get_api(){
		$cn = pg_connect("host=localhost port=8080 dbname=calculate user=postgres password=12345678");
		
		if (!$cn){
		  echo "An error occurred.\n";
		  exit;
		}
		
		$sql = "SELECT * FROM calculate_record;";
		$result = pg_query($cn,$sql);
		$data = pg_fetch_all($result);
		pg_close($cn);
		
		$response = array(
			"result" => "success",
			"message" => "",
			"data" => $data
		);
		
		return $response;
	}
	
	function post_api(){
		$content = trim(file_get_contents("php://input"));
		$decoded = json_decode($content, true);
		$currency = $decoded['currency'];
		$price = $decoded['price'];
		$discount = $decoded['discount'];
		
		$data = [
			'currency' => $currency,
		];
		$post_data = http_build_query($data);
		
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, 'http://localhost/get_real_time_rate');
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		$resp = json_decode($response,true);
		$rate = $resp["rate"];
		$result_price = 0.0;
		if($currency === 'TWD'){
			$result_price = $rate*($price-$discount);
		}
		else{
			$result_price = $rate*$price;
		}
		
		$response = array(
			'currency' => $currency,
			'rate' => $rate,
			'price' => $price,
			'discount' => $discount,
			'result_price' => $result_price
		);
		
		//將資料存入database
		$cn = pg_connect("host=localhost port=8080 dbname=calculate user=postgres password=12345678");
		
		if (!$cn){
		  echo "An error occurred.\n";
		  exit;
		}
		
		$sql = "INSERT INTO public.calculate_record(currency, rate, price, discount, result, record_time)
				VALUES ('".$currency."',".$rate.",".$price.",".$discount.",".$result_price.",current_timestamp);";
		$result = pg_query($cn,$sql);
		
		pg_close($cn);
		
		return $response;

	}
	
	//主程式
	switch($_SERVER['REQUEST_METHOD']){
		case 'GET':
			$response = get_api();
			echo json_encode($response);
			break;
		case 'POST':
			$response = post_api();
			echo json_encode($response);
			break;
		default:
	}
	
?>