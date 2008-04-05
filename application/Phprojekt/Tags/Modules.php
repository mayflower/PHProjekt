<?php
/**
 * User-Tag <-> Modules relation class
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * The class provide the functions for manage the relation between
 * the user-tag relation and modules
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Tags_Modules extends Zend_Db_Table_Abstract
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'TagsModules';

    /**
     * Constructs a Phprojekt_Tags_Modules
     */
    public function __construct()
    {
        $config = array('db' => Zend_Registry::get('db'));
        parent::__construct($config);
    }

    /**
     * Save a new relation User-Tag <-> Module-Item
     *
     * Is  nessesary check if exists,
     * since the relations are delete before insert it
     * but can be the same word in the string separated by spaces
     *
     * This function use the Zend_DB insert
     *
     * @param string  $module    The module to store
     * @param integer $itemId    The item ID
     * @param integer $tagUserId The User-Tag relation Id
     *
     * @return void
     */
    public function saveTags($module, $itemId, $tagUserId)
    {
        if ($this->find($module, $itemId, $tagUserId)->count() == 0) {
            $data['module']     = $module;
            $data['itemId']     = $itemId;
            $data['tagUserId']  = $tagUserId;
            $this->insert($data);
        }
    }

    /**
     * Return all the modules with the relation User-Tag
     *
     * @param integer $tagUserId - Relation User-Tag Id
     *
     * @return array
     */
    public function getModulesByRelationId($tagUserId)
    {
        $where        = array();
        $foundResults = array();

        $where[] = 'tagUserId  = '. $this->getAdapter()->quote($tagUserId);

        $modules = $this->fetchAll($where);
        foreach ($modules as $moduleData) {
            $foundResults[] = array('id'     => $moduleData->itemId,
                                    'module' => $moduleData->module);
        }

        return $foundResults;
    }

    /**
     * Return all the relations with the pair module-itemId
     *
     * @param string  $module    The module to store
     * @param integer $itemId    The item ID
     *
     * @return integer
     */
    public function getRelationIdByModule($module, $itemId)
    {
        $where        = array();
        $foundResults = array();

        $where[] = 'module  = '. $this->getAdapter()->quote($module);
        $where[] = 'itemId  = '. $this->getAdapter()->quote($itemId);

        $modules = $this->fetchAll($where);
        foreach ($modules as $moduleData) {
            $foundResults[] = $moduleData->tagUserId;
        }

        return $foundResults;
    }

    /**
     * Delete all the entries for one userId-module-itemId pair
     *
     * @param string  $module     The module to store
     * @param integer $itemId     The item ID
     * @param array   $tagUserIds All the relationsId for delete
     *
     * @return void
     */
    public function deleteRelations($module, $itemId, $tagUserIds)
    {
        $clone = clone($this);
        foreach ($tagUserIds as $tagUserId) {
            $where = array();
            $where[] = 'module = '. $clone->getAdapter()->quote($module);
            $where[] = 'itemId = '. $clone->getAdapter()->quote($itemId);
            $where[] = 'tagUserId = '. $clone->getAdapter()->quote($tagUserId);
            $clone->delete($where);
        }
    }
}