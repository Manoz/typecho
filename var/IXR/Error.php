<?php
/*
   IXR - The Inutio XML-RPC Library - (c) Incutio Ltd 2002
   Version 1.61 - Simon Willison, 11th July 2003 (htmlentities -> htmlspecialchars)
   Site:   http://scripts.incutio.com/xmlrpc/
   Manual: http://scripts.incutio.com/xmlrpc/manual.php
   Made available under the Artistic License: http://www.opensource.org/licenses/artistic-license.php
*/

/**
 * IXR error
 *
 * @package IXR
 */
class IXR_Error
{
    /**
     * Error Codes
     *
     * @access public
     * @var integer
     */
    public $code;

    /**
     * Error Messages
     *
     * @access public
     * @var string
     */
    public $message;

    /**
     * Constructors
     *
     * @access public
     * @param integer $code Error Codes
     * @param string $message Error Messages
     * @return void
     */
    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * Get xml
     *
     * @access public
     * @return string
     */
    public function getXml()
    {
        $xml = <<<EOD
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>{$this->code}</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>{$this->message}</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>

EOD;
        return $xml;
    }
}
