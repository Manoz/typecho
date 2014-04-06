<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id$
 */

/**
 * Mo files used to solve more than one problem to bring read and write
 * We rewrite the class to read a file
 *
 * @author qining
 * @category typecho
 * @package I18n
 */
class Typecho_I18n_GetTextMulti
{
    /**
     * Handles all file read and write
     *
     * @access private
     * @var array
     */
    private $_handles = array();

    /**
     * Constructors
     *
     * @access public
     * @param string $fileName Language file name
     * @return void
     */
    public function __construct($fileName)
    {
        $this->addFile($fileName);
    }

    /**
     * Adding a language file
     *
     * @access public
     * @param string $fileName Language file name
     * @return void
     */
    public function addFile($fileName)
    {
        $this->_handles[] = new Typecho_I18n_GetText($fileName, true);
    }

    /**
     * Translates a string
     *
     * @access public
     * @param string string to be translated
     * @return string translated string (or original, if not found)
     */
    public function translate($string)
    {
        foreach ($this->_handles as $handle) {
            $string = $handle->translate($string, $count);
            if (-1 != $count) {
                break;
            }
        }

        return $string;
    }

    /**
     * Plural version of gettext
     *
     * @access public
     * @param string single
     * @param string plural
     * @param string number
     * @return translated plural form
     */
    public function ngettext($single, $plural, $number)
    {
        foreach ($this->_handles as $handle) {
            $string = $handle->ngettext($single, $plural, $number, $count);
            if (-1 != $count) {
                break;
            }
        }

        return $string;
    }

    /**
     * Close all handles
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        foreach ($this->_handles as $handle) {
            /** Unset released memory */
            unset($handle);
        }
    }
}
