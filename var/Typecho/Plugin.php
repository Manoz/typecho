<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id$
 */

/**
 * Plugin processing class
 *
 * @category typecho
 * @package Plugin
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Plugin
{
    /**
     * All enabled plugin
     *
     * @access private
     * @var array
     */
    private static $_plugins = array();

    /**
     * File already loaded
     *
     * @access private
     * @var array
     */
    private static $_required = array();

    /**
     * The plugin object instantiation
     *
     * @access private
     * @var array
     */
    private static $_instances;

    /**
     * Temporary storage variable
     *
     * @access private
     * @var array
     */
    private static $_tmp = array();

    /**
     * A unique handle
     *
     * @access private
     * @var string
     */
    private $_handle;

    /**
     * Package
     *
     * @access private
     * @var string
     */
    private $_component;

    /**
     * Plugin trigger signal
     *
     * @access private
     * @var boolean
     */
    private $_signal;

    /**
     * Plugin init
     *
     * @access public
     * @param string $handle Plugin
     * @return void
     */
    public function __construct($handle)
    {
        /** Init variables */
        $this->_handle = $handle;
    }

    /**
     * Plugin handle comparison
     *
     * @access private
     * @param array $pluginHandles
     * @param array $otherPluginHandles
     * @return void
     */
    private static function pluginHandlesDiff(array $pluginHandles, array $otherPluginHandles)
    {
        foreach ($otherPluginHandles as $handle) {
            while (false !== ($index = array_search($handle, $pluginHandles))) {
                unset($pluginHandles[$index]);
            }
        }

        return $pluginHandles;
    }

    /**
     * Plugin init
     *
     * @access public
     * @param array $plugins Plugin list
     * @param mixed $callback Get plugin system variables proxy function
     * @return void
     */
    public static function init(array $plugins)
    {
        $plugins['activated'] = array_key_exists('activated', $plugins) ? $plugins['activated'] : array();
        $plugins['handles'] = array_key_exists('handles', $plugins) ? $plugins['handles'] : array();

        /** Init variables */
        self::$_plugins = $plugins;
    }

    /**
     * Get plugin object instantiation
     *
     * @access public
     * @return Typecho_Plugin
     */
    public static function factory($handle)
    {
        return isset(self::$_instances[$handle]) ? self::$_instances[$handle] :
        (self::$_instances[$handle] = new Typecho_Plugin($handle));
    }

    /**
     * Enable plugins
     *
     * @access public
     * @param string $pluginName Plugin Name
     * @return void
     */
    public static function activate($pluginName)
    {
        self::$_plugins['activated'][$pluginName] = self::$_tmp;
        self::$_tmp = array();
    }

    /**
     * Disable plugin
     *
     * @access public
     * @param string $pluginName Plugin Name
     * @return void
     */
    public static function deactivate($pluginName)
    {
        /** Remove all callbacks */
        if (isset(self::$_plugins['activated'][$pluginName]['handles']) && is_array(self::$_plugins['activated'][$pluginName]['handles'])) {
            foreach (self::$_plugins['activated'][$pluginName]['handles'] as $handle => $handles) {
                self::$_plugins['handles'][$handle] = self::pluginHandlesDiff(
                empty(self::$_plugins['handles'][$handle]) ? array() : self::$_plugins['handles'][$handle],
                empty($handles) ? array() : $handles);
                if (empty(self::$_plugins['handles'][$handle])) {
                    unset(self::$_plugins['handles'][$handle]);
                }
            }
        }

        /** Disable the current plugin */
        unset(self::$_plugins['activated'][$pluginName]);
    }

    /**
     * Export current plugin settings
     *
     * @access public
     * @return array
     */
    public static function export()
    {
        return self::$_plugins;
    }

    /**
     * Get plugin file header information
     *
     * @access public
     * @param string $pluginFile Plugin file path
     * @return array
     */
    public static function parseInfo($pluginFile)
    {
        $tokens = token_get_all(file_get_contents($pluginFile));
        $isDoc = false;
        $isFunction = false;
        $isClass = false;
        $isInClass = false;
        $isInFunction = false;
        $isDefined = false;
        $current = NULL;

        /** 初始信息 */
        $info = array(
            'description'       => '',
            'title'             => '',
            'author'            => '',
            'homepage'          => '',
            'version'           => '',
            'dependence'        => '',
            'activate'          => false,
            'deactivate'        => false,
            'config'            => false,
            'personalConfig'    => false
        );

        $map = array(
            'package'   =>  'title',
            'author'    =>  'author',
            'link'      =>  'homepage',
            'dependence'=>  'dependence',
            'version'   =>  'version'
        );

        foreach ($tokens as $token) {
            /** Get doc comment */
            if (!$isDoc && is_array($token) && T_DOC_COMMENT == $token[0]) {

                /** Branch reading */
                $described = false;
                $lines = preg_split("(\r|\n)", $token[1]);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line) && '*' == $line[0]) {
                        $line = trim(substr($line, 1));
                        if (!$described && !empty($line) && '@' == $line[0]) {
                            $described = true;
                        }

                        if (!$described && !empty($line)) {
                            $info['description'] .= $line . "\n";
                        } else if ($described && !empty($line) && '@' == $line[0]) {
                            $info['description'] = trim($info['description']);
                            $line = trim(substr($line, 1));
                            $args = explode(' ', $line);
                            $key = array_shift($args);

                            if (isset($map[$key])) {
                                $info[$map[$key]] = trim(implode(' ', $args));
                            }
                        }
                    }
                }

                $isDoc = true;
            }

            if (is_array($token)) {
                switch ($token[0]) {
                    case T_FUNCTION:
                        $isFunction = true;
                        break;
                    case T_IMPLEMENTS:
                        $isClass = true;
                        break;
                    case T_WHITESPACE:
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        break;
                    case T_STRING:
                        $string = strtolower($token[1]);
                        switch ($string) {
                            case 'typecho_plugin_interface':
                                $isInClass = $isClass;
                                break;
                            case 'activate':
                            case 'deactivate':
                            case 'config':
                            case 'personalconfig':
                                if ($isFunction) {
                                    $current = ('personalconfig' == $string ? 'personalConfig' : $string);
                                }
                                break;
                            default:
                                if (!empty($current) && $isInFunction && $isInClass) {
                                    $info[$current] = true;
                                }
                                break;
                        }
                        break;
                    default:
                        if (!empty($current) && $isInFunction && $isInClass) {
                            $info[$current] = true;
                        }
                        break;
                }
            } else {
                $token = strtolower($token);
                switch ($token) {
                    case '{':
                        if ($isDefined) {
                            $isInFunction = true;
                        }
                        break;
                    case '(':
                        if ($isFunction && !$isDefined) {
                            $isDefined = true;
                        }
                        break;
                    case '}':
                    case ';':
                        $isDefined = false;
                        $isFunction = false;
                        $isInFunction = false;
                        $current = NULL;
                        break;
                    default:
                        if (!empty($current) && $isInFunction && $isInClass) {
                            $info[$current] = true;
                        }
                        break;
                }
            }
        }

        return $info;
    }

    /**
     * Get plugin path and name of the class
     * The return value is an array
     * The first term is the plugin path, the second is the class name
     *
     * @access public
     * @param string $pluginName Plugin name
     * @param string $path Plugin directory
     * @return array
     */
    public static function portal($pluginName, $path)
    {
        switch (true) {
            case file_exists($pluginFileName = $path . '/' . $pluginName . '/Plugin.php'):
                $className = $pluginName . '_Plugin';
                break;
            case file_exists($pluginFileName = $path . '/' . $pluginName . '.php'):
                $className = $pluginName;
                break;
            default:
                throw new Typecho_Plugin_Exception('Missing Plugin ' . $pluginName, 404);
        }

        return array($pluginFileName, $className);
    }

    /**
     * Version dependency detection
     *
     * @access public
     * @param string $version Version
     * @param string $versionRange 依赖的版本规则
     * @return boolean
     */
    public static function checkDependence($version, $versionRange)
    {
        // If the rule is not detected, directly passing
        if (empty($versionRange)) {
            return true;
        }

        $items = array_map('trim', explode('-', $versionRange));
        if (count($items) < 2) {
            $items[1] = $items[0];
        }

        list ($minVersion, $maxVersion) = $items;

        // For * and ? support, four 9 is the largest version
        $minVersion = str_replace(array('*', '?'), array('9999', '9'), $minVersion);
        $maxVersion = str_replace(array('*', '?'), array('9999', '9'), $maxVersion);

        if (version_compare($version, $minVersion, '>=') && version_compare($version, $maxVersion, '<=')) {
            return true;
        }

        return false;
    }

    /**
     * After calling trigger plugin
     *
     * @access public
     * @param boolean $signal Trigger
     * @return Typecho_Plugin
     */
    public function trigger(&$signal)
    {
        $signal = false;
        $this->_signal = &$signal;
        return $this;
    }

    /**
     * Determine whether there are plugins
     *
     * @access public
     * @param string $pluginName Plugin Name
     * @return void
     */
    public function exists($pluginName) {
        return array_search($pluginName, self::$_plugins['activated']);
    }

    /**
     * Set a callback function
     *
     * @access public
     * @param string $component Current component
     * @param mixed $value The callback function
     * @return void
     */
    public function __set($component, $value)
    {
        $weight = 0;

        if (strpos($component, '_') > 0) {
            $parts = explode('_', $component, 2);
            list($component, $weight) = $parts;
            $weight = intval($weight) - 10;
        }

        $component = $this->_handle . ':' . $component;

        if (!isset(self::$_plugins['handles'][$component])) {
            self::$_plugins['handles'][$component] = array();
        }

        if (!isset(self::$_tmp['handles'][$component])) {
            self::$_tmp['handles'][$component] = array();
        }

        foreach (self::$_plugins['handles'][$component] as $key => $val) {
            $key = floatval($key);

            if ($weight > $key) {
                break;
            } else if ($weight == $key) {
                $weight += 0.001;
            }
        }

        self::$_plugins['handles'][$component][strval($weight)] = $value;
        self::$_tmp['handles'][$component][] = $value;

        ksort(self::$_plugins['handles'][$component], SORT_NUMERIC);
    }

    /**
     * Set the current position of the component by a magic function
     *
     * @access public
     * @param string $component Current component
     * @return Typecho_Plugin
     */
    public function __get($component)
    {
        $this->_component = $component;
        return $this;
    }

    /**
     * Callback handler
     *
     * @access public
     * @param string $component Current component
     * @param string $args Parameters
     * @return mixed
     */
    public function __call($component, $args)
    {
        $component = $this->_handle . ':' . $component;
        $last = count($args);
        $args[$last] = $last > 0 ? $args[0] : false;

        if (isset(self::$_plugins['handles'][$component])) {
            $args[$last] = NULL;
            $this->_signal = true;
            foreach (self::$_plugins['handles'][$component] as $callback) {
                $args[$last] = call_user_func_array($callback, $args);
            }
        }

        return $args[$last];
    }
}
