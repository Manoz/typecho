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
 * Database Pdo_Mysql adapter
 *
 * @package Db
 */
class Typecho_Db_Adapter_Pdo_Mysql extends Typecho_Db_Adapter_Pdo
{
    /**
     * Determine if adapters are available
     *
     * @access public
     * @return boolean
     */
    public static function isAvailable()
    {
        return parent::isAvailable() && in_array('mysql', PDO::getAvailableDrivers());
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
        $pdo = new PDO(!empty($config->dsn) ? $config->dsn :
            "mysql:dbname={$config->database};host={$config->host};port={$config->port}", $config->user, $config->password);
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
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
        return '`' . $string . '`';
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
}
