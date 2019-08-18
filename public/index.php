<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header("X-XSS-Protection: 1; mode=block");
    header('X-Content-Type-Options: nosniff');
    header("Strict-Transport-Security:max-age=63072000");

    use Phalcon\Config;
    use Phalcon\Loader;
	use Phalcon\Mvc\View;
	use Phalcon\Mvc\Application;
	use Phalcon\Di\FactoryDefault;
	use Phalcon\Mvc\Url as UrlProvider;
	use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
	use Phalcon\Flash\Direct as FlashDirect;
	use Phalcon\Events\Manager as EventsManager;
	use Phalcon\Mvc\Dispatcher as MvcDispatcher;
	use Phalcon\Mvc\View\Engine\Volt;
	use Phalcon\Mvc\Router;
    use Phalcon\Session\Adapter\Files;

    $loader = new Loader();
    $di     = new FactoryDefault();

    require __DIR__.'/config.php';
    $config = new Config($settings);

    $di->set('config', function () use ($config) {
        return $config;
    });

	$loader->registerFiles($config->path("core.files")->toArray());
	$loader->registerClasses($config->path("core.classes")->toArray());
	$loader->registerDirs($config->path("core.paths")->toArray());

	$loader->register();

	$di->set("url", function () use ($config) {
		$url = new UrlProvider();
		$url->setBaseUri($config->path("core.base_url"));
		return $url;
	});

	$di->set('voltService', function ($view, $di) use ($config) {
        $volt = new Volt($view, $di);
        $volt->setOptions($config->path("core.volt_options")->toArray());
        $compiler = $volt->getCompiler();
        $compiler->addFilter('number_format', 'number_format');
        $compiler->addFilter('count', 'count');
        $compiler->addFilter('strtotime', 'strtotime');
        $compiler->addFilter('array_chunk', 'array_chunk');
        $compiler->addFilter('in_array', 'in_array');
        $compiler->addFilter('implode', 'implode');
        $compiler->addFilter('unset', 'unset');
        $compiler->addFilter('getExpForLevel', 'unset');
        $compiler->addFunction('str_replace','str_replace');
        $compiler->addFunction('explode','explode');
        $compiler->addFunction('unset','unset');
        $compiler->addFunction('substr','substr');
        $compiler->addFilter('filemtime','filemtime');

        $volt->getCompiler()->addFunction('avatar', function($resolvedParams) {
            return "Functions::getAvatarUrl(".$resolvedParams.")";
        });
        $volt->getCompiler()->addFunction('getSeoTitle', function($resolvedParams) {
            return "Servers::genSeoTitle(".$resolvedParams.")";
        });
        $volt->getCompiler()->addFunction('elapsed', function($resolvedParams) {
            return "Functions::elapsed(".$resolvedParams.")";
        });
        return $volt;
    });

	$di->set("view", function () use ($config) {
		$view = new View();
		$view->setViewsDir($config->path("core.views.directory"));
		$view->registerEngines([
        	'.phtml' => 'voltService',
    	]);
		return $view;
	});

	$di->set('viewCache', function() use ($config) {
	   $frontCache = new Phalcon\Cache\Frontend\Output(array(
		   "lifetime" => $config->path("core.views.expires")
	   ));
	   $cache = new Phalcon\Cache\Backend\File($frontCache, array(
		   "cacheDir" => $config->path("core.views.extension")
	   ));
	   return $cache;
	});

	$di->setShared("dispatcher", function () {
        $eventsManager = new EventsManager();

        $eventsManager->attach("dispatch:beforeDispatch", new SecurityPlugin);
		$eventsManager->attach("dispatch:beforeException", new ExceptionsPlugin);

        $dispatcher = new MvcDispatcher();
        $dispatcher->setEventsManager($eventsManager);
        return $dispatcher;
    });

    if (count(CustomRouter::$routes) != 0) {
        $di->set('router', function () {
            $router = new Router();
            $router->removeExtraSlashes(true);
            $router->mount(new CustomRouter());
            $router->handle();
            return $router;
        });
    }

	$di->set("flash", function () {
        $flash = new FlashDirect([
                "error"   => "alert alert-danger",
                "success" => "alert alert-success",
                "notice"  => "alert alert-info",
                "warning" => "alert alert-warning",
        ]);
        return $flash;
	});

	$di->set("db", function () use ($config) {
        return new DbAdapter($config->path("database")->toArray());
    });

    $di->set('crypt', function() use ($config) {
        $crypt = new Phalcon\Crypt();
        $crypt->setKey($config->path("core.cookie_key"));
        return $crypt;
    });

    $di->set('session', function() {
        $session = new Files();
        $session->start(); // we need to start session
        return $session;
    });

	$application = new Application($di);

	try {
		$response = $application->handle();
		$response->send();
	} catch (Exception $e) {
		echo "Exception: ", $e->getMessage();
	}
