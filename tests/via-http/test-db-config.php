<?php
require __DIR__ . '/includes.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';
$result = $PDO->query('SELECT * FROM `' . $NestedSet->tableName . '`');

echo 'Hooray! Db config is correct.';

unset($db, $NestedSet, $result);