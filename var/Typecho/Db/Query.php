<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: DbQuery.php 97 2008-04-04 04:39:54Z magike.net $
 */

/**
 * Build the Typecho database class
 * Use:
 * $query = new Typecho_Db_Query();	// Or use sql DB accumulation method returns an instance of an object
 * $query->select('posts', 'post_id, post_title')
 * ->where('post_id = %d', 1)
 * ->limit(1);
 * echo $query;
 * The results will be printed
 * SELECT post_id, post_title FROM posts WHERE 1=1 AND post_id = 1 LIMIT 1
 *
 *
 * @package Db
 */
class Typecho_Db_Query
{
    /** Database keyword */
    const KEYWORDS = '*PRIMARY|AND|OR|LIKE|BINARY|BY|DISTINCT|AS|IN|IS|NULL';

    /**
     * Default field
     *
     * @var array
     * @access private
     */
    private $_default = array(
        'action' => NULL,
        'table'  => NULL,
        'fields' => '*',
        'join'   => array(),
        'where'  => NULL,
        'limit'  => NULL,
        'offset' => NULL,
        'order'  => NULL,
        'group'  => NULL,
        'having'  => NULL,
        'rows'   => array(),
    );

    /**
     * Database Adapter
     *
     * @var Typecho_Db_Adapter
     */
    private $_adapter;

    /**
     * Pre-query structure, composed of an array of
     * convenient combination of SQLQuery and String
     *
     * @var array
     */
    private $_sqlPreBuild;

    /**
     * Prefix
     *
     * @access private
     * @var string
     */
    private $_prefix;

    /**
     * Constructors, reference the database adapter as an internal data
     *
     * @param Typecho_Db_Adapter $adapter Database Adapter
     * @param string $prefix Prefix
     * @return void
     */
    public function __construct(Typecho_Db_Adapter $adapter, $prefix)
    {
        $this->_adapter = &$adapter;
        $this->_prefix = $prefix;

        $this->_sqlPreBuild = $this->_default;
    }

    /**
     * 过滤表Prefix,表Prefix由table.构成
     * Filter the prefix, table prefix. Structure
     *
     * @param string $string Need to parse the string
     * @return string
     */
    private function filterPrefix($string)
    {
        return (0 === strpos($string, 'table.')) ? substr_replace($string, $this->_prefix, 0, 6) : $string;
    }

    /**
     * Filter array keys
     *
     * @access private
     * @param string $str Pending field values
     * @return string
     */
    private function filterColumn($str)
    {
        $str = $str . ' 0';
        $length = strlen($str);
        $lastIsAlnum = false;
        $result = '';
        $word = '';
        $split = '';
        $quotes = 0;

        for ($i = 0; $i < $length; $i ++) {
            $cha = $str[$i];

            if (ctype_alnum($cha) || false !== strpos('_*', $cha)) {
                if (!$lastIsAlnum) {
                    if ($quotes > 0 && !ctype_digit($word) && '.' != $split
                    && false === strpos(self::KEYWORDS, strtoupper($word))) {
                        $word = $this->_adapter->quoteColumn($word);
                    } else if ('.' == $split && 'table' == $word) {
                        $word = $this->_prefix;
                        $split = '';
                    }

                    $result .= $word . $split;
                    $word = '';
                    $quotes = 0;
                }

                $word .= $cha;
                $lastIsAlnum = true;
            } else {

                if ($lastIsAlnum) {

                    if (0 == $quotes) {
                        if (false !== strpos(' ,)=<>.+-*/', $cha)) {
                            $quotes = 1;
                        } else if ('(' == $cha) {
                            $quotes = -1;
                        }
                    }

                    $split = '';
                }

                $split .= $cha;
                $lastIsAlnum = false;
            }

        }

        return $result;
    }

