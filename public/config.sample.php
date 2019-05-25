<?php
    define("timezone", "America/Chicago");
    date_default_timezone_set(timezone);

    /**
     * Displayed site-wide in the header/footer
     */
    define("server_name", "RuneCore");
    
    /**
     * Database credentials...self explanatory.
     */
	define("host",      "localhost");
	define("username", 	"");
	define("password", 	"");
	define("dbname", 	"");


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
    define("SMTP_HOST",       "");
    define("SMTP_USER", 	  "");
    define("SMTP_PASS", 	  "");
    define("SMTP_SECURITY",   "tls");
    define("SMTP_PORT", 	  587);
    define("SMTP_FROM_EMAIL", "");
    define("SMTP_FROM_NAME",  '');
    define("SMTP_HTML", 	  true);