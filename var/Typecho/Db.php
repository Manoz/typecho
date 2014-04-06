<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: Db.php 107 2008-04-11 07:14:43Z magike.net $
 */

/**
 * The method consist of obtaining data classes supports
 * You must define __TYPECHO_DB_HOST__, __TYPECHO_DB_PORT__, __TYPECHO_DB_NAME__,
 * __TYPECHO_DB_USER__, __TYPECHO_DB_PASS__, __TYPECHO_DB_CHAR__
 *
 * @package Db
 */
class Typecho_Db
{
    /** Read Database */
    const READ = 1;

    /** Write Database */
    const WRITE = 2;

    /** Ascending */
    const SORT_ASC = 'ASC';

    /** Descending */
    const SORT_DESC = 'DESC';

    /** Table connection */
    const INNER_JOIN = 'INNER';

    /** Sheet Connection */
    const OUTER_JOIN = 'OUTER';

    /** Table Left Connection */
    const LEFT_JOIN = 'LEFT';

    /** Table Right Connection */
    const RIGHT_JOIN = 'RIGHT';

    /** Database query */
    const SELECT = 'SELECT';

    /** Database updates */
    const UPDATE = 'UPDATE';

    /** Database insert */
    const INSERT = 'INSERT';

    /** Database delete */
    const DELETE = 'DELETE';

    /**
     * Database Adapter
     * @var Typecho_Db_Adapter
     */
    private $_adapter;

    /**
     * The default configuration
     *
     * @access private
     * @var Typecho_Config
     */
    private $_config;

    /**
     * Connection pool
     *
     * @access private
     * @var array
     */
    private $_pool;

    /**
     * Connected pools
     *
     * @access private
     * @var array
     */
    private $_connectedPool;

    /**
     * Prefix
     *
     * @access private
     * @var string
     */
    private $_prefix;

    /**
     * Adapter name
     *
     * @access private
     * @var string
     */
    private $_adapterName;

    /**
     * Examples of Database objects
     * @var Typecho_Db
     */
    private static $_instance;

    /**
     * Database Class constructors
     *
     * @param mixed $adapterName Adapter name
     * @param string $prefix Prefix
     * @throws Typecho_Db_Exception
     */
    public function __construct($adapterName, $prefix = 'typecho_')
    {
        /** Get the adapter name */
        $this->_adapterName = $adapterName;

        /** Database Adapter */
        $adapterName = 'Typecho_Db_Adapter_' . $adapterName;

        if (!call_user_func(array($adapterName, 'isAvailable'))) {
            throw new Typecho_Db_Exception("Adapter {$adapterName} is not available");
        }

        $this->_prefix = $prefix;

        /** Initializes internal variables */
        $this->_pool = array();
        $this->_connectedPool = array();
        $this->_config = array();

        // Object adapter is instantiated
        $this->_adapter = new $adapterName();
    }

    /**
     * Get the adapter name
     *
     * @access public
     * @return string
     */
    public function getAdapterName()
    {
        return $this->_adapterName;
    }

    /**
     * Get table prefix
     *
     * @access public
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * Get Config
     *
     * @access public
     * @return void
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * SQL lexical builder object is instantiated
     *
     * @return Typecho_Db_Query
     */
    public function sql()
    {
        return new Typecho_Db_Query($this->_adapter, $this->_prefix);
    }

    /**
     * Provide support for multiple databases
     *
     * @access public
     * @param Typecho_Db $db Database instance
     * @param integer $op Database operations
     * @return void
     */
    public function addServer($config, $op)
    {
        $this->_config[] = Typecho_Config::factory($config);
        $key = key($this->_config);

        /** Will connect into the pool */
        switch ($op) {
            case self::READ:
            case self::WRITE:
                $this->_pool[$op][] = $key;
                break;
            default:
                $this->_pool[self::READ][] = $key;
                $this->_pool[self::WRITE][] = $key;
                break;
        }
    }

    /**
     * Set default Database Objects
     *
     * @access public
     * @param Typecho_Db $db Database Objects
     * @return void
     */
    public static function set(Typecho_Db $db)
    {
        self::$_instance = $db;
    }

    /**
     * Get an instantiate database object
     * Database storage instantiated with static variables,
     * You can ensure the data connection only once
     *
     * @return Typecho_Db
     * @throws Typecho_Db_Exception
     */
    public static function get()
    {
        if (empty(self::$_instance)) {
            /** Typecho_Db_Exception */
            throw new Typecho_Db_Exception('Missing Database Object');
        }

        return self::$_instance;
    }

