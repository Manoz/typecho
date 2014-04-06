<?php
/**
 * Plugin interface
 *
 * @category typecho
 * @package Plugin
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Plugin interface
 *
 * @package Plugin
 * @abstract
 */
interface Typecho_Plugin_Interface
{
    /**
     * Enable plugin method, if it fails to enable throw an exception
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate();

    /**
     * Disabling plugin method, if it fails, an exception is thrown directly
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate();

    /**
     * Get plugin configuration panel
     *
     * @static
     * @access public
     * @param Typecho_Widget_Helper_Form $form Configuration panel
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form);

    /**
     * Individual user's configuration panel
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form);
}
