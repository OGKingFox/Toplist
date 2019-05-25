<?php
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;

class ExceptionsPlugin extends Plugin {

    private static $file = "./error_logs.txt";

    public function beforeException(Event $event, Dispatcher $dispatcher, Exception $exception) {
        $message = $exception->getMessage();
        $stack   = $exception->getTraceAsString();

        error_log($message . "\n" . $stack . "\n\n", 3, self::$file);
        return false;
    }

}
