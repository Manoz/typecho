<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Textarea fields helper
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Textarea fields helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Form_Element_Textarea extends Typecho_Widget_Helper_Form_Element
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
        $input = new Typecho_Widget_Helper_Layout('textarea', array('id' => $name . '-0-' . self::$uniqueId, 'name' => $name));
        $this->label->setAttribute('for', $name . '-0-' . self::$uniqueId);
        $this->container($input->setClose(false));
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
        $this->input->html(htmlspecialchars($value));
    }
}
