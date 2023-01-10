<?php
require __DIR__ . '/includes.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';
$NestedSet->rebuild();

$NestedSet->tableName = 'test_taxonomy2';
$NestedSet->idColumnName = 'tid';
$NestedSet->leftColumnName = 't_left';
$NestedSet->rightColumnName = 't_right';
$NestedSet->levelColumnName = 't_level';
$NestedSet->positionColumnName = 't_position';
$NestedSet->rebuild();

$sql = 'SELECT `' . $NestedSet->idColumnName . '`, `' . $NestedSet->rightColumnName . '` FROM `' . $NestedSet->tableName . '` WHERE `' . $NestedSet->idColumnName . '` = :id';
$Sth = $PDO->prepare($sql);
$Sth->bindValue(':id', 3, \PDO::PARAM_INT);
$Sth->execute();
$row = $Sth->fetch();
$Sth->closeCursor();
unset($sql, $Sth);
if ($row != null && $row->{$NestedSet->rightColumnName} == '62') {
    echo 'Re-build correctly!';
} else {
    echo 'Re-build incorrect!';
}
unset($row);

$sql = 'SELECT * FROM `' . $NestedSet->tableName . '` ORDER BY `' . $NestedSet->levelColumnName . '` ASC';
$Sth = $PDO->prepare($sql);
$Sth->execute();
$result = $Sth->fetchAll();
$Sth->closeCursor();
unset($sql, $Sth);
echo '<pre>'.print_r($result, true).'</pre>';
unset($result);

unset($db, $NestedSet);