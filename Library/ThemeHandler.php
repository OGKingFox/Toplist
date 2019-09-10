<?php


use Phalcon\Config;

class ThemeHandler {

    /**
     * @var string
     */
    private $directory = "";

    /**
     * @var ThemeHandler
     */
    private static $instance;

    /**
     * @return ThemeHandler
     */
    public static function getInstance() {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new ThemeHandler();
        return self::$instance;
    }
    /**
     * ThemeHandler constructor.
     */
    public function __construct() {
        global $config;
        $path  = $config->path("core.base_path");
        $this->directory = $path.'/public/css/themes/';
    }

    /**
     * Returns a list of available themes saved in the theme folder.
     * @return array
     */
    public function getThemes() {
        $files = array_values(array_diff(scandir($this->directory), array('..', '.')));

        $list = [];

        foreach($files as $file) {
            $list[] = [
                'file'     => $file,
                'created'  => date("m/d/Y g:i A", filectime($this->directory.$file)),
                'modified' => date("m/d/Y g:i A", filemtime($this->directory.$file))
            ];
        }
        return $list;
    }

    /**
     * @param $name
     * @return string
     */
    public function getThemePath($name) {
        return $this->directory.$name;
    }

    /**
     * @param $name
     * @return false|string
     */
    public function getThemeFile($name) {
        return file_get_contents($this->directory.$name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function themeExists($name) {
        return file_exists($this->directory.$name);
    }

    /**
     * @param $name
     * @param $contents
     */
    public function createTheme($name, $contents) {
        $fp = fopen($this->directory.$name, 'w');
        fwrite($fp, $contents);
        fclose($fp);
    }

    /**
     * @param $name
     * @param $contents
     */
    public function updateTheme($name, $contents) {
        $fp = fopen($this->directory.$name, 'w');
        fwrite($fp, $contents);
        fclose($fp);
    }

    public function deleteTheme($name) {
        return unlink($this->directory.$name);
    }
}