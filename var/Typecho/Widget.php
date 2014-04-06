<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: Widget.php 107 2008-04-11 07:14:43Z magike.net $
 */

/**
 * Typecho Component base class
 *
 * @package Widget
 */
abstract class Typecho_Widget
{
    /**
     * Widget object pool
     *
     * @access private
     * @var array
     */
    private static $_widgetPool = array();

    /**
     * Helper list
     *
     * @access private
     * @var array
     */
    private $_helpers = array();

    /**
     * Each row of data stack
     *
     * @access protected
     * @var array
     */
    protected $row = array();

    /**
     * Data Stack
     *
     * @access public
     * @var array
     */
    public $stack = array();

    /**
     * The current value of the queue pointer sequence, starting with 1
     *
     * @access public
     * @var integer
     */
    public $sequence = 0;

    /**
     * Queue Length
     *
     * @access public
     * @var integer
     */
    public $length = 0;

    /**
     * Request object
     *
     * @var Typecho_Request
     * @access public
     */
    public $request;

    /**
     * Response object
     *
     * @var Typecho_Response
     * @access public
     */
    public $response;

    /**
     * Config object
     *
     * @access public
     * @var public
     */
    public $parameter;

    /**
     * Constructors, Init Components
     *
     * @access public
     * @param mixed $request request object
     * @param mixed $response response object
     * @param mixed $params Parameter List
     * @return void
     */
    public function __construct($request, $response, $params = NULL)
    {
        // Set the object inside a function
        $this->request = $request;
        $this->response = $response;
        $this->parameter = new Typecho_Config();

        if (!empty($params)) {
            $this->parameter->setDefault($params);
        }
    }

    /**
     * Resolve callback
     *
     * @param array $matches
     * @access protected
     * @return string
     */
    protected function __parseCallback($matches)
    {
        return $this->{$matches[1]};
    }

    /**
     * execute function.
     *
     * @access public
     * @return void
     */
    public function execute(){}

    /**
     * post event trigger
     *
     * @param boolean $condition Triggering conditions
     * @return mixed
     */
    public function on($condition)
    {
        if ($condition) {
            return $this;
        } else {
            return new Typecho_Widget_Helper_Empty();
        }
    }

    /**
     * Gets the plugin handles object
     *
     * @access public
     * @param string $handle Handle
     * @return Typecho_Plugin
     */
    public function pluginHandle($handle = NULL)
    {
        return Typecho_Plugin::factory(empty($handle) ? get_class($this) : $handle);
    }

    /**
     * Factory method, the class static place to the list
     *
     * @access public
     * @param string $alias Component alias
     * @param mixed $params Parameter passing
     * @param mixed $request Front-end parameters
     * @param boolean $enableResponse Whether to allow http receipt
     * @return object
     * @throws Typecho_Exception
     */
    public static function widget($alias, $params = NULL, $request = NULL, $enableResponse = true)
    {
        list($className) = explode('@', $alias);

        if (!isset(self::$_widgetPool[$alias])) {
            $fileName = str_replace('_', '/', $className) . '.php';
            require_once $fileName;

            /** If the class does not exist */
            if (!class_exists($className)) {
                throw new Typecho_Widget_Exception($className);
            }

            /** Init request */
            if (!empty($request)) {
                $requestObject = new Typecho_Request();
                $requestObject->setParams($request);
            } else {
                $requestObject = Typecho_Request::getInstance();
            }

            /** Init response */
            $responseObject = $enableResponse ? Typecho_Response::getInstance()
            : Typecho_Widget_Helper_Empty::getInstance();

            /** Init components */
            $widget = new $className($requestObject, $responseObject, $params);

            $widget->execute();
            self::$_widgetPool[$alias] = $widget;
        }

        return self::$_widgetPool[$alias];
    }

    /**
     * Release assembly
     *
     * @access public
     * @param string $alias Component name
     * @return void
     */
    public static function destory($alias)
    {
        if (isset(self::$_widgetPool[$alias])) {
            unset(self::$_widgetPool[$alias]);
        }
    }

    /**
     * The assignment class itself
     *
     * @param string $variable Variable name
     * @return void
     */
    public function to(&$variable)
    {
        return $variable = $this;
    }

    /**
     * All analytical data formatting within the stack
     *
     * @param string $format Data Format
     * @return void
     */
    public function parse($format)
    {
        while ($this->next()) {
            echo preg_replace_callback("/\{([_a-z0-9]+)\}/i",
                array($this, '__parseCallback'), $format);
        }
    }

    /**
     * The value of each row onto the stack
     *
     * @param array $value Value of each row
     * @return array
     */
    public function push(array $value)
    {
        // The line data in the order set
        $this->row = $value;
        $this->length ++;

        $this->stack[] = $value;
        return $value;
    }

    /**
     * According remainder output
     *
     * @access public
     * @param string $param Output value needs
     * @return void
     */
    public function alt()
    {
        $args = func_get_args();
        $num = func_num_args();
        $split = $this->sequence % $num;
        echo $args[(0 == $split ? $num : $split) -1];
    }

    /**
     * Order The output value
     *
     * @access public
     * @return void
     */
    public function sequence()
    {
        echo $this->sequence;
    }

    /**
     * Output data length
     *
     * @access public
     * @return void
     */
    public function length()
    {
        echo $this->length;
    }

    /**
     * Whether to return the stack is empty
     *
     * @return boolean
     */
    public function have()
    {
        return !empty($this->stack);
    }

    /**
     * Each line of the return value of the stack
     *
     * @return array
     */
    public function next()
    {
        if ($this->stack) {
            $this->row = @$this->stack[key($this->stack)];
            next($this->stack);
            $this->sequence ++;
        }

        if (!$this->row) {
            reset($this->stack);
            if ($this->stack) {
                $this->row = $this->stack[key($this->stack)];
            }

            $this->sequence = 0;
            return false;
        }

        return $this->row;
    }

    /**
     * The magic functions, for other functions articulated
     *
     * @access public
     * @param string $name Function name
     * @param array $args Function parameters
     * @return void
     */
    public function __call($name, $args)
    {
        echo $this->{$name};
    }

    /**
     * Magic function is used to obtain internal variables
     *
     * @access public
     * @param string $name Variable name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->row)) {
            return $this->row[$name];
        } else {
            $method = '___' . $name;

            if (method_exists($this, $method)) {
                return $this->$method();
            } else {
                $return = $this->pluginHandle()->trigger($plugged)->{$method}($this);
                if ($plugged) {
                    return $return;
                }
            }
        }

        return NULL;
    }

    /**
     * Value is set for each row stack
     *
     * @param string $name Corresponding key value
     * @param mixed $value The corresponding value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->row[$name] = $value;
    }

    /**
     * Verify that the stack value exists
     *
     * @access public
     * @param string $name
     * @return boolean
     */
    public function __isSet($name)
    {
        return isset($this->row[$name]);
    }
}
