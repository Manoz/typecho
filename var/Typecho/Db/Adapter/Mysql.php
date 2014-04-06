<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: Mysql.php 103 2008-04-09 16:22:43Z magike.net $
 */

/**
 * Mysql database adapter
 *
 * @package Db
 */
class Typecho_Db_Adapter_Mysql implements Typecho_Db_Adapter
{
    /**
     * Database connection string mark
     *
     * @access private
     * @var resource
     */
    private $_dbLink;

    /**
     * Determine if adapters are available
     *
     * @access public
     * @return boolean
     */
    public static function isAvailable()
    {
        return function_exists('mysql_connect');
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
        if ($this->_dbLink = @mysql_connect($config->host . (empty($config->port) ? '' : ':' . $config->port),
        $config->user, $config->password, true)) {
            if (@mysql_select_db($config->database, $this->_dbLink)) {
                if ($config->charset) {
                    mysql_query("SET NAMES '{$config->charset}'", $this->_dbLink);
                }
                return $this->_dbLink;
            }
        }

        /** Database exception */
        throw new Typecho_Db_Adapter_Exception(@mysql_error($this->_dbLink));
    }

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
        if ($resource = @mysql_query($query instanceof Typecho_Db_Query ? $query->__toString() : $query, $handle)) {
            return $resource;
        }

        /** Database exception */
        throw new Typecho_Db_Query_Exception(@mysql_error($this->_dbLink), mysql_errno($this->_dbLink));
    }

    /**
     * The data query as an array of one line out,
     * which corresponds to an array of key field names
     *
     * @param resource $resource Returns the query ressource identifier
     * @return array
     */
    public function fetch($resource)
    {
        return mysql_fetch_assoc($resource);
    }

    /**
     * The data queries where row as an object out,
     * which corresponds to an object attribute field name
     *
     * @param resource $resource Resource data query
     * @return object
     */
    public function fetchObject($resource)
    {
        return mysql_fetch_object($resource);
    }

    /**
     * Quotes escape function
     *
     * @param string $string Need to escape strings
     * @return string
     */
    public function quoteValue($string)
    {
        return '\'' . str_replace(array('\'', '\\'), array('\'\'', '\\\\'), $string) . '\'';
    }

    /**
     * Quotes filter
     *
     * @access public
     * @param string $string
     * @return string
     */
    public function quoteColumn($string)
    {
        return '`' . $string . '`';
    }

    /**
     * Synthetic query
     *
     * @access public
     * @param array $sql Lexical query object array
     * @return string
     */
    public function parseSelect(array $sql)
    {
        if (!empty($sql['join'])) {
            foreach ($sql['join'] as $val) {
                list($table, $condition, $op) = $val;
                $sql['table'] = "{$sql['table']} {$op} JOIN {$table} ON {$condition}";
            }
        }

        $sql['limit'] = (0 == strlen($sql['limit'])) ? NULL : ' LIMIT ' . $sql['limit'];
        $sql['offset'] = (0 == strlen($sql['offset'])) ? NULL : ' OFFSET ' . $sql['offset'];

        return 'SELECT ' . $sql['fields'] . ' FROM ' . $sql['table'] .
        $sql['where'] . $sql['group'] . $sql['having'] . $sql['order'] . $sql['limit'] . $sql['offset'];
    }

    /**
     * Remove the last number of rows affected by the query
     *
     * @param resource $resource Resource data query
     * @param mixed $handle Connection object
     * @return integer
     */
    public function affectedRows($resource, $handle)
    {
        return mysql_affected_rows($handle);
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
        return mysql_insert_id($handle);
    }
}
