<?php
/**
 * Typecho namespace API methods
 *
 * @category typecho
 * @package Common
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

define('__TYPECHO_MB_SUPPORTED__', function_exists('mb_get_info'));

/**
 * Typecho Public Methods
 *
 * @category typecho
 * @package Common
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Typecho_Common
{
    /** Version */
    const VERSION = '0.9/14.3.14';

    /**
     * Locked blocks
     *
     * @access private
     * @var array
     */
    private static $_lockedBlocks = array('<p></p>' => '');

    /**
     * Allow property
     *
     * @access private
     * @var array
     */
    private static $_allowableAttributes = array();

    /**
     * The default encoding
     *
     * @access public
     * @var string
     */
    public static $charset = 'UTF-8';

    /**
     * Exception class
     *
     * @access public
     * @var string
     */
    public static $exceptionHandle;

    /**
     * Locked labels callback
     *
     * @access private
     * @param array $matches Matched values
     * @return string
     */
    public static function __lockHTML(array $matches)
    {
        $guid = '<code>' . uniqid(time()) . '</code>';
        self::$_lockedBlocks[$guid] = $matches[0];
        return $guid;
    }

    /**
     * The array of illegal xss callback url
     * is removed when the filter function
     *
     * @access private
     * @param string $string Need to filter strings
     * @return string
     */
    public static function __removeUrlXss($string)
    {
        $string = str_replace(array('%0d', '%0a'), '', strip_tags($string));
        return preg_replace(array(
            "/\(\s*(\"|')/i",           // The beginning of the function
            "/(\"|')\s*\)/i",           // The end Function
        ), '', $string);
    }

    /**
     * Check for safety path
     *
     * @access public
     * @param string $path Check for safety path
     * @return boolean
     */
    public static function __safePath($path)
    {
        $safePath = rtrim(__TYPECHO_ROOT_DIR__, '/');
        return 0 === strpos($path, $safePath);
    }

    /**
     * __filterAttrs
     *
     * @param mixed $matches
     * @static
     * @access public
     * @return bool
     */
    public static function __filterAttrs($matches)
    {
        if (!isset($matches[2])) {
            return $matches[0];
        }

        $str = trim($matches[2]);

        if (empty($str)) {
            return $matches[0];
        }

        $attrs = self::__parseAttrs($str);
        $parsedAttrs = array();
        $tag = strtolower($matches[1]);

        foreach ($attrs as $key => $val) {
            if (in_array($key, self::$_allowableAttributes[$tag])) {
                $parsedAttrs[] = " {$key}" . (empty($val) ? '' : "={$val}");
            }
        }

        return '<' . $tag . implode('', $parsedAttrs) . '>';
    }

    /**
     * Analytics properties
     *
     * @access public
     * @param string $attrs Attribute string
     * @return array
     */
    public static function __parseAttrs($attrs)
    {
        $attrs = trim($attrs);
        $len = strlen($attrs);
        $pos = -1;
        $result = array();
        $quote = '';
        $key = '';
        $value = '';

        for ($i = 0; $i < $len; $i ++) {
            if ('=' != $attrs[$i] && !ctype_space($attrs[$i]) && -1 == $pos) {
                $key .= $attrs[$i];

                /** Last */
                if ($i == $len - 1) {
                    if ('' != ($key = trim($key))) {
                        $result[$key] = '';
                        $key = '';
                        $value = '';
                    }
                }

            } else if (ctype_space($attrs[$i]) && -1 == $pos) {
                $pos = -2;
            } else if ('=' == $attrs[$i] && 0 > $pos) {
                $pos = 0;
            } else if (('"' == $attrs[$i] || "'" == $attrs[$i]) && 0 == $pos) {
                $quote = $attrs[$i];
                $value .= $attrs[$i];
                $pos = 1;
            } else if ($quote != $attrs[$i] && 1 == $pos) {
                $value .= $attrs[$i];
            } else if ($quote == $attrs[$i] && 1 == $pos) {
                $pos = -1;
                $value .= $attrs[$i];
                $result[trim($key)] = $value;
                $key = '';
                $value = '';
            } else if ('=' != $attrs[$i] && !ctype_space($attrs[$i]) && -2 == $pos) {
                if ('' != ($key = trim($key))) {
                    $result[$key] = '';
                }

                $key = '';
                $value = '';
                $pos = -1;
                $key .= $attrs[$i];
            }
        }

        return $result;
    }

    /**
     * Automatically loaded class
     *
     * @param $className
     */
    public static function __autoLoad($className)
    {
        @include_once str_replace(array('\\', '_'), '/', $className) . '.php';
    }

    /**
     * Init program method
     *
     * @access public
     * @return void
     */
    public static function init()
    {
        /** Set up automatic loading function */
        if (function_exists('spl_autoload_register')) {
            spl_autoload_register(array('Typecho_Common', '__autoLoad'));
        } else {
            function __autoLoad($className) {
                Typecho_Common::__autoLoad($className);
            }
        }

        /** php6 compatible */
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $_GET = self::stripslashesDeep($_GET);
            $_POST = self::stripslashesDeep($_POST);
            $_COOKIE = self::stripslashesDeep($_COOKIE);

            reset($_GET);
            reset($_POST);
            reset($_COOKIE);
        }

        /** Set intercepted function exceptions */
        set_exception_handler(array('Typecho_Common', 'exceptionHandle'));
    }

    /**
     * Intercept function exceptions
     *
     * @access public
     * @param Exception $exception Intercepted exceptions
     * @return void
     */
    public static function exceptionHandle(Exception $exception)
    {
        @ob_end_clean();

        if (defined('__TYPECHO_DEBUG__')) {
            echo nl2br($exception->__toString());
        } else {
            if (404 == $exception->getCode() && !empty(self::$exceptionHandle)) {
                $handleClass = self::$exceptionHandle;
                new $handleClass($exception);
            } else {
                self::error($exception);
            }
        }

        exit;
    }

    /**
     * Output error page
     *
     * @access public
     * @param mixed $exception Error Messages
     * @return void
     */
    public static function error($exception)
    {
        $isException = is_object($exception);
        $message = '';

        if ($isException) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
        } else {
            $code = $exception;
        }

        $charset = self::$charset;

        if ($isException && $exception instanceof Typecho_Db_Exception) {
            $code = 500;
            @error_log($message);

            // Overwrite the original Error Messages
            $message = 'Database Server Error';

            if ($exception instanceof Typecho_Db_Adapter_Exception) {
                $code = 503;
                $message = 'Error establishing a database connection';
            } else if ($exception instanceof Typecho_Db_Query_Exception) {
                $message = 'Database Query Error';
            }
        } else {
            switch ($code) {
                case 500:
                    $message = 'Server Error';
                    break;

                case 404:
                    $message = 'Page Not Found';
                    break;

                default:
                    $code = 'Error';
                    break;
            }
        }


        /** Set http code */
        if (is_numeric($code) && $code > 200) {
            Typecho_Response::setStatus($code);
        }

        $message = nl2br($message);

        if (defined('__TYPECHO_EXCEPTION_FILE__')) {
            require_once __TYPECHO_EXCEPTION_FILE__;
        } else {
            echo
<<<EOF
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="{$charset}">
        <title>{$code}</title>
        <style>
            html {
                padding: 50px 10px;
                font-size: 20px;
                line-height: 1.4;
                color: #666;
                background: #F6F6F3;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }

            html,
            input { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; }
            body {
                max-width: 500px;
                _width: 500px;
                padding: 30px 20px 50px;
                margin: 0 auto;
                background: #FFF;
            }
            h1 {
                font-size: 50px;
                text-align: center;
            }
            h1 span { color: #bbb; }
            ul {
                padding: 0 0 0 40px;
            }
            .container {
                max-width: 380px;
                _width: 380px;
                margin: 0 auto;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>{$code}</h1>
            {$message}
        </div>
    </body>
</html>
EOF;
        }

        exit;
    }

    /**
     * Determine whether a class can be loaded
     * This function will traverse all the include directory,
     * so there will be some performance overhead, but will not be significant.
     * But we still recommend that you use it when you must detect
     * whether a class is loaded, it manifests itself in the following two cases:
     * 1. Needs to be loaded when the class does not exist, the system will
     *    not stop running (if you do not judge, you will be stopped due to a fatal error)
     * 2. You need to know which class can not be loaded in order to prompt the user
     * In addition to the above, you do not need to focus if those classes can not be loaded,
     * because when they do not exist, the system will automatically stop and throw an error.
     *
     * @access public
     * @param string $className Class name
     * @param string $path Specify the path name
     * @return boolean
     */
    public static function isAvailableClass($className, $path = NULL)
    {
        /** Get all the include directory */
        // Increase security catalog detection. Fix issue 106
        $dirs = array_map('realpath', array_filter(explode(PATH_SEPARATOR, get_include_path()),
        array('Typecho_Common', '__safePath')));

        $file = str_replace('_', '/', $className) . '.php';

        if (!empty($path)) {
            $path = realpath($path);
            if (in_array($path, $dirs)) {
                $dirs = array($path);
            } else {
                return false;
            }
        }

        foreach ($dirs as $dir) {
            if (!empty($dir)) {
                if (file_exists($dir . '/' . $file)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * To detect whether the app engine running, shielding certain features
     *
     * @static
     * @access public
     * @return boolean
     */
    public static function isAppEngine()
    {
        return !empty($_SERVER['HTTP_APPNAME'])                     // SAE
            || !!getenv('HTTP_BAE_ENV_APPID')                       // BAE
            || !!getenv('HTTP_BAE_LOGID')                           // BAE 3.0
            || (ini_get('acl.app_id') && class_exists('Alibaba'))   // ACE
            || (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'],'Google App Engine') !== false) // GAE
            ;
    }

    /**
     * Remove recursive array backslash
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public static function stripslashesDeep($value)
    {
        return is_array($value) ? array_map(array('Typecho_Common', 'stripslashesDeep'), $value) : stripslashes($value);
    }

    /**
     * Extraction of a multidimensional array Element, consisting of a new array, so the array become a flat array
     * Usage:
     * <code>
     * <?php
     * $fruit = array(array('apple' => 2, 'banana' => 3), array('apple' => 10, 'banana' => 12));
     * $banana = Typecho_Common::arrayFlatten($fruit, 'banana');
     * print_r($banana);
     * // Outputs: array(0 => 3, 1 => 12);
     * ?>
     * </code>
     *
     * @access public
     * @param array $value Array is handled
     * @param string $key Need to extract the key
     * @return array
     */
    public static function arrayFlatten(array $value, $key)
    {
        $result = array();

        if ($value) {
            foreach ($value as $inval) {
                if (is_array($inval) && isset($inval[$key])) {
                    $result[] = $inval[$key];
                } else {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Url reassembled according to the results of parse_url
     *
     * @access public
     * @param array $params Parsed arguments
     * @return string
     */
    public static function buildUrl($params)
    {
        return (isset($params['scheme']) ? $params['scheme'] . '://' : NULL)
        . (isset($params['user']) ? $params['user'] . (isset($params['pass']) ? ':' . $params['pass'] : NULL) . '@' : NULL)
        . (isset($params['host']) ? $params['host'] : NULL)
        . (isset($params['port']) ? ':' . $params['port'] : NULL)
        . (isset($params['path']) ? $params['path'] : NULL)
        . (isset($params['query']) ? '?' . $params['query'] : NULL)
        . (isset($params['fragment']) ? '#' . $params['fragment'] : NULL);
    }

    /**
     * Count the number of characters to output
     * <code>
     * echo splitByCount(20, 10, 20, 30, 40, 50);
     * </code>
     *
     * @access public
     * @param int $count
     * @return string
     */
    public static function splitByCount($count)
    {
        $sizes = func_get_args();
        array_shift($sizes);

        foreach ($sizes as $size) {
            if ($count < $size) {
                return $size;
            }
        }

        return 0;
    }

    /**
     * Since closing html repair function
     * Usage:
     * <code>
     * $input = 'This is a section of the truncated text html <a href="#"';
     * echo Typecho_Common::fixHtml($input);
     * //output: This is a section of the truncated text html
     * </code>
     *
     * @access public
     * @param string $string String processing needs repair
     * @return string
     */
    public static function fixHtml($string)
    {
        // Close self-closing labels
        $startPos = strrpos($string, "<");

        if (false == $startPos) {
            return $string;
        }

        $trimString = substr($string, $startPos);

        if (false === strpos($trimString, ">")) {
            $string = substr($string, 0, $startPos);
        }

        // Non-self-closing html tag list
        preg_match_all("/<([_0-9a-zA-Z-\:]+)\s*([^>]*)>/is", $string, $startTags);
        preg_match_all("/<\/([_0-9a-zA-Z-\:]+)>/is", $string, $closeTags);

        if (!empty($startTags[1]) && is_array($startTags[1])) {
            krsort($startTags[1]);
            $closeTagsIsArray = is_array($closeTags[1]);
            foreach ($startTags[1] as $key => $tag) {
                $attrLength = strlen($startTags[2][$key]);
                if ($attrLength > 0 && "/" == trim($startTags[2][$key][$attrLength - 1])) {
                    continue;
                }
                if (!empty($closeTags[1]) && $closeTagsIsArray) {
                    if (false !== ($index = array_search($tag, $closeTags[1]))) {
                        unset($closeTags[1][$index]);
                        continue;
                    }
                }
                $string .= "</{$tag}>";
            }
        }

        return preg_replace("/\<br\s*\/\>\s*\<\/p\>/is", '</p>', $string);
    }

    /**
     * Remove the string html labels
     * Usage:
     * <code>
     * $input = '<a href="http://test/test.php" title="example">hello</a>';
     * $output = Typecho_Common::stripTags($input, <a href="">);
     * echo $output;
     * //display: '<a href="http://test/test.php">hello</a>'
     * </code>
     *
     * @access public
     * @param string $html Strings to be processed
     * @param string $allowableTags Need to ignore the html labels
     * @return string
     */
    public static function stripTags($html, $allowableTags = NULL)
    {
        $normalizeTags = '';
        $allowableAttributes = array();

        if (!empty($allowableTags) && preg_match_all("/\<([_a-z0-9-]+)([^>]*)\>/is", $allowableTags, $tags)) {
            $normalizeTags = '<' . implode('><', array_map('strtolower', $tags[1])) . '>';
            $attributes = array_map('trim', $tags[2]);
            foreach ($attributes as $key => $val) {
                $allowableAttributes[strtolower($tags[1][$key])] =
                    array_map('strtolower', array_keys(self::__parseAttrs($val)));
            }
        }

        self::$_allowableAttributes = $allowableAttributes;
        $html = strip_tags($html, $normalizeTags);
        $html = preg_replace_callback("/<([_a-z0-9-]+)(\s+[^>]+)?>/is",
            array('Typecho_Common', '__filterAttrs'), $html);

        return $html;
    }

    /**
     * Search for string filters
     *
     * @access public
     * @param string $query Search string
     * @return string
     */
    public static function filterSearchQuery($query)
    {
        return str_replace('-', ' ', self::slugName($query));
    }

    /**
     * Illegal string in the url
     *
     * @param string $url Need to filter the url
     * @return string
     */
    public static function safeUrl($url)
    {
        //~ Filter for location of xss, Because of its specificity, removeXSS function can not be used
        //~ fix issue 66
        $params = parse_url(str_replace(array("\r", "\n", "\t", ' '), '', $url));

        /** Jump to prohibit illegal agreement */
        if (isset($params['scheme'])) {
            if (!in_array($params['scheme'], array('http', 'https'))) {
                return '/';
            }
        }

        /** Filter parse strings */
        $params = array_map(array('Typecho_Common', '__removeUrlXss'), $params);
        return self::buildUrl($params);
    }

    /**
     * Deal with cross-site attacks XSS filter function
     *
     * @author kallahar@kallahar.com
     * @link http://kallahar.com/smallprojects/php_xss_filter_function.php
     * @access public
     * @param string $val Strings to be processed
     * @return string
     */
    public static function removeXSS($val)
    {
       // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
       // this prevents some character re-spacing such as <java\0script>
       // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
       $val = preg_replace('/([\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19])/', '', $val);

       // straight replacements, the user should never need these since they're normal characters
       // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
       $search = 'abcdefghijklmnopqrstuvwxyz';
       $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
       $search .= '1234567890!@#$%^&*()';
       $search .= '~`";:?+/={}[]-_|\'\\';

       for ($i = 0; $i < strlen($search); $i++) {
          // ;? matches the ;, which is optional
          // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

          // &#x0040 @ search for the hex values
          $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
          // &#00064 @ 0{0,7} matches '0' zero to seven times
          $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
       }

       // now the only remaining whitespace attacks are \t, \n, and \r
       $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
       $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
       $ra = array_merge($ra1, $ra2);

       $found = true; // keep replacing as long as the previous round replaced something
       while ($found == true) {
          $val_before = $val;
          for ($i = 0; $i < sizeof($ra); $i++) {
             $pattern = '/';
             for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                   $pattern .= '(';
                   $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                   $pattern .= '|';
                   $pattern .= '|(&#0{0,8}([9|10|13]);)';
                   $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
             }
             $pattern .= '/i';
             $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
             $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags

             if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
             }
          }
       }

       return $val;
    }

    /**
     * Wide string truncated word function
     *
     * @access public
     * @param string $str Need to intercept strings
     * @param integer $start Start position interception
     * @param integer $length Need to intercept length
     * @param string $trim Intercept truncated identifier
     * @return string
     */
    public static function subStr($str, $start, $length, $trim = "...")
    {
        if (!strlen($str)) {
            return '';
        }

        $iLength = self::strLen($str) - $start;
        $tLength = $length < $iLength ? ($length - self::strLen($trim)) : $length;

        if (__TYPECHO_MB_SUPPORTED__) {
            $str = mb_substr($str, $start, $tLength, self::$charset);
        } else {
            if ('UTF-8' == strtoupper(self::$charset)) {
                if (preg_match_all("/./u", $str, $matches)) {
                    $str = implode('', array_slice($matches[0], $start, $tLength));
                }
            } else {
                $str = substr($str, $start, $tLength);
            }
        }

        return $length < $iLength ? ($str . $trim) : $str;
    }

    /**
     * Get wide string length function
     *
     * @access public
     * @param string $str Need to get the string length
     * @return integer
     */
    public static function strLen($str)
    {
        if (__TYPECHO_MB_SUPPORTED__) {
            return mb_strlen($str, self::$charset);
        } else {
            return 'UTF-8' == strtoupper(self::$charset)
                ? strlen(utf8_decode($str)) : strlen($str);
        }
    }

    /**
     * Check whether legitimate encoded data
     *
     * @param string|array $str
     * @return boolean
     */
    public static function checkStrEncoding($str)
    {
        if (is_array($str)) {
            return array_map(array('Typecho_Common', 'checkStrEncoding'), $str);
        }

        if (__TYPECHO_MB_SUPPORTED__) {
            return mb_check_encoding($str, self::$charset);
        } else {
            // just support utf-8
            return preg_match('//u', $str);
        }
    }

    /**
     * Generate abbreviated name
     *
     * @access public
     * @param string $str Need to generate a string of abbreviated name
     * @param string $default The default abbreviated name
     * @param integer $maxLength The maximum length of abbreviated name
     * @return string
     */
    public static function slugName($str, $default = NULL, $maxLength = 128)
    {
        $str = trim($str);

        if (!strlen($str)) {
            return $default;
        }

        if (__TYPECHO_MB_SUPPORTED__) {
            mb_regex_encoding(self::$charset);
            mb_ereg_search_init($str, "[\w" . preg_quote('_-') . "]+");
            $result = mb_ereg_search();
            $return = '';

            if ($result) {
                $regs = mb_ereg_search_getregs();
                $pos = 0;
                do {
                    $return .= ($pos > 0 ? '-' : '') . $regs[0];
                    $pos ++;
                } while ($regs = mb_ereg_search_regs());
            }

            $str = $return;
        } else if ('UTF-8' == strtoupper(self::$charset)) {
            if (preg_match_all("/[\w" . preg_quote('_-') . "]+/u", $str, $matches)) {
                $str = implode('-', $matches[0]);
            }
        } else {
            $str = str_replace(array("'", ":", "\\", "/", '"'), "", $str);
            $str = str_replace(array("+", ",", ' ', '，', ' ', ".", "?", "=", "&", "!", "<", ">", "(", ")", "[", "]", "{", "}"), "-", $str);
        }

        $str = trim($str, '-_');
        $str = !strlen($str) ? $default : $str;
        return substr($str, 0, $maxLength);
    }

    /**
     * Remove the html segments
     *
     * @access public
     * @param string $html Input string
     * @return string
     */
    public static function removeParagraph($html)
    {
        /** Locked labels */
        $html = self::lockHTML($html);
        $html = str_replace(array("\r", "\n"), '', $html);

        $html = trim(preg_replace(
        array("/\s*<p>(.*?)<\/p>\s*/is", "/\s*<br\s*\/>\s*/is",
        "/\s*<(div|blockquote|pre|code|script|table|fieldset|ol|ul|dl|h[1-6])([^>]*)>/is",
        "/<\/(div|blockquote|pre|code|script|table|fieldset|ol|ul|dl|h[1-6])>\s*/is", "/\s*<\!--more-->\s*/is"),
        array("\n\\1\n", "\n", "\n\n<\\1\\2>", "</\\1>\n\n", "\n\n<!--more-->\n\n"),
        $html));

        return trim(self::releaseHTML($html));
    }

    /**
     * Locked labels
     *
     * @access public
     * @param string $html Input string
     * @return string
     */
    public static function lockHTML($html)
    {
        return preg_replace_callback("/<(code|pre|script)[^>]*>.*?<\/\\1>/is", array('Typecho_Common', '__lockHTML'), $html);
    }

    /**
     * Release labels
     *
     * @access public
     * @param string $html Input string
     * @return string
     */
    public static function releaseHTML($html)
    {
        $html = trim(str_replace(array_keys(self::$_lockedBlocks), array_values(self::$_lockedBlocks), $html));
        self::$_lockedBlocks = array('<p></p>' => '');
        return $html;
    }

    /**
     * Text piecewise functions
     *
     * @param string $string Require segmented string
     * @return string
     */
    public static function cutParagraph($string)
    {
        static $loaded;
        if (!$loaded) {
            $loaded = true;
        }

        return Typecho_Common_Paragraph::process($string);
    }

    /**
     * Generate a random string
     *
     * @access public
     * @param integer $length String length
     * @param boolean $specialChars Are there special characters?
     * @return string
     */
    public static function randString($length, $specialChars = false)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($specialChars) {
            $chars .= '!@#$%^&*()';
        }

        $result = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[rand(0, $max)];
        }
        return $result;
    }

    /**
     * The hash encryption string
     *
     * @access public
     * @param string $string Need to hash strings
     * @param string $salt Scrambler
     * @return string
     */
    public static function hash($string, $salt = NULL)
    {
        /** Generate a random string */
        $salt = empty($salt) ? self::randString(9) : $salt;
        $length = strlen($string);
        $hash = '';
        $last = ord($string[$length - 1]);
        $pos = 0;

        /** Scrambling to determine the length of */
        if (strlen($salt) != 9) {
            /** If 9 is not directly returned */
            return;
        }

        while ($pos < $length) {
            $asc = ord($string[$pos]);
            $last = ($last * ord($salt[($last % $asc) % 9]) + $asc) % 95 + 32;
            $hash .= chr($last);
            $pos ++;
        }

        return '$T$' . $salt . md5($hash);
    }

    /**
     * Determine whether the same hash value
     *
     * @access public
     * @param string $from Source string
     * @param string $to Target string
     * @return boolean
     */
    public static function hashValidate($from, $to)
    {
        if ('$T$' == substr($to, 0, 3)) {
            $salt = substr($to, 3, 9);
            return self::hash($from, $salt) === $to;
        } else {
            return md5($from) === $to;
        }
    }

    /**
     * The path into links
     *
     * @access public
     * @param string $path path
     * @param string $prefix Prefix
     * @return string
     */
    public static function url($path, $prefix)
    {
        $path = (0 === strpos($path, './')) ? substr($path, 2) : $path;
        return rtrim($prefix, '/') . '/' . str_replace('//', '/', ltrim($path, '/'));
    }

    /**
     * Get the picture
     *
     * @access public
     * @param string $fileName 文件名
     * @return string
     */
    public static function mimeContentType($fileName)
    {
        // Determine parallel
        if (function_exists('mime_content_type')) {
            return mime_content_type($fileName);
        }

        if (function_exists('finfo_open')) {
            $fInfo = @finfo_open(FILEINFO_MIME);

            if (false !== $fInfo) {
                $mimeType = finfo_file($fInfo, $fileName);
                finfo_close($fInfo);
                return $mimeType;
            }
        }

        $mimeTypes = array(
          'ez' => 'application/andrew-inset',
          'csm' => 'application/cu-seeme',
          'cu' => 'application/cu-seeme',
          'tsp' => 'application/dsptype',
          'spl' => 'application/x-futuresplash',
          'hta' => 'application/hta',
          'cpt' => 'image/x-corelphotopaint',
          'hqx' => 'application/mac-binhex40',
          'nb' => 'application/mathematica',
          'mdb' => 'application/msaccess',
          'doc' => 'application/msword',
          'dot' => 'application/msword',
          'bin' => 'application/octet-stream',
          'oda' => 'application/oda',
          'ogg' => 'application/ogg',
          'prf' => 'application/pics-rules',
          'key' => 'application/pgp-keys',
          'pdf' => 'application/pdf',
          'pgp' => 'application/pgp-signature',
          'ps' => 'application/postscript',
          'ai' => 'application/postscript',
          'eps' => 'application/postscript',
          'rss' => 'application/rss+xml',
          'rtf' => 'text/rtf',
          'smi' => 'application/smil',
          'smil' => 'application/smil',
          'wp5' => 'application/wordperfect5.1',
          'xht' => 'application/xhtml+xml',
          'xhtml' => 'application/xhtml+xml',
          'zip' => 'application/zip',
          'cdy' => 'application/vnd.cinderella',
          'mif' => 'application/x-mif',
          'xls' => 'application/vnd.ms-excel',
          'xlb' => 'application/vnd.ms-excel',
          'cat' => 'application/vnd.ms-pki.seccat',
          'stl' => 'application/vnd.ms-pki.stl',
          'ppt' => 'application/vnd.ms-powerpoint',
          'pps' => 'application/vnd.ms-powerpoint',
          'pot' => 'application/vnd.ms-powerpoint',
          'sdc' => 'application/vnd.stardivision.calc',
          'sda' => 'application/vnd.stardivision.draw',
          'sdd' => 'application/vnd.stardivision.impress',
          'sdp' => 'application/vnd.stardivision.impress',
          'smf' => 'application/vnd.stardivision.math',
          'sdw' => 'application/vnd.stardivision.writer',
          'vor' => 'application/vnd.stardivision.writer',
          'sgl' => 'application/vnd.stardivision.writer-global',
          'sxc' => 'application/vnd.sun.xml.calc',
          'stc' => 'application/vnd.sun.xml.calc.template',
          'sxd' => 'application/vnd.sun.xml.draw',
          'std' => 'application/vnd.sun.xml.draw.template',
          'sxi' => 'application/vnd.sun.xml.impress',
          'sti' => 'application/vnd.sun.xml.impress.template',
          'sxm' => 'application/vnd.sun.xml.math',
          'sxw' => 'application/vnd.sun.xml.writer',
          'sxg' => 'application/vnd.sun.xml.writer.global',
          'stw' => 'application/vnd.sun.xml.writer.template',
          'sis' => 'application/vnd.symbian.install',
          'wbxml' => 'application/vnd.wap.wbxml',
          'wmlc' => 'application/vnd.wap.wmlc',
          'wmlsc' => 'application/vnd.wap.wmlscriptc',
          'wk' => 'application/x-123',
          'dmg' => 'application/x-apple-diskimage',
          'bcpio' => 'application/x-bcpio',
          'torrent' => 'application/x-bittorrent',
          'cdf' => 'application/x-cdf',
          'vcd' => 'application/x-cdlink',
          'pgn' => 'application/x-chess-pgn',
          'cpio' => 'application/x-cpio',
          'csh' => 'text/x-csh',
          'deb' => 'application/x-debian-package',
          'dcr' => 'application/x-director',
          'dir' => 'application/x-director',
          'dxr' => 'application/x-director',
          'wad' => 'application/x-doom',
          'dms' => 'application/x-dms',
          'dvi' => 'application/x-dvi',
          'pfa' => 'application/x-font',
          'pfb' => 'application/x-font',
          'gsf' => 'application/x-font',
          'pcf' => 'application/x-font',
          'pcf.Z' => 'application/x-font',
          'gnumeric' => 'application/x-gnumeric',
          'sgf' => 'application/x-go-sgf',
          'gcf' => 'application/x-graphing-calculator',
          'gtar' => 'application/x-gtar',
          'tgz' => 'application/x-gtar',
          'taz' => 'application/x-gtar',
          'gz'  => 'application/x-gtar',
          'hdf' => 'application/x-hdf',
          'phtml' => 'application/x-httpd-php',
          'pht' => 'application/x-httpd-php',
          'php' => 'application/x-httpd-php',
          'phps' => 'application/x-httpd-php-source',
          'php3' => 'application/x-httpd-php3',
          'php3p' => 'application/x-httpd-php3-preprocessed',
          'php4' => 'application/x-httpd-php4',
          'ica' => 'application/x-ica',
          'ins' => 'application/x-internet-signup',
          'isp' => 'application/x-internet-signup',
          'iii' => 'application/x-iphone',
          'jar' => 'application/x-java-archive',
          'jnlp' => 'application/x-java-jnlp-file',
          'ser' => 'application/x-java-serialized-object',
          'class' => 'application/x-java-vm',
          'js' => 'application/x-javascript',
          'chrt' => 'application/x-kchart',
          'kil' => 'application/x-killustrator',
          'kpr' => 'application/x-kpresenter',
          'kpt' => 'application/x-kpresenter',
          'skp' => 'application/x-koan',
          'skd' => 'application/x-koan',
          'skt' => 'application/x-koan',
          'skm' => 'application/x-koan',
          'ksp' => 'application/x-kspread',
          'kwd' => 'application/x-kword',
          'kwt' => 'application/x-kword',
          'latex' => 'application/x-latex',
          'lha' => 'application/x-lha',
          'lzh' => 'application/x-lzh',
          'lzx' => 'application/x-lzx',
          'frm' => 'application/x-maker',
          'maker' => 'application/x-maker',
          'frame' => 'application/x-maker',
          'fm' => 'application/x-maker',
          'fb' => 'application/x-maker',
          'book' => 'application/x-maker',
          'fbdoc' => 'application/x-maker',
          'wmz' => 'application/x-ms-wmz',
          'wmd' => 'application/x-ms-wmd',
          'com' => 'application/x-msdos-program',
          'exe' => 'application/x-msdos-program',
          'bat' => 'application/x-msdos-program',
          'dll' => 'application/x-msdos-program',
          'msi' => 'application/x-msi',
          'nc' => 'application/x-netcdf',
          'pac' => 'application/x-ns-proxy-autoconfig',
          'nwc' => 'application/x-nwc',
          'o' => 'application/x-object',
          'oza' => 'application/x-oz-application',
          'pl' => 'application/x-perl',
          'pm' => 'application/x-perl',
          'p7r' => 'application/x-pkcs7-certreqresp',
          'crl' => 'application/x-pkcs7-crl',
          'qtl' => 'application/x-quicktimeplayer',
          'rpm' => 'audio/x-pn-realaudio-plugin',
          'shar' => 'application/x-shar',
          'swf' => 'application/x-shockwave-flash',
          'swfl' => 'application/x-shockwave-flash',
          'sh' => 'text/x-sh',
          'sit' => 'application/x-stuffit',
          'sv4cpio' => 'application/x-sv4cpio',
          'sv4crc' => 'application/x-sv4crc',
          'tar' => 'application/x-tar',
          'tcl' => 'text/x-tcl',
          'tex' => 'text/x-tex',
          'gf' => 'application/x-tex-gf',
          'pk' => 'application/x-tex-pk',
          'texinfo' => 'application/x-texinfo',
          'texi' => 'application/x-texinfo',
          '~' => 'application/x-trash',
          '%' => 'application/x-trash',
          'bak' => 'application/x-trash',
          'old' => 'application/x-trash',
          'sik' => 'application/x-trash',
          't' => 'application/x-troff',
          'tr' => 'application/x-troff',
          'roff' => 'application/x-troff',
          'man' => 'application/x-troff-man',
          'me' => 'application/x-troff-me',
          'ms' => 'application/x-troff-ms',
          'ustar' => 'application/x-ustar',
          'src' => 'application/x-wais-source',
          'wz' => 'application/x-wingz',
          'crt' => 'application/x-x509-ca-cert',
          'fig' => 'application/x-xfig',
          'au' => 'audio/basic',
          'snd' => 'audio/basic',
          'mid' => 'audio/midi',
          'midi' => 'audio/midi',
          'kar' => 'audio/midi',
          'mpga' => 'audio/mpeg',
          'mpega' => 'audio/mpeg',
          'mp2' => 'audio/mpeg',
          'mp3' => 'audio/mpeg',
          'm3u' => 'audio/x-mpegurl',
          'sid' => 'audio/prs.sid',
          'aif' => 'audio/x-aiff',
          'aiff' => 'audio/x-aiff',
          'aifc' => 'audio/x-aiff',
          'gsm' => 'audio/x-gsm',
          'wma' => 'audio/x-ms-wma',
          'wax' => 'audio/x-ms-wax',
          'ra' => 'audio/x-realaudio',
          'rm' => 'audio/x-pn-realaudio',
          'ram' => 'audio/x-pn-realaudio',
          'pls' => 'audio/x-scpls',
          'sd2' => 'audio/x-sd2',
          'wav' => 'audio/x-wav',
          'pdb' => 'chemical/x-pdb',
          'xyz' => 'chemical/x-xyz',
          'bmp' => 'image/x-ms-bmp',
          'gif' => 'image/gif',
          'ief' => 'image/ief',
          'jpeg' => 'image/jpeg',
          'jpg' => 'image/jpeg',
          'jpe' => 'image/jpeg',
          'pcx' => 'image/pcx',
          'png' => 'image/png',
          'svg' => 'image/svg+xml',
          'svgz' => 'image/svg+xml',
          'tiff' => 'image/tiff',
          'tif' => 'image/tiff',
          'wbmp' => 'image/vnd.wap.wbmp',
          'ras' => 'image/x-cmu-raster',
          'cdr' => 'image/x-coreldraw',
          'pat' => 'image/x-coreldrawpattern',
          'cdt' => 'image/x-coreldrawtemplate',
          'djvu' => 'image/x-djvu',
          'djv' => 'image/x-djvu',
          'ico' => 'image/x-icon',
          'art' => 'image/x-jg',
          'jng' => 'image/x-jng',
          'psd' => 'image/x-photoshop',
          'pnm' => 'image/x-portable-anymap',
          'pbm' => 'image/x-portable-bitmap',
          'pgm' => 'image/x-portable-graymap',
          'ppm' => 'image/x-portable-pixmap',
          'rgb' => 'image/x-rgb',
          'xbm' => 'image/x-xbitmap',
          'xpm' => 'image/x-xpixmap',
          'xwd' => 'image/x-xwindowdump',
          'igs' => 'model/iges',
          'iges' => 'model/iges',
          'msh' => 'model/mesh',
          'mesh' => 'model/mesh',
          'silo' => 'model/mesh',
          'wrl' => 'x-world/x-vrml',
          'vrml' => 'x-world/x-vrml',
          'csv' => 'text/comma-separated-values',
          'css' => 'text/css',
          '323' => 'text/h323',
          'htm' => 'text/html',
          'html' => 'text/html',
          'uls' => 'text/iuls',
          'mml' => 'text/mathml',
          'asc' => 'text/plain',
          'txt' => 'text/plain',
          'text' => 'text/plain',
          'diff' => 'text/plain',
          'rtx' => 'text/richtext',
          'sct' => 'text/scriptlet',
          'wsc' => 'text/scriptlet',
          'tm' => 'text/texmacs',
          'ts' => 'text/texmacs',
          'tsv' => 'text/tab-separated-values',
          'jad' => 'text/vnd.sun.j2me.app-descriptor',
          'wml' => 'text/vnd.wap.wml',
          'wmls' => 'text/vnd.wap.wmlscript',
          'xml' => 'text/xml',
          'xsl' => 'text/xml',
          'h++' => 'text/x-c++hdr',
          'hpp' => 'text/x-c++hdr',
          'hxx' => 'text/x-c++hdr',
          'hh' => 'text/x-c++hdr',
          'c++' => 'text/x-c++src',
          'cpp' => 'text/x-c++src',
          'cxx' => 'text/x-c++src',
          'cc' => 'text/x-c++src',
          'h' => 'text/x-chdr',
          'c' => 'text/x-csrc',
          'java' => 'text/x-java',
          'moc' => 'text/x-moc',
          'p' => 'text/x-pascal',
          'pas' => 'text/x-pascal',
          '***' => 'text/x-pcs-***',
          'shtml' => 'text/x-server-parsed-html',
          'etx' => 'text/x-setext',
          'tk' => 'text/x-tcl',
          'ltx' => 'text/x-tex',
          'sty' => 'text/x-tex',
          'cls' => 'text/x-tex',
          'vcs' => 'text/x-vcalendar',
          'vcf' => 'text/x-vcard',
          'dl' => 'video/dl',
          'fli' => 'video/fli',
          'gl' => 'video/gl',
          'mpeg' => 'video/mpeg',
          'mpg' => 'video/mpeg',
          'mpe' => 'video/mpeg',
          'qt' => 'video/quicktime',
          'mov' => 'video/quicktime',
          'mxu' => 'video/vnd.mpegurl',
          'dif' => 'video/x-dv',
          'dv' => 'video/x-dv',
          'lsf' => 'video/x-la-asf',
          'lsx' => 'video/x-la-asf',
          'mng' => 'video/x-mng',
          'asf' => 'video/x-ms-asf',
          'asx' => 'video/x-ms-asf',
          'wm' => 'video/x-ms-wm',
          'wmv' => 'video/x-ms-wmv',
          'wmx' => 'video/x-ms-wmx',
          'wvx' => 'video/x-ms-wvx',
          'avi' => 'video/x-msvideo',
          'movie' => 'video/x-sgi-movie',
          'ice' => 'x-conference/x-cooltalk',
          'vrm' => 'x-world/x-vrml',
          'rar' => 'application/x-rar-compressed',
          'cab' => 'application/vnd.ms-cab-compressed'
        );

        $part = explode('.', $fileName);
        $size = count($part);

        if ($size > 1) {
            $ext = $part[$size - 1];
            if (isset($mimeTypes[$ext])) {
                return $mimeTypes[$ext];
            }
        }

        return 'application/octet-stream';
    }

    /**
     * Find matching mime icon
     *
     * @access public
     * @param string $mime mime type
     * @return string
     */
    public static function mimeIconType($mime)
    {
        $parts = explode('/', $mime);

        if (count($parts) < 2) {
            return 'unknown';
        }

        list ($type, $stream) = $parts;

        if (in_array($type, array('image', 'video', 'audio', 'text', 'application'))) {
            switch (true) {
                case in_array($stream, array('msword', 'msaccess', 'ms-powerpoint', 'ms-powerpoint')):
                case 0 === strpos($stream, 'vnd.'):
                    return 'office';
                case false !== strpos($stream, 'html') || false !== strpos($stream, 'xml') || false !== strpos($stream, 'wml'):
                    return 'html';
                case false !== strpos($stream, 'compressed') || false !== strpos($stream, 'zip') ||
                in_array($stream, array('application/x-gtar', 'application/x-tar')):
                    return 'archive';
                case 'text' == $type && 0 === strpos($stream, 'x-'):
                    return 'script';
                default:
                    return $type;
            }
        } else {
            return 'unknown';
        }
    }
}
