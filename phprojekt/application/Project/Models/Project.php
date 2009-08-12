<?php
/**
 * Project model class
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Project model class
 *
 * The class of each model return the data for show
 * on the list and the form view
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_Models_Project extends Phprojekt_Item_Abstract
{
    public $hasMany = array('modulePermissions' => array('classname' => 'Project_Models_ProjectModulePermissions'));

    /**
     * Validate function for the projectId field
     *
     * @param integer $value Value of the projectId to check
     *
     * @return string Error msg
     */
    public function validateProjectId($value)
    {
        if (null !== $this->id && $this->id > 0) {
            $node = Phprojekt_Loader::getModel('Project', 'Project')->find($this->id);
            $tree = new Phprojekt_Tree_Node_Database($node, $this->id);
            if ($tree->setup()->getActiveRecord()->id == $value) {
                return 'The project can not be saved under itself';
            } else if ($this->_isInTheProject($value, $tree)) {
                return 'The project can not be saved under its children';
            }
        }

        return null;
    }

    /**
     * Check if the projectId is under the same project or a subproject of him
     *
     * @param integer                      $projectId The projectId to check
     * @param Phprojekt_Tree_Node_Database $node      The node of the current project
     *
     * @return boolean
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
     * Save the allow modules for one projectId
     * First delete all the older relations
     *
     * @param array $rights    Array with the modules to save
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
     * Add a new relation module-project without delete any entry
     * Used for add modules to the root project
     *
     * @param int $moduleId  The Module Id to add
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
     * Delete the tree cache after save
     *
     * @return boolean
     */
    public function parentSave()
    {
        $result = parent::parentSave();
        Phprojekt_Tree_Node_Database::deleteCache();

        return $result;
    }

    /**
     * Delete the tree cache after save
     *
     * @return boolean
     */
    public function save()
    {
        $result = parent::save();
        Phprojekt_Tree_Node_Database::deleteCache();

        return $result;
    }

    /**
     * Delete the tree cache after delete
     *
     * @return boolean
     */
    public function delete()
    {
        parent::delete();
        Phprojekt_Tree_Node_Database::deleteCache();
    }
}
