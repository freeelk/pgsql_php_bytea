<?php
//  Query string in browser: /delete.php?id=1
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

use PostgreSQLTutorial\Connection as Connection;
use PostgreSQLTutorial\BlobDB as BlobDB;

$pdo = Connection::get()->connect();
$blobDB = new BlobDB($pdo);
$blobDB->delete($_REQUEST['id']);