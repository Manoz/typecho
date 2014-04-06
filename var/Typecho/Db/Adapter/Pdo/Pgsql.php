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
 * Database Pdo_Pgsql adapter
 *
 * @package Db
 */
class Typecho_Db_Adapter_Pdo_Pgsql extends Typecho_Db_Adapter_Pdo
{
    /**
     * Determine if adapters are available
     *
     * @access public
     * @return boolean
     */
    public static function isAvailable()
    {
        return parent::isAvailable() && in_array('pgsql', PDO::getAvailableDrivers());
    }

    /**
     * Initialize the database
     *
     * @param Typecho_Config $config Database Configuration
     * @access public
     * @return PDO
     */
    public function init(Typecho_Config $config)
    {
        $pdo = new PDO("pgsql:dbname={$config->database};host={$config->host};port={$config->port}", $config->user, $config->password);
        $pdo->exec("SET NAMES '{$config->charset}'");
        return $pdo;
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
     * @param array $sql query object array
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
     * Remove the insert to return the last primary key
     *
     * @param resource $resource Resource data query
     * @param mixed $handle Connection object
     * @return integer
     */
    public function lastInsertId($resource, $handle)
    {
        /** Check whether there is a sequence, may require more strict inspection */
        if ($handle->query('SELECT oid FROM pg_class WHERE relname = ' . $this->quoteValue($this->_lastTable . '_seq'))->fetchAll()) {
            return $handle->lastInsertId($this->_lastTable . '_seq');
        }

        return 0;
    }
}
