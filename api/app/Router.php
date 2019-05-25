<?php
use Phalcon\Mvc\Micro\Collection as MicroCollection;

class Router {

    public static $routes = [
        [
            'class'    => 'IndexController',
            'prefix'   => '/',
            'patterns' => [
                [
                    'pattern' => '/',
                    'type' => 'get',
                    'function' => 'info'
                ]
            ]
        ],
        [
            'class'    => 'UsersController',
            'prefix'   => '/user',
            'patterns' => [
                [
                    'pattern' => '/{name}',
                    'type' => 'get',
                    'function' => 'find'
                ],
            ]
        ],
        [
            'class'    => 'AuthController',
            'prefix'   => '/',
            'patterns' => [
                [
                    'pattern' => '/',
                    'type' => 'get',
                    'function' => 'index'
                ]
            ]
        ],

    ];

    /**
     * @param $app \Phalcon\Mvc\Micro
     */
    public static function loadRoutes($app) {
        foreach (self::$routes as $route) {
            $collection = new MicroCollection();
            $collection->setHandler(new $route['class']());
            $collection->setPrefix($route['prefix']);

            $patterns = $route['patterns'];

            foreach ($patterns as $pattern) {
                if ($pattern['type'] == "get") {
                    $collection->get($pattern['pattern'], $pattern['function']);
                } else if ($pattern['type'] == "post") {
                    $collection->post($pattern['pattern'], $pattern['function']);
                }
            }

            $app->mount($collection);
        }
    }

}