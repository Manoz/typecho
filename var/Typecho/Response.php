<?php
/**
 * Typecho namespace API methods
 *
 * @category typecho
 * @package Response
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Typecho Public Methods
 *
 * @category typecho
 * @package Response
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Response
{
    /**
     * http code
     *
     * @access private
     * @var array
     */
    private static $_httpCode = array(
        100 => 'Continue',
        101	=> 'Switching Protocols',
        200	=> 'OK',
        201	=> 'Created',
        202	=> 'Accepted',
        203	=> 'Non-Authoritative Information',
        204	=> 'No Content',
        205	=> 'Reset Content',
        206	=> 'Partial Content',
        300	=> 'Multiple Choices',
        301	=> 'Moved Permanently',
        302	=> 'Found',
        303	=> 'See Other',
        304	=> 'Not Modified',
        305	=> 'Use Proxy',
        307	=> 'Temporary Redirect',
        400	=> 'Bad Request',
        401	=> 'Unauthorized',
        402	=> 'Payment Required',
        403	=> 'Forbidden',
        404	=> 'Not Found',
        405	=> 'Method Not Allowed',
        406	=> 'Not Acceptable',
        407	=> 'Proxy Authentication Required',
        408	=> 'Request Timeout',
        409	=> 'Conflict',
        410	=> 'Gone',
        411	=> 'Length Required',
        412	=> 'Precondition Failed',
        413	=> 'Request Entity Too Large',
        414	=> 'Request-URI Too Long',
        415	=> 'Unsupported Media Type',
        416	=> 'Requested Range Not Satisfiable',
        417	=> 'Expectation Failed',
        500	=> 'Internal Server Error',
        501	=> 'Not Implemented',
        502	=> 'Bad Gateway',
        503	=> 'Service Unavailable',
        504	=> 'Gateway Timeout',
        505	=> 'HTTP Version Not Supported'
    );

    /**
     * Character Encoding
     *
     * @var mixed
     * @access private
     */
    private $_charset;

    // The default character encoding
    const CHARSET = 'UTF-8';

    /**
     * Single Handle Cases
     *
     * @access private
     * @var Typecho_Response
     */
    private static $_instance = null;

    /**
     * Get a handle for a single case
     *
     * @access public
     * @return Typecho_Response
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new Typecho_Response();
        }

        return self::$_instance;
    }

    /**
     * Ajax receipt resolve internal function
     *
     * @access private
     * @param mixed $message Formatting data
     * @return string
     */
    private function _parseXml($message)
    {
        /** For an array type continues recursively */
        if (is_array($message)) {
            $result = '';

            foreach ($message as $key => $val) {
                $tagName = is_int($key) ? 'item' : $key;
                $result .= '<' . $tagName . '>' . $this->_parseXml($val) . '</' . $tagName . '>';
            }

            return $result;
        } else {
            return preg_match("/^[^<>]+$/is", $message) ? $message : '<![CDATA[' . $message . ']]>';
        }
    }

    /**
     * Set the default encoding receipt
     *
     * @access public
     * @param string $charset Character Set
     * @return void
     */
    public function setCharset($charset = null)
    {
        $this->_charset = empty($charset) ? self::CHARSET : $charset;
    }

    /**
     * Get Character Set
     *
     * @access public
     * @return string
     */
    public function getCharset()
    {
        if (empty($this->_charset)) {
            $this->setCharset();
        }

        return $this->_charset;
    }

    /**
     * Statement type and character set in the http request header
     *
     * @access public
     * @param string $contentType Document Type
     * @return void
     */
    public function setContentType($contentType = 'text/html')
    {
        header('Content-Type: ' . $contentType . '; charset=' . $this->getCharset(), true);
    }

    /**
     * Set http header
     *
     * @access public
     * @param string $name Name
     * @param string $value Corresponding values
     * @return void
     */
    public function setHeader($name, $value)
    {
        header($name . ': ' . $value, true);
    }

    /**
     * Set HTTP status
     *
     * @access public
     * @param integer $code http code
     * @return void
     */
    public static function setStatus($code)
    {
        if (isset(self::$_httpCode[$code])) {
            header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1') . ' ' . $code . ' ' . self::$_httpCode[$code], true, $code);
        }
    }

    /**
     * Thrown the ajax information receipt
     *
     * @access public
     * @param string $message Message Body
     * @return void
     */
    public function throwXml($message)
    {
        /** Set http header information */
        $this->setContentType('text/xml');

        /** Construct the message body */
        echo '<?xml version="1.0" encoding="' . $this->getCharset() . '"?>',
        '<response>',
        $this->_parseXml($message),
        '</response>';

        /** Termination subsequent output */
        exit;
    }

    /**
     * Thrown the json information receipt
     *
     * @access public
     * @param string $message Message Body
     * @return void
     */
    public function throwJson($message)
    {
        /** Set http header information */
        $this->setContentType('application/json');

        echo Json::encode($message);

        /** Termination subsequent output */
        exit;
    }

    /**
     * Redirect function
     *
     * @access public
     * @param string $location Redirect path
     * @param boolean $isPermanently Whether it is a permanent redirect
     * @return void
     */
    public function redirect($location, $isPermanently = false)
    {
        /** Typecho_Common */
        $location = Typecho_Common::safeUrl($location);

        if ($isPermanently) {
            header('Location: ' . $location, false, 301);
            exit;
        } else {
            header('Location: ' . $location, false, 302);
            exit;
        }
    }

    /**
     * Return the antecedents
     *
     * @access public
     * @param string $suffix Additional Address
     * @param string $default Default antecedents
     */
    public function goBack($suffix = NULL, $default = NULL)
    {
        // Get the source
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        // Determine the source
        if (!empty($referer)) {
            // ~ fix Issue 38
            if (!empty($suffix)) {
                $parts = parse_url($referer);
                $myParts = parse_url($suffix);

                if (isset($myParts['fragment'])) {
                    $parts['fragment'] = $myParts['fragment'];
                }

                if (isset($myParts['query'])) {
                    $args = array();
                    if (isset($parts['query'])) {
                        parse_str($parts['query'], $args);
                    }

                    parse_str($myParts['query'], $currentArgs);
                    $args = array_merge($args, $currentArgs);
                    $parts['query'] = http_build_query($args);
                }

                $referer = Typecho_Common::buildUrl($parts);
            }

            $this->redirect($referer, false);
        } else if (!empty($default)) {
            $this->redirect($default);
        }
    }
}
