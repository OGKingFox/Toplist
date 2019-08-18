<?php

$base_path = "/home/yoursite/public_html"; # an absolute path to your project's web root.

$settings = [
    'general' => [
        'site_name' => 'Rune-Nexus' # Your sites name, not yet used.
    ],
    'database' => [
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname'   => '',
    ],
    'paypal' => [ # https://developer.paypal.com/docs/checkout/
        'mode'       => 'sandbox',
        'sandbox'    => '',
        'production' => ''
    ],
    'recaptcha' => [ # https://www.google.com/recaptcha/intro/v3.html
        'public'  => '',
        'private' => ''
    ],
    'discord' => [ # https://discordapp.com/developers/docs/intro
        'oauth' => [
            'client_id'     => '',
            'client_secret' => '',
            'redirect_uri'  => 'http://localhost/login/auth',
            'auth_url'      => 'https://discordapp.com/api/oauth2/authorize',
            'token_url'     => 'https://discordapp.com/api/oauth2/token',
            'url_base'      => 'https://discordapp.com/api/users/@me',
        ],
        'webhook'   => '', # webhook url for non-bot stuff.
        'server_id' => '', # your server id.
        'bot_key'   => '', # your bot's private key.
    ],
    'imgur' => [ # https://apidocs.imgur.com/?version=latest
        'api_key' => ''
    ],
    'core' => [
        'base_url'   => '/', # include trailing and ending/
        'timezone'   => 'America/Chicago',
        'cookie_key' => '', # a secure key for encrypting cookies. optimally 64 length.
        'views' => [
            'directory'  => $base_path.'/app/views/',
            'cache_path' => $base_path.'/app/compiled/',
            'templates'  => $base_path.'/app/compiled/templates/',
            'extension'  => '.compiled',
            'expires'    => 43200, # 12 hours
        ],
        'volt_options' => [
            'compiledPath'      => $base_path.'/app/compiled/templates/',
            'compiledExtension' => '.compiled',
        ],
        'paths' => [
            $base_path.'/app/controllers/',
            $base_path.'/app/models/',
            $base_path.'/app/models/tools/',
            $base_path.'/app/plugins/',
            $base_path.'/Library/',
        ],
        'classes' => [
            'PHPMailer'	    => $base_path.'/Library/PHPMailer/class.phpmailer.php',
            'SMTP'	        => $base_path.'/Library/PHPMailer/class.smtp.php',
            'CustomRouter'  => $base_path.'/app/CustomRouter.php',
            'VoltExtension' => $base_path.'/app/VoltExtension.php',
        ],
        'files' => [
            $base_path.'/Library/HTMLPurifier/HTMLPurifier.standalone.php',
            $base_path.'/Library/discord/NexusBot.php',
            $base_path.'/Library/discord/helpers/BotMessage.php',
            $base_path.'/Library/discord/helpers/UserActions.php'
        ],
    ],
    'smtp' => [ # SMTP Server Settings
        'SMTP_HOST' => 'in-v3.mailjet.com',
        'SMTP_USER' => '',
        'SMTP_PASS' => '',
        'SMTP_SECURITY' => 'tls',
        'SMTP_PORT' => 587,
        'SMTP_FROM_EMAIL' => '',
        'SMTP_FROM_NAME' => 'Your site',
        'SMTP_HTML' => true,
    ]
];