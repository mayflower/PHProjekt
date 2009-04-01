<?php
/**
 * Helpdesk Module Controller for PHProjekt 6.0
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
 * @version    $Id$
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Helpdesk Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Helpdesk_IndexController extends IndexController
{
    /**
     * Returns the detail for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            // New item - Adds the author and the date of creation
            $record         = $this->getModelObject();
            $record->author = Phprojekt_Auth::getUserId();
            $record->date   = date("Y-m-d");
        } else {
            $record   = $this->getModelObject()->find($id);
            if ($record->solvedBy == null) {
                // This is because of the solved date being unable to be deleted from the item
                $record->solvedDate = '';
            }
        }

        Phprojekt_Converter_Json::echoConvert($record, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Saves the current item
     * Save if you are add one or edit one.
     * Use the model module for get the data
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            // New item
            $model   = $this->getModelObject();
            $message = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);
            $newItem = true;
        } else {
            // Existing item
            $model   = $this->getModelObject()->find($id);
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
            $newItem = false;
        }
        $params = $this->_setParams($this->getRequest()->getParams(), $model, $newItem);
        Default_Helpers_Save::save($model, $params);

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => $model->id);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Save some fields for many items
     * Only edit existing items
     * Use the model module to get the data
     *
     * If there is an error, the saving will return a Phprojekt_PublishedException
     * If not, it returns is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam string data Array with fields and values
     *
     * @return void
     */
    public function jsonSaveMultipleAction()
    {
        $data    = (array) $this->getRequest()->getParam('data');
        $message = Phprojekt::getInstance()->translate(self::EDIT_MULTIPLE_TRUE_TEXT);
        $showId  = array();

        // For every modified row
        foreach ($data as $id => $params) {
            $model  = $this->getModelObject()->find((int) $id);
            $params = $this->_setParams($params, $model);
            Default_Helpers_Save::save($model, $params);
            $showId[] = $id;
        }

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => implode(',', $showId));

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Set some values deppend on the params
     *
     * Set the author, solvedBy, solvedDate
     * Also set the rights for each user (owner, assigned and the normal access tab)
     *
     * @param array                    $params The post values
     * @param Helpdesk_Models_Helpdesk $model  The current module to save
     *
     * @return array
     */
    private function _setParams($params, $model, $newItem = false)
    {
        if ($newItem) {
            $params['author'] = Phprojekt_Auth::getUserId();
            $params['date']   = date("Y-m-d");
        } else {
            // The author comes as a STRING but must be saved as an INT (and it doesn't change since the item creation)
            $params['author'] = $model->author;
        }

        if (!$newItem && isset($params['status'])) {
            if ($params['status'] != Helpdesk_Models_Helpdesk::STATUS_SOLVED) {
                // Status != 'Solved' - The solver should be null (the solved date can't be deleted, but should be)
                $params['solvedBy'] = '';
            } else {
                // Status 'Solved' - If it has just been changed to this state, save user and date
                if ($model->status != Helpdesk_Models_Helpdesk::STATUS_SOLVED) {
                    $params['solvedBy']   = Phprojekt_Auth::getUserId();
                    $params['solvedDate'] = date("Y-m-d");
                } else {
                    // The solver comes as a STRING but must be saved as an INT (and the Id doesn't change)
                    $params['solvedBy'] = $model->solvedBy;
                }
            }
        }

        // Add rights to the Assigned user, if any
        $assignedUser = (isset($params['assigned'])) ? $params['assigned'] : 0;

        // The assgined user is setted
        if ($assignedUser != 0) {
            // Is an Existing item
            // The logged user don't have access to the 'Access' tab
            if (!$newItem && (!isset($params['dataAccess']))) {
                // The rights will be added to the Request Params, but also it needs to be added the
                // already existing rights for users on this item. Case else, the saving routine deletes all
                // other rights that the ones added for the assigned user

                // Add already existing rights of the item,
                // except for the new assignedUser
                // except for the old assignedUser
                $currentRights = $model->getRights();
                $rightsType    = array('access', 'read', 'write', 'create', 'copy', 'delete', 'download', 'admin');
                foreach ($currentRights as $userRights) {
                    $userId = $userRights['userId'];
                    if ($userId != $assignedUser && $userId != $model->assigned) {
                        $params = Default_Helpers_Right::addUser($params, $userId);
                        foreach ($rightsType as $rightName) {
                            if (array_key_exists($rightName, $userRights)) {
                                if ($userRights[$rightName] == 1) {
                                    $rightCompleteName = 'check' . ucfirst($rightName) . 'Access';
                                    if (!array_key_exists($rightCompleteName, $params)) {
                                        $params[$rightCompleteName] = array();
                                    }
                                    $params[$rightCompleteName][$userId] = 1;
                                }
                            }
                        }
                    }
                }
            }

            // Add the assigned user basic write rights to $params
            // If is the owner, set full access
            if ($model->ownerId == $assignedUser) {
                $params = Default_Helpers_Right::allowAll($params, $model->ownerId);
            } else {
                $params = Default_Helpers_Right::allowReadWriteDelete($params, $assignedUser);
            }
        }

        return $params;
    }
}
