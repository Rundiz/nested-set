<?php


$dbConfig = require __DIR__ . DIRECTORY_SEPARATOR . 'db-config.php';


$PDO = new \PDO($dbConfig['dsn'], $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);


return $PDO;