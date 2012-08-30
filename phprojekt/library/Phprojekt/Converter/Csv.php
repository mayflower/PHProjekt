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
 * Convert a model into a CSV structure.
 */
class Phprojekt_Converter_Csv
{
    /**
     * The function check the parameters type
     * and choose which convert function must use.
     *
     * @param mix $param1 Array.
     * @param mix $param2 ORDERING_LIST for items / fieldInformation for tags.
     *
     * @return string Data in CSV format.
     */
    public static function convert($param1, $param2 = null)
    {
        // Convert Models
        if (is_array($param1) && isset($param1[0]) && $param1[0] instanceof Phprojekt_Model_Interface) {
            if (null === $param2) {
                $param2 = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT;
            }
            return self::_convertModel($param1, $param2);
        // Convert directly the output data
        } else {
            return self::_convertArray($param1);
        }
    }

    /**
     * Converts according to convert() and echos the result with the corresponding headers.
     *
     * @see convert()
     *
     * @param mix $param1 - Array.
     * @param mix $param2 - ORDERING_LIST for items / fieldInformation for tags.
     *
     * @return void
     */
    public static function echoConvert($param1, $param2 = null)
    {
        $outputString = self::convert($param1, $param2);

        if (!headers_sent()) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header("Cache-Control: must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Length: ' . strlen($outputString));
            header("Content-Disposition: attachment; filename=\"export.csv\"");
            header("Content-type: application/octet-stream; charset=utf-8");
        }

        $outputString = mb_convert_encoding(
            $outputString,
            'UTF-8',
            mb_detect_encoding($outputString)
        );

        echo $outputString;
    }

    /**
     * Convert a model or a model information into a CSV file.
     *
     * @param Phprojekt_Interface_Model|array $models The model to convert.
     * @param integer                         $order  A Phprojekt_ModelInformation_Default::ORDERING_*
     *                                                const that defines the ordering for the convert.
     * @param boolean                         $exportHeader Determine if the header needs to be exported.
     *
     * @return string Data in CSV format.
     */
    private static function _convertModel($models,
                                          $order = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT,
                                          $exportHeader = true)
    {
        $datas = array();
        $data  = array();
        $model = current((array) $models);

        $information = $model->getInformation($order);

        // Csv file header
        if ($exportHeader) {
            $metadata = $information->getFieldDefinition($order);
            if (is_array($metadata)) {
                foreach ($metadata as $oneCol) {
                    $data[] = $oneCol['label'];
                }
            }
            $datas[] = $data;
        }

        foreach ($models as $cmodel) {
            $data = array();
            foreach ($information->getFieldDefinition($order) as $field) {
                $key    = $field['key'];
                $value  = Phprojekt_Converter_Text::convert($cmodel, $field);
                $data[] = $value;
            }
            $datas[] = $data;
        }

        return self::_writeFile($datas);
    }

    /**
     * Convert and array data into a CSV file.
     *
     * @param array   $data         Data to convert.
     * @param boolean $exportHeader Determine if the header needs to be exported.
     *
     * @return string Data in CSV format.
     */
    private static function _convertArray($data)
    {
        return self::_writeFile($data);
    }

    /**
     * Writes header and content of the CSV file based on data array.
     *
     * @param array $data Data to write on file.
     *
     * @return string Data in CSV format.
     */
    private static function _writeFile($data)
    {
        $outputString = "";

        if (is_array($data)) {
            foreach ($data as $rowNbr => $oneRow) {
                if ($rowNbr > 0) {
                    $outputString .= "\"\n";
                }
                if (is_array($oneRow)) {
                    foreach ($oneRow as $colNbr => $oneData) {
                        if ($colNbr > 0) {
                            $outputString .= '","';
                        } else {
                            $outputString .= '"';
                        }
                        $outputString .= str_replace('"', '""', $oneData);
                    }
                }
            }
            $outputString .= "\"\n";
        }

        return $outputString;
    }
}
