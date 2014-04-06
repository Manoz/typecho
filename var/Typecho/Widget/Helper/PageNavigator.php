<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id$
 */

/**
 * Paging abstract class
 *
 * @package Widget
 */
abstract class Typecho_Widget_Helper_PageNavigator
{
    /**
     * Total number of records
     *
     * @access protected
     * @var integer
     */
    protected $_total;

    /**
     * Total number of pages
     *
     * @access protected
     * @var integer
     */
    protected $_totalPage;

    /**
     * Current page
     *
     * @access protected
     * @var integer
     */
    protected $_currentPage;

    /**
     * Number of page content
     *
     * @access protected
     * @var integer
     */
    protected $_pageSize;

    /**
     * Template page links
     *
     * @access protected
     * @var string
     */
    protected $_pageTemplate;

    /**
     * Anchor link
     *
     * @access protected
     * @var string
     */
    protected $_anchor;

    /**
     * Placeholder page
     *
     * @access protected
     * @var mixed
     */
    protected $_pageHolder = array('{page}', '%7Bpage%7D');

    /**
     * Init basic page information constructors
     *
     * @access public
     * @param integer $total Total number of records
     * @param integer $page Current page
     * @param integer $pageSize The number of records per page
     * @param string $pageTemplate Template page links
     * @return void
     */
    public function __construct($total, $currentPage, $pageSize, $pageTemplate)
    {
        $this->_total = $total;
        $this->_totalPage = ceil($total / $pageSize);
        $this->_currentPage = $currentPage;
        $this->_pageSize = $pageSize;
        $this->_pageTemplate = $pageTemplate;

        if (($currentPage > $this->_totalPage || $currentPage < 1) && $total > 0) {
            throw new Typecho_Widget_Exception('Page Not Exists', 404);
        }
    }

    /**
     * Settings page placeholder
     *
     * @access protected
     * @param string $holder Page placeholder
     * @return void
     */
    public function setPageHolder($holder)
    {
        $this->_pageHolder = array('{' . $holder . '}',
        str_replace(array('{', '}'), array('%7B', '%7D'), $holder));
    }

    /**
     * Set anchor
     *
     * @access public
     * @param string $anchor Anchor
     * @return void
     */
    public function setAnchor($anchor)
    {
        $this->_anchor = '#' . $anchor;
    }

    /**
     * Output method
     *
     * @access public
     * @return void
     */
    public function render()
    {
        throw new Typecho_Widget_Exception(get_class($this) . ':' . __METHOD__, 500);
    }
}
