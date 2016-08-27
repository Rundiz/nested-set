<?php
require __DIR__ . '/includes.php';


$db = require __DIR__ . '/db-config.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $db, 'tablename' => $db['tablename']]);

echo '<a href="test-list-taxonomy.php" target="list_referrer">Open referrer</a><br><br>'."\n\n";


echo 'Assuming that the editing taxonomy is name 2.1.1 (ID 9).<br>'."\n";
echo 'Changing its parent to 2.1.1.1 (ID 12). Is it under my children? (False means correct, True means incorrect.)<br>';
var_dump($NestedSet->isParentUnderMyChildren(9, 12));// true

echo '<br>'."\n";
echo 'Changing its parent to 2.1.1.3 (ID 14): ';
var_dump($NestedSet->isParentUnderMyChildren(9, 14));// true

echo '<br>'."\n";
echo 'Changing its parent to 2.1 (ID 4): ';
var_dump($NestedSet->isParentUnderMyChildren(9, 4));// false

echo '<br>'."\n";
echo 'Changing its parent to 2.4 (ID 7): ';
var_dump($NestedSet->isParentUnderMyChildren(9, 7));// false

echo '<br>'."\n";
echo 'Changing its parent to 3.3.3 (ID 30): ';
var_dump($NestedSet->isParentUnderMyChildren(9, 20));// false

unset($db, $NestedSet, $result);