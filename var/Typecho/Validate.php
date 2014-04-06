<?php
// vim: set et sw=4 ts=4 sts=4 fdm=marker ff=unix fenc=utf8
/**
 * Typecho Blog Platform
 *
 * Validation class
 * <code>
 * $test = "hello";
 * $Validation  = new TypechoValidation();
 * $Validation->form($test, array("alpha" => "不是字符");
 * var_dump($Validation->getErrorMsg());
 * </code>
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: Validation.php 106 2008-04-11 02:23:54Z magike.net $
 */

/**
 * Validation class
 *
 * @package Validate
 */
class Typecho_Validate
{
    /**
     * Internal data
     *
     * @access private
     * @var array
     */
    private $_data;

    /**
     * The current validation pointer
     *
     * @access private
     * @var string
     */
    private $_key;

    /**
     * An array of validation rules
     *
     * @access private
     * @var array
     */
    private $_rules = array();

    /**
     * Interrupt mode once the validation error is thrown and no longer continue
     *
     * @access private
     * @var boolean
     */
    private $_break = false;

    /**
     * Increase the validation rules
     *
     * @access public
     * @param string $key Numeric keys
     * @param string $rule Rule Name
     * @param string $message Error string
     * @return Typecho_Validation
     */
    public function addRule($key, $rule, $message)
    {
        if (func_num_args() <= 3) {
            $this->_rules[$key][] = array($rule, $message);
        } else {
            $params = func_get_args();
            $params = array_splice($params, 3);
            $this->_rules[$key][] = array_merge(array($rule, $message), $params);
        }

        return $this;
    }

    /**
     * Set to interrupt mode
     *
     * @access public
     * @return void
     */
    public function setBreak()
    {
        $this->_break = true;
    }

    /**
     * Run the Validator
     * This function does all the work.
     *
     * @access	public
     * @param   array $data Need to validate data
     * @param   array $rules Data validation rules to follow
     * @return	array
     * @throws  Typecho_Validate_Exception
     */
    public function run(array $data, $rules = NULL)
    {
        $result = array();
        $this->_data = $data;
        $rules = empty($rules) ? $this->_rules : $rules;

        // Cycle through the rules and test for errors
        foreach ($rules as $key => $rules) {
            $this->_key = $key;
            $data[$key] = (is_array($data[$key]) ? 0 == count($data[$key])
                : 0 == strlen($data[$key])) ? NULL : $data[$key];

            foreach ($rules as $params) {
                $method = $params[0];

                if ('required' != $method && 'confirm' != $method && 0 == strlen($data[$key])) {
                    continue;
                }

                $message = $params[1];
                $params[1] = $data[$key];
                $params = array_slice($params, 1);

                if (!call_user_func_array(is_array($method) ? $method : array($this, $method), $params)) {
                    $result[$key] = $message;
                    break;
                }
            }

            /** Open the interruption */
            if ($this->_break && $result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Minimum length
     *
     * @access public
     * @param string $str String to be processed
     * @param integer $length Minimum length
     * @return boolean
     */
    public static function minLength($str, $length)
    {
        return (Typecho_Common::strLen($str) >= $length);
    }

    /**
     * Verify that the input is consistent
     *
     * @access public
     * @param string $str String to be processed
     * @param string $key The key needs consistency check
     * @return boolean
     */
    public function confirm($str, $key)
    {
        return !empty($this->_data[$key]) ? ($str == $this->_data[$key]) : empty($str);
    }

    /**
     * Is empty
     *
     * @access public
     * @param string $str String to be processed
     * @return boolean
     */
    public function required($str)
    {
        return !empty($this->_data[$this->_key]);
    }

    /**
     * Enum type judgment
     *
     * @access public
     * @param string $str String to be processed
     * @param array $params Enum values
     * @return unknown
     */
    public static function enum($str, array $params)
    {
        $keys = array_flip($params);
        return isset($keys[$str]);
    }

    /**
     * Max Length
     *
     * @param $str
     * @param $length
     * @return bool
     */
    public static function maxLength($str, $length)
    {
        return (Typecho_Common::strLen($str) < $length);
    }

    /**
     * Valid Email
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function email($str)
    {
        return preg_match("/^[_a-z0-9-\.]+@([-a-z0-9]+\.)+[a-z]{2,}$/i", $str);
    }

    /**
     * Verify whether it is an URL
     *
     * @access public
     * @param string $str
     * @return boolean
     */
    public static function url($str)
    {
        $parts = @parse_url($str);
        if (!$parts) {
            return false;
        }

        return isset($parts['scheme']) &&
        in_array($parts['scheme'], array('http', 'https', 'ftp')) &&
        !preg_match('/(\(|\)|\\\|"|<|>|[\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19])/', $str);
    }

    /**
     * Alpha
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function alpha($str)
    {
        return preg_match("/^([a-z])+$/i", $str) ? true : false;
    }

    /**
     * Alpha-numeric
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function alphaNumeric($str)
    {
        return preg_match("/^([a-z0-9])+$/i", $str);
    }

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function alphaDash($str)
    {
        return preg_match("/^([_a-z0-9-])+$/i", $str) ? true : false;
    }

    /**
     * Detection of xss string
     *
     * @access public
     * @param string $str
     * @return boolean
     */
    public static function xssCheck($str)
    {
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';

        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

            // &#x0040 @ search for the hex values
            $str = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $str); // with a ;
            // &#00064 @ 0{0,7} matches '0' zero to seven times
            $str = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $str); // with a ;
        }

        return !preg_match('/(\(|\)|\\\|"|<|>|[\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19]|' . "\r|\n|\t" . ')/', $str);
    }

    /**
     * Numeric
     *
     * @access public
     * @param integer
     * @return boolean
     */
    public static function isFloat($str)
    {
        return preg_match("/^[0-9\.]+$/", $str);
    }

    /**
     * Is Numeric
     *
     * @access public
     * @param string
     * @return boolean
     */
    public static function isInteger($str)
    {
        return is_numeric($str);
    }
}
