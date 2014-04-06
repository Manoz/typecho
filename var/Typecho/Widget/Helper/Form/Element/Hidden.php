<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Hide domain helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Hide domain helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Form_Element_Hidden extends Typecho_Widget_Helper_Form_Element
{
    /**
     * Custom init function
     *
     * @access public
     * @return void
     */
    public function init()
    {
        /** Hide this rows */
        $this->setAttribute('style', 'display:none');
    }

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
        $input = new Typecho_Widget_Helper_Layout('input', array('name' => $name, 'type' => 'hidden'));
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
