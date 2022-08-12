<?php

function replicateData($verb, $table, $data, $updateId, $params = null) {
	if(REPLICATE) {
		$time_start = microtime(true);
		if($params != null) {
			$replicate = $params;
		} else {
			$replicate = array();
		}
		try {
			$headers = array('Accept' => 'application/json');
			$replicate['data'] = json_decode($data);
			$replicate['data']->store_id = STORE_ID;
			$replicate['store_key'] = STORE_SECRET_KEY;
			$replicate['table'] = $table;
			$replicate['update_id'] = $updateId;

			Unirest\Request::verifyPeer(false);
			$body = Unirest\Request\Body::json($replicate);
			
			if($verb == "POST") {
			} elseif($verb == "PUT") {
				$response = Unirest\Request::put(REPLCATION_URL, $headers, $body);
				if(LOG_RESPONSE) {
					logToFile("-------" . $table . " " . $verb . " START -------");
					logToFile(print_r($replicate, true));
					logToFile(print_r($response, true));
					logToFile("Total Execution Time: " . (microtime(true) - $time_start));
					logToFile("-------" . $table . " " . $verb . " END -------");
				}
			} elseif($verb == "DELETE") {
				$response = Unirest\Request::delete(REPLCATION_URL, $headers, $body);
				if(LOG_RESPONSE) {
					logToFile("-------" . $table . " " . $verb . " START -------");
					logToFile(print_r($replicate, true));
					logToFile(print_r($response, true));
					logToFile("Total Execution Time: " . (microtime(true) - $time_start));
					logToFile("-------" . $table . " " . $verb . " END -------");
				}
			}
			if(isset($response->body->error)) {
				throw new Exception("Error received in response: " . $response->body->error);
			}
		} catch (Exception $e) {
			// Fallback save
			echo $e;
		}
	}
}

function logToFile($msg) {
	$filename = "stdout.log";
	$fd = fopen($filename, "a");
	$str = "[" . date("Y/m/d h:i:s", time()) . "] " . $msg;
	fwrite($fd, $str . "\n");
	fclose($fd);
}
