<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../github/PracticeGithub.php';

use GitHubWebhook\Handler;

$config = require __DIR__ . '/../../../config/config.php';

try {
    $handler = new Handler($config['github']['webhook_secret'], __DIR__);
    $github = new PracticeGithub($config);

    if($handler->validate()) {
        $github->run($handler->getData(), $handler->getDelivery(), $handler->getEvent());
    } else {
        echo 'Wrong secret, too bad.';
    }

} catch (\Exception $e) {
    echo $e;
}
