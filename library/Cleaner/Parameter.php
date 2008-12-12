<?php
/**
 * Parameter class
 *
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    CVS: $Id$
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @since      File available since Release 6.0
 *
 */

/**
 * Abstraction of Parameter/Item of some scope
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: <package_version>
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @since      File available since Release 6.0
 */
class Cleaner_Parameter
{
    /**
     * Sanitized Value
     *
     * @var mixed
     */
    protected $_sanitizedValue;

    /**
     * Original Value
     *
     * @var mixed
     */
    protected $_originalValue;

    /**
     * Default Value
     *
     * @var mixed
     */
    protected $_defaultValue;

    /**
    * Flag, if it's the default value
    *
    * @var bool
    */
    protected $_isDefault;

    /**
     * Flag, if value was valid
     *
     * @var bool
     */
    protected $_isValid;

    /**
     * Flag, if value is sanitized
     *
     * @var bool
     */
    protected $_isSanitized;

    /**
     * Messeages generated by Validator and/or Sanitizer
     *
     * @var Cleaner_Messages
     */
    protected $_messages;

    /**
     * Name of Validator/Sanitizer
     *
     * @var string
     */
    protected $_type;

    /**
     * Name/Key of parameter/item
     *
     * @var string
     */
    protected $_name;

    /**
     * Name/Key of scope
     *
     * @var string
     */
    protected $_scope;


    /**
     * Constructor of Cleaner_Parameter
     *
     * @param mixed  $sanitizedValue Sanitized Value of Parameter/Item Value
     * @param mixed  $orginalValue   Original Value of Parameter/Item Value
     * @param mixed  $defaultValue   Default Value of Parameter/Item Value
     * @param bool   $isValid        Flag, if value of Parameter/Item is valid
     * @param bool   $isSanitized    Flag, if value of Parameter/Item is
     *                               sanitized
     * @param bool   $isDefault      Flag, if value of Parameter/Item is the
     *                               default value
     * @param object $messages       Messeages generated by Validator and/or
     *                               Sanitizer
     * @param string $type           Name of Validator and/or Sanitizer
     * @param string $name           Name/Key of parameter/item
     * @param string $scope          Name/Key of scope
     *
     * @return void
     *
     */
    public function __construct($sanitizedValue, $orginalValue, $defaultValue,
        $isValid, $isSanitized, $isDefault, $messages, $type, $name, $scope)
    {
        $this->_sanitizedValue = $sanitizedValue;
        $this->_originalValue  = $orginalValue;
        $this->_defaultValue   = $defaultValue;

        $this->_isValid     = $isValid;
        $this->_isSanitized = $isSanitized;
        $this->_isDefault   = $isDefault;

        $this->_messages = $messages;

        $this->_type  = $type;
        $this->_name  = $name;
        $this->_scope = $scope;
    }

    /**
     * Get Name of Validator and/or Sanitizer
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get Name/Key of parameter/item
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get Name/Key of scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * Get Sanitized Value of Parameter/Item Value
     *
     * @return mixed
     */
    public function getSanitizedValue()
    {
        return $this->_sanitizedValue;
    }

    /**
     * Get Original Value of Parameter/Item Value
     *
     * @return mixed
     */
    public function getOriginalValue()
    {
        return $this->_originalValue;
    }

    /**
     * Get Default Value of Parameter/Item Value
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    /**
     * Get Flag, if value of Parameter/Item is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->_isValid;
    }

    /**
     * Get Flag, if value of Parameter/Item is sanitized
     *
     * @return bool
     */
    public function isSanitized()
    {
        return $this->_isSanitized;
    }

    /**
     * Get Flag, if value of Parameter/Item is the default value
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->_isDefault;
    }

    /**
     * Get Messeages generated by Validator and/or Sanitizer
     *
     * @return Cleaner_Messages
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Get OriginalValue, when valid, DefaultValue if value was
     * null/empty/whitespaces, otherwise SanitizedValue, except Sanitizing
     * was switched off, the you get the OriginalValue of Parameter/Item
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($this->_isValid) {
            // When valid, work with orginalValue
            return $this->originalValue;
        } else if ($this->_isDefault) {
            // When parameter not set work with the DefaultValue
            return $this->_defaultValue;
        } else if ($this->_isSanitized) {
            // When not valid and not empty work with the SanitizedValue
            return $this->_sanitizedValue;
        }

        // Only happens, when sanitize was set to false when fetching parameter
        return $this->_originalValue;
    }

    /**
     * Get OriginalValue, when valid, DefaultValue if value was
     * null/empty/whitespaces, otherwise SanitizedValue, except Sanitizing
     * OriginalValue of Parameter/Item
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->getValue();
    }
}