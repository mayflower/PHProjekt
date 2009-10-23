<?php
/**
 * General configuration model
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id:
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Configurations for general stuff, applied to the system
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_Models_General_Configuration
{
    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant
     *
     * @return array
     */
    public function getFieldDefinition()
    {
        $converted = array();

        // Company name
        $data = array();
        $data['key']      = 'companyName';
        $data['label']    = Phprojekt::getInstance()->translate('Company Name');
        $data['type']     = 'text';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('companyName');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 255;
        $data['default']  = null;

        $converted[] = $data;

        return $converted;
    }

    public function validateConfigurations($params)
    {
        $message = null;

        // Company Name
        $companyName = Cleaner::sanitize('string', $params['companyName']);
        if (empty($companyName)) {
            $message = Phprojekt::getInstance()->translate('The Company name is empty');
        }

        return $message;
    }

    /**
     * Save the configurations into the table
     *
     * @param array $params $_POST fields
     *
     * @return void
     */
    public function setConfigurations($params)
    {
        $fields = $this->getFieldDefinition();

        $configuration = Phprojekt_Loader::getModel('Administration', 'Configuration');
        $configuration->setModule('General');

        foreach ($fields as $data) {
            foreach ($params as $key => $value) {
                if ($key == $data['key']) {
                    if ($key == 'companyName') {
                        // Update Root node
                        $project = Phprojekt_Loader::getModel('Project', 'Project');
                        $project->find(1);
                        $project->title = $value;
                        $project->save();
                        Phprojekt_Tree_Node_Database::deleteCache();
                    }
                    $where  = sprintf('key_value = %s AND module_id = 0', $configuration->_db->quote($key));
                    $record = $configuration->fetchAll($where);
                    if (isset($record[0])) {
                        $record[0]->keyValue = $key;
                        $record[0]->value    = $value;
                        $record[0]->save();
                    } else {
                        $configuration->moduleId = 0;
                        $configuration->keyValue = $key;
                        $configuration->value    = $value;
                        $configuration->save();
                    }
                    break;
                }
            }
        }
    }
}
