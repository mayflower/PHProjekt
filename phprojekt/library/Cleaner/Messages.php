<?php
/**
 * Messages class
 *
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    CVS: $Id$
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @since      File available since Release 1.0
 *
 */

/**
 * Abstraction of Messages Container
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: <package_version>
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @since      File available since Release 1.0
 */
class Cleaner_Messages
{
    /**
     * Cached translation messages
     *
     * @var array
     */
    protected static $_translations = array();

    /**
     * Keys of messages
     *
     * @var array
     */
    protected $_messages;

    /**
     * Constructor of Cleaner_Messages
     *
     */
    public function __construct()
    {
        $this->_messages = array();
    }

    /**
     * Get flag, if messages does not contain any message
     *
     * @return bool true, if there are no messages
     */
    public function isEmpty()
    {
        return (empty($this->_messages));
    }

    /**
     * Get an associative array with keys and localized messages
     *
     * @param string $locale Name of locale to use
     *
     * @return array Array with MessageKey => Localized Messagetext
     */
    public function get($locale)
    {
        if (!isset(self::$_translations[$locale])) {
            self::$_translations[$locale] = self::_load($locale);
        }

        $result = array();

        foreach ($this->_messages as $message) {
            $result[$message] = self::$_translations[$locale][$message];
        }

        return $result;
    }

    /**
     * Add an additional message key
     *
     * @param string $message Key of message
     *
     * @return void
     */
    public function add($message)
    {
        $this->_messages[] = $message;
    }

    /**
     * Check, if a certain messagekey was added
     *
     * @param string $message MessageKey
     *
     * @return bool
     */
    public function has($message)
    {
        return in_array($message, $this->_messages);
    }

    /**
     * Return the messages as a string
     *
     * @return string
     */
    public function __toString()
    {
        return implode(", ", $this->_messages);
    }

    /**
     * Load messages for a certain locale
     *
     * @param string $locale Name oflocale (for example de_DE, en_US)
     *
     * @return array
     */
    protected static function _load($locale)
    {
        $filename = 'Locale/' . $locale . '.php';

        if (!file_exists($filename)) {
            throw new Cleaner_SystemException('Locale not available');
        }

        return (include $filename);
    }
}