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
	// Remove deleted records first
	try {
		Unirest\Request::verifyPeer(false);
		$response = Unirest\Request::get(SYNCING_URL .'?table=deleted_data&store_id='. STORE_ID .'&type=deleted_data');
		$response = json_decode($response->raw_body, true);

		$i = 1;
		foreach ($response as $res) {
			echo $i++ .": Deleting record from: (". $res['tbl'] . ") with ". $res['delete_column'] .": (". $res['delete_value'] .")\n";

			if ($res['tbl'] == 'template') {
				$templateItems  = R::find('templateitem', ' template_id = ?', array($res['delete_value']));
				R::trashAll($templateItems);
			}
			$beans = R::find($res['tbl'], $res['delete_column'].' = ? ', array($res['delete_value']));
			R::trashAll($beans);

			replicateData("DELETE", "deleted_data", "{}", $res['id']);
		}

	} catch (Exception $e) {
		// Fallback save
		if(LOG_RESPONSE) {
			logToFile($e);
		}
	}

	$tables = ['order', 'orderitem', 'template', 'templateitem', 'orderpayinfo'];
	foreach ($tables as $table) {
		try {
			Unirest\Request::verifyPeer(false);
			$response = Unirest\Request::get(SYNCING_URL .'?table='. $table .'&store_id='. STORE_ID .'&type=sync_new_data');
			$response = json_decode($response->raw_body, true);
			foreach ($response as $res) {
				echo $i++ .": Processing Table: (". $res['table'] . ") with Central ID: (". $res['id'] .")\n";

				// Check if foreign keys for table exists on local
				if (($res['table'] == 'orderitem' || $res['table'] == 'orderpayinfo') && !$res['order_extrnid']) {
					$order = R::findOne('order', 'central_id = ?', array($res['data']['order_id']));
					if (!$order) {
						echo "Unable to process: (". $res['table'] . ") with Central ID: (". $res['id'] ."), Order with ID: (". $res['data']['order_id'] .") does not exists on local\n";
						continue;
					}

					// Setting foreignKeys to local ids
					$res['data']['order_id'] = $order->id;
				}

				// for orderitem vendor_id
				if ($res['table'] == 'orderitem' && (isset($res['vendor_extrnid']) &&  $res['vendor_extrnid'] == null)) {
					$vendor = R::findOne('vendor', 'central_id = ?', array($res['data']['vendor_id']));
					if (!$vendor) {
						echo "Unable to process: (". $res['table'] . ") with Central ID: (". $res['id'] ."), Vendor with ID: (". $res['data']['vendor_id'] .") does not exists on local\n";
						continue;
					}

					// Setting foreignKeys to local ids
					$res['data']['vendor_id'] = $vendor->id;
				}

				// for order contact_id
				if ($res['table'] == 'order' && !$res['contact_extrnid']) {
					$contact = R::findOne('contact', 'central_id = ?', array($res['data']['contact_id']));
					if (!$contact) {
						echo "Unable to process: (". $res['table'] . ") with Central ID: (". $res['id'] ."), Contact with ID: (". $res['data']['contact_id'] .") does not exists on local\n";
						continue;
					}

					// Setting foreignKeys to local ids
					$res['data']['contact_id'] = $contact->id;
				}

				// for order vehicle_id
				if ($res['table'] == 'order' && (isset($res['vehicle_extrnid']) &&  $res['vehicle_extrnid'] == null)) {
					$vehicle = R::findOne('vehicle', 'central_id = ?', array($res['data']['vehicle_id']));
					if (!$vehicle) {
						echo "Unable to process: (". $res['table'] . ") with Central ID: (". $res['id'] ."), Vehicle with ID: (". $res['data']['vehicle_id'] .") does not exists on local\n";
						continue;
					}

					// Setting foreignKeys to local ids
					$res['data']['vehicle_id'] = $vehicle->id;
				}

				// for templateitem template id
				if ($res['table'] == 'templateitem') {
					$template = R::findOne('template', 'central_id = ?', array($res['data']['template_id']));
					if (!$template) {
						echo "Unable to process: (". $res['table'] . ") with Central ID: (". $res['id'] ."), Template with ID: (". $res['data']['template_id'] .") does not exists on local\n";
						continue;
					}

					// Setting foreignKeys to local ids
					$res['data']['template_id'] = $template->id;
				}
				
				$bean = R::dispense($res['table']);
				foreach ($res['data'] as $key => $value) {
					$bean->$key = $value;
				}

				// for order and order teammembers
				if (($res['table'] == 'order') && isset($res['data']['teammember_id']) && !empty($res['data']['teammember_id'])) {
					foreach($res['data']['teammember_id'] as $tmid) {
						$teammember = R::load("teammember", $tmid);
						$bean->sharedTeammemberList[] = $teammember;
						unset($bean->teammember_id);
					}
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

				if ($res['table'] == 'order') { unset($bean->sharedTeammember); }
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
	$updt_tables = ['order', 'orderpayinfo'];
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

					// for order and order teammembers
					if (($res['table'] == 'order') && isset($res['data']['teammember_id']) && !empty($res['data']['teammember_id'])) {
						foreach($res['data']['teammember_id'] as $tmid) {
							$teammember = R::load("teammember", $tmid);
							$bean->sharedTeammemberList[] = $teammember;
							unset($bean->teammember_id);
						}
					}

					unset($bean->store_id);
					unset($bean->cached_version);
					R::store($bean);
					$bean->is_updated = false;
					if ($res['table'] == 'order') { unset($bean->sharedTeammember); }
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
