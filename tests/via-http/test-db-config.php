<?php
require __DIR__ . '/includes.php';


$db = require __DIR__ . '/db-config.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $db, 'tablename' => $db['tablename']]);
$result = $NestedSet->Database->PDO->query('SELECT * FROM `' . $db['tablename'] . '`');

echo 'Hooray! Db config is correct.';

unset($db, $NestedSet, $result);