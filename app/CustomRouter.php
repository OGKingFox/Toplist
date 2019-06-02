<?php
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Group as RouterGroup;

class CustomRouter extends RouterGroup {

	public static $routes = [
		[
			"route" => "/logout",
			"params" => [
				"controller" 	=> "index",
				"action"     	=> "logout"
			]
		],
		[
			"route" => "/game/{game}",
			"params" => [
				"controller" 	=> "index",
				"action"     	=> "index"
			]
		]
	];

	public function initialize() {
		foreach (self::$routes as $route) {
			$this->add($route['route'], $route['params']);
		}
	}

}

