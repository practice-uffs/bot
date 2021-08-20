<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../telegram/Telegram.php';

use Treinetic\ImageArtist\lib\Image;

$config = require __DIR__ . '/../../config/config.php';

try {
    $assetsDir = __DIR__ . '/../../resources/assets/';
    $telegram = new Telegram($config);
    $chatId = '-1001315997119';

    $person = new Image($assetsDir . 'fernando-bevilacqua.jpg');
    $confetti = new Image($assetsDir . 'confetti.png');
    $hat = new Image($assetsDir . 'hat.png');
    
    $hat->scaleToWidth($person->getWidth() * 0.25);
    $hat->rotate(-40);
    $confetti->resize($person->getWidth() * 1.1, $person->getHeight() * 1.1);
    
    $person->merge($confetti, ($person->getWidth() - $confetti->getWidth()) / 2, ($person->getHeight() - $confetti->getHeight()) / 2);
    $person->merge($hat, $person->getWidth() - $hat->getWidth() - 10, -90);

    $tempImagePath = sys_get_temp_dir() . 'out.png';
    $person->save($tempImagePath,IMAGETYPE_PNG);

    $photo = fopen($tempImagePath, 'r');
    $response = $telegram->sendPhoto($chatId, $photo, '');

    if ($response->getStatusCode() != 200) {
        echo 'Hum, something wrong' . PHP_EOL;
    }

} catch (\Exception $e) {
    echo 'Problem: ' . $e->getMessage() . PHP_EOL;
}
