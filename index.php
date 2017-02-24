<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

$fileNameTxt =  'hosts.txt';

use PostgreSQLTutorial\Connection as Connection;
use PostgreSQLTutorial\BlobDB as BlobDB;

try {
    // connect to the PostgreSQL database
    $pdo = Connection::get()->connect();
    //
    $blobDB = new BlobDB($pdo);

    $fileId = $blobDB->insert(3, $fileNameTxt, 'text/plain', 'assets/texts/' . $fileNameTxt);
    echo '<br>A file has been inserted with id ' . $fileId;
} catch (\PDOException $e) {
    echo $e->getMessage();
}