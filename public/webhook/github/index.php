<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../github/PracticeGithub.php';
require_once __DIR__ . '/../../../drive/PracticeGoogleDrive.php';

$config = require __DIR__ . '/../../../config/config.php';

try {
    $github = new PracticeGithub($config);
    $github->run();

} catch (\Exception $e) {
    echo $e;
}
