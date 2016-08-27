# NestedSet

The PHP nested set model for create/read/update/delete the tree data structure (hierarchy).

[![Latest Stable Version](https://poser.pugx.org/rundiz/nested-set/v/stable)](https://packagist.org/packages/rundiz/nested-set)
[![License](https://poser.pugx.org/rundiz/nested-set/license)](https://packagist.org/packages/rundiz/nested-set)
[![Total Downloads](https://poser.pugx.org/rundiz/nested-set/downloads)](https://packagist.org/packages/rundiz/nested-set)

![Nested Set] (tests/via-http/nested-set-model.jpg "Nested Set")

## Example

### Install
I recommend you to install this library via Composer and use Composer autoload for easily include the files. If you are not using Composer, you have to manually include these files by yourself.<br>
Please make sure that the path to files are correct.
```php
include_once '/path/to/Rundiz/NestedSet/Database.php';
include_once '/path/to/Rundiz/NestedSet/NestedSet.php';
```

Import taxonomy_unbuild_data.sql to MySQL or MariaDB (for test only).

### Configuration
You have to provide db configuration to the class to read, update, delete the data.
```php
$db['dsn'] = 'mysql:dbname=YOUR_DB_NAME;host=localhost;port=3306;charset=UTF8';
$db['username'] = 'admin';
$db['password'] = 'pass';
$db['options'] = [
    \PDO::ATTR_EMULATE_PREPARES => false,
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION // throws PDOException.
];
$db['tablename'] = 'test_taxonomy';
$NestedSet = new \Rundiz\NestedSet\NestedSet(['pdoconfig' => $db, 'tablename' => $db['tablename']]);
```
Now you have `$NestedSet` as object and it is ready to use.<br>
Let's try it!
```php
$result = $NestedSet->Database->PDO->query('SELECT * FROM `' . $db['tablename'] . '`');
var_dump($result);
```
The `$result` must show something that is not null if you install the sql data.

### Insert, Update the data
#### Get new position
You can get new position of the level by use `getNewPosition()` method.
```php
$new_position = $NestedSet->getNewPosition(4);// result is 4.
```
This new position is good for insert new taxonomy item.

#### Insert/Update
Everytime you insert or update the data, you have to run `rebuild()` method to generate level, left, right data. The incorrect level, left, right data can cause incorrect listing.
```php
$stmt = $PDO->prepare('INSERT INTO `test_taxonomy`(`parent_id`, `name`, `position`) VALUES (?, ?, ?)');
$stmt->execute([0, 'Root 4', 4]);
$NestedSet->rebuild();
```

```php
$stmt = $PDO->prepare('UPDATE `test_taxonomy`SET `name` = ?, `position` = ? WHERE `id` = ?;
$stmt->execute(['Root 4 new name', 4, 21]);
$NestedSet->rebuild();
```

#### Check parent under children
If you want to change the parent of selected item, you can check first that the new parent of selected item is under children of selected item or not.<br>
You can use `isParentUnderMyChildren()` method to check this and false means correct parent (new parent is not children of editing item).<br>
To continue on this please use the data in **taxonomy_unbuild_data.sql** file.
```php
$editing_item_id = 9;
$new_parent_id = 7;
var_dump($NestedSet->isParentUnderMyChildren($editing_item_id, $new_parent_id));// false (correct! the new parent is not child of this item)

$new_parent_id = 14;
var_dump($NestedSet->isParentUnderMyChildren($editing_item_id, $new_parent_id));// true (incorrect! the new parent is child of this item)
```

### Read the data
To read the selected item data with its children, you can use `getTaxonomyWithChildren()` method.
```php
$options['filter_taxonomy_id'] = 3;// The selected item ID.
$list_txn = $NestedSet->getTaxonomyWithChildren($options);
unset($options);
print_r($list_txn);
```

To read the selected item data with its parent in a line until root item, you can use `getTaxonomyWithParents()` method.
```php
$options['taxonomy_id'] = 13;// The selected item ID.
$list_txn = $NestedSet->getTaxonomyWithParents($options);
unset($options);
print_r($list_txn);
```

### List the items
You can list the items by use `listTaxonomy()` method for nested array data or use `listTaxonomyFlatten()` for flatten data.
```php
$options = [];
$options['unlimited'] = true;
$list_txn = $NestedSet->listTaxonomy($options);
unset($options);
// The variable $list_txn is array and have 2 keys (total, items).
```
Both methods parameters are same.

### Delete an item(s)
You can choose how to delete an item.

1. Delete selected item and ALL of its children.
2. Delete selected item and pull up its children to the current parent.

Every time you deleted, you have to run the `rebuild()` method to correct level, left, right data.

Delete selected item and ALL of its children.
```php
$NestedSet->deleteWithChildren(16);
$NestedSet->rebuild();
```

Delete selected item and pull up its children to the current parent.
```php
$NestedSet->deletePullUpChildren(9);
$NestedSet->rebuild();
```

For more example, please look in tests folder.