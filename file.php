<?php
//  Query string in browser: /file.php?id=1
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

use PostgreSQLTutorial\Connection as Connection;
use PostgreSQLTutorial\BlobDB as BlobDB;

$pdo = Connection::get()->connect();
$blobDB = new BlobDB($pdo);

// get document id from the query string
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$file = $blobDB->read($id);

