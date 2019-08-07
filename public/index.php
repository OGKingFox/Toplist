<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header("X-XSS-Protection: 1; mode=block");
    header('X-Content-Type-Options: nosniff');
    header("Strict-Transport-Security:max-age=63072000");

	use Phalcon\Loader;
	use Phalcon\Mvc\View;
	use Phalcon\Mvc\Application;
	use Phalcon\Di\FactoryDefault;
	use Phalcon\Mvc\Url as UrlProvider;
	use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
	use Phalcon\Flash\Direct as FlashDirect;
	use Phalcon\Flash\Session as FlashSession;
	use Phalcon\Events\Manager as EventsManager;
	use Phalcon\Mvc\Dispatcher as MvcDispatcher;
	use Phalcon\Session\Adapter\Database as Database;
	use Phalcon\Mvc\View\Engine\Volt;
	use Phalcon\Mvc\Router;

	$loader = new Loader();

	$loader->registerNamespaces([
		'RobThree\Auth' => "../Library/auth/",
	]);

	$loader->registerFiles([
		'config.php',
        '../Library/HTMLPurifier/HTMLPurifier.standalone.php'
    ]);

	$loader->registerClasses([
		"PHPMailer"	    => "../Library/PHPMailer/class.phpmailer.php",
        "SMTP"	        => "../Library/PHPMailer/class.smtp.php",
		"CustomRouter"  => "../app/CustomRouter.php",
        "VoltExtension" => "../app/VoltExtension.php",
    ]);

	$loader->registerDirs([
		"../app/controllers/",
		"../app/models/",
		"../app/plugins/",
		"../Library/"
	]);

	$loader->register();

	$di = new FactoryDefault();

	$di->set("url", function () {
		$url = new UrlProvider();
		$url->setBaseUri(base_url);
		return $url;
	});

	$di->set('voltService', function ($view, $di) {
        $volt = new Volt($view, $di);
        $volt->setOptions([
            'compiledPath'      => '../app/compiled/templates/',
            'compiledExtension' => '.compiled',
        ]);
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

	$di->set("view", function () {
		$view = new View();
		$view->setViewsDir("../app/views/");
		$view->registerEngines([
        	'.phtml' => 'voltService',
    	]);
		return $view;
	});

	$di->set('viewCache', function(){
	   $frontCache = new Phalcon\Cache\Frontend\Output(array(
		   "lifetime" => 43200 // 12 hours
	   ));
	   $cache = new Phalcon\Cache\Backend\File($frontCache, array(
		   "cacheDir" => "../app/compiled/"
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

	$di->set("db", function () {
        return new DbAdapter([
			"host"     => host,
			"username" => username,
			"password" => password,
			"dbname"   => dbname,
		]);
    });

    $di->set('crypt', function() {
        $crypt = new Phalcon\Crypt();
        $crypt->setKey('ReallyRandomKey');
        return $crypt;
    });

    $di->set('session', function(){
        $session = new \Phalcon\Session\Adapter\Files();
        $session->start(); // we need to start session
        return $session;
    });

	$application = new Application($di);

	try {
		$response = $application->handle();
		$response->send();
	} catch (\Exception $e) {
		echo "Exception: ", $e->getMessage();
	}
