<?php
/**
 * Timeproj model class
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id: 
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Timeproj model class
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Timecard_Models_Timeproj extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Validate object
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * Initialize new user
     * If is seted the user id in the session,
     * the class will get all the values of these user
     *
     * @param array $db Configuration for Zend_Db_Table
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Zend_Registry::get('db');
        }
        parent::__construct($db);

        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new Timecard_Models_TimeprojInformation();
    }

    /**
     * Get the information manager
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }

    /**
     * Get the rigths
     *
     * @return array
     */
    public function getRights()
    {
        return array();
    }

    /**
     * Save the rigths
     *
     * @return void
     */
    public function saveRights()
    {
    }

    /**
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $data      = $this->_data;
        $fields    = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        $this->_validate = new Phprojekt_Model_Validate();
        return $this->_validate->recordValidate($this, $data, $fields);
    }

    /**
     * Return the error data
     *
     * @return array
     */
    public function getError()
    {
        return (array) $this->_validate->error->getError();
    }    
}