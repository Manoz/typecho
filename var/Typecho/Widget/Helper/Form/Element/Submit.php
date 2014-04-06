<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Submit button single helper
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Submit button single helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Form_Element_Submit extends Typecho_Widget_Helper_Form_Element
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
        $this->setAttribute('class', 'typecho-option typecho-option-submit');
        $input = new Typecho_Widget_Helper_Layout('button', array('type' => 'submit'));
        $this->container($input);
        $this->inputs[] = $input;

        return $input;
    }

    /**
     * Set the value of the form element
     *
     * @access protected
     * @param mixed $value Form element values
     * @return void
     */
    protected function _value($value)
    {
        $this->input->html($value);
    }
}
