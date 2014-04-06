<?php
/**
 * Client Adapter
 *
 * @author qining
 * @category typecho
 * @package Http
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Client Adapter
 *
 * @author qining
 * @category typecho
 * @package Http
 */
abstract class Typecho_Http_Client_Adapter
{
    /**
     * Method name
     *
     * @access protected
     * @var string
     */
    protected $method = Typecho_Http_Client::METHOD_GET;

    /**
     * Passing parameters
     *
     * @access protected
     * @var string
     */
    protected $query;

    /**
     * Set the timeout
     *
     * @access protected
     * @var string
     */
    protected $timeout = 3;

    /**
     * Value needs to pass in the body
     *
     * @access protected
     * @var array
     */
    protected $data = array();

    /**
     * File List
     *
     * @access protected
     * @var array
     */
    protected $files = array();

    /**
     * Header Argument
     *
     * @access protected
     * @var array
     */
    protected $headers = array();

    /**
     * cookies
     *
     * @access protected
     * @var array
     */
    protected $cookies = array();

    /**
     * Protocol name and version
     *
     * @access protected
     * @var string
     */
    protected $rfc = 'HTTP/1.1';

    /**
     * Request address
     *
     * @access protected
     * @var string
     */
    protected $url;

    /**
     * Hostname
     *
     * @access protected
     * @var string
     */
    protected $host;

    /**
     * Prefix
     *
     * @access protected
     * @var string
     */
    protected $scheme = 'http';

    /**
     * path
     *
     * @access protected
     * @var string
     */
    protected $path = '/';

    /**
     * Set ip
     *
     * @access protected
     * @var string
     */
    protected $ip;

    /**
     * Port
     *
     * @access protected
     * @var integer
     */
    protected $port = 80;

    /**
     * Receipt header information
     *
     * @access protected
     * @var array
     */
    protected $responseHeader = array();

    /**
     * Receipt code
     *
     * @access protected
     * @var integer
     */
    protected $responseStatus;

    /**
     * Receipt of the body
     *
     * @access protected
     * @var string
     */
    protected $responseBody;

    /**
     * Determine if adapters are available
     *
     * @access public
     * @return boolean
     */
    public static function isAvailable()
    {
        return true;
    }

    /**
     * Set Method name
     *
     * @access public
     * @param string $method
     * @return Typecho_Http_Client_Adapter
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Set specified COOKIE value
     *
     * @access public
     * @param string $key Specified parameters
     * @param mixed $value The value
     * @return Typecho_Http_Client_Adapter
     */
    public function setCookie($key, $value)
    {
        $this->cookies[$key] = $value;
        return $this;
    }

    /**
     * Set Passing parameters
     *
     * @access public
     * @param mixed $query Passing parameters
     * @return Typecho_Http_Client_Adapter
     */
    public function setQuery($query)
    {
        $query = is_array($query) ? http_build_query($query) : $query;
        $this->query = empty($this->query) ? $query : $this->query . '&' . $query;
        return $this;
    }

    /**
     * Settings need to POST data
     *
     * @access public
     * @param array $data POST data needed
     * @return Typecho_Http_Client_Adapter
     */
    public function setData($data)
    {
        $this->data = $data;
        $this->setMethod(Typecho_Http_Client::METHOD_POST);
        return $this;
    }

    /**
     * Set of documents POST
     *
     * @access public
     * @param array $files POST documents needed
     * @return Typecho_Http_Client_Adapter
     */
    public function setFiles(array $files)
    {
        $this->files = empty($this->files) ? $files : array_merge($this->files, $files);
        $this->setMethod(Typecho_Http_Client::METHOD_POST);
        return $this;
    }

    /**
     * Set the timeout time
     *
     * @access public
     * @param integer $timeout Timeout
     * @return Typecho_Http_Client_Adapter
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Set http protocol
     *
     * @access public
     * @param string $rfc http protocol
     * @return Typecho_Http_Client_Adapter
     */
    public function setRfc($rfc)
    {
        $this->rfc = $rfc;
        return $this;
    }

    /**
     * Set ip address
     *
     * @access public
     * @param string $ip ip address
     * @return Typecho_Http_Client_Adapter
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Set Header Argument
     *
     * @access public
     * @param string $key Parameter name
     * @param string $value Parameter values
     * @return Typecho_Http_Client_Adapter
     */
    public function setHeader($key, $value)
    {
        $key = str_replace(' ', '-', ucwords(str_replace('-', ' ', $key)));
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Send request
     *
     * @access public
     * @param string $url Request address
     * @param string $rfc Request Protocol
     * @return string
     */
    public function send($url)
    {
        $params = parse_url($url);

        if (!empty($params['host'])) {
            $this->host = $params['host'];
        } else {
            throw new Typecho_Http_Client_Exception('Unknown Host', 500);
        }

        if (!empty($params['path'])) {
            $this->path = $params['path'];
        }

        if (!empty($params['query'])) {
            $this->path .= '?' . $params['query'] . (empty($this->query) ? NULL : '&' . $this->query);
            $url .= (empty($this->query) ? NULL : '&' . $this->query);
        } else {
            $url .= (empty($this->query) ? NULL : '?' . $this->query);
        }

        $this->scheme = $params['scheme'];
        $this->port = ('https' == $params['scheme']) ? 443 : 80;

        if (!empty($params['port'])) {
            $this->port = $params['port'];
        }

        /** Sorting cookie */
        if (!empty($this->cookies)) {
            $this->setHeader('Cookie', str_replace('&', '; ', http_build_query($this->cookies)));
        }

        $response = $this->httpSend($url);

        if (!$response) {
            return;
        }

        str_replace("\r", '', $response);
        $rows = explode("\n", $response);

        $foundStatus = false;
        $foundInfo = false;
        $lines = array();

        foreach ($rows as $key => $line) {
            if (!$foundStatus) {
                if (0 === strpos($line, "HTTP/")) {
                    if ('' == trim($rows[$key + 1])) {
                        continue;
                    } else {
                        $status = explode(' ', str_replace('  ', ' ', $line));
                        $this->responseStatus = intval($status[1]);
                        $foundStatus = true;
                    }
                }
            } else {
                if (!$foundInfo) {
                    if ('' != trim($line)) {
                        $status = explode(':', $line);
                        $name = strtolower(array_shift($status));
                        $data = implode(':', $status);
                        $this->responseHeader[trim($name)] = trim($data);
                    } else {
                        $foundInfo = true;
                    }
                } else {
                    $lines[] = $line;
                }
            }
        }

        $this->reponseBody = implode("\n", $lines);
        return $this->reponseBody;
    }

    /**
     * Get header information, return receipt
     *
     * @access public
     * @param string $key Header name
     * @return string
     */
    public function getResponseHeader($key)
    {
        $key = strtolower($key);
        return isset($this->responseHeader[$key]) ? $this->responseHeader[$key] : NULL;
    }

    /**
     * Get Receipt code
     *
     * @access public
     * @return integer
     */
    public function getResponseStatus()
    {
        return $this->responseStatus;
    }

    /**
     * Get receipt body
     *
     * @access public
     * @return string
     */
    public function getResponseBody()
    {
        return $this->reponseBody;
    }

    /**
     * Request method need to implement
     *
     * @access public
     * @param string $url Request address
     * @return string
     */
    abstract public function httpSend($url);
}
