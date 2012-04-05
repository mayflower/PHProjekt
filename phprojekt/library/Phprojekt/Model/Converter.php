<?php
/**
 * Helper class to convert models into arrays for Serialization.
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
 * @package    Phprojekt
 * @subpackage Model
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Helper class to convert models into arrays for Serialization.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Model
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
class Phprojekt_Model_Converter
{
    public static function convertModel(Phprojekt_Model_Interface $model)
    {
        return self::_convertModel(
            $model,
            $model->getInformation()->getFieldDefinition()
        );
    }

    public static function convertModels(array $models)
    {
        if (empty($models)) {
            return array();
        }

        $fields = $models[0]->getInformation()->getFieldDefinition();
        $ret    = array();
        foreach ($models as $m) {
            $ret[] = self::_convertModel($m, $fields);
        }
        return $ret;
    }

    private static function _convertModel(Phprojekt_Model_Interface $model, array $fields)
    {
        $ret = array();
        foreach ($fields as $f) {
            $key = $f['key'];
            $ret[$key] = $model->$key;
        }
        $ret['id'] = $model->id;
        return $ret;
    }
}
