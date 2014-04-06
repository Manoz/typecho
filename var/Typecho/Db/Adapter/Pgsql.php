<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id$
 */

/**
 * Pgsql database adapter
 *
 * @package Db
 */
class Typecho_Db_Adapter_Pgsql implements Typecho_Db_Adapter
{
    /**
     * Database connection string mark
     *
     * @access private
     * @var resource
     */
    private $_dbLink;

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
        return function_exists('pg_connect');
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
        if ($this->_dbLink = @pg_connect("host={$config->host} port={$config->port} dbname={$config->database} user={$config->user} password={$config->password}")) {
            if ($config->charset) {
                pg_query($this->_dbLink, "SET NAMES '{$config->charset}'");
            }
            return $this->_dbLink;
        }

        /** Database exception */
        throw new Typecho_Db_Adapter_Exception(@pg_last_error($this->_dbLink));
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
        $isQueryObject = $query instanceof Typecho_Db_Query;
        $this->_lastTable = $isQueryObject ? $query->getAttribute('table') : NULL;
        if ($resource = @pg_query($handle, $isQueryObject ? $query->__toString() : $query)) {
            return $resource;
        }

        /** Database exception */
        throw new Typecho_Db_Query_Exception(@pg_last_error($this->_dbLink),
        pg_result_error_field(pg_get_result($this->_dbLink), PGSQL_DIAG_SQLSTATE));
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
        return pg_fetch_assoc($resource);
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
        return pg_fetch_object($resource);
    }

    /**
     * Quotes escape function
     *
     * @param string $string Need to escape strings
     * @return string
     */
    public function quoteValue($string)
    {
        return '\'' . pg_escape_string($string) . '\'';
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
        return pg_affected_rows($resource);
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
        /** Check whether there is a sequence, may require more strict inspection */
        if (pg_fetch_assoc(pg_query($handle, 'SELECT oid FROM pg_class WHERE relname = ' . $this->quoteValue($this->_lastTable . '_seq')))) {
            return pg_fetch_result(pg_query($handle, 'SELECT CURRVAL(' . $this->quoteValue($this->_lastTable . '_seq') . ')'), 0, 0);
        }

        return 0;
    }
}
