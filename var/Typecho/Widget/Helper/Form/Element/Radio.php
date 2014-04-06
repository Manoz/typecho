<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Single box helper
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Single box helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Form_Element_Radio extends Typecho_Widget_Helper_Form_Element
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
        foreach ($options as $value => $label) {
            $this->_options[$value] = new Typecho_Widget_Helper_Layout('input');
            $item = $this->multiline();
            $id = $this->name . '-' . $this->filterValue($value);
            $this->inputs[] = $this->_options[$value];

            $item->addItem($this->_options[$value]->setAttribute('name', $this->name)
            ->setAttribute('type', 'radio')
            ->setAttribute('value', $value)
            ->setAttribute('id', $id));

            $labelItem = new Typecho_Widget_Helper_Layout('label');
            $item->addItem($labelItem->setAttribute('for', $id)->html($label));
            $this->container($item);
        }

        return current($this->_options);
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
            $option->removeAttribute('checked');
        }

        if (isset($this->_options[$value])) {
            $this->value = $value;
            $this->_options[$value]->setAttribute('checked', 'true');
            $this->input = $this->_options[$value];
        }
    }
}
