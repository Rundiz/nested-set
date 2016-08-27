<?php
/**
 * Test DB configuration.<br>
 * Please edit your db configuration here before start testing.
 * 
 * If you don't have example data, please create database and import taxonomy_empty_table.sql or taxonomy_unbuild_data.sql (choose one) into the table.
 */


$db['dsn'] = 'mysql:dbname=github_rundiz_nested-set;host=localhost;port=3306;charset=UTF8';
$db['username'] = 'user';
$db['password'] = 'pass';
$db['options'] = [
    \PDO::ATTR_EMULATE_PREPARES => false,
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION // throws PDOException.
];
$db['tablename'] = 'test_taxonomy';

return $db;