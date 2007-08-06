<?php
/**
 * Tree class
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
 * Represents an node of a tree and provides iterator abilities.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
class Phprojekt_Filter_UserFilter implements Phprojekt_Filter_Interface
{

    /**
     * The next filter in chain
     *
     * @var Phprojekt_Filter_UserFilter
     */
    protected $_next = null;

    /**
     * Holds the actual identifier
     *
     * @var string
     */
    protected $_identifier = null;

    /**
     * Holds the actual value
     *
     * @var mixed
     */
    protected $_value = null;

    /**
     * Enter description here...
     *
     * @param unknown_type $identifier
     */
    public function __construct(Phprojekt_ActiveRecord_Abstract $record, $identifier, $value)
    {
        $info = $record->info();
        $cols = $info['columns'];

        if (array_key_exists($identifier, $cols)) {
            throw new Exception('Identifier not found');
        }

        $this->_identifier = $identifier;
    }

    /**
     * Set a value
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setValue($value) {
        $this->_value = $value;
    }

    /**
     * Filters a select
     *
     * @return void
     */
    public function filter(Zend_Db_Select &$select, Zend_Db_Adapter_Abstract $adapter)
    {
        $select->where(sprintf('%s = %s',
                    $adapter->quote($identifier),
                    $adapter->quote($value));
        if ($this->_next) {
            $this->_next->filter($select);
        }
    }

    /**
     * Adds a filter to the chain
     *
     * @param Phprojekt_Filter_Interface $filter
     *
     * @return void
     */
    public function addFilter(Phprojekt_Filter_Interface $filter)
    {
        if (null == $this->_next) {
            $this->_next = $filter;
        }

        $this->_next->addFilter($filter);
    }
}