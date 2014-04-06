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
 * SQLite database adapter
 *
 * @package Db
 */
class Typecho_Db_Adapter_SQLite implements Typecho_Db_Adapter
{
    /**
     * Database mark
     *
     * @access private
     * @var resource
     */
    private $_dbHandle;

    /**
     * Determine if adapters are available
     *
     * @access public
     * @return boolean
     */
    public static function isAvailable()
    {
        return function_exists('sqlite_open');
    }

    /**
     * Filter field name
     *
     * @access private
     * @param mixed $result
     * @return array
     */
    private function filterColumnName($result)
    {
        /** If the result is empty, return */
        if (!$result) {
            return $result;
        }

        $tResult = array();

        /** Loop through the array */
        foreach ($result as $key => $val) {
            /** Separated by dots */
            if (false !== ($pos = strpos($key, '.'))) {
                $key = substr($key, $pos + 1);
            }

            /** Divided by quotes */
            if (false === ($pos = strpos($key, '"'))) {
                $tResult[$key] = $val;
            } else {
                $tResult[substr($key, $pos + 1, -1)] = $val;
            }
        }

        return $tResult;
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
        if ($this->_dbHandle = sqlite_open($config->file, 0666, $error)) {
            return $this->_dbHandle;
        }

        /** Database exception */
        throw new Typecho_Db_Adapter_Exception($error);
    }

    /**
     * Execute database queries
     *
     * @param string $sql Query String
     * @param mixed $handle Connection object
     * @param boolean $op Query read switch
     * @throws Typecho_Db_Exception
     * @return resource
     */
    public function query($query, $handle, $op = Typecho_Db::READ, $action = NULL)
    {
        if ($resource = @sqlite_query($query instanceof Typecho_Db_Query ? $query->__toString() : $query, $handle)) {
            return $resource;
        }

        /** Database exception */
        $errorCode = sqlite_last_error($this->_dbHandle);
        throw new Typecho_Db_Query_Exception(sqlite_error_string($errorCode), $errorCode);
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
        return $this->filterColumnName(sqlite_fetch_array($resource, SQLITE_ASSOC));
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
        return (object) $this->filterColumnName(sqlite_fetch_array($resource, SQLITE_ASSOC));
    }

    /**
     * Quotes escape function
     *
     * @param string $string Need to escape strings
     * @return string
     */
    public function quoteValue($string)
    {
        return '\'' . str_replace('\'', '\'\'', $string) . '\'';
    }

    /**
     * Filter quotes
     *
     * @access public
     * @param string $string
     * @return string
     */
    public function quoteColumn($string)
    {
        return '"' . $string . '"';
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
        return sqlite_changes($handle);
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
        return sqlite_last_insert_rowid($handle);
    }
}
