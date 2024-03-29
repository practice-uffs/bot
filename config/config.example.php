<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This file contains all the configuration options for the PHP Telegram Bot.
 *
 * It is based on the configuration array of the PHP Telegram Bot Manager project.
 *
 * Simply adjust all the values that you need and extend where necessary.
 *
 * Options marked as [Manager Only] are only required if you use `manager.php`.
 *
 * For a full list of all options, check the Manager Readme:
 * https://github.com/php-telegram-bot/telegram-bot-manager#set-extra-bot-parameters
 */

return [
    // Add you bot's API key and name
    'api_key'      => 'your:bot_api_key',
    'bot_username' => 'username_bot', // Without "@"

    // [Manager Only] Secret key required to access the webhook
    'secret'       => 'super_secret',

    // When using the getUpdates method, this can be commented out
    'webhook'      => [
        'url' => 'https://your-domain/path/to/hook-or-manager.php',
        // Use self-signed certificate
        // 'certificate'     => __DIR__ . '/path/to/your/certificate.crt',
        // Limit maximum number of connections
        // 'max_connections' => 5,
    ],

    // All command related configs go here
    'commands'     => [
        // Define all paths for your custom commands
        'paths'   => [
            // __DIR__ . '/telegram/Commands',
        ],
        // Here you can set any command-specific parameters
        'configs' => [
            // - Google geocode/timezone API key for /date command (see DateCommand.php)
            // 'date'    => ['google_api_key' => 'your_google_api_key_here'],
            // - OpenWeatherMap.org API key for /weather command (see WeatherCommand.php)
            // 'weather' => ['owm_api_key' => 'your_owm_api_key_here'],
            // - Payment Provider Token for /payment command (see Payments/PaymentCommand.php)
            // 'payment' => ['payment_provider_token' => 'your_payment_provider_token_here'],
        ],
    ],

    'google_drive' => [
        'tasks_folder_id' => '', // id da pasta "Tarefas" usada pela equipe no Google Drive
        'team_spreadsheet_url' => '', // url da planilha de membros no Google Drive
    ],
    
    'chats' => [
        'todos' => -1001315997119, // id do grupo "Practice @TODOS"
        'lideres' => -1001180892973, // id do grupo "Practice @Líderes"
    ],

    // API key allowed to use owr own API
    'api_password' => '',

    'github'       => [
        'webhook_secret' => '', // secret informado no painel de webhook do Github
        'username'       => '', // nome de usuário que fará as interações como um bot, ex.: 'dovyski'
        'token'          => ''  // token de acesso do usuário informado acima.
    ],

    // Define all IDs of admin users
    'admins'       => [
        1231890240, // Fernando Bevilacqua
    ],

    // Enter your MySQL database credentials
    // 'mysql'        => [
    //     'host'     => '127.0.0.1',
    //     'user'     => 'root',
    //     'password' => 'root',
    //     'database' => 'telegram_bot',
    // ],

    // Logging (Debug, Error and Raw Updates)
    // 'logging'  => [
    //     'debug'  => __DIR__ . '/php-telegram-bot-debug.log',
    //     'error'  => __DIR__ . '/php-telegram-bot-error.log',
    //     'update' => __DIR__ . '/php-telegram-bot-update.log',
    // ],

    // Set custom Upload and Download paths
    'paths'        => [
        'download' => __DIR__ . '/telegram/Download',
        'upload'   => __DIR__ . '/telegram/Upload',
    ],

    // Requests Limiter (tries to prevent reaching Telegram API limits)
    'limiter'      => [
        'enabled' => true,
    ],
];
