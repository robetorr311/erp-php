<?php 
define("STORE_ID", 2);
define("REPLICATE", true);
define("REPLCATION_URL","https://flux.majorleaguetire.com/api/v1/sync");
define("REREPLCATION_URL","http://127.0.0.1/cloud/api/v1/sync");
define("SYNCING_URL","http://fluxsm/api/v1/pullData");
define("INVENTORY_URL","http://127.0.0.1/cloud/api/v1/location/inventory");
define("LOG_RESPONSE",false);
define("TWO_WAY_SYNC", true);
define("STORE_SECRET_KEY", "A^zP3NE4,p8Y`>U");
R::setup( 'mysql:host=localhost;dbname=majorleaguetire_com','robetorr', 'rt988311' );