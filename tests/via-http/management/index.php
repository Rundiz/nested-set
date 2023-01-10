<?php
/** 
 * @license http://opensource.org/licenses/MIT MIT
 */

require dirname(__DIR__) . '/includes.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';

$options = [];
$options['unlimited'] = true;
$list_txn = $NestedSet->listTaxonomyFlatten($options);
unset($options);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Nested set management sample</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h1>Nested set management sample</h1>
        <form method="post" action="delete.php">
            <p><a href="add.php">Add new data</a> | <a href="../test-list-taxonomy-flatten.php">See more advance listing based on this DB table</a></p>
            <table class="list-taxonomy-table">
                <thead>
                    <tr>
                        <th class="column-checkbox"><input id="check-all" type="checkbox" value="all" onclick="return checkAll(this);"></th>
                        <th class="column-id">ID</th>
                        <th class="column-id">Parent</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Level</th>
                        <th>Left</th>
                        <th>Right</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($list_txn['items']) && is_array($list_txn['items'])) {
                        foreach ($list_txn['items'] as $row) {
                    ?> 
                    <tr>
                        <td class="column-checkbox"><input id="checkbox-id-<?php echo $row->{$NestedSet->idColumnName}; ?>" type="checkbox" name="id[]" value="<?php echo $row->{$NestedSet->idColumnName}; ?>"></td>
                        <td><?php echo $row->{$NestedSet->idColumnName}; ?></td>
                        <td><?php echo $row->{$NestedSet->parentIdColumnName}; ?></td>
                        <td class="column-name">
                            <label for="checkbox-id-<?php echo $row->{$NestedSet->idColumnName}; ?>">
                                <?php 
                                if ($row->{$NestedSet->levelColumnName} > 1) {
                                    echo str_repeat(' &nbsp; &nbsp;', ($row->{$NestedSet->levelColumnName} - 1));
                                    echo '|&mdash;';
                                }
                                echo $row->name; 
                                ?> 
                            </label>
                        </td>
                        <td><?php echo $row->{$NestedSet->positionColumnName}; ?></td>
                        <td><?php echo $row->{$NestedSet->levelColumnName}; ?></td>
                        <td><?php echo $row->{$NestedSet->leftColumnName}; ?></td>
                        <td><?php echo $row->{$NestedSet->rightColumnName}; ?></td>
                        <td><a href="edit.php?id=<?php echo $row->{$NestedSet->idColumnName}; ?>">Edit</a></td>
                    </tr>
                    <?php 
                        }// endforeach;
                        unset($row);
                    } else {
                    ?> 
                    <tr>
                        <td colspan="9">There is no data.</td>
                    </tr>
                    <?php
                    }// endif; $list_txn
                    ?> 
                </tbody>
            </table>
            <button type="submit">Delete selected (no confirm, all children will be deleted)</button>
        </form>
        <?php
        unset($list_txn, $NestedSet);
        ?> 
        <script type="application/javascript">
            function checkAll(thisObj) {
                if (typeof(thisObj) !== 'object') {
                    return true;
                }

                const allCheckboxes = thisObj.closest('table').querySelectorAll('tbody input[type="checkbox"]');
                allCheckboxes.forEach((item) => {
                    if (item.disabled !== true) {
                        item.checked = thisObj.checked;
                    }
                });

                return true;
            }// checkAll
        </script>
    </body>
</html>