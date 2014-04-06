<?php
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: Exception.php 106 2008-04-11 02:23:54Z magike.net $
 */

/**
 * Typecho exception base class
 * The main exception print function overloads
 *
 * @package Exception
 */
class Typecho_Exception extends Exception
{

    public function __construct($message, $code = 0)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
