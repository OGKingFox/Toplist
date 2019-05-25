<?php
use Phalcon\Mvc\User\Plugin;

class LoggingPlugin extends Plugin {

    private static $file = "../public/error_logs.txt";

    public static function log($message, $trace) {
        error_log($message . "\n" . $trace . "\n\n", 3, self::$file);
    }

}