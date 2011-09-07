<?php
/**
 * Project model class.
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
 * @category   PHProjekt
 * @package    Application
 * @subpackage Project
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Project model class.
 *
 * The class of each model return the data for show
 * on the list and the form view.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Project
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_Models_Project extends Phprojekt_Item_Abstract
{
    public $hasMany = array('modulePermissions' => array('classname' => 'Project_Models_ProjectModulePermissions'));

    /**
     * Validate function for the projectId field.
     *
     * @param integer $value Value of the projectId to check.
     *
     * @return string Error msg.
     */
    public function validateProjectId($value)
    {
        if (null !== $this->id && $this->id > 0) {
            $node = Phprojekt_Loader::getModel('Project', 'Project')->find($this->id);
            $tree = new Phprojekt_Tree_Node_Database($node, $this->id);
            if ($tree->setup()->getActiveRecord()->id == $value) {
                return Phprojekt::getInstance()->translate('The project can not be saved under itself');
            } else if ($this->_isInTheProject($value, $tree)) {
                return Phprojekt::getInstance()->translate('The project can not be saved under its children');
            }
        }

        return null;
    }

    /**
     * Check if the projectId is under the same project or a subproject of him.
     *
     * @param integer                      $projectId The projectId to check.
     * @param Phprojekt_Tree_Node_Database $node      The node of the current project.
     *
     * @return boolean False if not.
     */
    private function _isInTheProject($projectId, $node)
    {
        $allow = false;
        if ($node->hasChildren()) {
            $childrens = $node->getChildren();
            foreach ($childrens as $childrenNode) {
                if ($projectId == $childrenNode->id) {
                    $allow = true;
                    break;
                } else {
                    if ($this->_isInTheProject($projectId, $childrenNode)) {
                        $allow = true;
                        break;
                    }
                }
            }
        }

        return $allow;
    }

    /**
     * Save the allow modules for one projectId.
     * First delete all the older relations
     *
     * @param array $rights Array with the modules to save.
     *
     * @return void
     */
    public function saveModules($rights)
    {
        foreach ($this->modulePermissions->fetchAll() as $relation) {
            $relation->delete();
        }
        foreach ($rights as $moduleId) {
            $modulePermissions = $this->modulePermissions->create();
            $modulePermissions->moduleId  = $moduleId;
            $modulePermissions->projectId = $this->id;
            $modulePermissions->save();
        }
    }

    /**
     * Add a new relation module-project without delete any entry.
     * Used for add modules to the root project.
     *
     * @param integer $moduleId The Module Id to add.
     *
     * @return void
     */
    public function addModule($moduleId)
    {
        $modulePermissions = $this->modulePermissions->create();
        $modulePermissions->moduleId = $moduleId;
        $modulePermissions->projectId = $this->id;
        $modulePermissions->save();
    }

    /**
     * Delete the tree cache after save.
     *
     * @return boolean True for a sucessful save.
     */
    public function parentSave()
    {
        $result = parent::parentSave();
        Phprojekt_Tree_Node_Database::deleteCache();
        $this->deleteCumulativeCompletePercentCache();

        return $result;
    }

    /**
     * Delete the tree cache after save.
     *
     * @return boolean True for a sucessful save.
     */
    public function save()
    {
        $result = parent::save();
        Phprojekt_Tree_Node_Database::deleteCache();

        $this->deleteCumulativeCompletePercentCache();
        return $result;
    }

    /**
     * Delete the tree cache after delete.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();
        Phprojekt_Tree_Node_Database::deleteCache();
        $this->deleteCumulativeCompletePercentCache();
    }

    /**
     * Save the rights for the current item.
     *
     * The users are a POST array with user IDs.
     *
     * @param array $rights Array of user IDs with the bitmask access.
     *
     * @return void
     */
    public function saveRights($rights)
    {
        // Do the default action
        parent::saveRights($rights);

        // Update access and delete the cache also for the children
        $itemRights   = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, $this->id);
        $tree         = $tree->setup();

        $users = array();
        foreach ($rights as $userId => $access) {
            $users[] = (int) $userId;
        }

        // Just a check
        if (empty($users)) {
            $users[] = 1;
        }

        // Keep on the childen only the access for the allowed users in the parent
        foreach ($tree as $node) {
            $projectId = (int) $node->id;

            // Delete users that are not allowed in the parent
            $where = sprintf('module_id = 1 AND item_id = %d AND user_id NOT IN (%s)', $projectId,
                implode(",", $users));
            $itemRights->delete($where);

            // Reset access by module-item-user
            foreach ($users as $userId) {
                // Reset cache
                $sessionName = 'Phprojekt_Item_Rights-getItemRight' . '-1-' . $projectId . '-' . $userId;
                $rightNamespace = new Zend_Session_Namespace($sessionName);
                $rightNamespace->unsetAll();
            }

            // Reset access by module-item
            $sessionName    = 'Phprojekt_Item_Rights-getUsersRights' . '-1-' . $projectId;
            $rightNamespace = new Zend_Session_Namespace($sessionName);
            $rightNamespace->unsetAll();

            // Reset users by module-item
            $sessionName    = 'Phprojekt_Item_Rights-getUsersWithRight' . '-1-' . $projectId;
            $rightNamespace = new Zend_Session_Namespace($sessionName);
            $rightNamespace->unsetAll();

            // Reset users by project
            $sessionName = 'Phprojekt_User_User-getAllowedUsers' . '-' . $projectId;
            $rightNamespace   = new Zend_Session_Namespace($sessionName);
            $rightNamespace->unsetAll();
        }
    }

    /**
     * Validate the data of the current record.
     *
     * @return boolean True for valid.
     */
    public function recordValidate()
    {
        if (!$this->_validate->validateDateRange($this->startDate, $this->endDate)) {
            return false;
        } else {
            return parent::recordValidate();
        }
    }

    /**
     * Retrieves the calculated completion of this project, i.e. The average
     * completion of all subprojects and todos.
     *
     * @return int Completion percentage.
     */
    public function getCumulativeCompletePercent()
    {
        if (empty($this->id)) {
            return 0;
        }

        $cache = Phprojekt::getInstance()->getCache();
        $id    = $this->getCumulativeCompletePercentCacheId();
        if ($cache->test($id)) {
            return $cache->load($id);
        }

        $db    = Phprojekt::getInstance()->getDb();
        $count = 0;
        $sum   = 0;

        $project     = new Project_Models_Project();
        $subprojects = $project->fetchAll(
            $db->quoteInto('project_id = ?', $this->id)
        );

        foreach ($subprojects as $s) {
            $count += 1;
            $sum   += $s->completePercent;
        }

        $todo  = new Todo_Models_Todo();
        $todos = $todo->fetchAll($db->quoteInto('project_id = ?', $this->id));

        foreach ($todos as $t) {
            switch ($t->currentStatus) {
            case Todo_Models_Todo::STATUS_WAITING:  // Don't count these
                break;
            case Todo_Models_Todo::STATUS_ENDED:    // Assume 100%
                $count +=   1;
                $sum   += 100;
                break;
            default: // Assume 0% (accepted, working, stopped)
                $count += 1;
                break;
            }
        }

        $completion = ($count > 0) ? ($sum / $count) : 0;
        $cache->save($completion, $id);
        return $completion;
    }

    /**
     * Calculates the cache id for getCumulativeCompletePercent of the project
     * with the given id.
     *
     * @param int id The id of the project. If null, use the current project.
     *
     * @return string The cache id.
     */
    protected function getCumulativeCompletePercentCacheId($projectId = null)
    {
        if (is_null($projectId)) {
            $projectId = $this->id;
        }

        return 'Project_Models_Project__getCumulativeCompletePercent__'
                . $projectId;
    }

    /**
     * Delete the CumulativeCompletePercent caches for this project and its
     * parent project.
     */
    public function deleteCumulativeCompletePercentCache()
    {
        $cache = Phprojekt::getInstance()->getCache();
        $cache->remove($this->getCumulativeCompletePercentCacheId());
        if (!is_null($this->projectId)) {
            $cache->remove(
                $this->getCumulativeCompletePercentCacheId($this->projectId)
            );
        }
    }

    /**
     * Returns the tree node for this project.
     *
     * The node allows navigating to subprojects and other operations on the project tree.
     *
     * @return Phprojekt_Tree_Node_Database A Tree node belonging to this project.
     */
    public function getTree()
    {
        $tree = new Phprojekt_Tree_Node_Database($this);
        $tree->setup();
        return $tree;
    }
}
