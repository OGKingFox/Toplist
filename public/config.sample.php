<?php
/**
 * Forces a timezone change incase your server isn't configured to your timezone
 */
define("timezone", "America/Chicago");
date_default_timezone_set(timezone);

/**
 * Database credentials...self explanatory.
 */
define("host",      "");
define("username", 	"");
define("password", 	"");
define("dbname", 	"");

/**
 * Discord OAuth Credentials
 * https://discordapp.com/developers/applications/
 */
define('OAUTH2_CLIENT_ID', '');
define('OAUTH2_CLIENT_SECRET', '');

/**
 * OAuth Login URL. Edit with your own URL.
 */
define("redirect_uri", "http://localhost/toplist/login/auth");

define("auth_url", "https://discordapp.com/api/oauth2/authorize");
define("token_url", "https://discordapp.com/api/oauth2/token");
define("url_base", "https://discordapp.com/api/users/@me");

/**
 * Webhook URL for the Bot
 */
define("webhook_url", "");

/**
 * For image uploads via the create/edit forms using QuillJS
 */
define("IMGUR_KEY", "");

/**
 * Google ReCaptcha Keys
 */
define("CAPTCHA_PUBLIC", "");
define("CAPTCHA_PRIVATE", "");

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
 * Enter your own SMTP server settings here or use the ones provided. doesnt matter to me :P
 */
define("SMTP_HOST", 		"");
define("SMTP_USER", 		"");
define("SMTP_PASS", 		"");
define("SMTP_SECURITY", 	"tls");
define("SMTP_PORT", 		587);
define("SMTP_FROM_EMAIL", 	"");
define("SMTP_FROM_NAME", 	'');
define("SMTP_HTML", 		true);