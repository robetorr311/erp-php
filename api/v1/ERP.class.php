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

date_default_timezone_set('UTC');

class ERP extends API
{
    protected $user;
    protected $db;
    protected $token;
    protected $refreshedToken;

    public function __construct($request, $origin) {
        parent::__construct($request);

        $headers = apache_request_headers();
        if(array_key_exists("Authorization", $headers)) {
        	try {
        		$t = substr($headers['Authorization'],7);
        		$token = JWT::decode($t, SECRET_KEY, array(ALGO));
        		$this->user = $token->data;
        		$usersession = R::findOne('usersession', " tokenid = ? and ipaddress = ? ", array($token->jti,$_SERVER['REMOTE_ADDR']));
        		if($usersession != null) {
        			$usersession->lastused = date('Y-m-d G:i:s');
        			R::store($usersession);
        		}
        		        		
        		$this->token = $token->jti;
        	} catch(\Firebase\JWT\ExpiredException $e) {
        		$tks = explode('.', substr($headers['Authorization'],7));
        		$payload = JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
        
        		$tknCount = R::getCell('SELECT count(*) FROM `usersession` where tokenid=? and ipaddress=? and lastused >= DATE_SUB(NOW(), INTERVAL 2 HOUR) limit 1', array($payload->jti,$_SERVER['REMOTE_ADDR']));
        
        		if($tknCount < 1) {
        			$usersession = R::findOne('usersession', " tokenid = ? and ipaddress = ? ", array($payload->jti,$_SERVER['REMOTE_ADDR']));
        			if($usersession != null) {
        				R::trash($usersession);
        			}
        			$this->user = null;
        		} else {
        			$usersession = R::findOne('usersession', " tokenid = ? and ipaddress = ? ", array($payload->jti,$_SERVER['REMOTE_ADDR']));
        			if($usersession != null) {
        				$usersession->lastused = date('Y-m-d G:i:s');
        				R::store($usersession);
        			}
        			$payload->exp = time() + SESSION_TIME_SECONDS;
        			$this->refreshedToken = JWT::encode($payload, SECRET_KEY, ALGO);
        			$this->user = $payload->data;
        		}
        		$this->token = $payload->jti;
        	} catch(Exception $e) {
        		$this->user = null;
        	}
        }
    }

