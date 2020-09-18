<?php
require __DIR__ . '/includes.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';

if (!file_exists(dirname(__DIR__) . '/common/demo-data.sql')) {
    throw new \Exception('demo sql file could not be found. (common/demo-data.sql)');
}

// empty tables.
$sql = 'TRUNCATE TABLE `test_taxonomy`';
$PDO->exec($sql);
$sql = 'TRUNCATE TABLE `test_taxonomy2`';
$PDO->exec($sql);
unset($sql);

$sqlFileContents = file_get_contents(dirname(__DIR__) . '/common/demo-data.sql');
$expSql = explode(';', $sqlFileContents);
unset($sqlFileContents);
if (is_array($expSql)) {
    foreach ($expSql as $sql) {
        if (!empty($sql)) {
            $result = $PDO->exec($sql);
        }
    }
    unset($sql);
}
unset($expSql);
// rebuild first table.
$NestedSet->rebuild();
// rebuild 2nd table.
$NestedSet->tableName = 'test_taxonomy2';
$NestedSet->idColumnName = 'tid';
$NestedSet->leftColumnName = 't_left';
$NestedSet->rightColumnName = 't_right';
$NestedSet->levelColumnName = 't_level';
$NestedSet->positionColumnName = 't_position';
$NestedSet->rebuild();
$NestedSet->restoreColumnsName();

echo 'Success! The data was imported and rebuilt successfully.';