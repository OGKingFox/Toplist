<?php
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group as RouterGroup;

class CustomRouter extends RouterGroup {

	public function initialize() {
		$routes = array(
            array(
                "route" => "/logout",
                "params" => [
                    "controller" 	=> "index",
                    "action"     	=> "logout"
                ]
            )
		);

		foreach ($routes as $route) {
			$this->add($route['route'], $route['params']);
		}
	}

}
?>
