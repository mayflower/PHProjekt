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
 * Configurations for general stuff, applied to the system.
 */
class Core_Models_General_Configuration extends Phprojekt_ModelInformation_Default
{
    /**
     * Sets a fields definitions for each field.
     *
     * @return void
     */
    public function setFields()
    {
        // companyName
        $this->fillField('companyName', 'Company Name', 'text', 1, 1, array(
            'required' => true,
            'length'   => 255));
    }

    /**
     * Validate the configurations.
     *
     * @param array $params Array with values to save.
     *
     * @return string|null Error message.
     */
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
     * Save the configurations into the table.
     *
     * @param array $params Array with values to save.
     *
     * @return void
     */
    public function setConfigurations($params)
    {
        $fields = $this->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        $configuration = new Phprojekt_Configuration();
        $configuration->setModule('General');

        foreach ($fields as $data) {
            foreach ($params as $key => $value) {
                if ($key == $data['key']) {
                    if ($key == 'companyName') {
                        // Update Root node
                        $project = new Project_Models_Project();
                        $project->find(1);
                        $project->title = $value;
                        $project->parentSave();
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
