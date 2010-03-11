<?php
/**
 * Convert a model into a json structure.
 * This is usually done by a controller to send data to the client.
 *
 * The Phprojekt_Convert_Json takes care that a apporpriate structure
 * is made from the given model.
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
 * @subpackage Converter
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * Convert a model into a json structure.
 * This is usually done by a controller to send data to the client.
 *
 * The Phprojekt_Convert_Json takes care that a apporpriate structure
 * is made from the given model.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Converter
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria PArra <soria_parra@mayflower.de>
 */
class Phprojekt_Converter_Json
{
    /**
     * Converts according to convert() and echos the result.
     *
     * @see convert()
     *
     * @param mix $param1 - Tree class / Item class / Array.
     * @param mix $param2 - ORDERING_LIST for items / fieldInformation for tags.
     *
     * @return void
     */
    public static function echoConvert($param1, $param2 = null)
    {
        if (!headers_sent()) {
            $front = Zend_Controller_Front::getInstance();
            $front->getResponse()->setHeader('Content-Type', 'application/json; charset=utf-8');
        }
        echo self::convert($param1, $param2);
    }

    /**
     * The function check the parameters type and choose which convert function must use.
     *
     * @param mix $param1 - Tree class / Item class / Array
     * @param mix $param2 - ORDERING_LIST for items / fieldInformation for tags
     *
     * @return string Data in JSON format.
     */
    public static function convert($param1, $param2 = null)
    {
        // Convert a Tree class
        if ($param1 instanceof Phprojekt_Tree_Node_Database) {
            return self::_convertTree($param1);

        // Convert Models
        } else if (is_array($param1) && isset($param1[0]) && $param1[0] instanceof Phprojekt_Model_Interface) {
            if (null === $param2) {
                $param2 = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT;
            }
            return self::_convertModel($param1, $param2);

        // Convert normal values
        } else if (is_array($param1) && !empty($param1) && null === $param2) {
            return self::_convertValue($param1);

        // Convert tags or Search
        } else if (is_array($param1) && is_array($param2) && !empty($param2)) {
            return self::_convertMetadataAndData($param1, $param2);

        // Convert Models
        } else if ($param1 instanceof Phprojekt_Model_Interface) {
            if (null === $param2) {
                $param2 = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT;
            }
            return self::_convertModel($param1, $param2);

        // Default, text values
        } else {
            return self::_convertValue($param1);
        }
    }

    /**
     * Convert a model or a model information into a json stream.
     *
     * @param Phprojekt_Interface_Model|array $models The model to convert.
     * @param integer                         $order  A Phprojekt_ModelInformation_Default::ORDERING_*
     *                                                const that defines the ordering for the convert.
     *
     * @return string Data in JSON format.
     */
    private static function _convertModel($models, $order = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        if (!is_array($models)) {
            $model = $models;
        } else {
            $model = current((array) $models);
        }

        $information     = $model->getInformation($order);
        $fieldDefinition = $information->getFieldDefinition($order);

        // We can check the returned array, but at the moment we just pass it
        $datas   = array();
        $data    = array();
        $numRows = 0;

        // We have to do this ugly convert, because Zend_Json_Encoder doesnot check
        // if a value in an array is an object
        if (!is_array($models) && $models instanceof Phprojekt_Model_Interface) {
            foreach ($fieldDefinition as $field) {
                $data['id'] = (int) $models->id;

                $key   = $field['key'];
                $value = $models->$key;
                if (is_numeric($value) && $field['integer']) {
                    $data[$key] = (int) $value;
                } else if (is_scalar($value)) {
                    $data[$key] = $value;
                } else {
                    if ($field['integer']) {
                        if (is_null($value) && !is_null($field['default'])) {
                            $data[$key] = (int) $field['default'];
                        } else {
                            $data[$key] = (int) $value;
                        }
                    } else {
                        if (is_null($value) && !is_null($field['default'])) {
                            $data[$key] = (string) $field['default'];
                        } else {
                            $data[$key] = (string) $value;
                        }
                    }
                }
            }
            $data['rights'] = $models->getRights();
            $datas[]        = $data;
        } else {
            $ids = array();
            foreach ($models as $cmodel) {
                $data['id'] = (int) $cmodel->id;
                foreach ($fieldDefinition as $field) {
                    $key   = $field['key'];
                    $value = $cmodel->$key;
                    if (is_numeric($value) && $field['integer']) {
                        $data[$key] = (int) $value;
                    } else if (is_scalar($value)) {
                        $data[$key] = $value;
                    } else {
                        if ($field['integer']) {
                            if (is_null($value) && !is_null($field['default'])) {
                                $data[$key] = (int) $field['default'];
                            } else {
                                $data[$key] = (int) $value;
                            }
                        } else {
                            if (is_null($value) && !is_null($field['default'])) {
                                $data[$key] = (string) $field['default'];
                            } else {
                                $data[$key] = (string) $value;
                            }
                        }
                    }
                }
                $ids[]          = $data['id'];
                $data['rights'] = array();
                $datas[]        = $data;
            }

            // Use the last model for get all the rights
            $rights = $cmodel->getMultipleRights($ids);
            foreach ($datas as $index => $data) {
                $datas[$index]['rights'] = $rights[$datas[$index]['id']];
                unset($rights[$datas[$index]['id']]);
            }
        }

        $data = array('metadata' => $fieldDefinition,
                      'data'     => $datas,
                      'numRows'  => (int) count($datas));

        return self::_makeJsonString($data);
    }

    /**
     * Convert a model or a model information into a json stream.
     *
     * @param Phprojekt_Interface_Model $tree Tree instance to convert.
     *
     * @return string Data in JSON format.
     */
    private static function _convertTree(Phprojekt_Tree_Node_Database $tree)
    {
        $treeNodes = array();
        $index     = 0;
        foreach ($tree as $node) {
            $references = array();
            foreach ($node->getChildren() as $child) {
                $references[] = array('_reference' => $child->id);
            }
            $treeNodes[$index] = array('name'     => $node->title,
                                       'id'       => $node->id,
                                       'parent'   => $node->projectId,
                                       'path'     => $node->path);
            if (!empty($references)) {
                $treeNodes[$index]['children'] = $references;
            }
            $index++;
        }

        $data               = array();
        $data['identifier'] = 'id';
        $data['label']      = 'name';
        $data['items']      = $treeNodes;

        return self::_makeJsonString($data);
    }

    /**
     * Just convert a normal value, and return it with the json-comment-filtered.
     *
     * @param mix $data Some value to convert.
     *
     * @return string Data in JSON format.
     */
    private static function _convertValue($data)
    {
        if (is_array($data) && empty($data)) {
            $data = array('metadata' => array());
        }
        return self::_makeJsonString($data);
    }

    /**
     * Convert the tag or search data to json-format.
     *
     * @param array $data            The data values.
     * @param array $fieldDefinition The definition of each field.
     *
     * @return string Data in JSON format.
     */
    private static function _convertMetadataAndData($data, $fieldDefinition)
    {
        $numRows = count($data);
        $data    = array('metadata' => $fieldDefinition,
                         'data'     => $data,
                         'numRows'  => (int) $numRows);

        return self::_makeJsonString($data);
    }

    /**
     * Enclose the json result in comments for security reasons, see "json-comment-filtered dojo"
     * the content-type dojo expects is: json-comment-filtered.
     *
     * @param array $data Data to convert.
     *
     * @return string Data in JSON format.
     */
    private static function _makeJsonString($data)
    {
        return '{}&&(' . Zend_Json_Encoder::encode($data) . ')';
    }
}
