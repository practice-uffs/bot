<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../app/PracticeBot.php';
require_once __DIR__ . '/../../../telegram/Telegram.php';

$config = require __DIR__ . '/../../../config/config.php';

function error($message) {
    echo json_encode(['error' => $message]);
    exit();
}

function assertHasValidApiKey($config) {
    $apiKey = isset($_REQUEST['api_key']) ? $_REQUEST['api_key'] : null;
    $localApiPassword = @$config['api_password'];

    if (empty($localApiPassword)) {
        error('Api password has not been defined in config.php');
    }

    if (empty($apiKey) || $apiKey !== $localApiPassword) {
        error('Invalid API key');
    }
}

$action  = isset($_REQUEST['action'])  ? $_REQUEST['action']  : 'message';
$group   = isset($_REQUEST['group'])   ? $_REQUEST['group']   : null;
$chatId  = isset($_REQUEST['chat_id']) ? $_REQUEST['chat_id'] : null;
$message = isset($_REQUEST['message']) ? $_REQUEST['message'] : null;
$photo   = isset($_REQUEST['photo'])   ? $_REQUEST['photo']   : null;

header('Content-Type: application/json');
assertHasValidApiKey($config);

if (isset($group)) {
    if (!isset($config['chats'][$group])) {
        error('Group not found: ' . $group);
    }
    $chatId = $config['chats'][$group];
}

if (empty($chatId)) {
    error('Chat is empty');
}

$telegram = new Telegram($config);

try {
    switch($action) {
    case 'message':
        if (empty($message)) {
            error('Message is empty');
        }
        $response = $telegram->sendMessage($chatId, $message);
        break;
    case 'photo':
        if (empty($photo)) {
            error('Photo is empty');
        }
        $response = $telegram->sendPhoto($chatId, $photo, $message);
        break;
    default:
        error('Unknown action: ' . $action);
    }

    if ($response->getStatusCode() != 200) {
        error('Unable to send message: ' . $response->getBody());
    }
    
    echo json_encode(['ok' => true, 'action' => $action]);

} catch (Exception $e) {
    error($e->getMessage());
}

?>