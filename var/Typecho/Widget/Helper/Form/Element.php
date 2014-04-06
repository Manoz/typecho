<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Form element helper
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Form element helper class
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
abstract class Typecho_Widget_Helper_Form_Element extends Typecho_Widget_Helper_Layout
{
    /**
     * Form Description
     *
     * @access private
     * @var string
     */
    protected $description;

    /**
     * Form Message
     *
     * @access protected
     * @var string
     */
    protected $message;

    /**
     * Multi-line input
     *
     * @access public
     * @var array()
     */
    protected $multiline = array();

    /**
     * Single case unique id
     *
     * @access protected
     * @var integer
     */
    protected static $uniqueId = 0;

    /**
     * Form element container
     *
     * @access public
     * @var Typecho_Widget_Helper_Layout
     */
    public $container;

    /**
     * Input field
     *
     * @access public
     * @var Typecho_Widget_Helper_Layout
     */
    public $input;

    /**
     * Inputs
     *
     * @var array
     * @access public
     */
    public $inputs = array();

    /**
     * Form title
     *
     * @access public
     * @var Typecho_Widget_Helper_Layout
     */
    public $label;

    /**
     * Form Validator
     *
     * @access public
     * @var array
     */
    public $rules = array();

    /**
     * Form Name
     *
     * @access public
     * @var string
     */
    public $name;

    /**
     * Form values
     *
     * @access public
     * @var mixed
     */
    public $value;

    /**
     * Constructors
     *
     * @access public
     * @param string $name Form entry name
     * @param array $options Select items
     * @param mixed $value Form defaults
     * @param string $label Form title
     * @param string $description Form Description
     * @return void
     */
    public function __construct($name = NULL, array $options = NULL, $value = NULL, $label = NULL, $description = NULL)
    {
        /** Create html element, and set the class */
        parent::__construct('ul', array('class' => 'typecho-option', 'id' => 'typecho-option-item-' . $name . '-' . self::$uniqueId));
        $this->name = $name;
        self::$uniqueId ++;

        /** Run a custom init function */
        $this->init();

        /** Form title Init */
        if (NULL !== $label) {
            $this->label($label);
        }

        /** Initialization single table */
        $this->input = $this->input($name, $options);

        /** Initialization form values */
        if (NULL !== $value) {
            $this->value($value);
        }

        /** Form Description init */
        if (NULL !== $description) {
            $this->description($description);
        }
    }

    /**
     * filterValue
     *
     * @param mixed $value
     * @access protected
     * @return string
     */
    protected function filterValue($value)
    {
        if (preg_match_all('/[_0-9a-z-]+/i', $value, $matches)) {
            return implode('-', $matches[0]);
        }

        return '';
    }

    /**
     * Custom init function
     *
     * @access public
     * @return void
     */
    public function init(){}

    /**
     * Create the form title
     *
     * @access public
     * @param string $value Title string
     * @return Typecho_Widget_Helper_Form_Element
     */
    public function label($value)
    {
        /** Create a header element */
        if (empty($this->label)) {
            $this->label = new Typecho_Widget_Helper_Layout('label', array('class' => 'typecho-label'));
            $this->container($this->label);
        }

        $this->label->html($value);
        return $this;
    }

    /**
     * Increase the container element
     *
     * @access public
     * @param Typecho_Widget_Helper_Layout $item Form elements
     * @return $this
     */
    public function container(Typecho_Widget_Helper_Layout $item)
    {
        /** Create a form container */
        if (empty($this->container)) {
            $this->container = new Typecho_Widget_Helper_Layout('li');
            $this->addItem($this->container);
        }

        $this->container->addItem($item);
        return $this;
    }

    /**
     * Set messages
     *
     * @access public
     * @param string $message Message
     * @return Typecho_Widget_Helper_Form_Element
     */
    public function message($message)
    {
        if (empty($this->message)) {
            $this->message =  new Typecho_Widget_Helper_Layout('p', array('class' => 'message error'));
            $this->container($this->message);
        }

        $this->message->html($message);
        return $this;
    }

    /**
     * Set a description
     *
     * @access public
     * @param string $description Description
     * @return Typecho_Widget_Helper_Form_Element
     */
    public function description($description)
    {
        /** Create the description of the elements */
        if (empty($this->description)) {
            $this->description = new Typecho_Widget_Helper_Layout('p', array('class' => 'description'));
            $this->container($this->description);
        }

        $this->description->html($description);
        return $this;
    }

    /**
     * Set the value of the form element
     *
     * @access public
     * @param mixed $value Form element values
     * @return Typecho_Widget_Helper_Form_Element
     */
    public function value($value)
    {
        $this->value = $value;
        $this->_value($value);
        return $this;
    }

    /**
     * Multi-line output mode
     *
     * @access public
     * @return Typecho_Widget_Helper_Layout
     */
    public function multiline()
    {
        $item = new Typecho_Widget_Helper_Layout('span');
        $this->multiline[] = $item;
        return $item;
    }

    /**
     * Multi-line output mode
     *
     * @access public
     * @return Typecho_Widget_Helper_Form_Element
     */
    public function multiMode()
    {
        foreach ($this->multiline as $item) {
            $item->setAttribute('class', 'multiline');
        }
        return $this;
    }

    /**
     * Initialize the current entry
     *
     * @access public
     * @param Typecho_Widget_Helper_Layout $container Container object
     * @param string $name Form elements name
     * @param array $options Select items
     * @return Typecho_Widget_Helper_Form_Element
     */
    abstract public function input($name = NULL, array $options = NULL);

    /**
     * Set the value of the form element
     *
     * @access protected
     * @param mixed $value Form element values
     * @return void
     */
    abstract protected function _value($value);

    /**
     * Increase validator
     *
     * @access public
     * @return Typecho_Widget_Helper_Form_Element
     */
    public function addRule($name)
    {
        $this->rules[] = func_get_args();
        return $this;
    }
}
