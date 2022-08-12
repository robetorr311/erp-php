<?php
require_once 'rb.php';
require_once 'config.php';
require_once dirname(__FILE__) . '/Unirest/Exception.php';
require_once dirname(__FILE__) . '/Unirest/Method.php';
require_once dirname(__FILE__) . '/Unirest/Response.php';
require_once dirname(__FILE__) . '/Unirest/Request.php';
require_once dirname(__FILE__) . '/Unirest/Request/Body.php';

if(REPLICATE) {
	$syncs = R::findAll('sync');
	
	foreach($syncs as $sync) {
		$f = R::load($sync->table, $sync->keyid);
		try {
			$headers = array('Accept' => 'application/json');
			$replicate = array();
			$replicate['data'] = json_decode($f->__toString());
			$replicate['data']->store_id = STORE_ID;
			$replicate['store_key'] = STORE_SECRET_KEY;
			$replicate['table'] = $sync->table;
			$replicate['key'] = $sync->keyid;
			Unirest\Request::verifyPeer(false);
			$body = Unirest\Request\Body::json($replicate);
			$response = Unirest\Request::post(REREPLCATION_URL, $headers, $body);
			if(LOG_RESPONSE) {
				logToFile(print_r($response,true));
			}
			if(gettype($response->body) == "integer" || gettype($response->body) == "boolean") {
			    if($sync->table == "order") {
			        R::exec("insert into sync select null, 'POST','orderitem',null,id, id, now() from orderitem where order_id=" . $sync->keyid);
			        R::exec("insert into sync select null, 'POST','orderpayinfo',null,id, id, now() from orderpayinfo where order_id=" . $sync->keyid);
			        R::exec("insert into sync select null, 'POST','order_teammember',null,id, id, now() from order_teammember where order_id=" . $sync->keyid);
			    }
				R::trash($sync);
			}
		} catch (Exception $e) {
			// Fallback save
			if(LOG_RESPONSE) {
				logToFile($e);
			}
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