	protected function vehicle() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'POST') {
			$vehicle = R::dispense('vehicle');
			$body = @file_get_contents('php://input');
			$body = json_decode($body);
				
			$vehicle->year = $body->year;
			$vehicle->make = $body->make;
			$vehicle->model = $body->model;
			
			if(isset($body->trim)) {
				$vehicle->trim = $body->trim;
			}
			
			if(isset($body->mileage)) {
				$vehicle->mileage = $body->mileage;
			}
			
			if(isset($body->vin)) {
				$vehicle->vin = $body->vin;
			}
			
			if(isset($body->license)) {
				$vehicle->license = $body->license;
			}
			
			if(isset($body->active)) {
				$vehicle->active = $body->active;
			}
			
			if(isset($body->fleetnum)) {
				$vehicle->fleetnum = trim($body->fleetnum);
			}
			
			if (isset($body->contact_id)) {
				$vehicle->customer_id = R::getCell('select customer_id from contact where id=? limit 1',array($body->contact_id));
			} else {
				$vehicle->customer_id = $body->customer_id;
			}	

			$id = R::store($vehicle);
			$this->replicateData("POST", "vehicle", $vehicle->__toString(), 0);
			$this->setCustomerCache($vehicle->customer_id, time());
			
			return array("id"=>$id);
		} elseif($this->method == 'GET'){
			if($this->verb == "byContact") {
				$beans = R::findMulti( 'vehicle', "
						SELECT customer.*, contact.*, vehicle.* FROM customer
						INNER JOIN contact on contact.customer_id = customer.id
						INNER JOIN vehicle on vehicle.customer_id = customer.id
						WHERE vehicle.active=1 AND contact.id = ?
						", array($this->args[0]) );
				return $this->handleArray($beans['vehicle']);
			} elseif($this->verb == "search") {
				$findBy = " active = 1";
				$argArray = array();
				if(isset($_GET['vin'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					$findBy .= " vin = ? ";
					$argArray[] = $_GET['vin'];
				}
				if(isset($_GET['license'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					$findBy .= " license = ? ";
					$argArray[] = $_GET['license'];
				}
				if(isset($_GET['fleetnum'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					$findBy .= " fleetnum = ? ";
					$argArray[] = $_GET['fleetnum'];
				}
				
				$vehicle = null;
				if($findBy != "") {
					$vehicle = R::find('vehicle', $findBy , $argArray );
				}

				return $this->handleArray($vehicle);
			} else {
				$vehicle  = R::findOne('customer', ' id = ? ', array($this->args[0]) );
				return json_decode($vehicle->__toString());
			}
		} elseif($this->method == 'PUT'){
			$body = json_decode($this->file);
			if(!isset($body->id)) {
				return "Missing parameter: id";
			}
			
			$vehicle  = R::load('vehicle', $body->id);
				
			$vehicle->year = $body->year;
			$vehicle->make = $body->make;
			$vehicle->model = $body->model;
			 	
			if(isset($body->trim)) {
				$vehicle->trim = $body->trim;
			} else {
				$vehicle->trim = null;
			}
			 	
			if(isset($body->mileage)) {
				$vehicle->mileage = $body->mileage;
			} else {
				$vehicle->mileage = null;
			}
			 	
			if(isset($body->vin)) {
				$vehicle->vin = $body->vin;
			} else {
				$vehicle->vin = null;
			}
			 	
			if(isset($body->license)) {
				$vehicle->license = $body->license;
			} else {
				$vehicle->license = null;
			}
			 	
			if(isset($body->active)) {
			 	$vehicle->active = $body->active;
			}
			 	
			if(isset($body->fleetnum)) {
    			$vehicle->fleetnum = trim($body->fleetnum);
			} else {
				$vehicle->fleetnum = null;
			}
			 
			if(isset($body->customer_id)) {
				$vehicle->customer_id = $body->customer_id;
			}
			
			$id = R::store($vehicle);
			$this->replicateData("PUT", "vehicle", $vehicle->__toString(), $body->id);
			$this->setCustomerCache($vehicle->customer_id, time());
			
			return array("id"=>$id);
		} elseif($this->method == 'DELETE'){
				$vehicle  = R::findOne('vehicle', ' endpoint = ? ', array( $this->verb ) );
				R::trash($vehicle);
				return array("success" => true);
		}
	
	}
	
	protected function order() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'POST') {
		    $body = @file_get_contents('php://input');
		    $body = json_decode($body);
		    
		    if($this->verb == "validate") {
		        $orderId = R::getCell("select id from `order` o where type in ('I','W') and id=? limit 1", array($body->orderNumber));
		        if($orderId != null && $orderId != "") {
		            return array("id"=>$orderId);
		        }
		        
		        $orderId = R::getCell("select id from `order` o where type in ('I','W') and central_id=? limit 1", array($body->orderNumber));
		        if($orderId != null && $orderId != "") {
		            return array("id"=>$orderId);
		        }
		        
		        return array("valid"=>false);
		    } else {
    			$order = R::dispense('order');
    			if(LOG_RESPONSE) {
    				$this->logToFile("************* Order POST *************");
    				$this->logToFile(print_r($body,true));
    			}
    			
    			$order->created = date('Y-m-d G:i:s');
    			$order->updated = date('Y-m-d G:i:s');
    			$order->type = $body->tickettype;
    			$order->status = $body->ticketstatus;
    			$order->promisedtime = $body->promisedtime;
    
    			if(isset($body->startdate) && $body->startdate != null) {
    				$order->startdate = $body->startdate;
    			} else {
    				$order->startdate = null;
    			}
    			
    			if(isset($body->starttime) && $body->starttime != null) {
    				$order->starttime = $body->starttime;
    			} else {
    				$order->starttime = null;
    			}
    			
    			if(isset($body->duration) && $body->duration != null) {
    				$order->duration = $body->duration;
    			} else {
    				$order->duration = null;
    			}
    			
    			if(isset($body->customernotes)) {
    				$order->customernotes = $body->customernotes;
    			}
    			
    			if(isset($body->mileage)) {
    				$order->mileage = $body->mileage;
    			}
    			
    			if(isset($body->ordertotal)) {
    				$order->ordertotal = $body->ordertotal;
    			}
    				
    			if(isset($body->ordertax)) {
    				$order->ordertax = $body->ordertax;
    			}
    				
    			if(isset($body->ordermargin)) {
    				$order->ordermargin = $body->ordermargin;
    			}
    			
    			$order->contact_id = $body->contact_id;
    			if(isset($body->vehicle_id)) {
    				$order->vehicle_id = $body->vehicle_id;
    			}
    			
    			$order->optcounter = 1;
    			if(isset($body->teammember_id) && !empty($body->teammember_id)) {
    				foreach($body->teammember_id as $tmid) {
    					$teammember = R::load("teammember", $tmid);
    					$order->sharedTeammemberList[] = $teammember;
    				}
    			}

				if (isset($body->payments) && count($body->payments) > 0) {
					$order->type = "I";
				}

    			$id = R::store($order);
    			$this->replicateData("POST", "order", $order->__toString(), 0);
    			
    			$otms = R::getALl("select * from order_teammember where order_id=?", array($id));
    			foreach($otms as $otm) {
    				$this->replicateData("POST", "order_teammember", json_encode($otm), 0);
    			}
    			
    			foreach($body->items as $i) {
    				$item = R::dispense('orderitem');
    				$item->order_id = $id;
    				$item->itemtype_id = $i->type;
    				$item->partnumber = $i->partnumber;
    				$item->description = $i->description;
    				$item->quantity = $i->quantity;
    				$item->retail = $i->retail;
    				$item->cost = $i->cost;
    				$item->taxcat = $i->taxcat;

    				if(isset($i->dotnumber) && $i->dotnumber != "") {
    					$item->dotnumber = $i->dotnumber;
    				}

    				if(isset($i->manufacturer) && $i->manufacturer != "") {
						$item->manufacturer = $i->manufacturer;
					}

    				if(isset($i->teammember_id) && $i->teammember_id != "") {
    					$item->teammember_id = $i->teammember_id;
    				}

    				if(isset($i->vendor_id) && $i->vendor_id != "") {
    					$item->vendor_id = $i->vendor_id;
    				}
    				
    				if(isset($i->invoicenumber) && $i->invoicenumber != "") {
    					$item->invoicenumber = $i->invoicenumber;
    				}
    				
    				if(isset($i->tax) && $i->tax != "") {
    					$item->tax = $i->tax;
    				}
    				
    				R::store($item);
    				$this->replicateData("POST", "orderitem", $item->__toString(), 0);
    				
    				if($i->type == 3 || $i->type == 4) {
    					// Reserve inventory for new items
    					$inv  = R::findOne( 'inventory', "partnumber = ?", array($i->partnumber));
    					if($inv != null) {
    						$inv->reserved += $i->quantity;
    						R::store($inv);
    						$this->replicateData("PUT", "inventory", $inv->__toString(), $inv->id);
    					}
    				}
    			}

				if (isset($body->payments)) {
					foreach($body->payments as $i) {
						$paymentMethod = R::load('paymentmethod', $i->method);
						$payment = R::dispense('orderpayinfo');
						$payment->order_id = $id;
						$payment->paymentmethod_id = $i->method;
						$payment->amount = $i->amount;
						if($paymentMethod->open == 0) {
							$payment->paydate = date('Y-m-d G:i:s');
							$payment->closedmethod = $i->method;
						}
						if(isset($i->checknumber)) {
							$payment->checknumber = $i->checknumber;
						}
						R::store($payment);
						$this->replicateData("POST", "orderpayinfo", $payment->__toString(), 0);
					}
				}

				if (isset($body->appointment_id) && $body->appointment_id != null) {
					$appointment = R::load('appointment', $body->appointment_id);
					$appointment->order_id = $id;
					R::store($appointment);
					$this->replicateData("PUT", "appointment", $appointment->__toString(), $body->appointment_id);
				}

    			return array("id"=>$id);
		    }
		} elseif($this->method == 'GET'){
			if($this->verb == "open") {
				/*
				 * Eastern ........... America/New_York
					Central ........... America/Chicago
					Mountain .......... America/Denver
					Mountain no DST ... America/Phoenix
					Pacific ........... America/Los_Angeles
					Alaska ............ America/Anchorage
					Hawaii ............ America/Adak
					Hawaii no DST ..... Pacific/Honolulu
				 */
				$orders = R::getAll("select o.type, TIME_FORMAT(o.promisedtime, '%h:%i %p') as promisedFormatted, group_concat(concat(oi.partnumber, ',', oi.description, ',', oi.quantity)) as itemDetails,o.customernotes, o.promisedtime, o.startdate, o.id, cu.businessname, co.firstname, co.lastname, co.phone1, v.year, v.make, v.model, v.trim, v.license, ir.id as inspectionreport_id, o.status, GROUP_CONCAT(DISTINCT e.name SEPARATOR ', ') as name from `order` o left join contact co on (o.contact_id = co.id) left join customer cu on (co.customer_id = cu.id) left join vehicle v on (o.vehicle_id=v.id) left join inspectionreport ir on (o.id=ir.order_id) left join order_teammember ot on (o.id=ot.order_id) left join teammember tm on (ot.teammember_id = tm.id) left join employee e on (tm.employee_id = e.id) left join orderitem oi on (o.id = oi.order_id)  where o.type in ('W','E') group by o.id order by o.status desc, o.promisedtime asc");
				$openOrders = array("orders" => array(), "estimates" => array());
				date_default_timezone_set('America/New_York');
				foreach($orders as $o) {
					if($o["promisedtime"] != null ) {
						$o["minstopromised"] = round((strtotime($o["promisedtime"]) - time())/60,0);
					}
					if($o["type"] == "W") {
						$openOrders["orders"][] = $o;
					} else {
						$openOrders["estimates"][] = $o;
					}
				}

				$openOrders['requestedAppointments'] = [];
				$requestedAppointments = R::getAll('select * from appointment where order_id is null');
				if ($requestedAppointments) {
					$openOrders['requestedAppointments'] = $requestedAppointments;
				}
				
				return $openOrders;
			} elseif ($this->verb == "byVehicle") {
				$orders = R::getALl("select o.*, DATE_FORMAT(o.updated, '%m/%d/%Y') as orderdate, e.name as technician from `order` o left join order_teammember ot on (o.id=ot.order_id) left join teammember tm on ot.teammember_id=tm.id left join employee e on tm.employee_id = e.id where type in ('I','W') and o.vehicle_id = ? order by type desc, updated desc", array($this->args[0]));
				return $orders;
			} elseif ($this->verb == "noVehicle") {
				$orders = R::getALl("select o.*, DATE_FORMAT(o.updated, '%m/%d/%Y') as orderdate, e.name as technician from `order` o left join order_teammember ot on (o.id=ot.order_id) left join teammember tm on ot.teammember_id=tm.id left join employee e on tm.employee_id = e.id where type in ('I','W') and o.vehicle_id is null and o.contact_id in (select id from contact where customer_id=(select customer_id from contact where id=?)) order by type desc, updated desc", array($this->args[0]));
				return $orders;
			} elseif ($this->verb == "revision") {
				$rev = R::getCell('select optcounter from `order` where id=? limit 1', array($this->args[0]));
				return array("rev" => (int)$rev);
			} elseif ($this->verb == "appointments") {
				$orders = R::getAll("select o.type, DATE_FORMAT(o.startdate, '%m/%d/%Y') as start_date, o.starttime, o.id, o.customernotes, (CASE o.status WHEN '00' THEN 'blue' WHEN '50' THEN 'red' ELSE 'green' END) as color, o.duration, c.firstname, c.lastname, c.phone1, c.phone2, c.phone3 from `order` o left join contact c on (o.contact_id = c.id) where o.type in ('W','I') and o.starttime != '' and o.startdate != ''");
				date_default_timezone_set('America/New_York');
				$openWorkOrders = array();
				foreach($orders as $i => $o) {
					$datetime = new DateTime($o['start_date']. ' ' .$o['starttime']);
					$start = $datetime->format(DateTime::ATOM);
					$datetime->modify("+{$o['duration']} minutes");
					$end = $datetime->format(DateTime::ATOM);
					$openWorkOrders[$i]['title'] = '#'.$o['id'] . ": " . $o["firstname"] . " " . $o["lastname"] . " - " . $o["customernotes"];
					$openWorkOrders[$i]['poptitle'] = '#'.$o['id'];
					$phones = "";
					if($o["phone1"] != null) {
						$phones .= "Phone 1: " . $o["phone1"] . "<br />";
					}
					if($o["phone2"] != null) {
						$phones .= "Phone 2: " . $o["phone2"] . "<br />";
					}
					if($o["phone3"] != null) {
						$phones .= "Phone 3: " . $o["phone3"] . "<br />";
					}
					$openWorkOrders[$i]['description'] = "<strong>" . $o["firstname"] . " " . $o["lastname"] . "</strong><br />" . $phones . "<br />" . $o["customernotes"];
					$openWorkOrders[$i]['start'] = $start;
					$openWorkOrders[$i]['end'] = $end;
					$openWorkOrders[$i]['url'] = 'workorderedit.php?fromAppointment=1&orderId='.$o['id'];
					$openWorkOrders[$i]['color'] = $o['color'];
				}
				return $openWorkOrders;
			} else {
				$order  = R::find('order', ' id = ? ', array($this->args[0]) );
				$store  = R::findOne('store', ' id = ? ', array(STORE_ID));
				$retArray = array();
				foreach($order as $o) {
					$obj = json_decode($o->__toString());
					$updated_date = new DateTime($obj->updated);
					// timezone conversion	
					$current_date = new DateTime($obj->updated);
					$timezone = (isset($store->timezone))? $store->timezone : 'America/New_York';
					$current_date->setTimezone(new DateTimeZone($timezone));	
					$obj->updated = $current_date->format('m/d/Y H:i:s');

					$obj->customer = json_decode($o->contact->customer->__toString());
					$obj->customer->contact = json_decode($o->contact->__toString());
					if($o->vehicle) {
						$obj->vehicle = json_decode($o->vehicle->__toString());
					}
					$itemList = array();
					foreach($o->ownOrderitemList as $i) {
						$itemList[] = json_decode($i->__toString());
					}
					$obj->items = $itemList;
					
					$teammemberList = array();
					$tmember = "";
					foreach($o->sharedTeammemberList as $t) {
						$teammemberList[] = json_decode($t->__toString());
						if($tmember != "") {
							$tmember .= ", ";
						}
						$tmember .= $t->employee->name;
					}
					$obj->teammembers = $teammemberList;
					$obj->teammember = $tmember;
					
					$paymentList = array();
					$paymethod = "";
					foreach($o->ownOrderpayinfoList as $p) {
						$paymentList[] = json_decode($p->__toString());
						if($paymethod != "") {
							$paymethod .= ", ";
						}
						$paymethod .= $p->paymentmethod->name;
						if(isset($p->checknumber)) {
							$paymethod .= " #" . $p->checknumber;
						}
					}
					if(count($paymentList) > 0) {
						$obj->payments = $paymentList;
						$obj->paymethod = $paymethod;
					}

					$obj->show_reference_no = $store->show_reference_no;
					if ($store->show_reference_no) {
						$variables = array(
						    '{id}' => $obj->id,
						    '{date}' => $updated_date->format('Y-m-d'),
						    '{margin}' => (isset($obj->ordermargin))? $obj->ordermargin: '0.00',
						    '{tax}' => $obj->ordertax,
						    '{total}' => $obj->ordertotal,
						);

						$obj->reference_number = strtr($store->reference_number, $variables);
					}

					$retArray[] = $obj;
				}
				
				return $retArray;
			}
		} elseif($this->method == 'PUT'){
			if($this->verb == "orderDelete") {
				$order_id = $this->args[0];
				// Remove reserved inventory from deleted order
				$rows = R::getAll("select * from inventory where partnumber in (select partnumber from orderitem where itemtype_id in (3,4) and order_id=?)",  array($order_id));
				$inventoryList = R::convertToBeans( 'inventory', $rows );
				$rows = R::getAll("select * from orderitem where itemtype_id in (3,4) and order_id=?",  array($order_id));
				$orderItems = R::convertToBeans( 'inventory', $rows );
				foreach($inventoryList as $inv) {
					foreach($orderItems as $oi) {
						if($inv->partnumber == $oi->partnumber) {
							$inv->reserved -= $oi->quantity;
							R::store($inv);
							$this->replicateData("PUT", "inventory", $inv->__toString(), $inv->id);
							// delete order item
							$order_item  = R::findOne('orderitem', ' id = ? ', array( $oi->id ) );
							R::trash($order_item);
						}
					}
				}
			
				// set type of order to 'D'
				$order = R::load('order', $order_id);
				$order->type = 'D';
				$this->replicateData("PUT", "order", $order->__toString(), $order_id);
				R::store($order);
			
				return array('success' => true);
			} elseif($this->verb == "payment") {
			    if($this->user->isAdmin != 1) {
			        return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
			    }
			    $order  = R::findOne('order', ' id = ? ', array($this->args[0]) );
			    if($order->type != "I") { array("error" => "Order not invoiced"); }
			    $body = json_decode($this->file);
			    
			    $payments  = R::find('orderpayinfo', ' order_id = ?', array($this->args[0]));
			    R::trashAll($payments);
			    
			    if(isset($body->payments)) {
		            foreach($body->payments as $i) {
		                $paymentMethod = R::load('paymentmethod', $i->method);
		                $payment = R::dispense('orderpayinfo');
		                $payment->order_id = $this->args[0];
		                $payment->paymentmethod_id = $i->method;
		                $payment->amount = $i->amount;
		                if($paymentMethod->open == 0) {
		                    $payment->paydate = date('Y-m-d G:i:s');
		                    $payment->closedmethod = $i->method;
		                }
		                if(isset($i->checknumber)) {
		                    $payment->checknumber = $i->checknumber;
		                }
		                R::store($payment);
		                $this->replicateData("POST", "orderpayinfo", $payment->__toString(), 0);
		            }
		        }
			    return array("id"=>$this->args[0]);
			} else {
				$order  = R::findOne('order', ' id = ? ', array($this->args[0]) );
				if($order->type == "I") { return "Order already invoiced"; }
				$body = json_decode($this->file);

				if(LOG_RESPONSE) {
					$this->logToFile("************* Order PUT *************");
					$this->logToFile(print_r($body,true));
				}

				$order->updated = date('Y-m-d G:i:s');
				if(isset($body->tickettype)) {
					$order->type = $body->tickettype;
				}
				if(isset($body->ticketstatus)) {
					$order->status = $body->ticketstatus;
				}
				if(isset($body->promisedtime)) {
					$order->promisedtime = $body->promisedtime;
				}
				
				if(isset($body->startdate) && $body->startdate != null) {
					$order->startdate = $body->startdate;
				} else {
					$order->startdate = null;
				}
					
				if(isset($body->starttime) && $body->starttime != null) {
					$order->starttime = $body->starttime;
				} else {
					$order->starttime = null;
				}
				
				if(isset($body->duration) && $body->duration != null) {
					$order->duration = $body->duration;
				} else {
					$order->duration = null;
				}
			
				if(isset($body->customernotes)) {
					$order->customernotes = $body->customernotes;
				}
			
				if(isset($body->mileage)) {
					$order->mileage = $body->mileage;
				}
			
				if(isset($body->ordertotal)) {
					$order->ordertotal = $body->ordertotal;
				}
			
				if(isset($body->ordertax)) {
					$order->ordertax = $body->ordertax;
				}
			
				if(isset($body->ordermargin)) {
					$order->ordermargin = $body->ordermargin;
				}
			
				$order->contact_id = $body->contact_id;
			
				if(isset($body->vehicle_id) && $body->vehicle_id > 0) {
					$order->vehicle_id = $body->vehicle_id;
				}
			
				$reduceInventory = false;
				if(isset($body->payments) && count($body->payments) > 0) {
					$order->type = "I";
					if(isset($order->vehicle_id) && isset($order->mileage)) {
						$vehicle = $order->vehicle;
						$vehicle->mileage = $order->mileage;
						R::store($vehicle);
						$this->replicateData("PUT", "vehicle", $vehicle->__toString(), $order->vehicle_id);
					}
					$reduceInventory = true;
				}
			
				// Remove reserved inventory from previous items
				$rows = R::getAll("select * from inventory where partnumber in (select partnumber from orderitem where itemtype_id in (3,4) and order_id=?)",  array($this->args[0]));
				$inventoryList = R::convertToBeans( 'inventory', $rows );
				$rows = R::getAll("select * from orderitem where itemtype_id in (3,4) and order_id=?",  array($this->args[0]));
				$partList = R::convertToBeans( 'inventory', $rows );
				foreach($inventoryList as $inv) {
					foreach($partList as $part) {
						if($inv->partnumber == $part->partnumber) {
							$inv->reserved -= $part->quantity;
							R::store($inv);
							$this->replicateData("PUT", "inventory", $inv->__toString(), $inv->id);
						}
					}
				}
			
				$order->xownOrderitemList = array();
				$order->sharedTeammemberList = array();
				$order->optcounter = $order->optcounter + 1;
				
				if(isset($body->teammember_id) && !empty($body->teammember_id)) {
					foreach($body->teammember_id as $tmid) {
						$teammember = R::load("teammember", $tmid);
						$order->sharedTeammemberList[] = $teammember;
					}
				}
				$id = R::store($order);
				$this->replicateData("PUT", "order", $order->__toString(), $order->id);
				
				$otms = R::getALl("select * from order_teammember where order_id=?", array($id));
				foreach($otms as $otm) {
					$this->replicateData("POST", "order_teammember", json_encode($otm), 0);
				}
			
				foreach($body->items as $i) {
					$item = R::dispense('orderitem');
					$item->order_id = $id;
					$item->itemtype_id = $i->type;
					$item->partnumber = $i->partnumber;
					$item->description = $i->description;
					$item->quantity = $i->quantity;
					$item->retail = $i->retail;
					$item->cost = $i->cost;
					$item->taxcat = $i->taxcat;

					if(isset($i->dotnumber) && $i->dotnumber != "") {
						$item->dotnumber = $i->dotnumber;
					}

    				if(isset($i->teammember_id) && $i->teammember_id != "") {
    					$item->teammember_id = $i->teammember_id;
    				} 				

					if(isset($i->vendor_id) && $i->vendor_id != "") {
						$item->vendor_id = $i->vendor_id;
					}
				
					if(isset($i->invoicenumber) && $i->invoicenumber != "") {
						$item->invoicenumber = $i->invoicenumber;
					}

					if(isset($i->manufacturer) && $i->manufacturer != "") {
						$item->manufacturer = $i->manufacturer;
					}
				
					if(isset($i->tax) && $i->tax != "") {
						$item->tax = $i->tax;
					}
					R::store($item);
					$this->replicateData("POST", "orderitem", $item->__toString(), $item->id);
					
					if($i->type == 3 || $i->type == 4) {
						// Reserve inventory for new items
						$inv  = R::findOne( 'inventory', "partnumber = ?", array($i->partnumber));
						if($inv != null) {
							if(!$reduceInventory) {
								$inv->reserved += $i->quantity;
								R::store($inv);
								$this->replicateData("PUT", "inventory", $inv->__toString(), $inv->id);
							} else {
								$inv->quantity -= $i->quantity;
								R::store($inv);
								$this->replicateData("PUT", "inventory", $inv->__toString(), $inv->id);
							}
						}
					} elseif (($i->type == 1 || $i->type == 2) && $reduceInventory) {
						$invoice = R::findOne( 'invoice', "number = ? and vendor_id = ?", array($item->invoicenumber, $item->vendor_id));
						if($invoice == null) {
							$invoice = R::dispense('invoice');
							$invoice->number = $item->invoicenumber;
							$invoice->vendor_id = $item->vendor_id;
							$invoice->created = date('Y-m-d G:i:s');
							$invoice->paid = 0;
							R::store($invoice);
							$this->replicateData("POST", "invoice", $invoice->__toString(), 0);
						}
					
						$item = R::dispense('invoiceitem');
						$item->invoice_id = $invoice->id;
						$item->partnumber = $i->partnumber;
						$item->quantity = $i->quantity;
						$item->cost = $i->cost;
						R::store($item);
						$this->replicateData("POST", "invoiceitem", $item->__toString(), 0);
					}
				}
			
				if(!isset($body->items) || count($body->items) < 1) {
					$this->replicateData("POST", "orderitem", "{}", 0);
				}
				if(isset($body->payments)) {
					$orderPays = R::count('orderpayinfo', ' order_id = ? ', array($id));
					if($orderPays < 1) {
						foreach($body->payments as $i) {
							$paymentMethod = R::load('paymentmethod', $i->method);
							$payment = R::dispense('orderpayinfo');
							$payment->order_id = $id;
							$payment->paymentmethod_id = $i->method;
							$payment->amount = $i->amount;
							if($paymentMethod->open == 0) {
								$payment->paydate = date('Y-m-d G:i:s');
								$payment->closedmethod = $i->method;
							}
							if(isset($i->checknumber)) {
								$payment->checknumber = $i->checknumber;
							}
							R::store($payment);
							$this->replicateData("POST", "orderpayinfo", $payment->__toString(), 0);
						}
					}
				}
				return array("id"=>$id);
			}
		} elseif($this->method == 'DELETE'){
				$order  = R::findOne('order', ' endpoint = ? ', array( $this->verb ) );
				R::trash($order);
				return array("success" => true);
		}
	
	}

	protected function appointment() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'PUT') {
			if($this->verb == 'decline') {
				$appointment = R::load('appointment', $this->args[0]);
				$appointment->order_id = -1;
				R::store($appointment);
				$this->replicateData("PUT", "appointment", $appointment->__toString(), $this->args[0]);
				return array('success' => true);
			}
		}
	}
	
	protected function customer() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'POST') {
			$customer = R::dispense('customer');
			$body = @file_get_contents('php://input');
			$body = json_decode($body);
			
			$customer->usertype = $body->usertype;
			$customer->taxexempt = $body->taxexempt;
			
			if(isset($body->taxexemptnum)) {
				$customer->taxexemptnum = $body->taxexemptnum;
			}
			
			if(isset($body->businessname)) {
				$customer->businessname = $body->businessname;
			}
			
			if(isset($body->addressline1)) {
				$customer->addressline1 = $body->addressline1;
			}
				
			if(isset($body->addressline2)) {
				$customer->addressline2 = $body->addressline2;
			}
				
			if(isset($body->addressline3)) {
				$customer->addressline3 = $body->addressline3;
			}
			
			if(isset($body->city)) {
				$customer->city = $body->city;
			}
			
			if(isset($body->state)) {
				$customer->state = $body->state;
			}
			
			if(isset($body->zip)) {
				$customer->zip = $body->zip;
			}
			
			if(isset($body->internal)) {
				$customer->internal = $body->internal;
			} else {
				$customer->internal = 0;
			}

	
			$contact = R::dispense('contact');
			$contact->firstname = $body->contact->firstname;
			$contact->lastname = $body->contact->lastname;
			$contact->phone1type = $body->contact->phone1type;
			$extraCharacters = array("(",")","-"," ");
			$contact->phone1 = str_replace($extraCharacters, '', $body->contact->phone1);
			
			if(isset($body->contact->phone2)) {
				$contact->phone2type = $body->contact->phone2type;
				$contact->phone2 = str_replace($extraCharacters, '', $body->contact->phone2);
			}
			
			if(isset($body->contact->phone3)) {
				$contact->phone3type = $body->contact->phone3type;
				$contact->phone3 = str_replace($extraCharacters, '', $body->contact->phone3);
			}
			
			if(isset($body->contact->email)) {
				$contact->email = $body->contact->email;
			}
			
			if(isset($body->contact->isPrimary)) {
				$contact->isprimary = "true";
			}else {
				$contact->isprimary = "false";
			}
			if(isset($body->contact->isDeclined)) {
				$contact->isdeclined = "true";
			}else {
				$contact->isdeclined = "false";
			}			
			$customer->ownContactList[] = $contact;
			
			$id = R::store($customer);
			$this->replicateData("POST", "customer", $customer->__toString(), 0);
			$this->replicateData("POST", "contact", $contact->__toString(), 0);
			
			return array("id"=>$id,"contact_id"=>$contact->id);
		} elseif($this->method == 'GET'){
			if($this->verb == "byVehicle") {
				$beans = R::findMulti( 'customer,contact', "
						SELECT customer.*, contact.*, vehicle.* FROM customer
						INNER JOIN contact on contact.customer_id = customer.id
						INNER JOIN vehicle on vehicle.customer_id = customer.id
						WHERE vehicle.active=1 AND vehicle.id = ?
						", array($this->args[0]) );
				
				$retArray = array();
				foreach($beans['contact'] as $a) {
					$obj = json_decode($a->customer->__toString());
					$obj->contact = json_decode($a->__toString());
					$retArray[] = $obj;
				}
				return $retArray;
			} elseif($this->verb == "search") {
				$findBy = "";
				$argArray = array();
				if(isset($_GET['firstName'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					if(strpos($_GET['firstName'], '*') !== false) {
						$findBy .= " contact.firstname like ? ";
						$argArray[] = str_replace("*", "%", $_GET['firstName']);
					} else {
						$findBy .= " contact.firstname = ? ";
						$argArray[] = $_GET['firstName'];
					}
				}
				if(isset($_GET['lastName'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					if(strpos($_GET['lastName'], '*') !== false) {
						$findBy .= " contact.lastname like ? ";
						$argArray[] = str_replace("*", "%", $_GET['lastName']);
					} else {
						$findBy .= " contact.lastname = ? ";
						$argArray[] = $_GET['lastName'];
					}
				}
				if(isset($_GET['phone'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					$findBy .= " (contact.phone1 = ? || contact.phone2 = ? || contact.phone3 = ?) ";
					$argArray[] = $_GET['phone'];
					$argArray[] = $_GET['phone'];
					$argArray[] = $_GET['phone'];
				}
				if(isset($_GET['businessname'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					$findBy .= " customer.businessname like ? ";
					$argArray[] = "%" . $_GET['businessname'] . "%";
				}
				
				$customer = null;
				if($findBy != "") {				
					$beans = R::findMulti( 'contact', "
						SELECT customer.*, contact.* FROM customer
        				INNER JOIN contact on contact.customer_id = customer.id
        				WHERE $findBy
    					", $argArray );
				}

				$retArray = array();
				foreach($beans['contact'] as $a) {
					$obj = json_decode($a->customer->__toString());
					$obj->contact = json_decode($a->__toString());
					$retArray[] = $obj;
				}
				return $retArray;
			} elseif($this->verb == "all") {
				$page = 1;
				$limit = 25;
				if(count($this->args) > 0) {
					$page = $this->args[0];
				}
				
				$findBy = " ";
				$argArray = array();
				if(isset($_GET['firstName'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					if(strpos($_GET['firstName'], '*') !== false) {
						$findBy .= " co.firstname like ? ";
						$argArray[] = str_replace("*", "%", $_GET['firstName']);
					} else {
						$findBy .= " co.firstname = ? ";
					$argArray[] = $_GET['firstName'];
					}
				}
				if(isset($_GET['lastName'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					if(strpos($_GET['lastName'], '*') !== false) {
						$findBy .= " co.lastname like ? ";
						$argArray[] = str_replace("*", "%", $_GET['lastName']);
					} else {
						$findBy .= " co.lastname = ? ";
						$argArray[] = $_GET['lastName'];
					}
				}
				if(isset($_GET['phone'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					$findBy .= " (co.phone1 = ? || co.phone2 = ? || co.phone3 = ?) ";
					$argArray[] = $_GET['phone'];
					$argArray[] = $_GET['phone'];
					$argArray[] = $_GET['phone'];
				}
				if(isset($_GET['businessname'])) {
					if($findBy != "") {
						$findBy .= " AND";
					}
					$findBy .= " cu.businessname like ? ";
					$argArray[] = "%" . $_GET['businessname'] . "%";
				}
				
				$retArray = array();
				$retArray['total'] = R::getCell("select count(co.lastname) from customer cu left join contact co on (cu.id = co.customer_id) where co.isprimary='true'" . $findBy . " order by COALESCE(cu.businessname,co.lastname) asc", $argArray); 
				$argArray[] = (($page-1)*$limit);
				$argArray[] = $limit;
				$retArray['rows'] = R::getAll("select cu.id, co.firstname, co.lastname, co.phone1, cu.businessname, cu.addressline1, cu.city, cu.state, cu.zip from customer cu left join contact co on (cu.id = co.customer_id) where co.isprimary='true'" . $findBy . " order by COALESCE(cu.businessname,co.lastname) asc LIMIT ?,?",$argArray);
				$retArray['page'] = $page;
				$retArray['totalPages'] = ceil($retArray['total']/$limit);
				return $retArray;
			} elseif($this->verb == "detail") {
				$retArray = array();
				if(isset($_GET['invoiceId'])) {
					$order = R::load('order',$_GET['invoiceId']);
					$customer = $order->contact->customer;
					$this->args[0] = $customer->id;
				} else {
					$customer = R::findOne('customer', ' id = ? ', array($this->args[0]) );
				}
				$retArray['customer'] = json_decode($customer->__toString());
				$vehicles = R::getALl("select * from vehicle where customer_id=? order by year desc, make asc, model", array($this->args[0]));
				$contacts = R::getAll("select * from contact where customer_id=? order by lastname asc", array($this->args[0]));
				$orders = R::getALl("select o.*, DATE_FORMAT(o.updated, '%m/%d/%Y') as orderdate, e.name as technician from `order` o left join contact c on (o.contact_id=c.id) left join order_teammember ot on o.id=ot.order_id left join teammember tm on ot.teammember_id=tm.id left join employee e on tm.employee_id = e.id where type in ('I','W') and c.customer_id = ? order by type desc, updated desc", array($this->args[0]));
				$retArray['vehicles'] = $vehicles;
				$retArray['contacts'] = $contacts;
				$retArray['orders'] = array();
				foreach($orders as $o) {
					if(!array_key_exists($o['vehicle_id'],$retArray['orders'])) {
						$retArray['orders'][$o['vehicle_id']] = array();
					}
					$retArray['orders'][$o['vehicle_id']][] = $o;
				}
				return $retArray;
			} else {
				if(count($this->args) > 0) {
					$customer = R::findOne('customer', ' id = ? ', array($this->args[0]) );
					return json_decode($customer->__toString());
				} else {
					$customer = R::findAll('customer');
					$retArray = array();
					foreach($customer as $c) {
						$retArray[] = json_decode($c->__toString());
					}
					return $retArray;
				}
			}
			
		} elseif($this->method == 'PUT'){
			$body = json_decode($this->file);
			if(!isset($body->customer_id)) {
				return "Missing parameter: customer_id";
			}
			
			$customer = R::load('customer',$body->customer_id);
			
			$customer->usertype = $body->usertype;
			$customer->taxexempt = $body->taxexempt;
				
			if(isset($body->taxexemptnum)) {
				$customer->taxexemptnum = $body->taxexemptnum;
			}
				
			if(isset($body->businessname)) {
				$customer->businessname = $body->businessname;
			} elseif($customer->usertype = 'P') {
				$customer->businessname = null;
			}
				
			if(isset($body->addressline1)) {
				$customer->addressline1 = $body->addressline1;
			}
			
			if(isset($body->addressline2)) {
				$customer->addressline2 = $body->addressline2;
			}
			
			if(isset($body->addressline3)) {
				$customer->addressline3 = $body->addressline3;
			}
				
			if(isset($body->city)) {
				$customer->city = $body->city;
			}
				
			if(isset($body->state)) {
				$customer->state = $body->state;
			}
				
			if(isset($body->zip)) {
				$customer->zip = $body->zip;
			}
			
			if(isset($body->internal)) {
				$customer->internal = $body->internal;
			} else {
				$customer->internal = 0;
			}

			if(isset($body->contact_id)) {
				$contact = R::load('contact',$body->contact_id);
					
				if($contact->customer_id != $body->customer_id) {
					return "Invalid parameter: contact_id";
				}
				$contact->firstname = $body->contact->firstname;
				$contact->lastname = $body->contact->lastname;
				$contact->phone1type = $body->contact->phone1type;
				$contact->phone1 = $body->contact->phone1;
					
				if(isset($body->contact->phone2)) {
					$contact->phone2type = $body->contact->phone2type;
					$contact->phone2 = $body->contact->phone2;
				}
					
				if(isset($body->contact->phone3)) {
					$contact->phone3type = $body->contact->phone3type;
					$contact->phone3 = $body->contact->phone3;
				}
					
				if(isset($body->contact->email)) {
					$contact->email = $body->contact->email;
				}
					
				if(isset($body->contact->isPrimary)) {
					// setting only this contact isprimary to true
					$this->unsetPrimaryContacts($body->customer_id, $body->contact_id);
					$contact->isprimary = "true";
				}else {
					$contact->isprimary = "false";
				}
				if(isset($body->contact->isDeclined)) {
					// setting only this contact isprimary to true
					$this->unsetDeclinedEmailContacts($body->customer_id, $body->contact_id);
					$contact->isdeclined = "true";
				}else {
					$contact->isdeclined = "false";
				}					
				$customer->ownContactList[] = $contact;
				$this->replicateData("PUT", "contact", $contact->__toString(), $body->contact_id);
			}
				
			$id = R::store($customer);
			$customer->cached_version = time();
			$this->replicateData("PUT", "customer", $customer->__toString(), $body->customer_id);

			return array("id"=>$id);
		} elseif($this->method == 'DELETE'){
				$customer  = R::findOne('customer', ' endpoint = ? ', array( $this->verb ) );
				R::trash($customer);
				return array("success" => true);
		}
	
	}
	
	protected function contact() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'POST') {
			$body = @file_get_contents('php://input');
			$body = json_decode($body);
			$contact = R::dispense('contact');
			$contact->firstname = $body->firstname;
			$contact->lastname = $body->lastname;
			$contact->phone1type = $body->phone1type;
			$contact->phone1 = str_replace(' ', '', $body->phone1);
			
			if(isset($body->phone2)) {
				$contact->phone2type = $body->phone2type;
				$contact->phone2 = str_replace(' ', '', $body->phone2);
			}
			
			if(isset($body->phone3)) {
				$contact->phone3type = $body->phone3type;
				$contact->phone3 = str_replace(' ', '', $body->phone3);
			}
			
			if(isset($body->email)) {
				$contact->email = $body->email;
			}
			
			if(isset($body->isPrimary)) {
				// setting only this contact isprimary to true
				$this->unsetPrimaryContacts($body->customer_id);
				$contact->isprimary = "true";
			} else {
				$contact->isprimary = "false";
			}
			if(isset($body->isDeclined)) {
				// setting only this contact isprimary to true
				$this->unsetDeclinedEmailContacts($body->customer_id);
				$contact->isdeclined = "true";
			} else {
				$contact->isdeclined = "false";
			}			
			$contact->customer_id = $body->customer_id;
			
			$id = R::store($contact);
			$this->replicateData("POST", "contact", $contact->__toString(), 0);
			$this->setCustomerCache($contact->customer_id, time());
			
			return array("id"=>$id);
		} elseif($this->method == 'GET'){
		} elseif($this->method == 'PUT'){
			$body = json_decode($this->file);
			if(!isset($body->contact_id)) {
				return "Missing parameter: contact_id";
			}

			$contact = R::load('contact',$body->contact_id);
			
			if($contact->customer_id != $body->customer_id) {
				return "Invalid parameter: contact_id";
			}

			$contact->firstname = $body->firstname;
			$contact->lastname = $body->lastname;
			$contact->phone1type = $body->phone1type;
			$contact->phone1 = str_replace(' ', '', $body->phone1);
					
			if(isset($body->phone2)) {
				$contact->phone2type = $body->phone2type;
				$contact->phone2 = str_replace(' ', '', $body->phone2);
			} else {
				$contact->phone2type = null;
				$contact->phone2 = null;
			}
					
			if(isset($body->phone3)) {
				$contact->phone3type = $body->phone3type;
				$contact->phone3 = str_replace(' ', '', $body->phone3);
			} else {
				$contact->phone3type = null;
				$contact->phone3 = null;
			}
					
			if(isset($body->email)) {
				$contact->email = $body->email;
			} else {
				$contact->email = null;
			}
					
			if(isset($body->isPrimary)) {
				// setting only this contact isprimary to true
				$this->unsetPrimaryContacts($body->customer_id, $body->contact_id);
				$contact->isprimary = "true";
			} else {
				$contact->isprimary = "false";
			}
			if(isset($body->isDeclined)) {
				// setting only this contact isprimary to true
				$this->unsetDeclinedEmailContacts($body->customer_id, $body->contact_id);
				$contact->isdeclined = "true";
			} else {
				$contact->isdeclined = "false";
			}
			$contact->customer_id = $body->customer_id;

			$id = R::store($contact);
			$this->replicateData("PUT", "contact", $contact->__toString(), $body->contact_id);
			$this->setCustomerCache($contact->customer_id, time());

			return array("id"=>$id);
		} elseif($this->method == 'DELETE'){
		}
	}
	
	protected function store() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'POST') {
			$body = @file_get_contents('php://input');
			$body = json_decode($body);
			
			if($this->user->isAdmin != 1) {
				return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
			}

			if ($this->verb == "taxrate") {
				$taxrate = R::dispense('taxrate');
				$taxrate->store_id = STORE_ID;
				$taxrate->name = $body->name;
				$taxrate->category = $body->category;
				$taxrate->rate = $body->rate;
				$taxrate->exemption = $body->exemption;
				$taxrate->active = true;
				
				$id = R::store($taxrate);
				$this->setStoreCache(time());
				$this->replicateData("POST", "taxrate", $taxrate->__toString(), 0);
				return array("id"=>$id);
			} elseif ($this->verb == "teammember") {
				$e = R::dispense('employee');
				$e->name = $body->name;
				$e->active = true;
				
				$id = R::store($e);
				$this->replicateData("POST", "employee", $e->__toString(), 0);

				$tm = R::dispense('teammember');
				$tm->store_id = STORE_ID;
				$tm->employee_id = $id;
				$tm->role_id = $body->role;

				R::store($tm);
				$this->setStoreCache(time());
				$this->replicateData("POST", "teammember", $tm->__toString(), 0);
				return array("id"=>$id);
			} elseif ($this->verb == "paymentmethod") {
				if ($body->default == 1) {
					$paymentmethod = R::findOne('paymentmethod', ' store_id = ?  and paymentmethod.default=1', array(STORE_ID));
					if ($paymentmethod) {
						$paymentmethod = json_decode($paymentmethod->__toString());
						$payment = R::load('paymentmethod', $paymentmethod->id);
						$payment->default = 0;
						
						$this->replicateData("PUT", "paymentmethod", $payment->__toString(), $paymentmethod->id);
						R::store($payment);
					}
				}
				
				$pm = R::dispense('paymentmethod');
				$pm->store_id = STORE_ID;
				$pm->name = $body->name;
				$pm->paymenttype_id = $body->paymenttype;
				$pm->open = $body->open;
				$pm->default = $body->default;
				$pm->active = true;
				
				$id = R::store($pm);
				$this->setStoreCache(time());
				$this->replicateData("POST", "paymentmethod", $pm->__toString(), 0);

				return array("id"=>$id);
			} elseif ($this->verb == "users") {
				$useraccount = R::dispense("user");
				$useraccount->email = $body->email;
				$useraccount->password = password_hash($body->password, PASSWORD_BCRYPT);
				$useraccount->isadmin = $body->ifadmin;
				
				$id = R::store($useraccount);
				$this->replicateData("POST", "user", $useraccount->__toString(), 0);

				return array("id"=>$id);
			}
		} elseif($this->method == 'GET'){
			if($this->verb == "details") {
				$store  = R::findOne('store', ' id = ? ', array(STORE_ID));
				$taxrates = R::find('taxrate', ' store_id = ? and active=true', array(STORE_ID));
				$itemtypes  = R::findAll('itemtype');
				$roles  = R::findAll('role');
				$employees = R::getAll('SELECT teammember.id, teammember.employee_id, teammember.role_id, employee.name, (SELECT role.name from role where role.id = teammember.role_id) as role_name from teammember left join employee on teammember.employee_id = employee.id where employee.active=1 and teammember.store_id=?', array(STORE_ID));
				$vendors = R::find('vendor', ' store_id = ?  and active=1', array(STORE_ID));
				$users = R::find('user', 'active=1');
				return array("store" => json_decode($store->__toString()),"rates" => $this->handleArray($taxrates),
						"itemtypes" => $this->handleArray($itemtypes), "team" => $employees, "vendors" => $this->handleArray($vendors),
						"roles" => $this->handleArray($roles), "user" => $this->handleArray($users)
				);
			} elseif($this->verb == "paymentmethods") {
				$paymenttypes  = R::findAll('paymenttype');
				$paymentmethods = R::getAll('SELECT paymentmethod.*, (SELECT paymenttype.name from paymenttype where paymenttype.id = paymentmethod.paymenttype_id) as paymenttype_name from paymentmethod where paymentmethod.active=1 and paymentmethod.store_id=?', array(STORE_ID));
				return array("paymentmethods" => $paymentmethods, "paymenttypes" => $this->handleArray($paymenttypes));
			} elseif($this->verb == "checkcache") {
				$cached_version  = R::getCell('select cached_version from store where id = ?', array(STORE_ID));
				return $cached_version;
			} else {
				$itemtype  = R::findOne('customer', ' id = ? ', array($this->args[0]) );
				return json_decode($itemtype->__toString());
			}
		} elseif($this->method == 'PUT'){
			$body = json_decode($this->file);
			
			if($this->user->isAdmin != 1) {
				return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
			}

			if($this->verb == "details") {
				$s = R::load('store', STORE_ID);
				$s->identifier = $body->identifier;
				$s->name = $body->name;
				$s->address1 = $body->address1;
				
				if(isset($body->address2)) {
					$s->address2 = $body->address2;
				} else {
					$s->address2 = null;
				}
				
				$s->city = $body->city;
				$s->state = $body->state;
				$s->zip = $body->zip;
				$s->phone = $body->phone;
				
				if(isset($body->fax)) {
					$s->fax = $body->fax;
				} else {
					$s->fax = null;
				}
				
				$s->email = $body->email;
				$s->logo = 'logo.jpg';
				$s->laborrate = $body->laborrate;
				$s->timezone = $body->timezone;
				$s->show_reference_no = $body->show_reference_no;
				if ($body->show_reference_no) {
					$s->reference_number = $body->reference_number;
				} else {
					$s->reference_number = null;
				}

				$s->email_reminder = $body->email_reminder;

				$s->cached_version = time();
	
				$id = R::store($s);
				$this->replicateData("PUT", "store", $s->__toString(), STORE_ID);
			
				return array("id"=>$id);
			} elseif ($this->verb == "taxrate") {
				$tr = R::load('taxrate', $body->taxrate_id);
				$tr->store_id = STORE_ID;
				$tr->name = $body->name;
				$tr->category = $body->category;
				$tr->rate = $body->rate;
				$tr->exemption = $body->exemption;
	
				$this->replicateData("PUT", "taxrate", $tr->__toString(), $body->taxrate_id);
			
				$id = R::store($tr);
				$this->setStoreCache(time());
				return array("id"=>$id);
			} elseif ($this->verb == "taxrateDelete") {
				$taxrate = R::load('taxrate', $this->args[0]);
				$taxrate->active = false;
				R::store($taxrate);
				$this->setStoreCache(time());
				$this->replicateData("PUT", "taxrate", $taxrate->__toString(), $this->args[0]);
				return array('success' => true);
			} elseif ($this->verb == "teammember") {
				$e = R::load('employee', $body->employee_id);
				$e->name = $body->name;
				$id = R::store($e);
				$this->replicateData("PUT", "employee", $e->__toString(), $body->employee_id);
				
				$tm = R::findOne('teammember', " employee_id = ? and store_id = ? ", array($id, STORE_ID));
				$tm->role_id = $body->role;
				R::store($tm);
				$this->setStoreCache(time());
				$this->replicateData("PUT", "teammember", $tm->__toString(), $tm->employee_id);

				return array("id"=>$id);
			} elseif ($this->verb == "teammemberDelete") {
				$e = R::load('employee', $this->args[0]);
				$e->active = false;
				$id = R::store($e);
				$this->setStoreCache(time());
				$this->replicateData("PUT", "employee", $e->__toString(), $this->args[0]);

				return array('success' => true);
			} elseif ($this->verb == "paymentmethod") {
				if ($body->default == 1) {
					$paymentmethod = R::findOne('paymentmethod', ' store_id = ?  and paymentmethod.default=1', array(STORE_ID));
					if ($paymentmethod) {
						$paymentmethod = json_decode($paymentmethod->__toString());

						if ($paymentmethod->id != $body->paymentmethod_id) {
							$payment = R::load('paymentmethod', $paymentmethod->id);
							$payment->default = 0;
							$this->replicateData("PUT", "paymentmethod", $payment->__toString(), $paymentmethod->id);
							R::store($payment);
						}
					}
				}

				$pm = R::load('paymentmethod', $body->paymentmethod_id);
				$pm->store_id = STORE_ID;
				$pm->name = $body->name;
				$pm->paymenttype_id = $body->paymenttype;
				$pm->open = $body->open;
				$pm->default = $body->default;

				$id = R::store($pm);
				$this->setStoreCache(time());
				$this->replicateData("PUT", "paymentmethod", $pm->__toString(), $body->paymentmethod_id);

				return array("id"=>$id);
			} elseif ($this->verb == "paymentmethodDelete") {
				$pm = R::load('paymentmethod', $this->args[0]);
				$pm->active = false;

				$id = R::store($pm);
				$this->setStoreCache(time());
				$this->replicateData("PUT", "paymentmethod", $pm->__toString(), $this->args[0]);
				
				return array('success' => true);
			} elseif ($this->verb == "users") {
				$useraccount = R::load("user", $body->userid);
				$useraccount->email = $body->email;
				$useraccount->isadmin = $body->ifadmin;
				if ($body->password != "" || !empty($body->password)) {
					$useraccount->password = password_hash($body->password, PASSWORD_BCRYPT);
				}
				$id = R::store($useraccount);
				$this->setStoreCache(time());
				$this->replicateData("PUT", "user", $useraccount->__toString(), $body->userid);

				return array("id"=>$id);
			} elseif ($this->verb == "userDelete") {
				$useraccount = R::load("user", $this->args[0]);
				$useraccount->active = 0;
                $id = R::store($useraccount);
				$this->setStoreCache(time());
				$this->replicateData("PUT", "user", $useraccount->__toString(), $this->args[0]);

				return array('success' => true);
			}
		} elseif($this->method == 'DELETE'){
		}
	
	}
	
	protected function vendor() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'POST') {
			$body = @file_get_contents('php://input');
			$body = json_decode($body);
			$v = R::dispense('vendor');
			$v->vendorname = $body->vendorname;
			$v->store_id = STORE_ID;
			$v->firstname = $body->firstname;
			$v->lastname = $body->lastname;
			$v->phone1 = $body->phone1;
			if(isset($body->phone2) && $body->phone2 != "") {
				$v->phone2 = $body->phone2;
			}
			if(isset($body->email) && $body->email != "") {
				$v->email = $body->email;
			}
			$v->address1 = $body->address1;
			if(isset($body->address2) && $body->address2 != "") {
				$v->address2 = $body->address2;
			}
			$v->zip = $body->zip;
			$v->city = $body->city;
			$v->state = $body->state;
			$v->active = $body->active;
			$id = R::store($v);
			$this->replicateData("POST", "vendor", $v->__toString(), 0);
			
			return array("id"=>$id);
		} elseif($this->method == 'GET'){
			if($this->verb == "all") {
				$rows = R::getAll("select * from vendor where store_id=? order by vendorname", array(STORE_ID));
				$vendors = R::convertToBeans( 'vendor', $rows );				
				return $this->handleArray($vendors);
			} elseif($this->verb == "history") {
					$rows = R::getAll('SELECT i.id, i.number, DATE_FORMAT(i.created, "%m/%d/%Y") as date, i.vendor_id, i.paid, sum(ii.quantity*ii.cost) as total FROM invoice i left join invoiceitem ii on i.id = ii.invoice_id where vendor_id=? group by i.id order by i.number desc', array($this->args[0]));
					return $rows;
			} else {
				$vendors  = R::findOne('vendor', ' id = ? ', array($this->args[0]) );
				return json_decode($vendors->__toString());
			}
		} elseif($this->method == 'PUT'){
			$body = json_decode($this->file);
			if(!isset($body->vendor_id)) {
				return "Missing parameter: vendor_id";
			}

			$v = R::load('vendor',$body->vendor_id);
			$v->vendorname = $body->vendorname;
			$v->store_id = STORE_ID;
			$v->firstname = $body->firstname;
			$v->lastname = $body->lastname;
			$v->phone1 = $body->phone1;
			if(isset($body->phone2) && $body->phone2 != "") {
				$v->phone2 = $body->phone2;
			}
			if(isset($body->email) && $body->email != "") {
				$v->email = $body->email;
			}
			$v->address1 = $body->address1;
			if(isset($body->address2) && $body->address2 != "") {
				$v->address2 = $body->address2;
			}
			$v->zip = $body->zip;
			$v->city = $body->city;
			$v->state = $body->state;
			$v->active = $body->active;
			$this->replicateData("PUT", "vendor", $v->__toString(), $body->vendor_id);
			
			$id = R::store($v);
			return array("id"=>$id);
		} elseif($this->method == 'DELETE'){
			$itemtype  = R::findOne('itemtype', ' endpoint = ? ', array( $this->verb ) );
			R::trash($itemtype);
			return array("success" => true);
		}
	}
	
	protected function inventory() {
		if($this->user == null) {
			return Array("data" => array("error" => "User session timed out"), "statuscode" => 401);
		}
		
		if($this->method == 'POST') {
			if($this->verb == "item") {
				if($this->user->isAdmin != 1) {
					return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
				}
				$body = @file_get_contents('php://input');
				$body = json_decode($body);
				$inventory = R::dispense('inventory');
				$inventory->manufacturer = $body->manufacturer;
				$inventory->partnumber = $body->partnumber;
				$inventory->description = $body->description;
				$inventory->cost = $body->cost;
				$inventory->retail = $body->retail;
				$inventory->quantity = $body->quantity;
				$inventory->reserved = $body->reserved;
				
				$id = R::store($inventory);
				$this->replicateData("POST", "inventory", $inventory->__toString(), 0);	
				return array("id"=>$id);
			} elseif ($this->verb == "uploadinventory") {
				if($this->user->isAdmin != 1) {
					return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
				}

				$partNumbersExist = null;
				$rows = R::getAll('SELECT partnumber from inventory');
				$partnumbers = array_column($rows, 'partnumber');

				if (!empty($_FILES['inventory_csv_file']['name'])) {
					if (($handle = fopen($_FILES['inventory_csv_file']['tmp_name'], 'r')) !== FALSE) {
						while (($row = fgetcsv($handle)) !== FALSE) {
							if (in_array($row[1], $partnumbers)) {
								$partNumbersExist .= $row[1] .', ';
							} else {
								$inventory = R::dispense('inventory');
								$inventory->manufacturer = $row[0];
								$inventory->partnumber = $row[1];
								$inventory->description = $row[2];
								$inventory->cost = floatval($row[3]);
								$inventory->retail = floatval($row[4]);
								$inventory->quantity = $row[5];
								$inventory->reserved = $row[6];
								$id = R::store($inventory);
								$this->replicateData("POST", "inventory", $inventory->__toString(), 0);
							}
						}

						fclose($handle);

						if ($partNumbersExist) {
							return 'The following part numbers already exist: '.rtrim($partNumbersExist, ', ') .'. The rest are successfully added.';
						}
						return array('upload' => true);
					}

					return array('error' => 'Unable to read file.');

				}

				return array('error' => 'Unable to upload inventroy.');
			} else {
				$body = @file_get_contents('php://input');
				$body = json_decode($body);
				$invoice = R::dispense('invoice');
				$invoice->number = $body->invoice;
				$invoice->vendor_id = $body->vendor_id;
				$invoice->created = date('Y-m-d G:i:s');
				$invoice->paid = 0;
				
				$id = R::store($invoice);
				$this->replicateData("POST", "invoice", $invoice->__toString(), 0);
				
				foreach($body->items as $k => $i) {
					$item = R::dispense('invoiceitem');
					$item->invoice_id = $id;
					$item->inventory_id = $i->partnumber;
					$item->quantity = $i->quantity;
					$item->cost = $i->cost;
					R::store($item);
					$this->replicateData("POST", "invoiceitem", $item->__toString(), 0);
					
					$iItem = R::load('inventory',$i->partnumber);
					$itemPartNumber = $iItem->partnumber;
					$itemOldCost = $iItem->cost;
					$totalItemCost = ($iItem->cost * $iItem->quantity) + ($item->cost * $item->quantity);
					$totalItemCount = $iItem->quantity + $item->quantity;
					$itemAverage = $totalItemCost / $totalItemCount;
					$iItem->cost = $itemAverage;
					$iItem->quantity = $totalItemCount;
					R::store($iItem);
					$this->replicateData("PUT", "inventory", $iItem->__toString(), $iItem->id);
					
					$rows = R::getAll("select oi.* from orderitem oi left join `order` o on oi.order_id=o.id where o.type='W' and oi.partnumber=? and oi.cost=?",array($itemPartNumber,$itemOldCost));
					$oItems = R::convertToBeans( 'orderitem', $rows );
					foreach($oItems as $oItem) {
						$oItem->cost = $itemAverage;
						R::store($oItem);
						$this->replicateData("PUT", "orderitem", $oItem->__toString(), $oItem->id);
					}
				}
			
				return array("id"=>$id);
			}
		} elseif($this->method == 'GET'){
			if($this->verb == "all") {
				$rows = R::getAll("select * from inventory order by partnumber");
				$inventory = R::convertToBeans( 'inventory', $rows );
				
				if($inventory == null) {return array();}
				
				$retArray = array();
				$columns = array();
				$columns[] = array("data"=>"manufacturer","label"=>"Manufacturer");
				$columns[] = array("data"=>"partnumber","label"=>"Part Number");
				$columns[] = array("data"=>"description","label"=>"Description");
				$columns[] = array("data"=>"cost","defaultContent"=>"","label"=>"Cost");
				$columns[] = array("data"=>"retail","defaultContent"=>"","label"=>"Retail");
				$columns[] = array("data"=>"quantity","defaultContent"=>"","label"=>"On Hand");
				$columns[] = array("data"=>"reserved","defaultContent"=>"","label"=>"Reserved");
				
				$locations = array();
				
				$allLocations = null;
				if(isset($_GET['allLocations']) && $_GET['allLocations'] == "true") {
					try {
						$response = @Unirest\Request::get(INVENTORY_URL, array(), array("store" => STORE_ID));
						if($response->code == 200) {
							$allLocations = json_decode($response->raw_body, true);
						}
					} catch (Exception $e) {
					}
				}
				
				foreach($inventory as $i) {
					if($allLocations != null) {
						$localI = json_decode($i->__toString(),true);
						if(array_key_exists($localI['partnumber'],$allLocations)) {
							foreach($allLocations[$localI['partnumber']] as $ai) {
								if(!array_key_exists($ai['identifier'],$locations)) {
									$locations[$ai['identifier']] = 1;
								}
								$localI[$ai['identifier']] = $ai['quantity'];
							}

							unset($allLocations[$localI['partnumber']]);
						}
						$retArray[] = $localI;
					} else {
						$retArray[] = json_decode($i->__toString());
					}
				}

				// pushing all unmached inventory to $retArray
				if($allLocations != null) {
					foreach($allLocations as $partnumber => $value) {
						foreach ($allLocations[$partnumber] as $ai) {
							if ($key = array_search($partnumber, array_column($retArray, 'partnumber'))) {
								$retArray[$key][$ai['identifier']] = $ai['quantity'];
							} else {
								$row = array(
									"partnumber" => $partnumber,
									"manufacturer" => $ai['manufacturer'],
									"description" => $ai['description'],
									"manufacturer" => $ai['manufacturer'],
									$ai['identifier'] => $ai['quantity'],
									"row_from" => $ai['identifier']
								);
								$retArray[] = $row;
							}
						}
					}
				}
				if(isset($_GET['format']) && $_GET['format'] == "datatable") {
					foreach($locations as $k=>$v) {
						$columns[] = array("data"=>$k, "defaultContent"=>0, "label"=>$k);
					}
					if($this->user->isAdmin == 1) {
						$columns[] = array("label"=>"Edit", "sortable" => false);
					}
					return array("columns"=>$columns, "data" => $retArray);
				}
				return $retArray;
			} elseif($this->verb == "vendorpartnum") {
				$vendors = R::find('vendor', ' store_id = ?  and active=1', array(STORE_ID));
				$partnum = R::getAll('SELECT id, partnumber, description from inventory order by partnumber asc');
				$retArray = array("vendors" => $this->handleArray($vendors), "partnumbers" => $partnum);
				return $retArray;
			} elseif($this->verb == "partlist") {
				$rows = R::getAll("select i.id, i.manufacturer, i.partnumber, i.description, i.cost, i.retail, iv.vendor_id, max(iv.created) as created, (i.quantity-i.reserved) as stock from inventory i left join invoiceitem ii on i.id=ii.inventory_id left join invoice iv on ii.invoice_id=iv.id group by i.id order by partnumber");
				if($rows == null) {return array();}
				if(isset($_GET['format']) && $_GET['format'] == "datatable") {
					return array("data" => $rows);
				}
				return $rows;
			} elseif($this->verb == "filter") {
				$partnums = R::getAll( "SELECT id, partnumber, description from inventory where partnumber like ?", array('%' . $this->args[0] . '%'));
				return $partnums;
			} elseif($this->verb == "history") {
				$returnArray = array();
				$returnArray['detail'] = R::getAll('select inv.number, DATE_FORMAT(inv.created, "%m/%d/%Y") as date, inv.paid, v.vendorname from invoice inv left join vendor v on (inv.vendor_id = v.id) where inv.id=?', array($this->args[0]));
				$returnArray['items'] = R::getAll('SELECT COALESCE(ivt.partnumber,oi.partnumber) as partnumber, COALESCE(ivt.description, oi.description) as description, ii.quantity, ii.cost, (ii.quantity*ii.cost) as total FROM `invoiceitem` ii left join inventory ivt on ii.inventory_id=ivt.id left join invoice i on ii.invoice_id=i.id left join orderitem oi on (i.number=oi.invoicenumber and i.vendor_id=oi.vendor_id) where invoice_id=?', array($this->args[0]));
				return $returnArray;
			} elseif($this->verb == "searchByPartNumber"){
				if(count($this->args) > 0) {
					$partnum = $this->args[0];
				}
				$partNumber = R::findOne('inventory', ' partnumber = ? ', array($partnum));
				return $partNumber;
			} else {
				$inventory  = R::findOne('inventory', ' id = ? ', array($this->args[0]) );
				return json_decode($inventory->__toString());
			}
		} elseif($this->method == 'PUT'){
			$body = json_decode($this->file);
			if(!isset($body->inventory_id)) {
				return "Missing parameter: inventory_id";
			}
			if($this->verb == "item") {
				if($this->user->isAdmin != 1) {
					return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
				}
				$i = R::load('inventory',$body->inventory_id);
				$i->manufacturer = $body->manufacturer;
				$i->partnumber = $body->partnumber;
				$i->description = $body->description;
				$i->cost = $body->cost;
				$i->retail = $body->retail;
				$i->quantity = $body->quantity;
				$i->reserved = $body->reserved;
	
				$this->replicateData("PUT", "inventory", $i->__toString(), $body->inventory_id);
			
				$id = R::store($i);
				return array("id"=>$id);
			} 
		} elseif($this->method == 'DELETE'){
			$itemtype  = R::findOne('itemtype', ' endpoint = ? ', array( $this->verb ) );
			R::trash($itemtype);
			return array("success" => true);
		}
	}
	
	protected function template() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'POST') {
			$template = R::dispense('template');
			$body = @file_get_contents('php://input');
			$body = json_decode($body);

			if(isset($body->templatename) && $body->templatename != "") {
				$template->name = $body->templatename;
			} else {
				return array("data"=>array("error" => "Invalid name"), "statuscode" => 400);
			}
			
			$templateExists = R::findOne('template', ' name = ? ', array($body->templatename));
			if($templateExists != null) {
				return array("error" => "A template exists with that name.");
			}
			
			$id = R::store($template);
			$this->replicateData("POST", "template", $template->__toString(), 0);
			
			foreach($body->items as $i) {
				$item = R::dispense('templateitem');
				$item->template_id = $id;
				$item->itemtype_id = $i->type;
				$item->partnumber = $i->partnumber;
				$item->description = $i->description;
				$item->quantity = $i->quantity;
				$item->retail = $i->retail;
				$item->cost = $i->cost;
				$item->taxcat = $i->taxcat;
				if(isset($i->dotnumber) && $i->dotnumber != "") {
					$item->dotnumber = $i->dotnumber;
				}
				
				if(isset($i->vendor_id) && $i->vendor_id != "") {
					$item->vendor_id = $i->vendor_id;
				}
				
				if(isset($i->invoicenumber) && $i->invoicenumber != "") {
					$item->invoicenumber = $i->invoicenumber;
				}
				
				if(isset($i->tax) && $i->tax != "") {
					$item->tax = $i->tax;
				}
				
				R::store($item);
				$this->replicateData("POST", "templateitem", $item->__toString(), 0);
			}

			return array("id"=>$id);
		} elseif($this->method == 'GET'){
			if($this->verb == "filter") {
				$templates = R::getAll( 'SELECT id, name from template where name like ?', array('%' . $this->args[0] . '%'));
				return $templates;
			} else {
				$template = R::findOne('template', ' id = ? ', array($this->args[0]) );
				$returnArray = json_decode($template->__toString());
				$templateItems = R::find('templateitem', ' template_id = ? ', array($this->args[0]) );
				foreach($templateItems as $ti) {
					$returnArray->items[] = json_decode($ti->__toString());
				}
				return $returnArray;
			}
		} elseif($this->method == 'PUT'){
		} elseif($this->method == 'DELETE'){
			$templateItems  = R::find('templateitem', ' template_id = ?', array($this->args[0]));
			R::trashAll($templateItems);
			$template = R::findOne('template', ' id = ? ', array($this->args[0]) );
			R::trash($template);
			return array("status" => "success");
		}
	}
	
	protected function accountsreceivable() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'PUT'){
			$body = json_decode($this->file);
			if($this->verb == "paid") {
				foreach($body as $o) {
					$ordpayinfo = R::findOne('orderpayinfo', ' id = ? ', array( $o->id ) );
					$ordpayinfo->paydate = date('Y-m-d G:i:s');
					$ordpayinfo->closedmethod = $o->payid;
					if(isset($o->checknumber)) {
						$ordpayinfo->checknumber = $o->checknumber;
					}
					R::store($ordpayinfo);
					$this->replicateData("PUT", "orderpayinfo", $ordpayinfo->__toString(), $ordpayinfo->id);
				}
			}
		}
	}
	
	protected function accountspayable() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'PUT'){
			$body = json_decode($this->file);
			if($this->verb == "paid") {
				foreach($body as $i) {
					$invoice = R::load('invoice', $i);
					$invoice->paid = 1;
					R::store($invoice);
					$this->replicateData("PUT", "invoice", $invoice->__toString(), $i);
				}
			}
		}
	}
	
	protected function user() {
		if($this->method == 'POST') {
			$body = @file_get_contents('php://input');
			$body = json_decode($body, true);
	
			if($this->verb == "authenticate") {
				if(!isset($body['username']) || $body['username'] == "") {
					throw new Exception("Email is required");
				}
				if(!isset($body['password']) || $body['password'] == "") {
					throw new Exception("Password is required");
				}
	
				$usr = R::findOne('user', ' email = ? and active = 1', array($body['username']) );
	
				if($usr == null) {
					throw new Exception("Invalid email or password");
				}
	
				//$p = password_hash("passw0rd", PASSWORD_BCRYPT);
				if(!password_verify($body["password"], $usr->password)) {
					throw new Exception("Invalid email or password");
				}
	
				$tokenId = base64_encode(random_bytes(32));
				$issuedAt = time();
				$notBefore = $issuedAt;
				$expire = $notBefore + SESSION_TIME_SECONDS;
				$serverName = DOMAIN;
	
				$usersession = R::dispense('usersession');
				$usersession->tokenid = $tokenId; 
				$usersession->created = date('Y-m-d G:i:s');
				$usersession->ipaddress = $_SERVER['REMOTE_ADDR'];
				$usersession->lastused = date('Y-m-d G:i:s');
				
				$data = [
						'iat'  => $issuedAt,
						'jti'  => $tokenId,
						'iss'  => $serverName,
						'nbf'  => $notBefore,
						'exp'  => $expire,
						'data' => [
								'userId'   => $usr["id"],
								'email' => $usr["email"],
								'orgId' => $usr["organization_id"],
								'isAdmin' => $usr["isadmin"]
						]
				];
	
				$jwt = JWT::encode($data, SECRET_KEY, ALGO);
	
				R::store($usersession);
	
				return array('token' => $jwt,'ur' => $usr["isadmin"]);
			}
		} elseif($this->method == 'GET'){
			if($this->user == null) {
				return array("data"=>array("error" => "Expired or Invalid Token"),"statuscode"=>401);
			}
			if($this->verb == "stores") {
				$returnArray = array();
				$returnArray['stores'] = $this->db->rawQuery('select id, identifier from store where organization_id=?', array($this->user->orgId));
				if($this->refreshedToken != null) {
					$returnArray['refreshToken'] = $this->refreshedToken;
				}
				return $returnArray;
			} else {
				$this->user->loggedIn = true;
				return $this->user;
			}
		} elseif($this->method == 'PUT'){
		} elseif($this->method == 'DELETE'){
			if($this->verb == "authenticate") {
				if($this->user == null || $this->token == null) {
					return array('success' => true);
				}

				$usersession = R::findOne('usersession', " tokenid = ? and ipaddress = ? ", array($this->token,$_SERVER['REMOTE_ADDR']));
				R::trash($usersession);
				return array('success' => true);
			}
		}
	}
	
	protected function report() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'GET'){
			if($this->verb == "postedtotals") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$orders = R::getAll('select o.ordertax, DATE_FORMAT(o.updated, "%m/%d/%Y") as date, o.id, cu.businessname, co.firstname, co.lastname, opi.amount, pm.name, cu.internal from `order` o left join contact co on (o.contact_id = co.id) left join customer cu on (co.customer_id = cu.id) left join orderpayinfo opi on (o.id = opi.order_id) left join paymentmethod pm on (opi.paymentmethod_id=pm.id) where o.type="I" AND o.updated >= ? AND o.updated <= ?', array($start,$end));
				$returnArray = array();
				$paymentTotals = array();
				$chartData = array();
				$chartData['labels'] = array();
				$chartData['datasets'] = array();
				$chartData['datasets'][] = array();
				$chartData['datasets'][0]['label'] = "";
				$chartData['datasets'][0]['fill'] = "false";
				$chartData['datasets'][0]['borderWidth'] = 1;
				$chartData['datasets'][0]['data'] = array();
				$chartData['datasets'][0]['backgroundColor'] = array();
				$chartData['datasets'][0]['borderColor'] = array();
				$grandTotal = 0;
				foreach($orders as $o) {
					if(!array_key_exists($o['name'],$paymentTotals)) {
						$paymentTotals[$o['name']] = 0;
					}
					if($o['internal'] != "1") {
						$paymentTotals[$o['name']] = $paymentTotals[$o['name']] + $o['amount'];
						$grandTotal += $o['amount'];
					}
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$returnArray['summary'][$o['name']]['invoices'][] = array("date" => $o['date'], "number" => $o['id'], "name" => $name, "amount" => $o['amount'], "internal" => $o['internal']);
				}
				
				foreach($paymentTotals as $p => $a) {
					$returnArray['totals']['paymeth'][] = array("name" => $p, "amount" => $a);
					$returnArray['summary'][$p]['total'] = $a;
					$chartData['datasets'][0]['data'][] = $a;
					$chartData['datasets'][0]['backgroundColor'][] = "rgba(0, 105, 177, 1)";
					$chartData['datasets'][0]['borderColor'][] = "rgba(0, 105, 177, 1)";
					$chartData['labels'][] = $p;
				}
				$returnArray['totals']['grand'] = $grandTotal;
				
				$oitems = R::getAll('SELECT oi.quantity, oi.retail, oi.tax, oi.taxcat, CASE WHEN oi.cost IS NULL OR oi.cost = "" THEN 0 ELSE oi.cost END AS cost, it.category, it.dotrequired, c.taxexempt, tr.exemption FROM `order` o LEFT JOIN orderitem oi ON ( o.id = oi.order_id ) LEFT JOIN itemtype it ON ( oi.itemtype_id = it.id ) LEFT JOIN taxrate tr on (it.category = tr.category) LEFT JOIN contact co on (o.contact_id = co.id) LEFT JOIN customer c on (co.customer_id = c.id) WHERE o.type = "I" AND (c.internal = 0 OR c.internal is null) AND o.updated >=  ? AND o.updated <=  ? group by oi.id', array($start,$end));
				
				$partCost = 0;
				$tireCost = 0;
				$partSales = 0;
				$tireSales = 0;
				$taxes = array();
				foreach($oitems as $oi) {
					if(!array_key_exists($oi['category'],$taxes)) {
						$taxes[$oi['category']] = 0;
					}

					if($oi['tax'] != "") {
						$taxes[$oi['category']] = $taxes[$oi['category']] + $oi['tax'];
					} else {
						if($oi['taxexempt'] == 1) {
							if($oi['exemption'] == "") {
								$oi['taxcat'] = 0;
							} else {
								$oi['taxcat'] = $oi['taxcat'] - ($oi['taxcat'] * ($oi['exemption']/100));
							}
						}
						$taxes[$oi['category']] = $taxes[$oi['category']] + ($oi['quantity'] * $oi['retail'] * ($oi['taxcat']/100));
					}
					if(!array_key_exists($oi['category'] . "sales",$returnArray['totals'])) {
						$returnArray['totals'][$oi['category'] . "sales"] = 0;
					}
					$returnArray['totals'][$oi['category'] . "sales"] += ($oi['quantity'] * $oi['retail']);
					if($oi['dotrequired'] == 1) {
						$tireCost += ($oi['quantity'] * $oi['cost']);
					} else {
						$partCost += ($oi['quantity'] * $oi['cost']);
					}
					
					if($oi['category'] == "part") {
						if($oi['dotrequired'] == 1) {
							$tireSales += ($oi['quantity'] * $oi['retail']);
						} else {
							$partSales += ($oi['quantity'] * $oi['retail']);
						}
					}
				}
				
				$returnArray['totals']['tirecost'] = $tireCost;
				$returnArray['totals']['partcost'] = $partCost;
				$returnArray['totals']['tiresales'] = $tireSales;
				$returnArray['totals']['partsales'] = $partSales;
				
				foreach($taxes as $c => $a) {
					$returnArray['totals']['taxes'][] = array("name" => $c, "amount" => round($a,2));
				}
				
				$arpayments = R::getAll('select sum(opi.amount) as amount, pm.name from orderpayinfo opi left join paymentmethod pm on (opi.closedmethod=pm.id) where paymentmethod_id != closedmethod and opi.paydate >= ? and opi.paydate <= ? group by opi.closedmethod order by name asc', array($start,$end));
				$returnArray['arpayments'] = $arpayments;
				
				$totalInvoices = R::getCell('select count(o.id) from `order` o left join contact co on (o.contact_id = co.id) left join customer cu on (co.customer_id = cu.id) where o.type="I" AND (cu.internal = 0 OR cu.internal is null) AND o.updated >= ? AND o.updated <= ?', array($start,$end));
				if($totalInvoices > 0) {
					$returnArray['totals']['average'] = round($grandTotal/$totalInvoices,2);
				} else {
					$returnArray['totals']['average'] = 0;
				}
				$returnArray['chartjs'] = $chartData;
				$returnArray['totals']['invoicecount'] = $totalInvoices;

				return $returnArray;
			} elseif($this->verb == "accountsreceivable") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$orders = R::getAll('select o.ordertax,  DATE_FORMAT(o.updated, "%m/%d/%Y") as date, o.id, opi.id as opiid, cu.businessname, co.firstname, co.lastname, cu.addressline1, cu.addressline2, cu.addressline3, cu.city, cu.state, cu.zip, co.phone1, opi.amount, pm.name, v.fleetnum from `order` o left join contact co on (o.contact_id = co.id) left join customer cu on (co.customer_id = cu.id) left join orderpayinfo opi on (o.id = opi.order_id) left join paymentmethod pm on (opi.paymentmethod_id=pm.id) left join vehicle v on (o.vehicle_id = v.id) where o.type="I" and opi.paydate is null and o.updated >= ? and o.updated <= ? order by cu.id asc, o.id asc', array($start,$end));
				$returnArray = array();
				foreach($orders as $o) {
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$fleetnum = $o['fleetnum'];
					if($fleetnum == null) {
						$fleetnum = "";
					}
					$returnArray["accounts"][$name]["orders"][] = array("date" => $o['date'], "number" => $o['id'], "orderpayinfoid" => $o['opiid'], "name" => $name, "amount" => $o['amount'], "fleetnum" => $fleetnum);
					if(!array_key_exists("details",$returnArray["accounts"][$name])) {
						$returnArray["accounts"][$name]["details"] = array("total" => 0, "name" => $name, "addressline1" => $o['addressline1'], "addressline2" => $o['addressline2'], "addressline3" => $o['addressline3'], "city" => $o['city'], "state" => $o['state'], "zip" => $o['zip'], "phone1" => $o['phone1']);
					}

					$returnArray["accounts"][$name]["details"]["total"] = $returnArray["accounts"][$name]["details"]["total"] + $o['amount'];
				}
				
				$paymentmethods = R::find('paymentmethod', ' store_id = ? and active=1 and open=0', array(STORE_ID) );
				$returnArray["paymentmethods"] = $this->handleArray($paymentmethods);
				
				
				return $returnArray;
			} elseif($this->verb == "accountspayable") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$invoices = R::getAll('select i.id, i.number, DATE_FORMAT(i.created, "%m/%d/%Y") as date, v.vendorname from invoice i left join vendor v on (i.vendor_id = v.id) where i.paid = 0 and i.created >= ? and i.created <= ? order by v.vendorname asc, i.created asc', array($start,$end));
				$returnArray = array();
				foreach($invoices as $i) {
					$iTotal = R::getCell("select sum(quantity * cost) from invoiceitem where invoice_id=?", array($i['id']));
					$returnArray[$i['vendorname']][] = array("date" => $i['date'], "number" => $i['number'], "id" => $i['id'], "amount" => number_format($iTotal,2));
				}
				return $returnArray;
			} elseif($this->verb == "lowmargin") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$orders = R::getAll('select o.ordermargin,  DATE_FORMAT(o.updated, "%m/%d/%Y") as date, o.id, cu.businessname, co.firstname, co.lastname, opi.amount, pm.name from `order` o left join contact co on (o.contact_id = co.id) left join customer cu on (co.customer_id = cu.id) left join orderpayinfo opi on (o.id = opi.order_id) left join paymentmethod pm on (opi.paymentmethod_id=pm.id) where o.type="I" and o.ordermargin < 30 and o.updated >= ? and o.updated <= ? order by ordermargin asc', array($start,$end));
				
				$returnArray = array();
				foreach($orders as $o) {
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$returnArray[] = array("date" => $o['date'], "number" => $o['id'], "name" => $name, "amount" => $o['amount'], "margin" => $o['ordermargin']);
				}
				return $returnArray;
			} elseif($this->verb == "salestaxexempt") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$orders = R::getAll('select DATE_FORMAT(o.updated, "%m/%d/%Y") as date, o.id, o.ordertotal, cu.businessname, co.firstname, co.lastname, cu.taxexemptnum from `order` o left join contact co on (o.contact_id = co.id) left join customer cu on (co.customer_id = cu.id) where o.ordertotal > 0 and o.ordertax < 0.01 and o.type="I" AND o.updated >= ? AND o.updated <= ?', array($start,$end));
				
				$returnArray = array();
				foreach($orders as $o) {
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$returnArray[] = array("date" => $o['date'], "id" => $o['id'], "name" => $name, "amount" => $o['ordertotal'], "taxexemptnum" => $o['taxexemptnum']);
				}
				return $returnArray;
			} elseif($this->verb == "techproductivity") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$orders = R::getAll('select DATE_FORMAT(o.updated, "%m/%d/%Y") as date, o.id, cu.businessname, co.firstname, co.lastname, sum(oi.retail) as amount, e.name from `order` o left join orderitem oi on (o.id = oi.order_id and oi.dotnumber is null) left join contact co on (o.contact_id = co.id) left join customer cu on (co.customer_id = cu.id) left join order_teammember ot on (o.id=ot.order_id) left join teammember tm on (ot.teammember_id = tm.id) left join employee e on (tm.employee_id = e.id) where o.type="I" and o.updated >= ? and o.updated <=  ? group by o.id order by o.id asc', array($start,$end));
				$returnArray = array();
				foreach($orders as $o) {
					if(!array_key_exists($o['name'],$returnArray)) {
						$returnArray[$o['name']] = array("total" => 0, "invoices" => array());
					}
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$returnArray[$o['name']]['total'] += $o['amount'];
					$returnArray[$o['name']]['invoices'][] = array("date" => $o['date'], "number" => $o['id'], "name" => $name, "amount" => $o['amount']);
				}
				return $returnArray;
			} elseif ($this->verb == "inventorysold") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";


				$rows = R::getAll("SELECT o.id, oi.partnumber, oi.quantity, oi.description, (oi.quantity*oi.cost) as total_cost, (oi.quantity*oi.retail) as total_retail, (SELECT manufacturer from `inventory` ivt WHERE oi.partnumber = ivt.partnumber) as manufacturer, oi.invoicenumber, DATE(o.updated) as date FROM `order` o LEFT JOIN orderitem oi ON o.id = oi.order_id WHERE o.type = 'I' and oi.itemtype_id = '4' and o.updated >= ? and o.updated <= ?", array($start, $end));
				$items_sold = R::convertToBeans( 'order', $rows );
				
				$retArray = array();
				$columns = array();
				$columns[] = array("data"=>"manufacturer", "defaultContent"=>"", "label"=>"Manufacturer");
				$columns[] = array("data"=>"partnumber", "defaultContent"=>"", "label"=>"Part Number");
				$columns[] = array("data"=>"description", "defaultContent"=>"", "label"=>"Description");
				$columns[] = array("data"=>"total_cost", "defaultContent"=>0, "label"=>"Total Cost");
				$columns[] = array("data"=>"total_retail", "defaultContent"=>0, "label"=>"Total Retail");
				$columns[] = array("data"=>"quantity", "defaultContent"=>0, "label"=>"Total Quantity");
				$columns[] = array("data"=>"invoicenumber", "defaultContent"=>"", "label"=>"Invoice Number");
				$columns[] = array("data"=>"updated", "defaultContent"=>0, "label"=>"Date");

				if($items_sold == null) {
					return array("columns"=>$columns, "data" => $retArray);
				}

				foreach($items_sold as $i) {
					//$retArray[] = json_decode($i->__toString());
					$retArray[] = $i;
				}
				if(isset($_GET['format']) && $_GET['format'] == "datatable") {
					return array("columns"=>$columns, "data" => $retArray);
				}
				return $retArray;
			} elseif($this->verb == "inventoryDollars") {
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$end = $_GET['end'] . " 23:59:59";

				$rows_inventory = R::getAll("SELECT id, partnumber, description, cost, quantity, (cost * quantity) as rawCost from inventory");

				$rows_invoiceitem = R::getAll("SELECT invi.invoice_id, invi.inventory_id, (sum(invi.quantity)) as total_quantity, (sum(invi.cost)) as total_cost  from invoiceitem invi left join invoice inv on inv.id = invi.invoice_id where inv.created >= ? group by invi.inventory_id", array($end));

				$returnArray = array();
				$columns = array();
				$columns[] = array("data"=>"partnumber", "defaultContent"=>"", "label"=>"Part Number");
				$columns[] = array("data"=>"description", "defaultContent"=>"", "label"=>"Description");
				$columns[] = array("data"=>"unit_cost", "defaultContent"=>0, "label"=>"Unit Cost");
				$columns[] = array("data"=>"total_quantity", "defaultContent"=>0, "label"=>"Quantity on Hand");
				$columns[] = array("data"=>"total_cost", "defaultContent"=>0, "label"=>"Total Cost of items");
				$unit_cost = 0;
				$total_quantity = 0;
				$total_cost = 0;

				foreach ($rows_inventory as $inventory) {
					$unit_cost = $inventory['cost'];
					$total_quantity = $inventory['quantity'];
					if ($total_quantity == 0) {
						$total_cost = 0;
					} else {
						$total_cost = $inventory['rawCost'];
					}

					foreach ($rows_invoiceitem as $invoiceitem) {
						if ($invoiceitem['inventory_id'] == $inventory['id']) {
							$total_quantity = $inventory['quantity'] - $invoiceitem['total_quantity'];
							$total_cost = $inventory['rawCost'] - $invoiceitem['total_cost'];
							if($total_quantity != 0) {
								$unit_cost = $total_cost / $total_quantity;
							} else {
							    $unit_cost = 0;
							}
						}
					}
					$returnArray[] = array('id' => $inventory['id'], 'partnumber' => $inventory['partnumber'], 'description' => $inventory['description'], 'unit_cost' => round($unit_cost, 2), 'total_quantity' => $total_quantity, 'total_cost' => round($total_cost, 2));
				}

				if(isset($_GET['format']) && $_GET['format'] == "datatable") {
					return array("columns" => $columns, "data" => $returnArray);
				}
				return $returnArray;
			} elseif($this->verb == "outsidepurchasetiressold") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
			
				$rows = R::getAll("SELECT o.id, oi.manufacturer as manufacturer, oi.partnumber, oi.quantity, oi.description, (oi.quantity*oi.cost) as total_cost, (oi.quantity*oi.retail) as total_retail, (SELECT vendorname from `vendor` vnd WHERE oi.vendor_id = vnd.id) as vendor FROM `order` o LEFT JOIN orderitem oi ON o.id = oi.order_id WHERE o.type = 'I' and oi.itemtype_id = '2' and o.updated >= ? and o.updated <= ?", array($start, $end));
				$items_sold = R::convertToBeans( 'order', $rows );
				
				$retArray = array();
				$columns = array();
				$columns[] = array("data"=>"vendor", "defaultContent"=>"", "label"=>"Vendor");
				$columns[] = array("data"=>"manufacturer", "defaultContent"=>"", "label"=>"Manufacturer");
				$columns[] = array("data"=>"partnumber", "defaultContent"=>"", "label"=>"Part Number");
				$columns[] = array("data"=>"description", "defaultContent"=>"", "label"=>"Description");
				$columns[] = array("data"=>"total_cost", "defaultContent"=>0, "label"=>"Total Cost");
				$columns[] = array("data"=>"total_retail", "defaultContent"=>0, "label"=>"Total Retail");
				$columns[] = array("data"=>"quantity", "defaultContent"=>0, "label"=>"Total Quantity");
				
				if($items_sold == null) {
					return array("columns"=>$columns, "data" => $retArray);
				}
				
				foreach($items_sold as $i) {
					$retArray[] = json_decode($i->__toString());
				}
				
				if(isset($_GET['format']) && $_GET['format'] == "datatable") {
					return array("columns"=>$columns, "data" => $retArray);
				}
				return $retArray;
			} elseif ($this->verb == "bestseller") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";

				$orders = R::getAll("SELECT oi.partnumber, oi.description, sum(oi.retail) as totalprice, sum(oi.quantity) as total_quantity_sold, round((sum(oi.retail)/sum(oi.quantity)),2) as unitprice FROM `order` o LEFT JOIN orderitem oi ON o.id = oi.order_id WHERE o.type = 'I' and oi.itemtype_id in (1, 2, 3, 4) and o.updated >= ? and o.updated <= ? group by oi.partnumber, oi.description order by total_quantity_sold desc", array($start, $end));
                return $orders;
			} elseif($this->verb == "deletedorders") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$orders = R::getAll('select DATE_FORMAT(o.updated, "%m/%d/%Y") as date, o.id, o.ordertotal, co.firstname, co.lastname from `order` o left join contact co on (o.contact_id = co.id) where o.type="D" and o.updated >= ? and o.updated <= ? order by o.id asc', array($start,$end));
				
				$returnArray = $orders;
				return $returnArray;
			}
		}
	}

	protected function inspectionReport() {
		if($this->user == null) {
			return Array("data" => array("error" => "User session timed out"), "statuscode" => 401);
		}

		if($this->method == 'POST') {
			$body = @file_get_contents('php://input');
			$body = json_decode($body);

			$inspectionreport = R::dispense('inspectionreport');
			$inspectionreport->order_id = $body->orderId;
			$inspectionreport->preliminary_inspection = json_encode($body->preliminaryInspection);
			$inspectionreport->cluster = json_encode($body->cluster);
			$inspectionreport->under_hood = json_encode($body->underHood);
			$inspectionreport->under_car = json_encode($body->underCar);
			$inspectionreport->steering_suspension = json_encode($body->steeringSuspension);
			$inspectionreport->tires = json_encode($body->tires);
			$inspectionreport->brakes = json_encode($body->brakes);
			$inspectionreport->tech_initials = json_encode($body->technicianInitials);
			$id = R::store($inspectionreport);
			$this->replicateData("POST", "inspectionreport", $inspectionreport->__toString(), 0);

			return ['id' => $id];
		} elseif($this->method == 'GET'){
			$returnArray = [];
			$order  = R::findOne('order', ' id = ? ', array($this->args[0]));
			$returnArray['vehicle'] = json_decode($order->vehicle->__toString());
			$inspectionreport = R::findOne('inspectionreport', ' order_id = ? ', array($this->args[0]));
			if ($inspectionreport) {
				$inspectionreport = json_decode($inspectionreport->__toString());

				$inspectionreport->preliminary_inspection = json_decode($inspectionreport->preliminary_inspection);
				$inspectionreport->cluster = json_decode($inspectionreport->cluster);
				$inspectionreport->under_hood = json_decode($inspectionreport->under_hood);
				$inspectionreport->under_car = json_decode($inspectionreport->under_car);
				$inspectionreport->steering_suspension = json_decode($inspectionreport->steering_suspension);
				$inspectionreport->tires = json_decode($inspectionreport->tires);
				$inspectionreport->brakes = json_decode($inspectionreport->brakes);
				$inspectionreport->tech_initials = json_decode($inspectionreport->tech_initials);
				$returnArray['inspectionreport'] = $inspectionreport;
			}

			return $returnArray;
		} elseif($this->method == 'PUT'){
			$body = json_decode($this->file);
			if(!isset($body->id)) {
				return "Missing parameter: id";
			}
			
			$inspectionreport  = R::load('inspectionreport', $body->id);
			$inspectionreport->preliminary_inspection = json_encode($body->preliminaryInspection);
			$inspectionreport->cluster = json_encode($body->cluster);
			$inspectionreport->under_hood = json_encode($body->underHood);
			$inspectionreport->under_car = json_encode($body->underCar);
			$inspectionreport->steering_suspension = json_encode($body->steeringSuspension);
			$inspectionreport->tires = json_encode($body->tires);
			$inspectionreport->brakes = json_encode($body->brakes);
			$inspectionreport->tech_initials = json_encode($body->technicianInitials);
			
			$id = R::store($inspectionreport);
			$this->replicateData("PUT", "inspectionreport", $inspectionreport->__toString(), $body->id);
			
			return array("id"=>$id);
		}
	}

	protected function replicateData($verb, $table, $data, $updateId) {
		if(REPLICATE) {
			//$time_start = microtime(true);
			//if($params != null) {
			//	$replicate = $params;
			//} else {
				$replicate = array();
			//}
// 			try {
// 				$headers = array('Accept' => 'application/json');
				$replicate['data'] = json_decode($data);
				$replicate['data']->store_id = STORE_ID;
				$replicate['store_key'] = STORE_SECRET_KEY;
// 				$replicate['table'] = $table;
// 				$replicate['update_id'] = $updateId;
				
// 				Unirest\Request::verifyPeer(false);
// 				$body = Unirest\Request\Body::json($replicate);
				
// 				if($verb == "POST") {
// 					$response = Unirest\Request::post(REPLCATION_URL, $headers, $body);
// 					if(LOG_RESPONSE) {
// 						$this->logToFile("-------" . $table . " " . $verb . " START -------");
// 						$this->logToFile(print_r($replicate,true));
// 						$this->logToFile(print_r($response,true));
// 						$this->logToFile("Total Execution Time: " . (microtime(true) - $time_start));
// 						$this->logToFile("-------" . $table . " " . $verb . " END -------");
// 					}
// 				} elseif($verb == "PUT") {
// 					$response = Unirest\Request::put(REPLCATION_URL, $headers, $body);
// 					if(LOG_RESPONSE) {
// 						$this->logToFile("-------" . $table . " " . $verb . " START -------");
// 						$this->logToFile(print_r($replicate,true));
// 						$this->logToFile(print_r($response,true));
// 						$this->logToFile("Total Execution Time: " . (microtime(true) - $time_start));
// 						$this->logToFile("-------" . $table . " " . $verb . " END -------");
// 					}
// 				}
// 				if(isset($response->body->error)) {
// 					throw new Exception("Error received in response: " . $response->body->error);
// 				}
// 			} catch (Exception $e) {
// 				// Fallback save
// 				if(LOG_RESPONSE) {
// 					$this->logToFile("-------" . $table . " " . $verb . " START (Exception)-------");
// 					$this->logToFile(print_r($replicate,true));
// 					$this->logToFile(print_r($response,true));
// 					$this->logToFile($e);
// 					$this->logToFile("Total Execution Time: " . (microtime(true) - $time_start));
// 					$this->logToFile("-------" . $table . " " . $verb . " END -------");
// 				}
				$s = R::dispense('sync');
				$s->verb = $verb;
				$s->table = $table;
				$s->keyid = $replicate['data']->id;
				$s->failed = date('Y-m-d G:i:s');
				R::store($s);
// 			}
		}
	}

	protected function account()
	{
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}

		if ($this->method == 'PUT') {
			$body = @file_get_contents('php://input');
			$body = json_decode($body, true);
	
			if ($this->verb == "password") {
				if(!isset($body['oldpass']) || trim($body['oldpass']) == "") {
					throw new Exception("Old password is required");
				}

				if(!isset($body['newpass']) || trim($body['newpass']) == "") {
					throw new Exception("New password is required");
				}

				$oldpass = $body['oldpass'];
				$newpass = $body['newpass'];

				if ($oldpass == $newpass) {
					throw new Exception("New password can not be same as old password");
				}

				if (strlen($newpass) < 5) {
					throw new Exception("Password should contain atleast 5 characters");
				}

				$usr = R::load('user', $this->user->userId );				
				if (!password_verify($oldpass, $usr->password)) {
					throw new Exception("Old password is wrong");
				}

				$usr->password = password_hash($newpass, PASSWORD_BCRYPT);
				R::store($usr);

				return array('success' => true);
			}
		}
	}

	protected function handleArray($arr) {
		if($arr == null) {return array();}
		$retArray = array();
		foreach($arr as $a) {
			$retArray[] = json_decode($a->__toString());
		}
		return $retArray;
	}
	
	protected function logToFile($msg) {
		$filename = "stdout.log";
		$fd = fopen($filename, "a");
		$str = "[" . date("Y/m/d h:i:s", time()) . "] " . $msg;
		fwrite($fd, $str . "\n");
		fclose($fd);
	}

	protected function unsetPrimaryContacts($customer_id, $contact_id = null) {
		$primaryContacts = R::find('contact', 'customer_id = ? and isprimary = ?', array($customer_id, "true"));
		foreach ($primaryContacts as $primaryContact) {
			if($primaryContact->id != $contact_id) {
				$primaryContact->isprimary = "false";
				R::store($primaryContact);
				$this->replicateData("PUT", "contact", $primaryContact->__toString(), $primaryContact->id);
			}
		}
	}
	protected function unsetDeclinedEmailContacts($customer_id, $contact_id = null) {
		$declinedContacts = R::find('contact', 'customer_id = ? and isdeclined = ?', array($customer_id, "true"));
		foreach ($declinedContacts as $declinedContact) {
			if($declinedContact->id != $contact_id) {
				$declinedContact->isdeclined = "false";
				R::store($declinedContact);
				$this->replicateData("PUT", "contact", $declinedContact->__toString(), $declinedContact->id);
			}
		}
	}
	protected function setStoreCache($cached_version) {
		$store = R::load('store', STORE_ID);
		$store->cached_version = $cached_version;
		R::store($store);
		$this->replicateData("PUT", 'store', $store->__toString(), STORE_ID);
	}

	protected function setCustomerCache($id, $cached_version) {
        $customer = R::load('customer', $id);
        $customer->cached_version = $cached_version;
        $this->replicateData("PUT", "customer", $customer->__toString(), $id);
    }
 }
