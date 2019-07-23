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
		],
        [
            "route" => "/view/{id:[0-9]+}-{server:[A-Za-z0-9\-]+}",
            "params" => [
                "controller" 	=> "index",
                "action"     	=> "view"
            ]
        ],
        [
            "route" => "/view/{id:[0-9]+}",
            "params" => [
                "controller" 	=> "index",
                "action"     	=> "view"
            ]
        ]
	];

	public function initialize() {
		foreach (self::$routes as $route) {
			$this->add($route['route'], $route['params']);
		}
	}

}

