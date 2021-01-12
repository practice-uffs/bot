<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../drive/PracticeGoogleDrive.php';

$config = require __DIR__ . '/../../config/config.php';

$drive = new PracticeGoogleDrive();
$service = $drive->getService();

// Print the names and IDs for up to 10 files.
$optParams = array(
  'pageSize' => 10,
  'fields' => 'nextPageToken, files(id, name)'
);
$results = $service->files->listFiles($optParams);

if (count($results->getFiles()) == 0) {
    print "No files found.\n";
} else {
    print "Files:\n";
    foreach ($results->getFiles() as $file) {
        printf("%s (%s)\n", $file->getName(), $file->getId());
    }
}

try {
    $folderMeta = new Google_Service_Drive_DriveFile();

    $folderMeta->setName('testing');
    $folderMeta->setMimeType('application/vnd.google-apps.folder');
    $folderMeta->parents[] = '1oIY0h4_pNKWUhfTHJKUqccQ7qoVoW8_z';
    
    $folder = $service->files->create($folderMeta);
    
    var_dump($folder->getId());

} catch(\Exception $e) {
    echo $e;
}

