<?php
/**
 * Configuration Management
 *
 * @category typecho
 * @package Config
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Configuration Management
 *
 * @category typecho
 * @package Config
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Config implements Iterator
{
    /**
     * Current configuration
     *
     * @access private
     * @var array
     */
    private $_currentConfig = array();

    /**
     * Instantiate the current configuration
     *
     * @access public
     * @param mixed $config Configuration List
     */
    public function __construct($config = array())
    {
        /** Init parameters */
        $this->setDefault($config);
    }

    /**
     * Factory mode to instantiate the current configuration
     *
     * @access public
     * @param array $config Configuration List
     * @return Typecho_Config
     */
    public static function factory($config = array())
    {
        return new Typecho_Config($config);
    }

    /**
     * The default configuration settings
     *
     * @access public
     * @param mixed $config Configuration Information
     * @param boolean $replace Whether to replace the existing information
     * @return void
     */
    public function setDefault($config, $replace = false)
    {
        if (empty($config)) {
            return;
        }

        /** Init parameters */
        if (is_string($config)) {
            parse_str($config, $params);
        } else {
            $params = $config;
        }

        /** Set the default Argument */
        foreach ($params as $name => $value) {
            if ($replace || !array_key_exists($name, $this->_currentConfig)) {
                $this->_currentConfig[$name] = $value;
            }
        }
    }

    /**
     * Reset pointer
     *
     * @access public
     * @return void
     */
    public function rewind()
    {
        reset($this->_currentConfig);
    }

    /**
     * Returns the current value
     *
     * @access public
     * @return mixed
     */
    public function current()
    {
        return current($this->_currentConfig);
    }

    /**
     * After moving the pointer
     *
     * @access public
     * @return void
     */
    public function next()
    {
        next($this->_currentConfig);
    }

    /**
     * Get the current pointer
     *
     * @access public
     * @return mixed
     */
    public function key()
    {
        return key($this->_currentConfig);
    }

    /**
     * Verify whether the current value reaches the final
     *
     * @access public
     * @return boolean
     */
    public function valid()
    {
        return false !== $this->current();
    }

    /**
     * Magic function to get a configuration value
     *
     * @access public
     * @param string $name Configuration Name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->_currentConfig[$name]) ? $this->_currentConfig[$name] : NULL;
    }

    /**
     * Magic function to set a configuration value
     *
     * @access public
     * @param string $name Configuration Name
     * @param mixed $value Configuration values
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_currentConfig[$name] = $value;
    }

    /**
     * Direct output default configuration values
     *
     * @access public
     * @param string $name Configuration Name
     * @param array $args Arguments
     * @return void
     */
    public function __call($name, $args)
    {
        echo $this->_currentConfig[$name];
    }

    /**
     * Analyzing if the current configuration value exists
     *
     * @access public
     * @param string $name Configuration Name
     * @return boolean
     */
    public function __isSet($name)
    {
        return isset($this->_currentConfig[$name]);
    }

    /**
     * Magic method, print the current configuration array
     *
     * @access public
     * @return string
     */
    public function __toString()
    {
        return serialize($this->_currentConfig);
    }
}
