<?php 
$methods = "vehicle,order,orderitem,technician,address,customer,itemtype";
$arr = explode(",",$methods);
echo "<pre>";
foreach ($arr as $api) {
	echo "
	protected function ${api}() {
		if(\$this->method == 'POST') {
			$${api} = R::dispense('${api}');
				\$body = @file_get_contents('php://input');
				$${api}->body = \$body;
				\$id = R::store($${api});
				return array(\"id\"=>\$id);
		} elseif(\$this->method == 'GET'){
			$${api}  = R::findOne('customer', ' id = ? ', array(\$this->args[0]) );
			return json_decode($${api}->__toString());
		} elseif(\$this->method == 'PUT'){
             $${api}  = R::findOne('${api}', ' endpoint = ? ', array( \$this->verb ) );
			 \$body = \$this->file;
				$${api}->body = \$body;
				R::store($${api});
				return json_decode($${api}->body);
		} elseif(\$this->method == 'DELETE'){
				$${api}  = R::findOne('${api}', ' endpoint = ? ', array( \$this->verb ) );
				R::trash($${api});
				return array(\"success\" => true);
		}
	
	}
	";
}
echo "tags:";
foreach ($arr as $api) {
	echo "

  - name: ${api}
    description: Get information related to ${api}	
	";
}
echo "<textarea>";
echo "paths:";
foreach ($arr as $api) {
  
echo "
  /${api}:
    get:
      tags:
        - ${api}
      summary: Finds ${api}
      description: ''
      produces:
        - application/json
      responses:
        '200':
          description: successful operation
        '400':
          description: Invalid status value
    post:
      tags:
        - ${api}
      summary: Add a new ${api} to the store
      description: ''
      consumes:
        - application/json
      produces:
        - application/json
      responses:
        '405':
          description: Invalid input
    put:
      tags:
        - ${api}
      summary: Update an existing ${api}
      description: ''
      consumes:
        - application/json
      produces:
        - application/json
      parameters:
        - in: body
          name: body
          description: ${api} object that needs to be added to the store
      responses:
        '400':
          description: Invalid ID supplied
        '404':
          description: ${api} not found
        '405':
          description: Validation exception
    delete:
      tags:
        - ${api}
      summary: Deletes a ${api}
      description: ''
      produces:
        - application/json
      responses:
        '400':
          description: Invalid ID supplied
        '404':
          description: ${api} not found";
}
echo "</textarea>";
echo "</pre>";
?>