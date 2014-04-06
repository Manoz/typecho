<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: DbAdapter.php 97 2008-04-04 04:39:54Z magike.net $
 */

/**
 * Typecho database adapter
 * Fit the definition of a common database interfaces
 *
 * @package Db
 */
interface Typecho_Db_Adapter
{
    /**
     * Determine if adapters are available
     *
     * @access public
     * @return boolean
     */
    public static function isAvailable();

    /**
     * Database connection function
     *
     * @param Typecho_Config $config Database Configuration
     * @return resource
     */
    public function connect(Typecho_Config $config);

    /**
     * Execute database queries
     *
     * @param string $query SQL database query string
     * @param mixed $handle Connection object
     * @param integer $op Database read and write state
     * @param string $action Database action
     * @return resource
     */
    public function query($query, $handle, $op = Typecho_Db::READ, $action = NULL);

    /**
     * The data query as an array of one line out,
     * which corresponds to an array of key field names
     *
     * @param resource $resource Resource data query
     * @return array
     */
    public function fetch($resource);

    /**
     * The data queries where row as an object out,
     * which corresponds to an object attribute field name
     *
     * @param resource $resource Resource data query
     * @return object
     */
    public function fetchObject($resource);

    /**
     * Quotes escape function
     *
     * @param string $string Need to escape strings
     * @return string
     */
    public function quoteValue($string);

    /**
     * Filter quotes
     *
     * @access public
     * @param string $string
     * @return string
     */
    public function quoteColumn($string);

    /**
     * Synthetic query
     *
     * @access public
     * @param array $sql Lexical query object array
     * @return string
     */
    public function parseSelect(array $sql);

    /**
     * Remove the last number of rows affected by the query
     *
     * @param resource $resource Resource data query
     * @param mixed $handle Connection object
     * @return integer
     */
    public function affectedRows($resource, $handle);

    /**
     * Remove the insert to return last primary key
     *
     * @param resource $resource Resource data query
     * @param mixed $handle Connection object
     * @return integer
     */
    public function lastInsertId($resource, $handle);
}
