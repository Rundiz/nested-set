<?php
require __DIR__ . '/includes.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';

echo 'Get parents of <strong>2.1.1.2</strong> (ID 13)<br>'."\n";
echo '<pre>'.print_r($NestedSet->getTaxonomyWithParents(['filter_taxonomy_id' => 13]), true).'</pre>'."\n";
echo "\n\n\n";

echo 'Get parents by <strong>search</strong> and <strong>skip current</strong> of <strong>3.2.1</strong> (ID18)<br>'."\n";
$options = [];
$options['search']['columns'] = ['name'];
$options['search']['searchValue'] = '3.2.1';
$options['skipCurrent'] = true;
echo '<pre>'.print_r($NestedSet->getTaxonomyWithParents($options), true).'</pre>'."\n";
unset($options);

unset($db, $NestedSet);