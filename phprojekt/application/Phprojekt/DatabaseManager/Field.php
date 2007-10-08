<?php
/**
 * Represent a field in an active record and hold additional information from
 * the DatabaseManager
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Enter description here...
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_DatabaseManager_Field
{
    protected $_dbManager;
    protected $_metadata = array();
    public $value;

    /**
     * Initialise a new object
     *
     * @param Phprojekt_DatabaseManager $dbm
     * @param string                    $name
     * @param mixed                     $value
     */
    public function __construct(Phprojekt_DatabaseManager $dbm, $name, $value)
    {
        $this->value = (string) $value;

        $this->_metadata = $dbm->find($name);
    }

    public function __get($name)
    {
        if (isset($this->_metadata->$name)) {
            return $this->_metadata->$name;
        }
    }

    public function __toString()
    {
        return $this->value;
    }
}