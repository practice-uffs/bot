<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/PracticeBot.php';

$config = require __DIR__ . '/../../config/config.php';

$team_sreadsheet_url = $config['google_drive']['team_spreadsheet_url'];
print_r($team_sreadsheet_url);

$members = PracticeBot::getGoogleDriveSpreadsheetByUrl($team_sreadsheet_url);






