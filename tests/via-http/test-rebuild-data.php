<?php
require __DIR__ . '/includes.php';


$db = require __DIR__ . '/db-config.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $db, 'tablename' => $db['tablename']]);

$NestedSet->rebuild();

$sql = 'SELECT `' . $NestedSet->id_column_name . '`, `' . $NestedSet->right_column_name . '` FROM `' . $NestedSet->table_name . '` WHERE `' . $NestedSet->id_column_name . '` = :id';
$stmt = $NestedSet->Database->PDO->prepare($sql);
$stmt->bindValue(':id', 3, \PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch();
unset($sql, $stmt);
if ($row != null && $row->{$NestedSet->right_column_name} == '40') {
    echo 'Re-build correctly!';
} else {
    echo 'Re-build incorrect!';
}
unset($row);

$sql = 'SELECT * FROM `' . $NestedSet->table_name . '` ORDER BY `' . $NestedSet->level_column_name . '` ASC';
$stmt = $NestedSet->Database->PDO->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll();
unset($sql, $stmt);
echo '<pre>'.print_r($result, true).'</pre>';
unset($result);

unset($db, $NestedSet);