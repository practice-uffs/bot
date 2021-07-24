<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../telegram/Telegram.php';

use Treinetic\ImageArtist\lib\Image;

$config = require __DIR__ . '/../config/config.php';

try {
    $telegram = new Telegram($config);
    //$reponse = $telegram->sendMessage('-1001252002318', 'https://www.carspecs.us/photos/c8447c97e355f462368178b3518367824a757327-2000.jpg');

    $person = new Image(__DIR__ . '/fernando-bevilacqua.png');
    $confetti = new Image(__DIR__ . '/confetti.png');
    $hat = new Image(__DIR__ . '/hat.png');
    
    $hat->scaleToWidth($person->getWidth() * 0.25);
    $hat->rotate(-40);
    $confetti->resize($person->getWidth() * 1.1, $person->getHeight() * 1.1);
    
    $person->merge($confetti, ($person->getWidth() - $confetti->getWidth()) / 2, ($person->getHeight() - $confetti->getHeight()) / 2);
    $person->merge($hat, $person->getWidth() - $hat->getWidth() - 10, -50);

    $person->save(__DIR__ . '/out.png',IMAGETYPE_PNG);

    $photo = fopen(__DIR__ . '/out.png', 'r');
    $reponse = $telegram->sendPhoto('-1001252002318', $photo, 'user.png');

    if ($reponse->getStatusCode() != 200) {
        echo 'Hum, something wrong' . PHP_EOL;
    }

} catch (\Exception $e) {
    echo 'Problem: ' . $e->getMessage() . PHP_EOL;
}
