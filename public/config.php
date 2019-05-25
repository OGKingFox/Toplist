<?php
/**
 * Ignores the user timezone and uses this for everything. should prevent vote from showing the wrong time remaining.
 * do not change!
 */
define("timezone", "America/Chicago");
date_default_timezone_set(timezone);

define("server_name", "Gaming Toplist");

/**
 * Database credentials...self explanatory.
 */
define("host",      "localhost");
define("username", 	"root");
define("password", 	"");
define("dbname", 	"toplist");


/**
 * this is the folder you have it in. Should include starting and trailing slash.
 * if in root directory, should just be /
 */
define("base_url",  "/toplist/");