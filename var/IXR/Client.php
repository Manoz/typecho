<?php
/*
   IXR - The Inutio XML-RPC Library - (c) Incutio Ltd 2002
   Version 1.61 - Simon Willison, 11th July 2003 (htmlentities -> htmlspecialchars)
   Site:   http://scripts.incutio.com/xmlrpc/
   Manual: http://scripts.incutio.com/xmlrpc/manual.php
   Made available under the Artistic License: http://www.opensource.org/licenses/artistic-license.php
*/

/**
 * IXR client
 * reload by typecho team(http://www.typecho.org)
 *
 * @package IXR
 */
class IXR_Client
{
    /** Default client */
    const DEFAULT_USERAGENT = 'The Incutio XML-RPC PHP Library(Reload By Typecho)';

    /**
     * Server address
     *
     * @access private
     * @var string
     */
    private $server;

    /**
     * Port Name
     *
     * @access private
     * @var integer
     */
    private $port;

    /**
     * Path name
     *
     * @access private
     * @var string
     */
    private $path;

    /**
     * Address
     *
     * @access private
     * @var string
     */
    private $url;

    /**
     * Client
     *
     * @access private
     * @var string
     */
    private $useragent;

    /**
     * Receipt structure
     *
     * @access private
     * @var string
     */
    private $response;

    /**
     * Message Body
     *
     * @access private
     * @var string
     */
    private $message = false;

    /**
     * Debugging
     *
     * @access private
     * @var boolean
     */
    private $debug = false;

    /**
     * Prefix Request
     *
     * @access private
     * @var string
     */
    private $prefix = NULL;

    // Storage place for an error message
    private $error = false;

    /**
     * Client constructor
     *
     * @access public
     * @param string $server    Server address
     * @param string $path      Path name
     * @param integer $port     Port Name
     * @param string $useragent Client
     * @return void
     */
    public function __construct($server, $path = false, $port = 80, $useragent = self::DEFAULT_USERAGENT, $prefix = NULL)
    {
        if (!$path) {
            $this->url = $server;

            // Assume we have been given a Url instead
            $bits = parse_url($server);
            $this->server = $bits['host'];
            $this->port = isset($bits['port']) ? $bits['port'] : 80;
            $this->path = isset($bits['path']) ? $bits['path'] : '/';

            // Make absolutely sure we have a path
            if (isset($bits['query'])) {
                $this->path .= '?' . $bits['query'];
            }
        } else {
            /** Typecho_Common */
            require_once 'Typecho/Common.php';

            $this->url = Typecho_Common::buildUrl(array(
                'scheme'    =>  'http',
                'host'      =>  $server,
                'path'      =>  $path,
                'port'      =>  $port
            ));

            $this->server = $server;
            $this->path = $path;
            $this->port = $port;
        }

        $this->prefix = $prefix;
        $this->useragent = $useragent;
    }

    /**
     * Set debug mode
     *
     * @access public
     * @return void
     */
    public function __setDebug()
    {
        $this->debug = true;
    }

    /**
     * Execution request
     *
     * @access public
     * @return void
     */
    public function __rpcCall()
    {
        $args = func_get_args();
        $method = array_shift($args);
        $request = new IXR_Request($method, $args);
        $xml = $request->getXml();

        $client = Typecho_Http_Client::get();
        if (!$client) {
            $this->error = new IXR_Error(-32300, 'transport error - could not open socket');
            return false;
        }

        $client->setHeader('Content-Type', 'text/xml')
        ->setHeader('User-Agent', $this->useragent)
        ->setData($xml)
        ->send($this->url);

        $contents = $client->getResponseBody();

        if ($this->debug) {
            echo '<pre>'.htmlspecialchars($contents)."\n</pre>\n\n";
        }

        // Now parse what we've got back
        $this->message = new IXR_Message($contents);
        if (!$this->message->parse()) {
            // XML error
            $this->error = new IXR_Error(-32700, 'parse error. not well formed');
            return false;
        }

        // Is the message a fault?
        if ($this->message->messageType == 'fault') {
            $this->error = new IXR_Error($this->message->faultCode, $this->message->faultString);
            return false;
        }

        // Message must be OK
        return true;
    }

    /**
     * Add Prefix
     * <code>
     * $rpc->metaWeblog->newPost();
     * </code>
     *
     * @access public
     * @param string $prefix Prefix
     * @return void
     */
    public function __get($prefix)
    {
        return new IXR_Client($this->server, $this->path, $this->port, $this->useragent, $this->prefix . $prefix . '.');
    }

    /**
     * Magic features increase
     * by 70
     *
     * @access public
     * @return mixed
     */
    public function __call($method, $args)
    {
        array_unshift($args, $this->prefix . $method);
        $return = call_user_func_array(array($this, '__rpcCall'), $args);

        if ($return) {
            return $this->__getResponse();
        } else {
            require_once 'IXR/Exception.php';
            throw new IXR_Exception($this->__getErrorMessage(), $this->__getErrorCode());
        }
    }

    /**
     * Get the return value
     *
     * @access public
     * @return void
     */
    public function __getResponse()
    {
        // methodResponses can only have one param - return that
        return $this->message->params[0];
    }

    /**
     * Is an error
     *
     * @access public
     * @return void
     */
    public function __isError()
    {
        return (is_object($this->error));
    }

    /**
     * Get the error code
     *
     * @access public
     * @return void
     */
    public function __getErrorCode()
    {
        return $this->error->code;
    }

    /**
     * Get the error message
     *
     * @access public
     * @return void
     */
    public function __getErrorMessage()
    {
        return $this->error->message;
    }
}
