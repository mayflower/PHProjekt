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
 * A file backed by a filemanager entry.
 */
class WebDAV_Models_FilemanagerFile extends Sabre_DAV_FS_File
{
    protected $_name;
    protected $_hash;
    protected $_filemanager;

    /**
     * Constructor
     *
     * @param string $name The name of the file to represent
     * @param string $hash The md5 hash of the file to represent
     * @param Filemanager_Models_Filemanager
     *               $filemanager The filemanager object that the file is a part of.
     */
    public function __construct($name, $hash, $filemanager)
    {
        $this->_name        = $name;
        $this->_hash        = $hash;
        $this->_filemanager = $filemanager;

        parent::__construct(Phprojekt::getInstance()->getConfig()->uploadPath . '/' . $hash);
    }

    /**
     * Returns the name of this file.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Deletes this file.
     */
    public function delete()
    {
        $hashesToNames = $this->_getHashesToNames();
        unset ($hashesToNames[$this->_hash]);
        $this->_setHashesToNames($hashesToNames);
    }

    /**
     * Renames this file.
     */
    public function setName($name)
    {
        $hashesToNames = $this->_getHashesToNames();
        $hashesToNames[$this->_hash] = $name;
        $this->_setHashesToNames($hashesToNames);

        $this->_name = $name;
    }

    /**
     * Returns the filemanager's files as an array with the hashes as keys and the names as values.
     */
    private function _getHashesToNames()
    {
        return self::_filesStringToHashNameMap($this->_filemanager->files);
    }

    /**
     * Sets the filemanager's files to the given value.
     *
     * The structure is assumed to be like the one returned by _getHashesToNames.
     */
    private function _setHashesToNames($hashesToNamesMap)
    {
        $this->_filemanager->files = self::_hashNameMapToFilesString($hashesToNamesMap);
        $this->_filemanager->save();
    }

    /**
     * Converts our serialization format to a array of hashes to names.
     */
    private static function _filesStringToHashNameMap($string)
    {
        $ret = array();
        foreach (explode('||', $string) as $entry) {
            list($hash, $name) = explode('|', $entry, 2);
            $ret[$hash] = $name;
        }
        return $ret;
    }

    /**
     * Converts an array of hashes to names to our serialization format.
     */
    private static function _hashNameMapToFilesString($hashNames)
    {
        $entries = array();
        foreach ($hashNames as $hash => $name) {
            $entries[] = $hash . '|' . $name;
        }
        return implode('||', $entries);
    }
}
