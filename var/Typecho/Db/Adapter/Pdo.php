<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: Mysql.php 89 2008-03-31 00:10:57Z magike.net $
 */

/**
 * PDOMysql database adapter
 *
 * @package Db
 */
abstract class Typecho_Db_Adapter_Pdo implements Typecho_Db_Adapter
{
    /**
     * Database Objects
     *
     * @access protected
     * @var PDO
     */
    protected $_object;

    /**
     * The last table operation
     *
     * @access protected
     * @var string
     */
    protected $_lastTable;

    /**
     * Determine if adapters are available
     *
     * @access public
     * @return boolean
     */
    public static function isAvailable()
    {
        return extension_loaded('pdo');
    }

    /**
     * Database connection function
     *
     * @param Typecho_Config $config Database Configuration
     * @throws Typecho_Db_Exception
     * @return resource
     */
    public function connect(Typecho_Config $config)
    {
        try {
            $this->_object = $this->init($config);
            $this->_object->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->_object;
        } catch (PDOException $e) {
            /** Database exception */
            throw new Typecho_Db_Adapter_Exception($e->getMessage());
        }
    }

    /**
     * Initialize the database
     *
     * @param Typecho_Config $config Database Configuration
     * @abstract
     * @access public
     * @return PDO
     */
    abstract public function init(Typecho_Config $config);

    /**
     * Execute database queries
     *
     * @param string $query SQL database query string
     * @param mixed $handle Connection object
     * @param integer $op Database read and write state
     * @param string $action Database action
     * @throws Typecho_Db_Exception
     * @return resource
     */
    public function query($query, $handle, $op = Typecho_Db::READ, $action = NULL)
    {
        try {
            $isQueryObject = $query instanceof Typecho_Db_Query;
            $this->_lastTable = $isQueryObject ? $query->getAttribute('table') : NULL;
            $resource = $handle->prepare($isQueryObject ? $query->__toString() : $query);
            $resource->execute();
        } catch (PDOException $e) {
            /** Database exception */
            throw new Typecho_Db_Query_Exception($e->getMessage(), $e->getCode());
        }

        return $resource;
    }

    /**
     * The data query as an array of one line out,
     * which corresponds to an array of key field names
     *
     * @param resource $resource Returns the ressource identifier
     * @return array
     */
    public function fetch($resource)
    {
        return $resource->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * The data queries where row as an object out, which corresponds to an object attribute field name
     *
     * @param resource $resource Resource data query
     * @return object
     */
    public function fetchObject($resource)
    {
        return $resource->fetchObject();
    }

    /**
     * Quotes escape function
     *
     * @param string $string Need to escape strings
     * @return string
     */
    public function quoteValue($string)
    {
        return $this->_object->quote($string);
    }

    /**
     * Filter quotes
     *
     * @access public
     * @param string $string
     * @return string
     */
    public function quoteColumn($string){}

    /**
     * Synthetic query
     *
     * @access public
     * @param array $sql Lexical query object array
     * @return string
     */
    public function parseSelect(array $sql){}

    /**
     * Remove the last number of rows affected by the query
     *
     * @param resource $resource Resource data query
     * @param mixed $handle Connection object
     * @return integer
     */
    public function affectedRows($resource, $handle)
    {
        return $resource->rowCount();
    }

    /**
     * Remove the insert to return last primary key
     *
     * @param resource $resource Resource data query
     * @param mixed $handle Connection object
     * @return integer
     */
    public function lastInsertId($resource, $handle)
    {
        return $handle->lastInsertId();
    }
}
