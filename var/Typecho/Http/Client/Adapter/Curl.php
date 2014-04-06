<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * CURL adapter
 *
 * @author qining
 * @category typecho
 * @package Http
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * CURL adapter
 *
 * @author qining
 * @category typecho
 * @package Http
 */
class Typecho_Http_Client_Adapter_Curl extends Typecho_Http_Client_Adapter
{
    /**
     * Determine if adapters are available
     *
     * @access public
     * @return boolean
     */
    public static function isAvailable()
    {
        return function_exists('curl_version');
    }

    /**
     * Send request
     *
     * @access public
     * @param string $url Request address
     * @return string
     */
    public function httpSend($url)
    {
        $ch = curl_init();

        if ($this->ip) {
            $url = $this->scheme . '://' . $this->ip . $this->path;
            $this->headers['Rfc'] = $this->method . ' ' . $this->path . ' ' . $this->rfc;
            $this->headers['Host'] = $this->host;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PORT, $this->port);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        /** Set HTTP version */
        switch ($this->rfc) {
            case 'HTTP/1.0':
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                break;
            case 'HTTP/1.1':
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                break;
            default:
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE);
                break;
        }

        /** Set header information */
        if (!empty($this->headers)) {
            if (isset($this->headers['User-Agent'])) {
                curl_setopt($ch, CURLOPT_USERAGENT, $this->headers['User-Agent']);
                unset($this->headers['User-Agent']);
            }

            $headers = array();

            if (isset($this->headers['Rfc'])) {
                $headers[] = $this->headers['Rfc'];
                unset($this->headers['Rfc']);
            }

            foreach ($this->headers as $key => $val) {
                $headers[] = $key . ': ' . $val;
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        /** Method POST */
        if (Typecho_Http_Client::METHOD_POST == $this->method) {
            if (!isset($this->headers['content-type'])) {
                curl_setopt($ch, CURLOPT_POST, true);
            }

            if (!empty($this->data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($this->data) ? http_build_query($this->data) : $this->data);
            }

            if (!empty($this->files)) {
                foreach ($this->files as $key => &$file) {
                    $file = '@' . $file;
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->files);
            }
        }

        $response = curl_exec($ch);
        if (false === $response) {
            throw new Typecho_Http_Client_Exception(curl_error($ch), 500);
        }

        curl_close($ch);
        return $response;
    }
}
