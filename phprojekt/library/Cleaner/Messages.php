<?php
/**
 * Messages class.
 *
 * Abstraction of Messages Container.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category  PHProjekt
 * @package   Cleaner
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 * @link      http://www.thinkforge.org/projects/Cleaner
 * @since     File available since Release 6.0
 * @version   Release: 6.1.0
 * @author    Peter Voringer <peter.voringer@mayflower.de>
 */

/**
 * Messages class.
 *
 * Abstraction of Messages Container.
 *
 * @category  PHProjekt
 * @package   Cleaner
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 * @link      http://www.thinkforge.org/projects/Cleaner
 * @since     File available since Release 6.0
 * @version   Release: 6.1.0
 * @author    Peter Voringer <peter.voringer@mayflower.de>
 */
class Cleaner_Messages
{
    /**
     * Cached translation messages.
     *
     * @var array
     */
    protected static $_translations = array();

    /**
     * Keys of messages.
     *
     * @var array
     */
    protected $_messages;

    /**
     * Constructor of Cleaner_Messages.
     *
     */
    public function __construct()
    {
        $this->_messages = array();
    }

    /**
     * Get flag, if messages does not contain any message.
     *
     * @return boolean True for 0 messages.
     */
    public function isEmpty()
    {
        return (empty($this->_messages));
    }

    /**
     * Get an associative array with keys and localized messages.
     *
     * @param string $locale Name of locale to use.
     *
     * @return array Array with MessageKey => Localized Messagetext.
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
     * Add an additional message key.
     *
     * @param string $message Key of message.
     *
     * @return void
     */
    public function add($message)
    {
        $this->_messages[] = $message;
    }

    /**
     * Check, if a certain messagekey was added.
     *
     * @param string $message MessageKey.
     *
     * @return boolean True if has.
     */
    public function has($message)
    {
        return in_array($message, $this->_messages);
    }

    /**
     * Return the messages as a string.
     *
     * @return string Message.
     */
    public function __toString()
    {
        return implode(", ", $this->_messages);
    }

    /**
     * Load messages for a certain locale.
     *
     * @param string $locale Name oflocale (for example de_DE, en_US).
     *
     * @return string
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
