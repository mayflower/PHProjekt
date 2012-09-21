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
 * Convert a field type into a readable format for users.
 */
class Phprojekt_Converter_Text
{
    /**
     * Convert a value of the field in a readable format for users.
     *
     * @param Phprojekt_Model_Interface $model The record model.
     * @param array                     $field Array with the field data.
     *
     * @return string User readable value.
     */
    public static function convert($model, $field)
    {
        $value = "";
        switch ($field['type']) {
            case 'selectbox':
            case 'multipleselectbox':
                // Search the value
                foreach ($field['range'] as $range) {
                    if ($range['id'] == $model->$field['key']) {
                        $value = $range['name'];
                    }
                }
                break;
            case 'percentage':
                $value = number_format(doubleval($model->$field['key']), 2);
                break;
            case 'upload':
                $i     = 0;
                $files = explode('||', $model->$field['key']);
                foreach ($files as $file) {
                    $i++;
                    if ($i > 1) {
                        $value .= ', ';
                    }
                    $fileName = substr(strstr($file, '|'), 1);
                    $value   .= $fileName;
                }
                break;
            case 'time':
                $temp  = $model->$field['key'];
                $value = substr($temp, 0, strrpos($temp, ":"));
                break;
            case 'display':
                // Search if there is an Id value that should be translated into a descriptive String
                foreach ($field['range'] as $range) {
                    if (is_array($range)) {
                        if ($range['id'] == $model->$field['key']) {
                            $value = $range['name'];
                            break 2;
                        }
                    }
                }
                if ($value == '') {
                    $value = $model->$field['key'];
                }
                break;
            case 'textarea':
                $value = str_replace("\n", " ", strip_tags($model->$field['key']));
                break;
            case 'text':
            case 'date':
            default:
                $value = $model->$field['key'];
                break;
        }

        return $value;
    }
}
