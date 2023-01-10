<?php
/**
 * Test DB configuration.<br>
 * Please edit your db configuration here before start testing.
 * 
 * If you don't have example data, please create database and import test-database-structure.sql into the database.
 */


$db['dsn'] = 'mysql:dbname=github_rundiz_nested-set;host=localhost;port=3306;charset=UTF8';
$db['username'] = 'user';
$db['password'] = 'pass';
$db['options'] = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, // throws PDOException.
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
    \PDO::ATTR_STRINGIFY_FETCHES => true,// fix PHP 8.1+ that number (string type) will becomes int.
];


return $db;