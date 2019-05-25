<?php
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group as RouterGroup;

class CustomRouter extends RouterGroup {

	public function initialize() {
		$routes = array(
            array(
                "route" => "/admin/{type:[A-Za-z0-9\-]+}",
                "params" => [
                    "controller" 	=> "admin",
                    "action"     	=> "view"
                ]
            )
		);

		foreach ($routes as $route) {
			$this->add($route['route'], $route['params']);
		}
	}

}
?>
