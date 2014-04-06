<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Form processing helper
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Form processing helper
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Widget_Helper_Form extends Typecho_Widget_Helper_Layout
{
    /** Form post Method */
    const POST_METHOD = 'post';

    /** Form get Method */
    const GET_METHOD = 'get';

    /** Standard encoding method */
    const STANDARD_ENCODE = 'application/x-www-form-urlencoded';

    /** Hybrid encoding */
    const MULTIPART_ENCODE = 'multipart/form-data';

    /** Text encoding */
    const TEXT_ENCODE= 'text/plain';

    /**
     * Enter a list of elements
     *
     * @access private
     * @var array
     */
    private $_inputs = array();

    /**
     * Set the basic constructors properties
     *
     * @access public
     * @return void
     */
    public function __construct($action = NULL, $method = self::GET_METHOD, $enctype = self::STANDARD_ENCODE)
    {
        /** Set form tag */
        parent::__construct('form');

        /** Close self-closing */
        $this->setClose(false);

        /** Setting form properties */
        $this->setAction($action);
        $this->setMethod($method);
        $this->setEncodeType($enctype);
    }

    /**
     * Set the form encoding scheme
     *
     * @access public
     * @param string $enctype Encoding method
     * @return Typecho_Widget_Helper_Form
     */
    public function setEncodeType($enctype)
    {
        $this->setAttribute('enctype', $enctype);
        return $this;
    }

    /**
     * Increase the input element
     *
     * @access public
     * @param Typecho_Widget_Helper_Form_Abstract $input Input element
     * @return Typecho_Widget_Helper_Form
     */
    public function addInput(Typecho_Widget_Helper_Form_Element $input)
    {
        $this->_inputs[$input->name] = $input;
        $this->addItem($input);
        return $this;
    }

    /**
     * Adding Elements (overloads)
     *
     * @access public
     * @param Typecho_Widget_Helper_Layout $item Form elements
     * @return Typecho_Widget_Helper_Layout
     */
    public function addItem(Typecho_Widget_Helper_Layout $item)
    {
        if ($item instanceof Typecho_Widget_Helper_Form_Submit) {
            $this->addItem($item);
        } else {
            parent::addItem($item);
        }

        return $this;
    }

    /**
     * Get entry
     *
     * @access public
     * @param string $name Item name input
     * @return mixed
     */
    public function getInput($name)
    {
        return $this->_inputs[$name];
    }

    /**
     * Get all the submitted entries values
     *
     * @access public
     * @return array
     */
    public function getAllRequest()
    {
        $result = array();
        $source = (self::POST_METHOD == $this->getAttribute('method')) ? $_POST : $_GET;

        foreach ($this->_inputs as $name => $input) {
            $result[$name] = isset($source[$name]) ? $source[$name] : NULL;
        }
        return $result;
    }

    /**
     * Set the form submission method
     *
     * @access public
     * @param string $method Form submission method
     * @return Typecho_Widget_Helper_Form
     */
    public function setMethod($method)
    {
        $this->setAttribute('method', $method);
        return $this;
    }

    /**
     * The purpose of setting a form submission
     *
     * @access public
     * @param string $action The purpose of the form is submitted
     * @return Typecho_Widget_Helper_Form
     */
    public function setAction($action)
    {
        $this->setAttribute('action', $action);
        return $this;
    }

    /**
     * Get this inherent form value for all entries
     *
     * @access public
     * @return array
     */
    public function getValues()
    {
        $values = array();

        foreach ($this->_inputs as $name => $input) {
            $values[$name] = $input->value;
        }
        return $values;
    }

    /**
     * Get all the entries of this form
     *
     * @access public
     * @return array
     */
    public function getInputs()
    {
        return $this->_inputs;
    }

    /**
     * Get submited data source
     *
     * @access public
     * @param array $params Set Data parameter
     * @return array
     */
    public function getParams(array $params)
    {
        $result = array();
        $source = (self::POST_METHOD == $this->getAttribute('method')) ? $_POST : $_GET;

        foreach ($params as $param) {
            $result[$param] = isset($source[$param]) ? $source[$param] : NULL;
        }

        return $result;
    }

    /**
     * Form verification
     *
     * @access public
     * @return void
     */
    public function validate()
    {
        $validator = new Typecho_Validate();
        $rules = array();

        foreach ($this->_inputs as $name => $input) {
            $rules[$name] = $input->rules;
        }

        $id = md5(implode('"', array_keys($this->_inputs)));

        /** Form values */
        $formData = $this->getParams(array_keys($rules));
        $error = $validator->run($formData, $rules);

        if ($error) {
            /** Use session recording errors */
            $_SESSION['__typecho_form_message_' . $id] = $error;

            /** Use session record form values */
            $_SESSION['__typecho_form_record_' . $id] = $formData;
        }

        return $error;
    }

    /**
     * Display Form
     *
     * @access public
     * @return void
     */
    public function render()
    {
        $id = md5(implode('"', array_keys($this->_inputs)));

        /** Restore form values */
        if (isset($_SESSION['__typecho_form_record_' . $id])) {
            $record = $_SESSION['__typecho_form_record_' . $id];
            $message = $_SESSION['__typecho_form_message_' . $id];
            foreach ($this->_inputs as $name => $input) {
                $input->value(isset($record[$name]) ? $record[$name] : $input->value);

                /** Error message */
                if (isset($message[$name])) {
                    $input->message($message[$name]);
                }
            }

            unset($_SESSION['__typecho_form_record_' . $id]);
        }

        parent::render();
        unset($_SESSION['__typecho_form_message_' . $id]);
    }
}
