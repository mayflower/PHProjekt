<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Filemanager model class.
 */
class Filemanager_Models_Filemanager extends Phprojekt_Item_Abstract
{
    public function recordValidate()
    {
        return parent::recordValidate() && $this->_validateFilenamesAreUnique();
    }

    private function _validateFilenamesAreUnique()
    {
        $fileEntries = explode('||', $this->files);

        $nameFound = array();
        foreach ($fileEntries as $entry) {
            list($hash, $name) = explode('|', $entry, 2);
            if (array_key_exists($name, $nameFound)) {
                $this->_validate->error->addError(
                    array(
                        'field' => 'files',
                        'label' => Phprojekt::getInstance()->translate('Upload'),
                        'message' => Phprojekt::getInstance()->translate('Filenames must be unique, ')
                                     . "\"$name\""
                                     . Phprojekt::getInstance()->translate(' appears multiple times.')
                    )
                );
                return false;
            } else {
                $nameFound[$name] = true;
            }
        }

        return true;
    }
}