    /**
     * Select the query field
     *
     * @access public
     * @param mixed $field Query field
     * @return Typecho_Db_Query
     */
    public function select()
    {
        $args = func_get_args();
        return call_user_func_array(array($this->sql(), 'select'), $args ? $args : array('*'));
    }

    /**
     * Update recording operation (UPDATE)
     *
     * @param string $table Need to update the record of the table
     * @return Typecho_Db_Query
     */
    public function update($table)
    {
        return $this->sql()->update($table);
    }

    /**
     * Delete Record operation (DELETE)
     *
     * @param string $table Need to delete table records
     * @return Typecho_Db_Query
     */
    public function delete($table)
    {
        return $this->sql()->delete($table);
    }

    /**
     * Insert record operation (INSERT)
     *
     * @param string $table Need to insert a table record
     * @return Typecho_Db_Query
     */
    public function insert($table)
    {
        return $this->sql()->insert($table);
    }

    /**
     * Execute a query
     *
     * @param mixed $query Query or query object
     * @param boolean $op Database read and write state
     * @param string $action Operation actions
     * @return mixed
     */
    public function query($query, $op = self::READ, $action = self::SELECT)
    {
        /** Execute the query in the adapter */
        if ($query instanceof Typecho_Db_Query) {
            $action = $query->getAttribute('action');
            $op = (self::UPDATE == $action || self::DELETE == $action
            || self::INSERT == $action) ? self::WRITE : self::READ;
        } else if (!is_string($query)) {
            /** If the query is not an object or is not a string, then it is judged  to handle the query resource directly returned */
            return $query;
        }

        /** Select the connection pool */
        if (!isset($this->_connectedPool[$op])) {
            if (empty($this->_pool[$op])) {
                /** Typecho_Db_Exception */
                throw new Typecho_Db_Exception('Missing Database Connection');
            }

            $selectConnection = rand(0, count($this->_pool[$op]) - 1);
            $selectConnectionConfig = $this->_config[$selectConnection];
            $selectConnectionHandle = $this->_adapter->connect($selectConnectionConfig);
            $other = (self::READ == $op) ? self::WRITE : self::READ;

            if (!empty($this->_pool[$other]) && in_array($selectConnection, $this->_pool[$other])) {
                $this->_connectedPool[$other] = &$selectConnectionHandle;
            }
            $this->_connectedPool[$op] = &$selectConnectionHandle;
        }
        $handle = $this->_connectedPool[$op];

        /** Submit Query */
        $resource = $this->_adapter->query($query, $handle, $op, $action);

        if ($action) {
            // Returns the appropriate resources based on the query action
            switch ($action) {
                case self::UPDATE:
                case self::DELETE:
                    return $this->_adapter->affectedRows($resource, $handle);
                case self::INSERT:
                    return $this->_adapter->lastInsertId($resource, $handle);
                case self::SELECT:
                default:
                    return $resource;
            }
        } else {
            // If you execute a query directly, return resource
            return $resource;
        }
    }

    /**
     * Remove all rows once
     *
     * @param mixed $query Query object
     * @param array $filter Rows filter function, each row of the query as the first argument to specify the filter
     * @return array
     */
    public function fetchAll($query, array $filter = NULL)
    {
        // Execute the query
        $resource = $this->query($query, self::READ);
        $result = array();

        /** Remove the filter */
        if (!empty($filter)) {
            list($object, $method) = $filter;
        }

        // Remove each row
        while ($rows = $this->_adapter->fetch($resource)) {
            // Determine whether there is a filter
            $result[] = $filter ? call_user_func(array(&$object, $method), $rows) : $rows;
        }

        return $result;
    }

    /**
     * Remove a row
     *
     * @param mixed $query Query object
     * @param array $filter Rows filter function, each row of the query as the first argument to specify the filter
     * @return stdClass
     */
    public function fetchRow($query, array $filter = NULL)
    {
        $resource = $this->query($query, self::READ);

        /** Remove the filter */
        if ($filter) {
            list($object, $method) = $filter;
        }

        return ($rows = $this->_adapter->fetch($resource)) ?
        ($filter ? $object->$method($rows) : $rows) :
        array();
    }

    /**
     * An object is removed once
     *
     * @param mixed $query Query object
     * @param array $filter Rows filter function, each row of the query as the first argument to specify the filter
     * @return array
     */
    public function fetchObject($query, array $filter = NULL)
    {
        $resource = $this->query($query, self::READ);

        /** Remove the filter */
        if ($filter) {
            list($object, $method) = $filter;
        }

        return ($rows = $this->_adapter->fetchObject($resource)) ?
        ($filter ? $object->$method($rows) : $rows) :
        new stdClass();
    }
}
