<?php
require __DIR__ . '/includes.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';

echo 'Get new position from parent id 4.<br>';
echo 'ID 4 is name 2.1 and the result should be 4.<br>';
echo 'Result: ' . $NestedSet->getNewPosition(4);

unset($db, $NestedSet);