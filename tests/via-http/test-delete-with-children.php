<?php
require __DIR__ . '/includes.php';


$db = require __DIR__ . '/db-config.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $db, 'tablename' => $db['tablename']]);

// reset db -----------------------------------------------------------------------------------
if (!file_exists('taxonomy_unbuild_data.sql')) {
    throw new \Exception('demo sql file could not be found. (taxonomy_unbuild_data.sql)');
}
$sql = file_get_contents('taxonomy_unbuild_data.sql');
$sql = str_replace('`taxonomy`', '`' . $db['tablename'] . '`', $sql);
// drop the table first.
$NestedSet->Database->PDO->query('DROP TABLE IF EXISTS `' . $db['tablename'] . '`');
$NestedSet->Database->PDO->exec($sql);
// also rebuild data too.
$NestedSet->rebuild();
unset($sql);
// end reset db ------------------------------------------------------------------------------

echo 'Deleting taxonomy <strong>3.2</strong> (ID 16) with its ALL children.<br>'."\n";
$NestedSet->deleteWithChildren(16);
$NestedSet->rebuild();
echo '<pre>'.print_r($NestedSet->listTaxonomyFlatten(['unlimited' => true]), true).'</pre>'."\n\n";

// reset db -----------------------------------------------------------------------------------
if (!file_exists('taxonomy_unbuild_data.sql')) {
    throw new \Exception('demo sql file could not be found. (taxonomy_unbuild_data.sql)');
}
$sql = file_get_contents('taxonomy_unbuild_data.sql');
$sql = str_replace('`taxonomy`', '`' . $db['tablename'] . '`', $sql);
// drop the table first.
$NestedSet->Database->PDO->query('DROP TABLE IF EXISTS `' . $db['tablename'] . '`');
$NestedSet->Database->PDO->exec($sql);
// also rebuild data too.
$NestedSet->rebuild();
unset($sql);
// end reset db ------------------------------------------------------------------------------

unset($db, $NestedSet);