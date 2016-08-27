<?php
/**
 * @package Nested Set
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\NestedSet;

/**
 * Database class for working with CRUD, build the query.
 *
 * @author Vee W.
 */
class Database
{


    /**
     * @var \PDO The PDO class object.
     */
    public $PDO;


    /**
     * Database class constructor.
     * 
     * @param array $config Available config key: [dsn], [username], [password], [options] (see more at http://php.net/manual/en/pdo.construct.php)
     */
    public function __construct(array $config = [])
    {
        if (!array_key_exists('dsn', $config)) {
            $config['dsn'] = '';
        }
        if (!array_key_exists('username', $config)) {
            $config['username'] = '';
        }
        if (!array_key_exists('password', $config)) {
            $config['password'] = '';
        }
        if (!array_key_exists('options', $config)) {
            $config['options'] = [];
        }

        try {
            $this->PDO = new \PDO($config['dsn'], $config['username'], $config['password'], $config['options']);
            $this->PDO->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }// __construct


}
