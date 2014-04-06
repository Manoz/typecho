<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Single table Helper password
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Single table Helper password class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Form_Element_Password extends Typecho_Widget_Helper_Form_Element
{
    /**
     * Initialize the current entry
     *
     * @access public
     * @param string $name Form elements name
     * @param array $options Select items
     * @return Typecho_Widget_Helper_Layout
     */
    public function input($name = NULL, array $options = NULL)
    {
        $input = new Typecho_Widget_Helper_Layout('input', array('id' => $name . '-0-' . self::$uniqueId,
        'name' => $name, 'type' => 'password', 'class' => 'password'));
        $this->label->setAttribute('for', $name . '-0-' . self::$uniqueId);
        $this->container($input);
        $this->inputs[] = $input;
        return $input;
    }

    /**
     * Set a single default value in the table
     *
     * @access protected
     * @param string $value Table single default value
     * @return void
     */
    protected function _value($value)
    {
        $this->input->setAttribute('value', htmlspecialchars($value));
    }
}
