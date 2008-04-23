<?php
/**
 * Item Rights Class for PHProjekt 6.0
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
 * This class manage the rights for each item per user.
 * Return and save the rights using the module-itemId relation.
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
class Phprojekt_Item_Rights extends Zend_Db_Table_Abstract
{
    protected $_name = 'ItemRights';

    /**
     * Change the tablename for use with the Zend db class
     *
     * @param array $config The config array for the database
     */
    public function __construct($config = array())
    {
        if (null === $config) {
            $config = array('db' => Zend_Registry::get('db'));
        }

        if (!is_array($config)) {
            $config = array('db' => $config);
        }

        if (!array_key_exists('db', $config) ||
            !is_a($config['db'], 'Zend_Db_Adapter_Abstract')) {
            throw new Phprojekt_ActiveRecord_Exception("Phprojekt_Item_Rights class must "
                                                     . "be initialized using a valid "
                                                     . "Zend_Db_Adapter_Abstract");

        }
        parent::__construct($config);
    }

    /**
     * Save all the access for each user
     * The function will re-order the user and access for save it
     *
     * @param string  $module     The module to store
     * @param integer $itemId     The item ID
     * @param array   $adminUsers Array of userIds with admin access
     * @param array   $writeUsers Array of userIds with write access
     * @param array   $readUsers  Array of userIds with read access
     *
     * @return void
     */
    public function _save($module, $itemId, $adminUsers, $writeUsers, $readUsers)
    {
        // Delete the entries for this module-item and re-inserted the changes
        $this->_delete($module, $itemId);

        $userData = array();
        foreach ($adminUsers as $user) {
            $userData[$user]['admin'] = 1;
        }
        foreach ($writeUsers as $user) {
            $userData[$user]['write'] = 1;
        }
        foreach ($readUsers as $user) {
            $userData[$user]['read'] = 1;
        }
        foreach ($userData as $userId => $accessData) {
            $adminAccess = 0;
            $writeAccess = 0;
            $readAccess  = 0;
            if (isset($accessData['admin'])) {
                $adminAccess = 1;
            }
            if (isset($accessData['write'])) {
                $writeAccess = 1;
            }
            if (isset($accessData['read'])) {
                $readAccess = 1;
            }
            $this->_saveRight($module, $itemId, $userId, $adminAccess, $writeAccess, $readAccess);
        }
    }

    /**
     * Save an access right
     *
     * This function use the Zend_DB insert
     *
     * @param string  $module The module to store
     * @param integer $itemId The item ID
     * @param integer $userId The user to save
     * @param integer $admin  Value of the admin access
     * @param integer $write  Value of the write access
     * @param integer $read   Value of the read access
     *
     * @return void
     */
    private function _saveRight($module, $itemId, $userId, $admin, $write, $read)
    {
        $data['module']       = $module;
        $data['itemId']       = (int)$itemId;
        $data['userId']       = (int)$userId;
        $data['adminAccess']  = $admin;
        $data['writeAccess']  = $write;
        $data['readAccess']   = $read;
        $this->insert($data);
    }

    /**
     * Delete all the users for one object
     *
     * @param string  $module The module to delete
     * @param integer $itemId The item ID
     *
     * @return void
     */
    private function _delete($module, $itemId)
    {
        $where = array();
        $clone = clone($this);

        $where[] = 'module = '. $clone->getAdapter()->quote($module);
        $where[] = 'itemId = '. $clone->getAdapter()->quote($itemId);
        $clone->delete($where);
    }
}