<?php
require __DIR__ . '/includes.php';


$db = require __DIR__ . '/db-config.php';

if (!file_exists('taxonomy_unbuild_data.sql')) {
    throw new \Exception('demo sql file could not be found. (taxonomy_unbuild_data.sql)');
}

$NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $db, 'tablename' => $db['tablename']]);

$sql = file_get_contents('taxonomy_unbuild_data.sql');
$sql = str_replace('`taxonomy`', '`' . $db['tablename'] . '`', $sql);

// drop the table first.
$NestedSet->Database->PDO->query('DROP TABLE IF EXISTS `' . $db['tablename'] . '`');
$result = $NestedSet->Database->PDO->exec($sql);

if (is_int($result)) {
    echo 'The data in `' . $db['tablename'] . '` table has been reset.';
}

unset($db, $NestedSet, $result, $sql);