<?php
require __DIR__ . '/includes.php';


$db = require __DIR__ . '/db-config.php';

$NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $db, 'tablename' => $db['tablename']]);

unset($db);

$options = [];
$options['unlimited'] = true;
$list_txn = $NestedSet->listTaxonomy($options);
unset($options);


// functions ----------------------------------------------------------------------------------
/**
 * Render taxonomy to nested ul.
 * 
 * @param array $array The data array get from db.
 * @param \Rundiz\NestedSet\NestedSet $NestedSet The nested set class object for call column named in there.
 * @param boolean $first Leave this for correctly render ul class.
 * @return string Return rendered nested ul.
 */
function renderTaxonomyTree(array $array, \Rundiz\NestedSet\NestedSet $NestedSet = null, $first = true)
{
    if (!is_array($array)) {
        return '';
    }

    if ($first === true) {
        $output = '<ul class="taxonomy-tree">' . "\n";
    } else {
        $output = '<ul>' . "\n";
    }

    foreach ($array as $item) {

        // You can change column name or customize the display you want here.
        $output .= '<li>' . "\n";
        $output .= '<div class="taxonomy-item-content">' . $item->name;
        $output .= ' <span class="debug">';
        $output .= 'id: ' . $item->{$NestedSet->id_column_name} . ', ';
        $output .= 'parent_id: ' . $item->{$NestedSet->parent_id_column_name} . ', ';
        $output .= 'position: ' . $item->{$NestedSet->position_column_name} . ', ';
        $output .= 'level: ' . $item->{$NestedSet->level_column_name} . ', ';
        $output .= 'left: ' . $item->{$NestedSet->left_column_name} . ', ';
        $output .= 'right: ' . $item->{$NestedSet->right_column_name};
        $output .= '</span>';
        $output .= '</div>' . "\n";

        if (property_exists($item, 'children')) {
            $output .= renderTaxonomyTree($item->children, $NestedSet, false);
        }

        $output .= '</li>' . "\n";
    }// endforeach;

    $output .= '</ul>' . "\n";

    return $output;
}// renderTaxonomyTree
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Test list taxonomy tree</title>
        <style type="text/css">
            .taxonomy-tree {
                list-style: none;
                margin: 0;
                padding: 0;
            }
            .taxonomy-tree ul {
                list-style: none;
                margin: 0;
                padding-bottom: 0;
                padding-left: 20px;
                padding-right: 0;
                padding-top: 0;
            }
            .taxonomy-tree .taxonomy-item-content {
                background-color: #f1f1f1;
                border: 1px solid #ddd;
                border-radius: 2px;
                margin-bottom: 20px;
                padding: 2px 3px;
            }
            .taxonomy-tree .taxonomy-item-content .debug {
                color: #555;
                font-size: 0.75rem;
            }
        </style>
    </head>
    <body>
        <h1>Test list taxonomy tree</h1>
        <?php
        if (is_array($list_txn) && array_key_exists('total', $list_txn)) {
            echo '<p>Found total ' . $list_txn['total'] . ' items</p>'."\n";
        }
        if (is_array($list_txn) && array_key_exists('items', $list_txn)) {
            echo renderTaxonomyTree($list_txn['items'], $NestedSet);
        }// endif; $list_txn
        ?> 
    </body>
</html>