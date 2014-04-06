<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id$
 */

/**
 * Classic style pagination
 *
 * @author qining
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_PageNavigator_Classic extends Typecho_Widget_Helper_PageNavigator
{
    /**
     * Classic style pagination output
     *
     * @access public
     * @param string $prevWord Previous text
     * @param string $nextWord Next text
     * @return void
     */
    public function render($prevWord = 'PREV', $nextWord = 'NEXT')
    {
        $this->prev($prevWord);
        $this->next($nextWord);
    }

    /**
     * Output Previous
     *
     * @access public
     * @param string $prevWord Previous text
     * @return void
     */
    public function prev($prevWord = 'PREV')
    {
        // Output Previous
        if ($this->_total > 0 && $this->_currentPage > 1) {
            echo '<a class="prev" href="' . str_replace($this->_pageHolder, $this->_currentPage - 1, $this->_pageTemplate) . $this->_anchor . '">'
            . $prevWord . '</a>';
        }
    }

    /**
     * Output Next
     *
     * @access public
     * @param string $prevWord Next text
     * @return void
     */
    public function next($nextWord = 'NEXT')
    {
        // Output Next
        if ($this->_total > 0 && $this->_currentPage < $this->_totalPage) {
            echo '<a class="next" title="" href="' . str_replace($this->_pageHolder, $this->_currentPage + 1, $this->_pageTemplate) . $this->_anchor . '">'
            . $nextWord . '</a>';
        }
    }
}
