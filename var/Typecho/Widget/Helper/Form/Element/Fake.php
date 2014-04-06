<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Virtual domain helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Virtual domain helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Form_Element_Fake extends Typecho_Widget_Helper_Form_Element
{
    /**
     * Constructors
     *
     * @access public
     * @param string $name Form entry name
     * @param mixed $value Form defaults
     * @return void
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        self::$uniqueId ++;

        /** Run a custom init function */
        $this->init();

        /** Initialization single table */
        $this->input = $this->input($name, $options);

        /** Initialization form values */
        if (NULL !== $value) {
            $this->value($value);
        }
    }

    /**
     * Custom init function
     *
     * @access public
     * @return void
     */
    public function init()
    {}

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
        $input = new Typecho_Widget_Helper_Layout('input');
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
        $this->input->setAttribute('value', $value);
    }
}

