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
        $id      = (int) $this->getRequest()->getParam('id');
        $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');

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

        echo Phprojekt_Converter_Json::convert($record, Phprojekt_ModelInformation_Default::ORDERING_FORM);
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
            // Fills automatically the author and date of creation
            $this->getRequest()->setParam('author', Phprojekt_Auth::getUserId());
            $this->getRequest()->setParam('date', date("Y-m-d"));
        } else {
            // Existing item
            $model   = $this->getModelObject()->find($id);
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);

            // The author comes as a STRING but must be saved as an INT (and it doesn't change since the item creation)
            $this->getRequest()->setParam('author', $model->author);

            if ($this->getRequest()->getParam('status') != Helpdesk_Models_Helpdesk::STATUS_SOLVED) {
                // Status != 'Solved' - The solver should be null (the solved date can't be deleted, but should be)
                $this->getRequest()->setParam('solvedBy', '');
            } else {
                // Status 'Solved' - If it has just been changed to this state, save user and date
                if ($model->status != Helpdesk_Models_Helpdesk::STATUS_SOLVED) {
                    $this->getRequest()->setParam('solvedBy', Phprojekt_Auth::getUserId());
                    $this->getRequest()->setParam('solvedDate', date("Y-m-d"));
                } else {
                    // The solver comes as a STRING but must be saved as an INT (and the Id doesn't change)
                    $this->getRequest()->setParam('solvedBy', $model->solvedBy);
                }
            }
        }

        // Add rights to the Assigned user, if any
        $assignedUser = $this->getRequest()->getParam('assigned');
        if ($assignedUser != 0) {
            if (empty($id)) {
                // New item
                $this->addParamsRight($this->getRequest(), 'checkReadAccess', $assignedUser);
                $this->addParamsRight($this->getRequest(), 'checkWriteAccess', $assignedUser);
                $this->addParamsRight($this->getRequest(), 'checkDownloadAccess', $assignedUser);
            } else {
                // Existing item
                // Has the logged user access to the 'Access' tab?
                if ($this->getRequest()->getParam('dataAcess') == NULL) {
                    // No
                    // The rights will be added to the Request Params, but also it needs to be added the
                    // already existing rights for users on this item. Case else, the saving routine deletes all
                    // other rights that the ones added for the assigned user (it happens d

                    // 1 - Add already existing rights of the item
                    $currentRights        = $model->getRights();
                    $params               = $this->getRequest()->getParams();
                    $params['dataAccess'] = Array();
                    $rightsType           = Array('access', 'read', 'write', 'create', 'copy', 'delete', 'download',
                                                  'admin');
                    foreach ($currentRights as $userRights) {
                        $userId                         = $userRights['userId'];
                        $params['dataAccess'][$userId] = $userId;
                        foreach ($rightsType as $rightName) {
                            if (array_key_exists($rightName, $userRights)) {
                                if ($userRights[$rightName] == 1) {
                                    $rightCompleteName = 'check' . ucfirst($rightName) . 'Access';
                                    if (!array_key_exists($rightCompleteName, $params)) {
                                        $params[$rightCompleteName] = Array();
                                    }
                                    $params[$rightCompleteName][$userId] = 1;
                                }
                            }
                        }
                    }

                    // 2 - Add the assigned user basic rights to $params
                    $params = $this->addParamsRight($params, 'checkReadAccess', $assignedUser);
                    $params = $this->addParamsRight($params, 'checkWriteAccess', $assignedUser);
                    $params = $this->addParamsRight($params, 'checkDownloadAccess', $assignedUser);
                } else {
                    // Yes
                    $this->addParamsRight($this->getRequest(), 'checkReadAccess', $assignedUser);
                    $this->addParamsRight($this->getRequest(), 'checkWriteAccess', $assignedUser);
                    $this->addParamsRight($this->getRequest(), 'checkDownloadAccess', $assignedUser);
                }
            }
        }

        // Let's save
        if (!isset($params)) {
            Default_Helpers_Save::save($model, $this->getRequest()->getParams());
        } else {
            Default_Helpers_Save::save($model, $params);
        }

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
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
            $model = $this->getModelObject()->find((int) $id);
            // Has the status been modified?
            if (array_key_exists('status', $params)) {
                if ($params['status'] != Helpdesk_Models_Helpdesk::STATUS_SOLVED) {
                    // Status != 'Solved' - The solver should be null. The solved date can't be deleted, but should be.
                    $params['solvedBy'] = "";
                 } else {
                    // Status 'Solved' - If it has just been changed to this state, save user and date
                    if ($model->status != Helpdesk_Models_Helpdesk::STATUS_SOLVED) {
                         $params['solvedBy']   = Phprojekt_Auth::getUserId();
                         $params['solvedDate'] = date("Y-m-d");
                     }
                 }
            }

            // Add rights to the Assigned user, if any
            if (array_key_exists('assigned', $params)) {
                $assignedUser = $params['assigned'];
                if ($assignedUser != 0) {
                    // The rights will be added to the $params var, but also it needs to be added to $params, the
                    // already existing rights for users on this item. Case else, the saving routine deletes all
                    // other rights that the ones added for the assigned user.

                    // 1 - Add already existing rights of the item, to $params
                    $currentRights        = $model->getRights();
                    $params['dataAccess'] = Array();
                    $rightsType           = Array('access', 'read', 'write', 'create', 'copy', 'delete', 'download',
                                                  'admin');
                    foreach ($currentRights as $userRights) {
                        $userId                        = $userRights['userId'];
                        $params['dataAccess'][$userId] = $userId;
                        foreach ($rightsType as $rightName) {
                            if (array_key_exists($rightName, $userRights)) {
                                if ($userRights[$rightName] == 1) {
                                    $rightCompleteName = 'check' . ucfirst($rightName) . 'Access';
                                    if (!array_key_exists($rightCompleteName, $params)) {
                                        $params[$rightCompleteName] = Array();
                                    }
                                    $params[$rightCompleteName][$userId] = 1;
                                }
                            }
                        }
                    }

                    // 2 - Add the assigned user basic rights to $params
                    $params = $this->addParamsRight($params, 'checkReadAccess', $assignedUser);
                    $params = $this->addParamsRight($params, 'checkWriteAccess', $assignedUser);
                    $params = $this->addParamsRight($params, 'checkDownloadAccess', $assignedUser);
                }
            }
            Default_Helpers_Save::save($model, $params);
            $showId[] = $id;
        }

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => implode(',', $showId));

        echo Phprojekt_Converter_Json::convert($return);
    }
}
