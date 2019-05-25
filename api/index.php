<?php
use Phalcon\Mvc\Micro as Micro;
use \Phalcon\Loader as Loader;
use Phalcon\Db\Adapter\Pdo\Mysql as MysqlAdapter;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro\Collection as MicroCollection;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger\Factory;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

$loader = new Loader();
$di     = new FactoryDefault();
$app    = new Micro($di);

define("FORUM_PATH", "../forum/");
define("ACCESS_KEY", "5a0158fe-779e-437d-b0db-cc5017e547cb");

$loader->registerDirs(array(
    '../app/models/',
    '../library/',
    __DIR__ . '/app/controllers/'
));

$loader->registerFiles([
    __DIR__ . '/app/plugins/ApiSecurity.php',
    __DIR__ . '../Library/Functions.php',
    __DIR__ . '/app/Router.php',
    '../public/config.php'
]);

$loader->register();

$api_key = Functions::getBearerToken();
$ip_addr = $_SERVER['REMOTE_ADDR'];

if ($api_key != ACCESS_KEY && $ip_addr != "::1") {
    echo json_encode(['error' => 'Invalid access token.']);
    return;
}

$di->set('db', function () {
    return new DbAdapter([
        "host"       => "localhost",
        "username"   => "root",
        "password"   => '',
        "dbname"     => "toplist",
    ]);
});

$di->setShared('response', function () {
    $response = new \Phalcon\Http\Response();
    $response->setContentType('application/json', 'utf-8');
    return $response;
});

Router::loadRoutes($app);

$app->before(function () use ($app) {
    $security = new ApiSecurity();

    if (!$security->hasAccess()) {
        $app->response->setStatusCode(401, 'Access Denied');
        $app->response->setContent(json_encode(['error' => 'User does not have access'], JSON_PRETTY_PRINT));
        $app->response->send();
        $app->stop();
    }
});

$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, 'Not Found');
    $app->response->setContent(json_encode(['error' => 'This page could not be found.'], JSON_PRETTY_PRINT));
    $app->response->send();
});

$app->after(function () use ($app) {
    $app->response->setContent(json_encode($app->getReturnedValue(),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $app->response->send();
    $app->stop();
});

try {
    $app->handle();
} catch (Exception $e) {
    echo $e->getMessage();

    $logger = new FileAdapter('errors.log');
    $logger->log(Logger::CRITICAL, $e);
}
