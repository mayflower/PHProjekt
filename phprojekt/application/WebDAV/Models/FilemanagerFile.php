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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.4
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * WebDAV collection model.
 *
 * A file backed by a filemanager entry.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage WebDAV
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.4
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
class WebDAV_Models_FilemanagerFile extends Sabre_DAV_FS_File
{
    protected $_name;
    protected $_hash;
    protected $_filemanager;

    public function __construct($name, $hash, $filemanager)
    {
        $this->_name        = $name;
        $this->_hash        = $hash;
        $this->_filemanager = $filemanager;

        parent::__construct(Phprojekt::getInstance()->getConfig()->uploadPath . '/' . $hash);
    }

    public function getName()
    {
        return $this->_name;
    }

    public function delete()
    {
        $filestring = $this->_filemanager->files;
        $filestring = str_replace($this->_hash . '|' . $this->_name, '', $filestring);
        $filestring = str_replace('||||', '||', $filestring);

        $this->_filemanager->files = $filestring;
        $this->_filemanager->save();
    }

    public function setName($name)
    {
        $this->_filemanager->files = str_replace(
            $this->_hash . '|' . $this->_name,
            $this->_hash . '|' . $name,
            $this->_filemanager->files
        );
        $this->_filemanager->save();

    }
}
