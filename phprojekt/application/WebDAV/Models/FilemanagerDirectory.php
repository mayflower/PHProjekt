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
 * A directory in the webdav structure. Maps to a filemanager item.
 */
class WebDAV_Models_FilemanagerDirectory extends Sabre_DAV_Collection
{
    protected $_filemanager;

    protected $_files = array();

    /**
     * Constructor
     *
     * @param Project_Models_Project $filemanager The filemanager that this object represents.
     */
    public function __construct(Filemanager_Models_Filemanager $filemanager)
    {
        $this->_filemanager = $filemanager;

        if ($filemanager->files) {
            foreach (explode('||', $filemanager->files) as $entry) {
                list($hash, $name) = explode('|', $entry, 2);
                $this->_files[$name] = $hash;
            }
        }
    }

    /**
     * Retrieves the child node with this specific name.
     *
     * @param string $name The name of the child node to get.
     */
    public function getChild($name)
    {
        if (array_key_exists($name, $this->_files)) {
            return new WebDAV_Models_FilemanagerFile($name, $this->_files[$name], $this->_filemanager);
        } else {
            throw new Sabre_DAV_Exception_NotFound('File not found: ' . $name);
        }
    }

    /**
     * Checks if a child with the given name exists.
     *
     * @param string $name The name of the child.
     */
    public function childExists($name)
    {
        return array_key_exists($name, $this->_files);
    }

    /**
     * Called when the user creates a new file in this directory.
     */
    public function createFile($name, $data = NULL)
    {
        $hash = md5(mt_rand() . time());
        $newPath = Phprojekt::getInstance()->getConfig()->uploadPath . '/' . $hash;
        Default_Helpers_Upload::addFilesToUnusedFileList(array(array('md5' => $hash)));
        if (false === file_put_contents($newPath, $data)) {
            throw new Phprojekt_Exception_IOException('saving failed');
        }

        if (!empty($this->_filemanager->files)) {
            $this->_filemanager->files .= '||';
        }
        $this->_filemanager->files .= $hash . '|' . $name;

        $this->_filemanager->save();
        Default_Helpers_Upload::removeFilesFromUnusedFileList(array(array('md5' => $hash)));
    }

    /**
     * Called when the user creates a new subdirectory of this directory.
     */
    public function createDirectory($name)
    {
        throw new Sabre_DAV_Exception_NotImplemented(
            'Directories can only be created in the "Filemanagers" subdirectories of projects'
        );
    }

    /**
     * Returns the name of this directory.
     */
    public function getName()
    {
        return $this->_filemanager->title;
    }

    /**
     * Returns all elements in this directory.
     */
    public function getChildren()
    {
        $children = array();
        foreach ($this->_files as $name => $hash) {
            $children[] = new WebDAV_Models_FilemanagerFile($name, $hash, $this->_filemanager);
        }
        return $children;
    }

    /**
     * Retnames this directory.
     */
    public function setName($name)
    {
        $this->_filemanager->title = $name;
        $this->_filemanager->save();
    }

    /**
     * Deletes this directory.
     */
    public function delete()
    {
        $this->_filemanager->delete();
    }
}
