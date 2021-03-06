<?php
/*
   IXR - The Inutio XML-RPC Library - (c) Incutio Ltd 2002
   Version 1.61 - Simon Willison, 11th July 2003 (htmlentities -> htmlspecialchars)
   Site:   http://scripts.incutio.com/xmlrpc/
   Manual: http://scripts.incutio.com/xmlrpc/manual.php
   Made available under the Artistic License: http://www.opensource.org/licenses/artistic-license.php
*/

/**
 * IXR Base64 encoding
 *
 * @package IXR
 */
class IXR_Base64
{
    /**
     * Encoded data
     *
     * @var string
     */
    private $data;

    /**
     * Data initialization
     *
     * @param string $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get XML data
     *
     * @return string
     */
    public function getXml()
    {
        return '<base64>' . base64_encode($this->data) . '</base64>';
    }
}
