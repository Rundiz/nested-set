## v1.x

### v1.0.0
2020-09-15

* These properties was changed.<br>
    `id_column_name` -> `idColumnName`<br>
    `parent_id_column_name` -> `parentIdColumnName`<br>
    `left_column_name` -> `leftColumnName`<br>
    `right_column_name` -> `rightColumnName`<br>
    `level_column_name` -> `levelColumnName`<br>
    `position_column_name` -> `positionColumnName`<br>
    `table_name` -> `tableName`
* Removed `Database` class.
* Changed `NestedSet` class constructor from `__construct(array $config)` to `__construct(\PDO $PDO)`.
* Renamed `rebuildGetTreeWithChildren()` method to `getTreeWithChildren()`.
* The option key in the argument of `listTaxonomy()` method was changed.<br>
    `['search']['search_value']` -> `['search']['searchValue']`<br>