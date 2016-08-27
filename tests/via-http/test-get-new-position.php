<?php
require __DIR__ . '/includes.php';


$db = require __DIR__ . '/db-config.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $db, 'tablename' => $db['tablename']]);

echo 'Get new position from parent id 4.<br>';
echo 'ID 4 is name 2.1 and the result should be 4.<br>';
echo 'Result: ' . $NestedSet->getNewPosition(4);

unset($db, $NestedSet);