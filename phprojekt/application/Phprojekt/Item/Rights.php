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
 * Return and save the rights using the moduleId-itemId relation.
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
    public function __construct()
    {
        $config = array('db' => Zend_Registry::get('db'));

        parent::__construct($config);
    }

    /**
     * Save all the access for each user
     * The function will re-order the user and access for save it
     *
     * @param string  $moduleId   The module Id to store
     * @param integer $itemId     The item Id
     * @param array   $adminUsers Array of userIds with admin access
     * @param array   $writeUsers Array of userIds with write access
     * @param array   $readUsers  Array of userIds with read access
     *
     * @return void
     */
    public function _save($moduleId, $itemId, $adminUsers, $writeUsers, $readUsers)
    {
        // Delete the entries for this moduleId-itemId and re-inserted the changes
        $this->_delete($moduleId, $itemId);

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
            $this->_saveRight($moduleId, $itemId, $userId, $adminAccess, $writeAccess, $readAccess);
        }
    }

    /**
     * Save an access right
     *
     * This function use the Zend_DB insert
     *
     * @param string  $moduleId The module Id to store
     * @param integer $itemId   The item ID
     * @param integer $userId   The user to save
     * @param integer $admin    Value of the admin access
     * @param integer $write    Value of the write access
     * @param integer $read     Value of the read access
     *
     * @return void
     */
    private function _saveRight($moduleId, $itemId, $userId, $admin, $write, $read)
    {
        $data['moduleId']     = (int)$moduleId;
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
     * @param string  $moduleId The moduleId to delete
     * @param integer $itemId   The item ID
     *
     * @return void
     */
    private function _delete($moduleId, $itemId)
    {
        $where = array();
        $clone = clone($this);

        $where[] = 'moduleId = '. $clone->getAdapter()->quote($moduleId);
        $where[] = 'itemId = '. $clone->getAdapter()->quote($itemId);
        $clone->delete($where);
    }

    /**
     * Return the right
     *
     * @param string  $moduleId The module Id
     * @param integer $itemId   The item Id
     * @param integer $userId   The user Id
     * @param string  $right    Field to check
     *
     * @return integer
     */
    public function hasRight($moduleId, $itemId, $userId, $right)
    {
        // Cache the query
        $rightNamespace = new Zend_Session_Namespace('ItemRights'.'-'.$moduleId.'-'.$itemId.'-'.$userId);
        if (isset($rightNamespace->right) && !empty($rightNamespace->right)) {
            $value = $rightNamespace->right;
        } else {
            $rows = $this->find($moduleId, $itemId, $userId);
            $value = 0;
            foreach ($rows as $row) {
                foreach ($row->toArray() as $k => $v) {
                    if ($k == $right) {
                        $value = $v;
                        break;
                    }
                }
            }
            $rightNamespace->right = $value;
        }
        return $value;
    }

    /**
     * Return all the rights for a moduleId-ItemId
     *
     * @param string  $moduleId The module Id
     * @param integer $itemId   The item Id
     *
     * @return array
     */
    public function getRights($moduleId, $itemId)
    {
        // Cache the query
        //$rightNamespace = new Zend_Session_Namespace('ItemRights'.'-'.$moduleId.'-'.$itemId);
        //if (isset($rightNamespace->right) && !empty($rightNamespace->right)) {
        //    $values = $rightNamespace->right;
        //} else {
            $db     = Zend_Registry::get('db');
            $user   = new User_Models_User($db);
            $where  = array();
            $values = array();

            $where[] = 'moduleId = '. (int)$moduleId;
            $where[] = 'itemId = '. (int)$itemId;
            $where   = implode(' AND ', $where);
            $rows    = $this->fetchAll($where)->toArray();
            foreach ($rows as $row) {
                $row['userName'] = $user->findUserById($row['userId'])->username;
                $row['adminAccess'] = (boolean) $row['adminAccess'];
                $row['writeAccess'] = (boolean) $row['writeAccess'];
                $row['readAccess']  = (boolean) $row['readAccess'];
                $values[] = $row;
            }
            //$rightNamespace->right = $values;
        //}
        return $values;
    }
}