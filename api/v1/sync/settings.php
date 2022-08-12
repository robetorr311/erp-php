<?php
require_once dirname(dirname(__FILE__)) . '/rb.php';
require_once dirname(dirname(__FILE__)) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/Unirest/Exception.php';
require_once dirname(dirname(__FILE__)) . '/Unirest/Method.php';
require_once dirname(dirname(__FILE__)) . '/Unirest/Response.php';
require_once dirname(dirname(__FILE__)) . '/Unirest/Request.php';
require_once dirname(dirname(__FILE__)) . '/Unirest/Request/Body.php';
require_once dirname(__FILE__) . '/common_functions.php';

if(TWO_WAY_SYNC) {
	$cached_version = true;
	$tables = ['taxrate', 'employee', 'paymentmethod', 'user'];
	$i = 1;
	foreach ($tables as $table) {
		try {
			Unirest\Request::verifyPeer(false);
			$response = Unirest\Request::get(SYNCING_URL .'?table='. $table .'&store_id='. STORE_ID .'&type=sync_new_data');
			$response = json_decode($response->raw_body, true);

			foreach ($response as $res) {
				$cached_version = false;
				echo $i++ .": Processing Table: (". $res['table'] . ") with Central ID: (". $res['id'] .")\n";

				$bean = R::dispense($res['table']);
				foreach ($res['data'] as $key => $value) {
					$bean->$key = $value;
				}

				// check if row already exist on local
				$row_exist = R::findOne( $res['table'], "central_id = ?", array($res['id']));
				if ($row_exist != null) {
					$bean->id = $row_exist->id;
				} else {
					$bean->central_id = $res['id'];
					if ($res['table'] == 'employee') {
						unset($bean->store_id);
					}

					$id = R::store($bean);
					$bean->id = $id;
				}

				// handling teammembers
				if ($res['table'] == 'employee' && $row_exist == null) {
					$tm = R::dispense('teammember');
					$tm->store_id = STORE_ID;
					$tm->employee_id = $bean->id;
					$tm->role_id = $res['role_id'];

					R::store($tm);
					replicateData("PUT", "teammember", $tm->__toString(), $bean->central_id);
				}

				// handling paymentmethod default option
				if ($res['table'] == 'paymentmethod' && $row_exist == null) {
					if ($bean->default == 1) {
						$paymentmethod = R::findOne('paymentmethod', ' store_id = ?  and paymentmethod.default=1', array(STORE_ID));
						if ($paymentmethod) {
							$paymentmethod = json_decode($paymentmethod->__toString());

							if ($paymentmethod->id != $bean->id) {
								$payment = R::load('paymentmethod', $paymentmethod->id);
								$payment->default = 0;
								replicateData("PUT", "paymentmethod", $payment->__toString(), $paymentmethod->id);
								R::store($payment);
							}
						}
					}
				}

				$params = ['twoWaySync' => true];
				replicateData("PUT", $res['table'], $bean->__toString(), $res['id'], $params);
			}

		} catch (Exception $e) {
			// Fallback save
			if(LOG_RESPONSE) {
				logToFile($e);
			}
		}
	}

	// Syncying updates from central server
	$updt_tables = ['store', 'taxrate', 'employee', 'teammember', 'paymentmethod', 'user'];
	if (isset($argv[1]) && $argv[1] == 'sync_updates') {
		foreach ($updt_tables as $table) {
			try {
				Unirest\Request::verifyPeer(false);
				$response = Unirest\Request::get(SYNCING_URL .'?table='. $table .'&store_id='. STORE_ID .'&type=sync_updates');
				$response = json_decode($response->raw_body, true);

				foreach ($response as $res) {
					$cached_version = false;
					echo $i++ .": Updating Table: (". $res['table'] . ") with ID: (". $res['id'] .")\n";

					if ($table == 'teammember') {
						$bean = R::findOne($table, " employee_id = ?", array($res['id']));
					} else {
						$bean = R::load($res['table'], $res['id']);
					}
					foreach ($res['data'] as $key => $value) {
						$bean->$key = $value;
					}

					if ($res['table'] == 'employee') {
						unset($bean->store_id);
					}

					R::store($bean);
					$bean->is_updated = false;

					replicateData("PUT", $res['table'], $bean->__toString(), $res['id']);
				}

			} catch (Exception $e) {
				// Fallback save
				if(LOG_RESPONSE) {
					logToFile($e);
				}
			}
		}
	}

	if (!$cached_version) {
		$store = R::load('store', STORE_ID);
		$store->cached_version = time();
		R::store($store);
		replicateData("PUT", 'store', $store->__toString(), STORE_ID);
	}
}
