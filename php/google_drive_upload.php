<?php

require_once __DIR__ . '/../../vendor/autoload.php'; // Corrected path to autoload.php

function uploadToGoogleDrive($filePath) {
    $client = new Google_Client();
    $client->setAuthConfig('credentials.json');
    $client->addScope(Google_Service_Drive::DRIVE_FILE);

    $service = new Google_Service_Drive($client);

    $fileMetadata = new Google_Service_Drive_DriveFile(array(
        'name' => basename($filePath),
        'parents' => array('your-folder-id') // Optionally specify a folder
    ));

    $content = file_get_contents($filePath);

    $file = $service->files->create($fileMetadata, array(
        'data' => $content,
        'mimeType' => 'image/jpeg',
        'uploadType' => 'multipart',
    ));

    return 'https://drive.google.com/file/d/' . $file->id . '/view';
}
?>
