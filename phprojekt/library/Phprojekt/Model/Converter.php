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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Helper class to convert models into arrays for Serialization.
 */
class Phprojekt_Model_Converter
{
    /**
     * Convert a single model to an array.
     *
     * @param Phprojekt_Model_Interface $model The model to convert
     *
     * @return array A php array that represents $model.
     */
    public static function convertModel(Phprojekt_Model_Interface $model)
    {
        return self::_convertModel(
            $model,
            $model->getInformation()->getFieldDefinition()
        );
    }

    /**
     * Convert a bunch of models of the same type to arrays.
     *
     * @param array of Phprojekt_Model_Interface $models The models to convert. Must all be of the same type.
     *
     * @return array of arrays Arrays that represent the models
     */
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

    /**
     * Converts the model with the given fields.
     */
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
