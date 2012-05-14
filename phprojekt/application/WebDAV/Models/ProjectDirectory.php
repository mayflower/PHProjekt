<?php
/**
 * WebDAV collection model.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage WebDAV
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * WebDAV collection model.
 *
 * A directory in the webdav structure. Maps to a project.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage WebDAV
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
class WebDAV_Models_ProjectDirectory extends Sabre_DAV_Directory
{
    protected $_project;

    protected $_directory;

    protected $_subproject;

    /**
     * Constructor
     *
     * @param Project_Models_Project $project The project that this object represents.
     */
    public function __construct(Project_Models_Project $project)
    {
        $this->_project   = $project;
        $path             = Phprojekt::getInstance()->getConfig()->webdavPath . 'public/';
        $path            .= str_replace('/', '_', $this->_project->path);
        if (!is_dir($path)) {
            mkdir($path, 0700);
        }

        $this->_directory = new Sabre_DAV_FS_Directory($path);

        $this->_subprojects = $project->getTree()->getChildren();
    }

    /**
     * Retrieves the child node with this specific name.
     *
     * @param string $name The name of the child node to get.
     */
      public function getChild($name)
      {
          foreach ($this->_subprojects as $sub) {
              if ($sub->title == $name) {
                  return new WebDAV_Models_ProjectDirectory($sub->getActiveRecord());
              }
          }
          return $this->_directory->getChild($name);
      }

    /**
     * Checks if a child with the given name exists.
     *
     * @param string $name The name of the child.
     */
    public function childExists($name)
    {
        foreach ($this->_subprojects as $sub) {
            if ($sub->title == $name) {
                return true;
            }
        }
        return $this->_directory->childExists($name);
    }

    public function createFile($name, $data = NULL)
    {
        foreach ($this->_subprojects as $sub) {
            if ($sub->title == $name) {
                throw new Sabre_DAV_Exception_Conflict(
                    'Cannot create file because a subproject with the same name already exists'
                );
            }
        }

        $this->_directory->createFile($name, $data);
    }

    public function createDirectory($name)
    {
        foreach ($this->_subprojects as $sub) {
            if ($sub->title == $name) {
                throw new Sabre_DAV_Exception_Conflict(
                    'Cannot create directory because a subproject with the same name already exists'
                );
            }
        }

        $this->_directory->createDirectory($name);
    }

    public function getName()
    {
        return $this->_project->title;
    }

    public function getChildren()
    {
        $ret = $this->_directory->getChildren();
        foreach ($this->_subprojects as $sub) {
            $ret[] = new WebDAV_Models_ProjectDirectory($sub->getActiveRecord());
        }
        return $ret;
    }

}
