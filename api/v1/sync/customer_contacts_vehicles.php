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
	$tables = ['customer', 'contact', 'vehicle'];
	$i = 1;
	foreach ($tables as $table) {
		try {
			Unirest\Request::verifyPeer(false);
			$response = Unirest\Request::get(SYNCING_URL .'?table='. $table .'&store_id='. STORE_ID .'&type=sync_new_data');
			$response = json_decode($response->raw_body, true);

			foreach ($response as $res) {
				echo $i++ .": Processing Table: (". $res['table'] . ") with Central ID: (". $res['id'] .")\n";

				// Check if foreign keys for table exists on local
				if ($res['table'] == 'contact' || $res['table'] == 'vehicle') {
					$customer = R::findOne('customer', ' id = ? or central_id = ?', array($res['data']['customer_id'], $res['data']['customer_id']));
					if (!$customer) {
						echo "Unable to process: (". $res['table'] . ") with Central ID: (". $res['id'] ."), Customer with ID: (". $res['data']['customer_id'] .") does not exists on local\n";
						continue;
					}

					// Setting foreignKeys to local ids
					$res['data']['customer_id'] = $customer->id;
				}

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
					unset($bean->store_id);
					unset($bean->cached_version);
					
					$id = R::store($bean);
					$bean->id = $id;
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
	$updt_tables = ['customer', 'contact', 'vehicle'];
	if (isset($argv[1]) && $argv[1] == 'sync_updates') {
		foreach ($updt_tables as $table) {
			try {
				Unirest\Request::verifyPeer(false);
				$response = Unirest\Request::get(SYNCING_URL .'?table='. $table .'&store_id='. STORE_ID .'&type=sync_updates');
				$response = json_decode($response->raw_body, true);

				foreach ($response as $res) {
					echo $i++ .": Updating Table: (". $res['table'] . ") with ID: (". $res['id'] .")\n";

					$bean = R::load($res['table'], $res['id']);
					foreach ($res['data'] as $key => $value) {
						$bean->$key = $value;
					}

					unset($bean->store_id);
					unset($bean->cached_version);
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
}
