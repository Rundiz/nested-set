<?php
/** 
 * @license http://opensource.org/licenses/MIT MIT
 */

require dirname(__DIR__) . '/includes.php';

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed.';
    exit();
}

if (!isset($_POST['id']) || !is_array($_POST['id'])) {
    http_response_code(400);
    echo 'Please select at least one item.';
    exit();
}

$ids = $_POST['id'];

$NestedSet = new \Rundiz\NestedSet\NestedSet($PDO);
$NestedSet->tableName = 'test_taxonomy';

// prepare result variable for debugging only.
$result = [
    'delete' => [],
];

// loop ids array to delete one by one (with their children).
foreach ($ids as $id) {
    $result['delete'][$id] = $NestedSet->deleteWithChildren($id);
}// endforeach;
unset($id);

// rebuild data when done.
$NestedSet->rebuild();

unset($ids, $NestedSet);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Delete data.</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <pre><?php var_export($result); ?></pre>
        <a href="./">Back to listing page</a>
    </body>
</html>
<?php
unset($result);