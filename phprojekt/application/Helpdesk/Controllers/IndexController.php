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
 * Helpdesk Module Controller.
 */
class Helpdesk_IndexController extends IndexController
{
    /**
     * Returns the detail (fields and data) of one item from the model.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of one item.
     *  - The number of rows.
     *
     * If the request parameter "id" is null or 0, the data will be all values of a "new item",
     * if the "id" is an existing item, the data will be all the values of the item.
     *
     * For new items, the author and date values are set.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_FORM for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b> id of the item to consult.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $this->setCurrentProjectId();

        if (empty($id)) {
            // New item - Adds the author and the date of creation
            $record         = $this->getModelObject();
            $record->author = Phprojekt_Auth::getUserId();
            $record->date   = date("Y-m-d");
        } else {
            $record = $this->getModelObject()->find($id);
            if ($record->solvedBy == 0) {
                // This is because of the solved date being unable to be deleted from the item
                $record->solvedDate = '';
            }
        }

        Phprojekt_Converter_Json::echoConvert($record, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Set some values deppend on the params.
     *
     * Set the author, solvedBy, solvedDate.
     * Also set the rights for each user (owner, assigned and the normal access tab).
     *
     * @return array POST values with some changes.
     */
    public function setParams()
    {
        $args    = func_get_args();
        $params  = $args[0];
        $model   = $args[1];
        $newItem = (isset($args[2])) ? $args[2] : false;

        if ($newItem) {
            $params['author'] = (int) Phprojekt_Auth::getUserId();
            $params['date']   = date("Y-m-d");
            if ($params['status'] == Helpdesk_Models_Helpdesk::STATUS_SOLVED) {
                $params['solvedBy']   = (int) Phprojekt_Auth::getUserId();
                $params['solvedDate'] = date("Y-m-d");
            }
        } else {
            // The author comes as a STRING but must be saved as an INT (and it doesn't change since the item creation)
            $params['author'] = (int) $model->author;
        }

        if (!$newItem && isset($params['status'])) {
            if ($params['status'] != Helpdesk_Models_Helpdesk::STATUS_SOLVED) {
                // Status != 'Solved' - The solver should be null (the solved date can't be deleted, but should be)
                $params['solvedBy'] = 0;
            } else {
                // Status 'Solved' - If it has just been changed to this state, save user and date
                if ($model->status != Helpdesk_Models_Helpdesk::STATUS_SOLVED) {
                    $params['solvedBy']   = (int) Phprojekt_Auth::getUserId();
                    $params['solvedDate'] = date("Y-m-d");
                } else {
                    // The solver comes as a STRING but must be saved as an INT (and the Id doesn't change)
                    $params['solvedBy'] = (int) $model->solvedBy;
                }
            }
        }

        return Default_Helpers_Right::addRightsToAssignedUser('assigned', $params, $model, $newItem);
    }
}
