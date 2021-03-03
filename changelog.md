## v1.x

### v1.0.2
2021-03-03

* Fix `listTaxonomyBuildTreeWithChildren()` did not return array.

### v1.0.0
2020-09-15

#### Renamed

* Renamed properties.<br>
    `id_column_name` -> `idColumnName`<br>
    `parent_id_column_name` -> `parentIdColumnName`<br>
    `left_column_name` -> `leftColumnName`<br>
    `right_column_name` -> `rightColumnName`<br>
    `level_column_name` -> `levelColumnName`<br>
    `position_column_name` -> `positionColumnName`<br>
    `table_name` -> `tableName`
* Renamed method.<br>
    `rebuildGetTreeWithChildren()` -> `getTreeWithChildren()`

#### Removed

* Removed `Database` class.

#### Arguments changed

* Changed `NestedSet` class constructor from `__construct(array $config)` to `__construct(\PDO $PDO)`.
* The option key in the argument of `listTaxonomy()` method was changed.<br>
    `['search']['search_value']` -> `['search']['searchValue']`<br>
* The option keys in the argument of `getTaxonomyWithParents()` method was changed.<br>
    `['taxonomy_id']` -> `['filter_taxonomy_id']`<br>
    `['search']['search_value']` -> `['search']['searchValue']`<br>
    `['skip_current']` -> `['skipCurrent']`<br>

#### Arguments added

* The argument `$where` was added in the `getNewPosition()` method.
* The argument `$where` was added in the `getTreeWithChildren()` method.
* The argument `$where` was added in the `rebuild()` method.
* The option keys in the argument of `getTaxonomyWithChildren()` method was added.<br>
    `['where']['whereString']`<br>
    `['where']['whereValues']`
* The option keys in the argument of `getTaxonomyWithParents()` method was added.<br>
    `['where']['whereString']`<br>
    `['where']['whereValues']`
* The option keys in the argument of `listTaxonomy()` method was added.<br>
    `['where']['whereString']`<br>
    `['where']['whereValues']`
* The option keys in the argument of `listTaxonomyBindValues()` method was added.<br>
    `['where']['whereString']`<br>
    `['where']['whereValues']`
* The option keys in the argument of `listTaxonomyFlatten()` method was added.<br>
    `['where']['whereString']`<br>
    `['where']['whereValues']`

#### New method
* `restoreColumnsName()`.