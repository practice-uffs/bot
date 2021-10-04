<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../telegram/Telegram.php';
require_once __DIR__ . '/../../app/PracticeBot.php';

use Treinetic\ImageArtist\lib\Image;

date_default_timezone_set('America/Sao_Paulo');

function getMembersInfo($config) {
    $team_sreadsheet_url = $config['google_drive']['team_spreadsheet_url'];
    $team = PracticeBot::getGoogleDriveSpreadsheetByUrl($team_sreadsheet_url);

    return $team;
}

function isBirthdayToday($birthday_str) {
    $birthday_parts = explode('/', $birthday_str);

    if (count($birthday_parts) != 3) {
        return false;
    }

    $birthday_day = $birthday_parts[0];
    $birthday_month = $birthday_parts[1];

    $is_birthday_today = (int) $birthday_day == date('d') &&
                         (int) $birthday_month == date('m');
    
    return $is_birthday_today;
}

function printLog($message) {
    echo '[INFO] ' . date('d/m/Y h:i:s') . ' ' . $message . PHP_EOL;
}

function checkTodaysBirthdays($config) {
    printLog("Checking birthdays");

    if (!isset($config['chats']['todos'])) {
        throw new Exception('No chats defined for group "todos"');
    }

    $chatId = $config['chats']['todos'];
    $team = getMembersInfo($config);

    foreach($team as $member) {
        $birthday = isset($member['birthday']) ? $member['birthday'] : '';

        if (!isBirthdayToday($birthday)) {
            continue;
        }

        printLog("We have a birthday! " . print_r($member, true));
        sendBirthDayMessage($config, $member, $chatId);
    }

    printLog("Done checking birthdays!");
}

function sendBirthDayMessage($config, $member, $chatId) {
    $assetsDir = __DIR__ . '/../../resources/assets/';
    $profilePath = $assetsDir . 'practice-square.png';

    if (!empty($member['profile_url'])) {
        $profilePath = $member['profile_url'];
    }

    $person = new Image($profilePath);
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
    $message = "Pessoal, temos aniversário pra comemorar! 🙌🥳🤗 $member[name], do curso $member[enrollment] ($member[place]), pertencente à equipe $member[team] no Practice, faz aniversário hoje! Juntem-se a mim para dar os parabéns! Felicidade, saúde, conquistas e bons caminhos 🥳🎈🎉🍰";
    
    printLog("Sending message " . print_r([
        'chatId' => $chatId,
        'message' => $message
    ], true));
   
    $telegram = new Telegram($config);
    $response = $telegram->sendPhoto($chatId, $photo, $message);

    if ($response->getStatusCode() != 200) {
        echo 'Hum, something wrong' . PHP_EOL;
    }
}

// Global configs
$config = require __DIR__ . '/../../config/config.php';

try {
    checkTodaysBirthdays($config);

} catch (\Exception $e) {
    echo 'Problem: ' . $e->getMessage() . PHP_EOL;
}
