<?php
require __DIR__ . '/includes.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';

$pagination_page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
if ($pagination_page == null || !is_numeric($pagination_page)) {
    $pagination_page = 1;
}

unset($db);

// list taxonomy for select box. this have to list ALL and unlimited.
$options = [];
$options['unlimited'] = true;
$list_all_txn = $NestedSet->listTaxonomyFlatten($options);
unset($options);

// list taxonomy by filter & search.
$options = [];
$options['limit'] = 5;
$options['offset'] = ($pagination_page - 1) * $options['limit'];
if (isset($_GET['filter_parent_id']) && is_numeric(trim($_GET['filter_parent_id']))) {
    $options['filter_parent_id'] = trim($_GET['filter_parent_id']);
}
if (isset($_GET['search']) && trim($_GET['search']) != null) {
    $options['search'] = [];
    $options['search']['columns'] = ['name'];
    $options['search']['searchValue'] = trim($_GET['search']);
}
$list_txn = $NestedSet->listTaxonomyFlatten($options);
if (isset($list_txn) && is_array($list_txn) && array_key_exists('total', $list_txn)) {
    $total_pages = ($list_txn['total'] / $options['limit']);
} else {
    $total_pages = 0;
}
unset($options);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Test list taxonomy tree as flatten</title>
        <style type="text/css">
            fieldset {
                margin-bottom: 20px;
            }

            .list-taxonomy-table {
                border: 1px solid #ddd;
                border-collapse: collapse;
                width: 100%;
            }
            .list-taxonomy-table td,
            .list-taxonomy-table th {
                border: 1px solid #ddd;
                padding: 2px 5px;
                text-align: left;
            }
            .list-taxonomy-table .column-name {
                white-space: nowrap;
            }
        </style>
    </head>
    <body>
        <h1>Test list taxonomy tree as flatten</h1>
        <form method="get">
            <fieldset>
                <legend>Filter</legend>
                Parent taxonomy:
                <select name="filter_parent_id">
                    <option value="">-- None --</option>
                    <?php
                    if (isset($list_all_txn['items']) && is_array($list_all_txn['items'])) {
                        foreach ($list_all_txn['items'] as $row) {
                            echo '<option';
                            echo ' value="' . $row->{$NestedSet->idColumnName} . '"';
                            if (isset($_GET['filter_parent_id']) && $_GET['filter_parent_id'] == $row->{$NestedSet->idColumnName}) {
                                echo ' selected=""';
                            }
                            echo '>';
                            if ($row->{$NestedSet->levelColumnName} > 1) {
                                echo str_repeat(' &nbsp; &nbsp;', ($row->{$NestedSet->levelColumnName} - 1));
                                echo '|&mdash;';
                            }
                            echo $row->name;
                            echo '</option>' . "\n";
                        }// endforeach;
                        unset($row);
                    }
                    ?> 
                </select>
                Search:
                <input type="search" name="search" value="<?php echo filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING); ?>">
                <button type="submit">Submit</button>
                <a href="test-list-taxonomy-flatten.php">Reset filter</a>
                <?php
                if (isset($list_txn) && is_array($list_txn) && array_key_exists('total', $list_txn)) {
                    echo 'Found total ' . $list_txn['total'] . ' items';
                }
                ?> 
            </fieldset>
        </form>
        <table class="list-taxonomy-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Parent</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Level</th>
                    <th>Left</th>
                    <th>Right</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($list_txn) && is_array($list_txn) && array_key_exists('items', $list_txn)) {
                    foreach ($list_txn['items'] as $row) {
                ?> 
                <tr>
                    <td><?php echo $row->{$NestedSet->idColumnName}; ?></td>
                    <td><?php echo $row->{$NestedSet->parentIdColumnName}; ?></td>
                    <td class="column-name">
                        <?php 
                        if ($row->{$NestedSet->levelColumnName} > 1) {
                            echo str_repeat(' &nbsp; &nbsp;', ($row->{$NestedSet->levelColumnName} - 1));
                            echo '|&mdash;';
                        }
                        echo $row->name; 
                        ?>
                    </td>
                    <td><?php echo $row->{$NestedSet->positionColumnName}; ?></td>
                    <td><?php echo $row->{$NestedSet->levelColumnName}; ?></td>
                    <td><?php echo $row->{$NestedSet->leftColumnName}; ?></td>
                    <td><?php echo $row->{$NestedSet->rightColumnName}; ?></td>
                </tr>
                <?php 
                    }// endforeach;
                    unset($row);
                } else {
                ?> 
                <tr>
                    <td colspan="7">There is no data.</td>
                </tr>
                <?php
                }// endif; $list_txn
                ?> 
            </tbody>
        </table>
        <?php 
        echo 'pagination: ';
        echo '<a href="?filter_parent_id=' . urlencode(filter_input(INPUT_GET, 'filter_parent_id', FILTER_SANITIZE_NUMBER_INT)) . '&amp;search=' . urlencode(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING)) . '&amp;page=';
        if ($pagination_page == null || $pagination_page <= 1) {
            echo '" disabled="disabled" onclick="return false;"';
        } else {
            echo ($pagination_page - 1) . '"';
        }
        echo '>&lt;</a>';
        echo ' ';
        echo '<a href="?filter_parent_id=' . urlencode(filter_input(INPUT_GET, 'filter_parent_id', FILTER_SANITIZE_NUMBER_INT)) . '&amp;search=' . urlencode(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING)) . '&amp;page=';
        if ($pagination_page >= $total_pages) {
            echo '" disabled="disabled" onclick="return false;"';
        } else {
            echo ($pagination_page + 1) . '"';
        }
        echo '>&gt;</a>';
        unset($pagination_page, $total_pages);
        ?> 
    </body>
</html>