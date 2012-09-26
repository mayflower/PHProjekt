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
 * A directory containing all subprojects for a specific project.
 */
class WebDAV_Models_SubprojectsDirectory extends Sabre_DAV_Collection
{
    protected $_subprojects;

    public function __construct(Project_Models_Project $project)
    {
        $this->_subprojects = $project->getTree()->getChildren();
    }

    public function getChild($name)
    {
        foreach ($this->_subprojects as $sub) {
            if ($sub->title == $name) {
                return new WebDAV_Models_ProjectDirectory($sub->getActiveRecord());
            }
        }

        throw new Sabre_DAV_Exception_NotFound('Directory not found: ' . $name);
    }

    public function childExists($name)
    {
        foreach ($this->_subprojects as $sub) {
            if ($sub->title == $name) {
                return true;
            }
        }

        return false;
    }

    public function createFile($name, $data = NULL)
    {
        throw new Sabre_DAV_Exception_NotImplemented('Files can only be created in Filemanager directories');
    }

    public function createDirectory($name)
    {
        throw new Sabre_DAV_Exception_NotImplemented(
            'Directories can only be created in the "Filemanagers" subdirectories of projects'
        );
    }

    public function getName()
    {
        return WebDAV_Constants::SUBPROJECTS_NAME;
    }

    public function getChildren()
    {
        $children = array();
        foreach ($this->_subprojects as $sub) {
            $children[] = new WebDAV_Models_ProjectDirectory($sub->getActiveRecord());
        }

        return $children;
    }
}
