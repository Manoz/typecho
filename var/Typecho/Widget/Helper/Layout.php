<?php
/**
 * HTML layout helper
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * HTML layout helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Layout
{
    /**
     * Element list
     *
     * @access private
     * @var array
     */
    private $_items = array();

    /**
     * Form a list of properties
     *
     * @access private
     * @var array
     */
    private $_attributes = array();

    /**
     * The label name
     *
     * @access private
     * @var string
     */
    private $_tagName = 'div';

    /**
     * If self-closing
     *
     * @access private
     * @var boolean
     */
    private $_close = false;

    /**
     * Whether to force a self-closing
     *
     * @access private
     * @var boolean
     */
    private $_forceClose = NULL;

    /**
     * Internal data
     *
     * @access private
     * @var string
     */
    private $_html;

    /**
     * Parent node
     *
     * @access private
     * @var Typecho_Widget_Helper_Layout
     */
    private $_parent;

    /**
     * Set the constructors label name
     *
     * @access public
     * @param string $tagName The label name
     * @param array $attributes Attribute list
     * @return void
     */
    public function __construct($tagName = 'div', array $attributes = NULL)
    {
        $this->setTagName($tagName);

        if (!empty($attributes)) {
            foreach ($attributes as $attributeName => $attributeValue) {
                $this->setAttribute($attributeName, $attributeValue);
            }
        }
    }

    /**
     * Adding Elements
     *
     * @access public
     * @param Typecho_Widget_Helper_Layout $item Element
     * @return Typecho_Widget_Helper_Layout
     */
    public function addItem(Typecho_Widget_Helper_Layout $item)
    {
        $item->setParent($this);
        $this->_items[] = $item;
        return $this;
    }

    /**
     * Remove elements
     *
     * @access public
     * @param Typecho_Widget_Helper_Layout $item Element
     * @return Typecho_Widget_Helper_Layout
     */
    public function removeItem(Typecho_Widget_Helper_Layout $item)
    {
        unset($this->_items[array_search($item, $this->_items)]);
        return $this;
    }

    /**
     * getItems
     *
     * @access public
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Set internal data
     *
     * @access public
     * @param mixed $html Internal data
     * @return unknown
     */
    public function html($html = false)
    {
        if (false === $html) {
            if (empty($this->_html)) {
                foreach ($this->_items as $item) {
                    $item->render();
                }
            } else {
                echo $this->_html;
            }
        } else {
            $this->_html = $html;
            return $this;
        }
    }

    /**
     * Set the tag name
     *
     * @access public
     * @param string $tagName Label name
     * @return void
     */
    public function setTagName($tagName)
    {
        $this->_tagName = $tagName;
    }

    /**
     * getTagName
     *
     * @param mixed $tagName
     * @access public
     * @return void
     */
    public function getTagName($tagName)
    {}

    /**
     * Setting form properties
     *
     * @access public
     * @param string $attributeName Property Name
     * @param string $attributeValue Property Value
     * @return Typecho_Widget_Helper_Layout
     */
    public function setAttribute($attributeName, $attributeValue)
    {
        $this->_attributes[$attributeName] = $attributeValue;
        return $this;
    }

    /**
     * Remove a property
     *
     * @access public
     * @param string $attributeName Property Name
     * @return Typecho_Widget_Helper_Layout
     */
    public function removeAttribute($attributeName)
    {
        if (isset($this->_attributes[$attributeName])) {
            unset($this->_attributes[$attributeName]);
        }

        return $this;
    }

    /**
     * Get Properties
     *
     * @access public
     * @param string $attributeName Attribute name
     * @return string
     */
    public function getAttribute($attributeName)
    {
        return isset($this->_attributes[$attributeName]) ? $this->_attributes[$attributeName] : NULL;
    }

    /**
     * Setting self-closing
     *
     * @access public
     * @param boolean $close If self-closing
     * @return Typecho_Widget_Helper_Layout
     */
    public function setClose($close)
    {
        $this->_forceClose = $close;
        return $this;
    }

    /**
     * Set the parent node
     *
     * @access public
     * @param Typecho_Widget_Helper_Layout $parent Parent node
     * @return Typecho_Widget_Helper_Layout
     */
    public function setParent(Typecho_Widget_Helper_Layout $parent)
    {
        $this->_parent = $parent;
        return $this;
    }

    /**
     * Get the parent node
     *
     * @access public
     * @return Typecho_Widget_Helper_Layout
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Add to a collection of layouts element
     *
     * @access public
     * @param Typecho_Widget_Helper_Layout $parent Layout object
     * @return Typecho_Widget_Helper_Layout
     */
    public function appendTo(Typecho_Widget_Helper_Layout $parent)
    {
        $parent->addItem($this);
        return $this;
    }

    /**
     * Start tag
     *
     * @access public
     * @return void
     */
    public function start()
    {
        /** Output tab */
        echo $this->_tagName ? "<{$this->_tagName}" : NULL;

        /** Output Properties */
        foreach ($this->_attributes as $attributeName => $attributeValue) {
            echo " {$attributeName}=\"{$attributeValue}\"";
        }

        /** Support from closing */
        if (!$this->_close && $this->_tagName) {
            echo ">\n";
        }
    }

    /**
     * End tag
     *
     * @access public
     * @return void
     */
    public function end()
    {
        if ($this->_tagName) {
            echo $this->_close ? " />\n" : "</{$this->_tagName}>\n";
        }
    }

    /**
     * Set properties
     *
     * @access public
     * @param string $attributeName Property Name
     * @param string $attributeValue Property Value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    /**
     * Get Properties
     *
     * @access public
     * @param string $attributeName Property Name
     * @return void
     */
    public function __get($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : NULL;
    }

    /**
     * Output all elements
     *
     * @access public
     * @return void
     */
    public function render()
    {
        if (empty($this->_items) && empty($this->_html)) {
            $this->_close = true;
        }

        if (NULL !== $this->_forceClose) {
            $this->_close = $this->_forceClose;
        }

        $this->start();
        $this->html();
        $this->end();
    }
}
