<?php
require __DIR__ . '/includes.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';

// reset db -----------------------------------------------------------------------------------
if (!file_exists(dirname(__DIR__) . '/common/demo-data.sql')) {
    throw new \Exception('demo sql file could not be found. (demo-data.sql)');
}
$sql = file_get_contents(dirname(__DIR__) . '/common/demo-data.sql');
$sql = str_replace('`taxonomy`', '`' . $NestedSet->tableName . '`', $sql);
// empty the table first.
$PDO->query('TRUNCATE TABLE `' . $NestedSet->tableName . '`');
$PDO->exec($sql);
// also rebuild data too.
$NestedSet->rebuild();
unset($sql);
// end reset db ------------------------------------------------------------------------------

echo 'Deleting taxonomy <strong>3.2</strong> (ID 16) with its ALL children.<br>'."\n";
$NestedSet->deleteWithChildren(16);
$NestedSet->rebuild();
echo '<pre>'.print_r($NestedSet->listTaxonomyFlatten(['unlimited' => true]), true).'</pre>'."\n\n";

// reset db -----------------------------------------------------------------------------------
if (!file_exists(dirname(__DIR__) . '/common/demo-data.sql')) {
    throw new \Exception('demo sql file could not be found. (demo-data.sql)');
}
$sql = file_get_contents(dirname(__DIR__) . '/common/demo-data.sql');
$sql = str_replace('`taxonomy`', '`' . $NestedSet->tableName . '`', $sql);
// empty the table first.
$PDO->query('TRUNCATE TABLE `' . $NestedSet->tableName . '`');
$PDO->exec($sql);
// also rebuild data too.
$NestedSet->rebuild();
unset($sql);
// end reset db ------------------------------------------------------------------------------

unset($db, $NestedSet);