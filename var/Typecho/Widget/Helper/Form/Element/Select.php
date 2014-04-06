<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Drop-down selection box helper
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Drop-down selection box helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Form_Element_Select extends Typecho_Widget_Helper_Form_Element
{
    /**
     * Select a value
     *
     * @access private
     * @var array
     */
    private $_options = array();

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
        $input = new Typecho_Widget_Helper_Layout('select');
        $this->container($input->setAttribute('name', $name)
        ->setAttribute('id', $name . '-0-' . self::$uniqueId));
        $this->label->setAttribute('for', $name . '-0-' . self::$uniqueId);
        $this->inputs[] = $input;

        foreach ($options as $value => $label) {
            $this->_options[$value] = new Typecho_Widget_Helper_Layout('option');
            $input->addItem($this->_options[$value]->setAttribute('value', $value)->html($label));
        }

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
        foreach ($this->_options as $option) {
            $option->removeAttribute('selected');
        }

        if (isset($this->_options[$value])) {
            $this->_options[$value]->setAttribute('selected', 'true');
        }
    }
}
