<?php
use Phalcon\Mvc\Controller;

class ErrorsController extends Controller {

    public function show404Action() {
        echo '404';
    }

    public function show401Action() {
        echo '401';
    }

    public function show500Action() {
    	echo '500';
    }

}