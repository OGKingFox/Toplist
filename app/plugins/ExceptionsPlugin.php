<?php
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;

class ExceptionsPlugin extends Plugin {

    public function beforeException(Event $event, Dispatcher $dispatcher, Exception $exception) {

        LoggingPlugin::log($exception->getMessage(), $exception->getTraceAsString());

        if ($exception instanceof DispatcherException) {
            switch ($exception->getCode()) {
                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward([
                        'controller' => 'errors',
                        'action'     => 'show404'
                    ]);
                    return false;
            }
        }

        $dispatcher->forward([
            'controller' => 'errors',
            'action'     => 'show500'
        ]);

        $this->view->errmsg = $exception->getMessage();
        return false;
    }

}
