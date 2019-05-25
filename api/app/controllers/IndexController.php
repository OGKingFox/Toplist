<?php
/**
 * Created by PhpStorm.
 * User: foxtr
 * Date: 5/15/19
 * Time: 7:09 PM
 */

class IndexController extends \Phalcon\Mvc\Controller{

    public function info() {
        $routes = Router::$routes;
        $endpoints = [];

        foreach ($routes as $route) {
            $url = $route['prefix'] == '/' ? '' : $route['prefix'];
            $patterns = $route['patterns'];

            foreach($patterns as $pattern) {
                $fullUrl = $url.($pattern['pattern'] == "" ? '' : $pattern['pattern']);

                if ($pattern['parameters']) {
                    $endpoints[] = [
                        'type' => $pattern['type'],
                        'url' => $fullUrl,
                        'params' => $pattern['parameters']
                    ];
                } else {
                    $endpoints[] = [
                        'type' => $pattern['type'],
                        'url' => $fullUrl
                    ];
                }

            };
        }
        
        return $endpoints;
    }

}