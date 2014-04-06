<?php

/**
 * Date processing
 *
 * @author qining
 * @category typecho
 * @package Date
 */
class Typecho_Date
{
    /**
     * Expected time zone offset
     *
     * @access public
     * @var integer
     */
    public static $timezoneOffset = 0;

    /**
     * Server time zone offset
     *
     * @access public
     * @var integer
     */
    public static $serverTimezoneOffset = 0;

    /**
     * Current GMT timestamp
     *
     * @access public
     * @var integer
     */
    public static $gmtTimeStamp;

    /**
     * Timestamp can be directly converted
     *
     * @access public
     * @var integer
     */
    public $timeStamp = 0;

    /**
     * Init parameters
     *
     * @access public
     * @param integer $gmtTime GMT timestamp
     * @return void
     */
    public function __construct($gmtTime)
    {
        $this->timeStamp = $gmtTime + (self::$timezoneOffset - self::$serverTimezoneOffset);
    }

    /**
     * Set the expected current time zone offset
     *
     * @access public
     * @param integer $offset
     * @return void
     */
    public static function setTimezoneOffset($offset)
    {
        self::$timezoneOffset = $offset;
        self::$serverTimezoneOffset = idate('Z');
    }

    /**
     * Get formatted time
     *
     * @access public
     * @param string $format Time Format
     * @return string
     */
    public function format($format)
    {
        return date($format, $this->timeStamp);
    }

    /**
     * Get internationalization offset time
     *
     * @access public
     * @return string
     */
    public function word()
    {
        return Typecho_I18n::dateWord($this->timeStamp, self::gmtTime() + (self::$timezoneOffset - self::$serverTimezoneOffset));
    }

    /**
     * Access to individual data
     *
     * @access public
     * @param string $name Name
     * @return integer
     */
    public function __get($name)
    {
        switch ($name) {
            case 'year':
                return date('Y', $this->timeStamp);
            case 'month':
                return date('m', $this->timeStamp);
            case 'day':
                return date('d', $this->timeStamp);
            default:
                return;
        }
    }

    /**
     * Get GMT Time
     *
     * @access public
     * @return integer
     */
    public static function gmtTime()
    {
        return self::$gmtTimeStamp ? self::$gmtTimeStamp : (self::$gmtTimeStamp = @gmmktime());
    }
}
