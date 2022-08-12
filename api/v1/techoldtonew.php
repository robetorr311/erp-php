<?php
require_once 'API.class.php';
require_once 'rb.php';
require_once 'config.php';
require_once dirname(__FILE__) . '/Unirest/Exception.php';
require_once dirname(__FILE__) . '/Unirest/Method.php';
require_once dirname(__FILE__) . '/Unirest/Response.php';
require_once dirname(__FILE__) . '/Unirest/Request.php';
require_once dirname(__FILE__) . '/Unirest/Request/Body.php';
require_once 'JWT.php';
require_once 'ExpiredException.php';
require_once 'BeforeValidException.php';
require_once 'SignatureInvalidException.php';
use Firebase\JWT\JWT;

define("SECRET_KEY", "Ck8kIlsw]Vi<m,");
define("ALGO", "HS512");
define("DOMAIN", "flux");
define("SESSION_TIME_SECONDS",3600);

date_default_timezone_set('America/New_York');


$orders  = R::find( 'order', 'teammember_id is not null');
foreach($orders as $o) {
	$orderId = $o->id;
	$teammemberId = $o->teammember_id;
	$o->teammember_id=null;

	$teammember = R::load("teammember", $teammemberId);
	$o->sharedTeammemberList[] = $teammember;
	$id = R::store($o);
	replicateData("PUT", "order", $o->__toString(), $o->id);
			
	$otms = R::getALl("select * from order_teammember where order_id=?", array($o->id));
	foreach($otms as $otm) {
		replicateData("POST", "order_teammember", json_encode($otm), 0);
	}
}







function replicateData($verb, $table, $data, $updateId, $params = null) {
	if(true) {
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
			$replicate['table'] = $table;
			$replicate['update_id'] = $updateId;

			Unirest\Request::verifyPeer(false);
			$body = Unirest\Request\Body::json($replicate);

			if($verb == "POST") {
				$response = Unirest\Request::post(REPLCATION_URL, $headers, $body);
			} elseif($verb == "PUT") {
				$response = Unirest\Request::put(REPLCATION_URL, $headers, $body);
			}
			if(isset($response->body->error)) {
				throw new Exception("Error received in response: " . $response->body->error);
			}
		} catch (Exception $e) {
			$s = R::dispense('sync');
			$s->verb = $verb;
			$s->table = $table;
			$s->keyid = $replicate['data']->id;
			$s->failed = date('Y-m-d G:i:s');
			R::store($s);
		}
	}
}

