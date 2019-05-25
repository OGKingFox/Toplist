<?php
use \Phalcon\Mvc\View\Engine\Volt;
use \Phalcon\Mvc\ViewBaseInterface;
use \Phalcon\DiInterface;
use \Phalcon\Mvc\View\Engine\Volt\Compiler;

class VoltExtension extends Volt {

    private static $filters = [
        'number_format' => 'number_format',
        'strtotime' 	=> 'strtotime',
        'date' 			=> 'date',
        'chunk' 		=> 'array_chunk',
        'count' 		=> 'count',
        'substr' 		=> 'substr',
        'special_chars' => 'htmlspecialchars',
        'in_array' 		=> 'in_array',
        'array_filter' 	=> 'array_filter',
        'strlen' 		=> 'strlen',
        'explode'       => 'explode'
    ];

    private static $functions = [
        'replace'       => 'replace',
        'endsWith'      => 'endsWith'
    ];

    public function __construct(ViewBaseInterface $view, DiInterface $injector = null) {
        parent::__construct($view, $injector);
    }

    public function addFilters() {
        $compiler = $this->getCompiler();
        foreach (self::$filters as $key => $value) {
            $compiler->addFilter($key, $value);
        }
    }

    public function addFunctions() {
        $compiler = $this->getCompiler();
        foreach (self::$functions as $key => $value) {
            $compiler->addFunction($key, function($key) use ($value) {
                return "Functions::{$value}({$key})";
            });
        }
    }



}