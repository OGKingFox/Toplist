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
            "route" => "/report",
            "params" => [
                "controller" 	=> "index",
                "action"     	=> "report"
            ]
        ],
        [
            "route" => "/game/{id:[0-9]+}",
            "params" => [
                "controller" 	=> "index",
                "action"     	=> "index"
            ]
        ],
        [
            "route" => "/game/{game:[A-Za-z0-9\-]+}",
            "params" => [
                "controller" 	=> "index",
                "action"     	=> "index"
            ]
        ],
		[
			"route" => "/game/{id:[0-9]+}-{game:[A-Za-z0-9\-]+}",
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
        ],
        [
            "route" => "/like",
            "params" => [
                "controller" 	=> "index",
                "action"     	=> "like"
            ]
        ],
    ];

	public function initialize() {
		foreach (self::$routes as $route) {
			$this->add($route['route'], $route['params']);
		}
	}

}

