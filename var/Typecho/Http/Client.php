<?php
/**
 * Http Client
 *
 * @author qining
 * @category typecho
 * @package Http
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * Http Client
 *
 * @author qining
 * @category typecho
 * @package Http
 */
class Typecho_Http_Client
{
    /** POST method */
    const METHOD_POST = 'POST';

    /** GET method */
    const METHOD_GET = 'GET';

    /** Define line endings */
    const EOL = "\r\n";

    /**
     * Get available connections
     *
     * @access public
     * @return Typecho_Http_Client_Adapter
     */
    public static function get()
    {
        $adapters = func_get_args();

        if (empty($adapters)) {
            $adapters = array();
            $adapterFiles = glob(dirname(__FILE__) . '/Client/Adapter/*.php');
            foreach ($adapterFiles as $file) {
                $adapters[] = substr(basename($file), 0, -4);
            }
        }

        foreach ($adapters as $adapter) {
            $adapterName = 'Typecho_Http_Client_Adapter_' . $adapter;
            if (Typecho_Common::isAvailableClass($adapterName) && call_user_func(array($adapterName, 'isAvailable'))) {
                return new $adapterName();
            }
        }

        return false;
    }
}
