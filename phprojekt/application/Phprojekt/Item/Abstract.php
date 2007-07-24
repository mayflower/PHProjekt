<?php
/**
 * A item, with database manager support
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * A item, with database manager support
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
abstract class Phprojekt_Item_Abstract extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Represents the database_manager class
     *
     * @var Phprojekt_ActiveRecord_Abstract
     */
    public $_dbManager = '';

    /**
     * Initialize new object
     *
     * @param array $config Configuration for Zend_Db_Table
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $dbManager = new Phprojekt_DatabaseManager($config);

        $this->_dbManager = $dbManager;
    }

    /**
     * Get the field for list view from the databae_manager
     *
     * @param string $table The name of the module table
     *
     * @return array Array with the data of the fields for make the list
     */
    public function getFieldsForList($table)
    {
        return $this->_dbManager->getFieldsForList($table);
    }

    /**
     * Get the field for the form view from the databae_manager
     *
     * @param string $table The name of the module table
     *
     * @return array Array with the data of the fields for make the form
     */
    public function getFieldsForForm($table)
    {
        return $this->_dbManager->getFieldsForForm($table);
    }
}
