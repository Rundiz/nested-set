<?php
require __DIR__ . '/includes.php';


$db = require __DIR__ . '/db-config.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $db, 'tablename' => $db['tablename']]);

echo 'Get all children of item name <strong>Root 3</strong> (ID 3).<br>' . "\n";
$options = [];
$options['filter_taxonomy_id'] = 3;
$list_txn = $NestedSet->getTaxonomyWithChildren($options);
echo '<pre>'.print_r($list_txn, true).'</pre>'."\n";
unset($options);

echo '<hr>'."\n\n";

echo 'Get all children of item name <strong>Root 2.1.1</strong> (ID 9).<br>' . "\n";
$options = [];
$options['filter_taxonomy_id'] = 9;
$list_txn = $NestedSet->getTaxonomyWithChildren($options);
echo '<pre>'.print_r($list_txn, true).'</pre>'."\n";
unset($options);

unset($db, $NestedSet, $list_txn);