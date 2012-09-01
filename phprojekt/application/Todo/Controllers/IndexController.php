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
 * Todo Module Controller.
 */
class Todo_IndexController extends IndexController
{
    /**
     * When requesting the default values, adjust the start date to the start
     * date of the project.
     */
    public function jsonDetailAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        if (!empty($id)) {
            parent::jsonDetailAction();
        } else {
            $this->setCurrentProjectId();
            $project = new Project_Models_Project();
            $project->find(Phprojekt::getCurrentProjectId());

            $record            = new Todo_Models_Todo();
            $record->startDate = $project->startDate;
            $record->endDate   = $project->endDate;

            Phprojekt_Converter_Json::echoConvert(
                $record,
                Phprojekt_ModelInformation_Default::ORDERING_FORM
            );
        }
    }
    /**
     * Sets some values depending on the parameters.
     *
     * Set the rights for each user (owner, userId and the normal access tab).
     *
     * @return array POST values with some changes.
     */
    public function setParams()
    {
        $args    = func_get_args();
        $params  = $args[0];
        $model   = $args[1];
        $newItem = (isset($args[2])) ? $args[2] : false;

        return Default_Helpers_Right::addRightsToAssignedUser('userId', $params, $model, $newItem);
    }
}
