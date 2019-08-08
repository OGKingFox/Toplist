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
            "route" => "/vote/{server:[A-Za-z0-9\-]+}/{incentive:[A-Za-z0-9\-\s]+}",
            "params" => [
                "controller" 	=> "vote",
                "action"     	=> "index"
            ]
        ],
        [
            "route" => "/report",
            "params" => [
                "controller" 	=> "servers",
                "action"     	=> "report"
            ]
        ],
		[
			"route" => "/servers/{id:[0-9]+}-{game:[A-Za-z0-9\-]+}",
			"params" => [
				"controller" 	=> "servers",
				"action"     	=> "index"
			]
		],
        [
            "route" => "/servers/{id:[0-9]+}-{game:[A-Za-z0-9\-]+}/{page:[0-9]+}",
            "params" => [
                "controller" 	=> "servers",
                "action"     	=> "index"
            ]
        ],
        [
            "route" => "/docs",
            "params" => [
                "controller" 	=> "pages",
                "action"     	=> "docs"
            ]
        ],
        [
            "route" => "/advertising",
            "params" => [
                "controller" 	=> "pages",
                "action"     	=> "advertising"
            ]
        ],
        [
            "route" => "/faq",
            "params" => [
                "controller" 	=> "pages",
                "action"     	=> "faq"
            ]
        ],
        [
            "route" => "/terms",
            "params" => [
                "controller" 	=> "pages",
                "action"     	=> "terms"
            ]
        ],
        [
            "route" => "/privacy",
            "params" => [
                "controller" 	=> "pages",
                "action"     	=> "privacy"
            ]
        ]
    ];

	public function initialize() {
		foreach (self::$routes as $route) {
			$this->add($route['route'], $route['params']);
		}
	}

}

