<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Hello World
 *
 * @package HelloWorld
 * @author qining
 * @version 1.0.0
 * @link http://typecho.org
 */
class HelloWorld_Plugin implements Typecho_Plugin_Interface
{
    /**
     * Activate the plugin method, if activation fails, an exception is thrown directly
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('admin/menu.php')->navBar = array('HelloWorld_Plugin', 'render');
    }

    /**
     * Disabling plug-in method, if the disable fails, an exception is thrown directly
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}

    /**
     * Get plugin configuration panel
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form Configuration panel
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** Category Name */
        $name = new Typecho_Widget_Helper_Form_Element_Text('word', NULL, 'Hello World', _t('Say something'));
        $form->addInput($name);
    }

    /**
     * Individual user's configuration panel
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * Plugin method
     *
     * @access public
     * @return void
     */
    public static function render()
    {
        echo '<span class="message success">'
            . htmlspecialchars(Typecho_Widget::widget('Widget_Options')->plugin('HelloWorld')->word)
            . '</span>';
    }
}