    /**
     * Synthesized from the parameter query field
     *
     * @access private
     * @param array $parameters
     * @return string
     */
    private function getColumnFromParameters(array $parameters)
    {
        $fields = array();

        foreach ($parameters as $value) {
            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    $fields[] = $key . ' AS ' . $val;
                }
            } else {
                 $fields[] = $value;
            }
        }

        return $this->filterColumn(implode(' , ', $fields));
    }

    /**
     * Escape Argument
     *
     * @param array $values
     * @access protected
     * @return array
     */
    protected function quoteValues(array $values)
    {
        foreach ($values as &$value) {
            if (is_array($value)) {
                $value = '(' . implode(',', array_map(array($this->_adapter, 'quoteValue'), $value)) . ')';
            } else {
                $value = $this->_adapter->quoteValue($value);
            }
        }

        return $values;
    }

    /**
     * Get the query string property values
     *
     * @access public
     * @param string $attributeName Property Name
     * @return string
     */
    public function getAttribute($attributeName)
    {
        return isset($this->_sqlPreBuild[$attributeName]) ? $this->_sqlPreBuild[$attributeName] : NULL;
    }

    /**
     * Clear Query String property value
     *
     * @access public
     * @param string $attributeName Property Name
     * @return Typecho_Db_Query
     */
    public function cleanAttribute($attributeName)
    {
        if (isset($this->_sqlPreBuild[$attributeName])) {
            $this->_sqlPreBuild[$attributeName] = $this->_default[$attributeName];
        }
        return $this;
    }

    /**
     * Connection Table
     *
     * @param string $table Tables need to connect
     * @param string $condition Connection conditions
     * @param string $op Connection method (LEFT, RIGHT, INNER)
     * @return Typecho_Db_Query
     */
    public function join($table, $condition, $op = Typecho_Db::INNER_JOIN)
    {
        $this->_sqlPreBuild['join'][] = array($this->filterPrefix($table), $this->filterColumn($condition), $op);
        return $this;
    }

    /**
     * AND condition query
     *
     * @param string $condition Query
     * @param mixed $param Condition value
     * @return Typecho_Db_Query
     */
    public function where()
    {
        $condition = func_get_arg(0);
        $condition = str_replace('?', "%s", $this->filterColumn($condition));
        $operator = empty($this->_sqlPreBuild['where']) ? ' WHERE ' : ' AND';

        if (func_num_args() <= 1) {
            $this->_sqlPreBuild['where'] .= $operator . ' (' . $condition . ')';
        } else {
            $args = func_get_args();
            array_shift($args);
            $this->_sqlPreBuild['where'] .= $operator . ' (' . vsprintf($condition, $this->quoteValues($args)) . ')';
        }

        return $this;
    }

    /**
     * OR condition query
     *
     * @param string $condition Query
     * @param mixed $param Condition value
     * @return Typecho_Db_Query
     */
    public function orWhere()
    {
        $condition = func_get_arg(0);
        $condition = str_replace('?', "%s", $this->filterColumn($condition));
        $operator = empty($this->_sqlPreBuild['where']) ? ' WHERE ' : ' OR';

        if (func_num_args() <= 1) {
            $this->_sqlPreBuild['where'] .= $operator . ' (' . $condition . ')';
        } else {
            $args = func_get_args();
            array_shift($args);
            $this->_sqlPreBuild['where'] .= $operator . ' (' . vsprintf($condition, $this->quoteValues($args)) . ')';
        }

        return $this;
    }

    /**
     * Query to limit the number of rows
     *
     * @param integer $limit Need to query the number of rows
     * @return Typecho_Db_Query
     */
    public function limit($limit)
    {
        $this->_sqlPreBuild['limit'] = intval($limit);
        return $this;
    }

    /**
     * Query rows offset
     *
     * @param integer $offset Number of rows you want to shift
     * @return Typecho_Db_Query
     */
    public function offset($offset)
    {
        $this->_sqlPreBuild['offset'] = intval($offset);
        return $this;
    }

    /**
     * Query page
     *
     * @param integer $page Page
     * @param integer $pageSize The number of lines per page
     * @return Typecho_Db_Query
     */
    public function page($page, $pageSize)
    {
        $pageSize = intval($pageSize);
        $this->_sqlPreBuild['limit'] = $pageSize;
        $this->_sqlPreBuild['offset'] = (max(intval($page), 1) - 1) * $pageSize;
        return $this;
    }

    /**
     * Specify columns and their values ​​to be written
     *
     * @param array $rows
     * @return Typecho_Db_Query
     */
    public function rows(array $rows)
    {
        foreach ($rows as $key => $row) {
            $this->_sqlPreBuild['rows'][$this->filterColumn($key)] = is_null($row) ? 'NULL' : $this->_adapter->quoteValue($row);
        }
        return $this;
    }

    /**
     * Specify the columns and their values ​​to be written
     * One-way and will not escape quotes
     *
     * @param string $key Column Name
     * @param mixed $value Specified value
     * @return Typecho_Db_Query
     */
    public function expression($key, $value)
    {
        $this->_sqlPreBuild['rows'][$this->filterColumn($key)] = $this->filterColumn($value);
        return $this;
    }

    /**
     * Sort Order (ORDER BY)
     *
     * @param string $orderby Sorted index
     * @param string $sort Way of sort (ASC, DESC)
     * @return Typecho_Db_Query
     */
    public function order($orderby, $sort = Typecho_Db::SORT_ASC)
    {
        $this->_sqlPreBuild['order'] = ' ORDER BY ' . $this->filterColumn($orderby) . (empty($sort) ? NULL : ' ' . $sort);
        return $this;
    }

    /**
     * 集合聚集(GROUP BY)
     *
     * @param string $key Gather key
     * @return Typecho_Db_Query
     */
    public function group($key)
    {
        $this->_sqlPreBuild['group'] = ' GROUP BY ' . $this->filterColumn($key);
        return $this;
    }

    /**
     * HAVING (HAVING)
     *
     * @return Typecho_Db_Query
     */
    public function having()
    {
        $condition = func_get_arg(0);
        $condition = str_replace('?', "%s", $this->filterColumn($condition));
        $operator = empty($this->_sqlPreBuild['having']) ? ' HAVING ' : ' AND';

        if (func_num_args() <= 1) {
            $this->_sqlPreBuild['having'] .= $operator . ' (' . $condition . ')';
        } else {
            $args = func_get_args();
            array_shift($args);
            $this->_sqlPreBuild['having'] .= $operator . ' (' . vsprintf($condition, $this->quoteValues($args)) . ')';
        }

        return $this;
    }

    /**
     * Select the query field
     *
     * @access public
     * @param mixed $field Query field
     * @return Typecho_Db_Query
     */
    public function select($field = '*')
    {
        $this->_sqlPreBuild['action'] = Typecho_Db::SELECT;
        $args = func_get_args();

        $this->_sqlPreBuild['fields'] = $this->getColumnFromParameters($args);
        return $this;
    }

    /**
     * Query recording operation (SELECT)
     *
     * @param string $table Query table
     * @return Typecho_Db_Query
     */
    public function from($table)
    {
        $this->_sqlPreBuild['table'] = $this->filterPrefix($table);
        return $this;
    }

    /**
     * Update recording operation (UPDATE)
     *
     * @param string $table Need to update the record of the table
     * @return Typecho_Db_Query
     */
    public function update($table)
    {
        $this->_sqlPreBuild['action'] = Typecho_Db::UPDATE;
        $this->_sqlPreBuild['table'] = $this->filterPrefix($table);
        return $this;
    }

    /**
     * Delete recording operation (DELETE)
     *
     * @param string $table Need to delete table records
     * @return Typecho_Db_Query
     */
    public function delete($table)
    {
        $this->_sqlPreBuild['action'] = Typecho_Db::DELETE;
        $this->_sqlPreBuild['table'] = $this->filterPrefix($table);
        return $this;
    }

    /**
     * Insert recording operation (INSERT)
     *
     * @param string $table Need to insert a table record
     * @return Typecho_Db_Query
     */
    public function insert($table)
    {
        $this->_sqlPreBuild['action'] = Typecho_Db::INSERT;
        $this->_sqlPreBuild['table'] = $this->filterPrefix($table);
        return $this;
    }

    /**
     * Construct the final query
     *
     * @return string
     */
    public function __toString()
    {
        switch ($this->_sqlPreBuild['action']) {
            case Typecho_Db::SELECT:
                return $this->_adapter->parseSelect($this->_sqlPreBuild);
            case Typecho_Db::INSERT:
                return 'INSERT INTO '
                . $this->_sqlPreBuild['table']
                . '(' . implode(' , ', array_keys($this->_sqlPreBuild['rows'])) . ')'
                . ' VALUES '
                . '(' . implode(' , ', array_values($this->_sqlPreBuild['rows'])) . ')'
                . $this->_sqlPreBuild['limit'];
            case Typecho_Db::DELETE:
                return 'DELETE FROM '
                . $this->_sqlPreBuild['table']
                . $this->_sqlPreBuild['where'];
            case Typecho_Db::UPDATE:
                $columns = array();
                if (isset($this->_sqlPreBuild['rows'])) {
                    foreach ($this->_sqlPreBuild['rows'] as $key => $val) {
                        $columns[] = "$key = $val";
                    }
                }

                return 'UPDATE '
                . $this->_sqlPreBuild['table']
                . ' SET ' . implode(' , ', $columns)
                . $this->_sqlPreBuild['where'];
            default:
                return NULL;
        }
    }
}
