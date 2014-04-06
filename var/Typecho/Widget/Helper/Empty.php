<?php
/**
 * Widget object helper
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Widget object helper methods for handling null object
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Empty
{
    /**
     * Single Handle Cases
     *
     * @access private
     * @var Typecho_Widget_Helper_Empty
     */
    private static $_instance = null;

    /**
     * Get a handle to Single case
     *
     * @access public
     * @return Typecho_Widget_Helper_Empty
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new Typecho_Widget_Helper_Empty();
        }

        return self::$_instance;
    }

    /**
     * All methods return requests directly
     *
     * @access public
     * @param string $name Method name
     * @param array $args Parameter List
     * @return void
     */
    public function __call($name, $args)
    {
        return;
    }
}
