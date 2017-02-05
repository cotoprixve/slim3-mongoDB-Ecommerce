<?php
// Routes

$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $name = ( isset($args["name"]) )? $args["name"]:"";
    switch ($name) {
		case 'account':
			return $response->withRedirect($this->router->pathFor('detail'));
			break;
		case 'admin':
			return $response->withRedirect($this->router->pathFor('admin'));
			break;
		case 'cart':
	        $elp = explode('/',$request->getUri()->getBasePath());
	        $x['path'] = '/' . $elp[1].'/';//str_repeat("../", $nro);
			return $this->renderer->render($response, 'cart.phtml', $x);
			break;
    	default:
//			$this->logger->info("Slim-Skeleton '/' inicio");
			try {

			    $mng = new MongoDB\Driver\Manager("mongodb://localhost:27017");

				$allGetVars = $request->getQueryParams();
				if( count( $allGetVars ) > 0 ) {
					$keyword = $allGetVars["keyword"];
					$value = $allGetVars["search_cat"];
					# Set up filter
					$filter = [$value => new MongoDB\BSON\Regex("$keyword","i")];
					$query = new MongoDB\Driver\Query($filter);
					$cursor = $mng->executeQuery('test.products', $query);
					$total = json_decode(json_encode($cursor->toArray()), true);
				} else {
					$query = new MongoDB\Driver\Query([]); 			     
					$rows = $mng->executeQuery("test.products", $query);
					$total = json_decode(json_encode($rows->toArray()), true);
				}
				$arr = array(
					"cursor" =>$total,
					"num_docs" => count( $total )
					);
				return $this->renderer->render($response, 'index.phtml', $arr);
			    
			} catch (MongoDB\Driver\Exception\Exception $e) {

			    $filename = basename(__FILE__);
			    
			    echo "The $filename script has experienced an error.\n<br>"; 
			    echo "It failed with the following exception:\n<br>";
			    echo "Exception:", $e->getMessage(), "\n<br>";
			    echo "In file:", $e->getFile(), "\n<br>";
			    echo "On line:", $e->getLine(), "\n";
			}
    }

});
