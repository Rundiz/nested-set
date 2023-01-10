<?php
/** 
 * @license http://opensource.org/licenses/MIT MIT
 */

require dirname(__DIR__) . '/includes.php';

/* @var $PDO \PDO */
$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';

// query all items for select box.
$options = [];
$options['unlimited'] = true;
$list_txn = $NestedSet->listTaxonomyFlatten($options);
unset($options);

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
    // if form submitted.
    // process add the data.
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

    if (!isset($errorMsg) || empty($errorMsg)) {
        // if there is no error.
        // get new position to insert.
        $position = $NestedSet->getNewPosition($parent_id);
        $result = [];

        $sql = 'INSERT INTO `' . $NestedSet->tableName . '` (`' . $NestedSet->parentIdColumnName . '`, `name`, `' . $NestedSet->positionColumnName . '`) VALUES';
        $sql .= ' (:parent_id, :name, :position)';
        $Stmt = $PDO->prepare($sql);
        unset($sql);

        // execute insert command.
        $Stmt->bindValue(':parent_id', $parent_id, \PDO::PARAM_INT);
        $Stmt->bindValue(':name', $name);
        $Stmt->bindValue(':position', $position, \PDO::PARAM_INT);
        $result['execute'] = $Stmt->execute();

        // rebuild
        $NestedSet->rebuild();
        unset($Stmt);
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Add new data</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h1>Add new data</h1>
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
            <button type="submit">Submit</button>
            <a href="./">Back to listing page</a>
        </form>
    </body>
</html>
<?php
unset($errorMsg, $name, $parent_id, $position, $result);
unset($NestedSet);