<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details
 *
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * WebDAV collection model.
 *
 * A directory containing all filemanager items for a specific project.
 */
class WebDAV_Models_FilemanagersDirectory extends Sabre_DAV_Collection
{
    protected $_project;
    protected $_filemanagers;

    /**
     * Constructor
     *
     * @param Project_Models_Project $project The project that this object represents.
     */
    public function __construct(Project_Models_Project $project)
    {
        $this->_project = $project;

        $filemanager = new Filemanager_Models_Filemanager();
        $this->_filemanagers = $filemanager->fetchAll('project_id = ' . (int) $project->id);
    }

    /**
     * Retrieves the child node with this specific name.
     *
     * @param string $name The name of the child node to get.
     */
    public function getChild($name)
    {
        foreach ($this->_filemanagers as $filemanager) {
            if ($filemanager->title == $name) {
                return new WebDAV_Models_FilemanagerDirectory($filemanager);
            }
        }

        throw new Sabre_DAV_Exception_NotFound('Directory not found: ' . $name);
    }

    /**
     * Checks if a child with the given name exists.
     *
     * @param string $name The name of the child.
     */
    public function childExists($name)
    {
        foreach ($this->_filemanagers as $filemanager) {
            if ($filemanager->title == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates a file in this directory.
     */
    public function createFile($name, $data = NULL)
    {
        throw new Sabre_DAV_Exception_NotImplemented('Files can only be created in Filemanager directories');
    }

    /**
     * Creates a subdirectory below this one.
     */
    public function createDirectory($name)
    {
        $filemanager = new Filemanager_Models_Filemanager();

        $filemanager->title = $name;
        $filemanager->projectId = $this->_project->id;
        $filemanager->files = '';
        $filemanager->ownerId = Phprojekt_Auth::getUserId();
        $filemanager->save();

        $rights = $this->_getDefaultRightsForProject($this->_project->id);
        $filemanager->saveRights($rights);
    }

    public function getName()
    {
        return WebDAV_Constants::FILEMANAGERS_NAME;
    }

    /**
     * Gets all entries in this directory.
     */
    public function getChildren()
    {
        $filemanager = new Filemanager_Models_Filemanager();
        $files       = $filemanager->fetchAll(
            PHProjekt::getInstance()->getDB()->quoteInto('project_id = ?', $this->_project->id)
        );

        $children = array();
        foreach ($files as $file) {
            $children[] = new WebDAV_Models_FilemanagerDirectory($file);
        }

        return $children;
    }

    private function _getDefaultRightsForProject($projectId)
    {
        $model  = new Project_Models_Project();
        $record = $model->find($projectId);
        $rights = $record->getUsersRights();
        foreach ($rights as $userId => $accessArray) {
            $rights[$userId] = Phprojekt_Acl::convertArrayToBitmask($accessArray);
        }
        return $rights;
    }

}
