<?php
/**
 * Ignores the servers timezone and uses this for everything.
 */
define("timezone", "America/Chicago");
date_default_timezone_set(timezone);

define("server_name", "Gaming TopList");

/**
 * Database credentials...self explanatory.
 */
define("host",      "localhost");
define("username", 	"root");
define("password", 	"");
define("dbname", 	"toplist");

/**
 * Discord OAuth Tokens
 * https://discordapp.com/developers/applications/
 */
define('OAUTH2_CLIENT_ID', '');
define('OAUTH2_CLIENT_SECRET', '');

define("auth_url", "https://discordapp.com/api/oauth2/authorize");
define("token_url", "https://discordapp.com/api/oauth2/token");
define("url_base", "https://discordapp.com/api/users/@me");

/**
 * For image uploads via the create/edit forms using QuillJS
 */
define("IMGUR_KEY", "");

/**
 * this is the folder you have it in. Should include starting and trailing slash.
 * if in root directory, should just be /
 */
define("base_url",  "/");

/**
 * sandbox or production
 */
define("paypal_mode", "sandbox");

/**
 * Create a PayPal app:
 * https://developer.paypal.com/developer/applications/create
 */
define("sandbox_key", 	    "");
define("production_key", 	"");

/**
 * SMTP Settings
 * Enter your own SMTP server settings here.
 */
define("SMTP_HOST", 		"");
define("SMTP_USER", 		"");
define("SMTP_PASS", 		"");
define("SMTP_SECURITY", 	"tls");
define("SMTP_PORT", 		587);
define("SMTP_FROM_EMAIL", 	"");
define("SMTP_FROM_NAME", 	'');
define("SMTP_HTML", 		true);