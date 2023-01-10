<?php
/** 
 * @license http://opensource.org/licenses/MIT MIT
 */

$id = ($_GET['id'] ?? null);

if (!is_numeric($id)) {
    http_response_code(400);
    echo 'Invalid ID!';
    exit();
}

require dirname(__DIR__) . '/includes.php';

/* @var $PDO \PDO */
$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';

// get selected data. --------------------------------------------
// this data will be use in the form.
$sql = 'SELECT * FROM `' . $NestedSet->tableName . '` WHERE `' . $NestedSet->idColumnName . '` = :id';
$Stmt = $PDO->prepare($sql);
unset($sql);
$Stmt->bindValue(':id', $id, \PDO::PARAM_INT);
$Stmt->execute();
$result = $Stmt->fetchObject();
if (false === $result) {
    http_response_code(404);
    echo 'Not found selected ID.';
    exit();
}
// set DB values to variables.
$name = $result->name;
$parent_id = $result->{$NestedSet->parentIdColumnName};
$position = $result->{$NestedSet->positionColumnName};
unset($result);
// end get selected data. --------------------------------------

// query all items for select box.
$options = [];
$options['unlimited'] = true;
$list_txn = $NestedSet->listTaxonomyFlatten($options);
unset($options);

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // if form submitted.
    // process editing data.
    $parent_id = ($_POST['parent_id'] ?? 0);
    $parent_id = strip_tags(trim($parent_id));
    if (!is_numeric($parent_id)) {
        $parent_id = 0;
    }

    $name = ($_POST['name'] ?? '');
    $name = strip_tags(trim($name));
    if (empty($name)) {
        $errorMsg = 'Please enter name.';
    }

    $position = ($_POST['position'] ?? $position);
    $position = strip_tags(trim($position));

    if (!isset($errorMsg) || empty($errorMsg)) {
        if ($NestedSet->isParentUnderMyChildren($id, $parent_id) === true) {
            $errorMsg = 'Parent must not be under the children. Please select another parent.';
        }
    }

    if (!isset($errorMsg) || empty($errorMsg)) {
        // if there is no error.
        $result = [];

        // process the update.
        $sql = 'UPDATE `' . $NestedSet->tableName . '` SET `' . $NestedSet->parentIdColumnName . '` = :parent_id, `name` = :name, `' . $NestedSet->positionColumnName . '` = :position WHERE `' . $NestedSet->idColumnName . '` = :id';
        $Stmt = $PDO->prepare($sql);
        unset($sql);

        // execute update command
        $Stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $Stmt->bindValue(':parent_id', $parent_id, \PDO::PARAM_INT);
        $Stmt->bindValue(':name', $name);
        $Stmt->bindValue(':position', $position, \PDO::PARAM_INT);
        $result['execute'] = $Stmt->execute();
        unset($Stmt);

        // process position numbers on the same parent. -----------------------
        // this will be increase next item position +1 but not re-position all of them.
        // you still have to manually process it later to prevent position not start from 1.
        $listTxnForUpdatePosition = $NestedSet->listTaxonomy([
            'filter_parent_id' => $parent_id,
            'where' => [
                'whereString' => '(`parent`.`' . $NestedSet->positionColumnName . '` >= :position AND `parent`.`' . $NestedSet->idColumnName . '` != :id)',
                'whereValues' => [':position' => $position, ':id' => $id],
            ],
        ]);
        $result['executeUpdatePosition'] = [];
        if (isset($listTxnForUpdatePosition['items']) && is_array($listTxnForUpdatePosition['items'])) {
            $newPosition = ($position + 1);
            foreach ($listTxnForUpdatePosition['items'] as $row) {
                $sql = 'UPDATE `' . $NestedSet->tableName . '` SET `' . $NestedSet->positionColumnName . '` = :position WHERE `' . $NestedSet->idColumnName . '` = :id';
                $Stmt = $PDO->prepare($sql);
                unset($sql);
                $Stmt->bindValue(':position', $newPosition, \PDO::PARAM_INT);
                $Stmt->bindValue(':id', $row->{$NestedSet->idColumnName}, \PDO::PARAM_INT);
                $result['executeUpdatePosition'][$row->{$NestedSet->idColumnName}] = $Stmt->execute();
                unset($Stmt);
                $newPosition++;
            }// endforeach;
            unset($newPosition, $row);
        }
        unset($listTxnForUpdatePosition);
        // end process position numbers on the same parent. -------------------

        // last step: rebuild
        $NestedSet->rebuild();
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Edit data</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h1>Edit data</h1>
        <form method="post">
            <?php if (isset($errorMsg) && !empty($errorMsg)) { ?> 
            <div class="form-alert error"><?php echo $errorMsg; ?></div>
            <?php }// endif; $errorMsg ?> 

            <?php if (isset($result)) { ?> 
            <pre><?php var_export($result); ?></pre>
            <?php }// endif; $result ?> 

            <div class="form-group">
                <label for="parent_id">Parent:</label>
                <select id="parent_id" name="parent_id">
                    <option value="0"<?php if (isset($parent_id) && $parent_id == '0') {echo ' selected="selected"';} ?>>&mdash; NONE &mdash;</option>
                    <?php 
                    if (isset($list_txn['items']) && is_array($list_txn['items'])) {
                        foreach ($list_txn['items'] as $row) {
                            echo '<option value="' . $row->{$NestedSet->idColumnName} . '"';
                            if (isset($parent_id) && $parent_id == $row->{$NestedSet->idColumnName}) {
                                echo ' selected="selected"';
                            }
                            echo '>';
                            if ($row->{$NestedSet->levelColumnName} > 1) {
                                echo str_repeat(' &nbsp; &nbsp;', ($row->{$NestedSet->levelColumnName} - 1));
                                echo '|&mdash;';
                            }
                            echo $row->name;
                            echo '</option>' . PHP_EOL;
                        }// endforeach;
                        unset($row);
                    }
                    unset($list_txn);
                    ?> 
                </select>
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input id="name" type="text" name="name" value="<?php if (isset($name)) {echo htmlspecialchars($name, ENT_QUOTES);} ?>">
            </div>
            <div class="form-group">
                <label for="position">Position:</label>
                <input id="position" type="number" name="position" value="<?php if (isset($position)) {echo htmlspecialchars($position, ENT_QUOTES);} ?>" step="1">
                <p class="description">Please note that the position numbers on all other items must be manually process one by one.</p>
            </div>
            <button type="submit">Submit</button>
            <a href="./">Back to listing page</a>
        </form>
    </body>
</html>
<?php
unset($NestedSet);