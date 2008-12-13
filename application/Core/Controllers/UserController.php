<?php
/**
 * User Module Controller for PHProjekt 6.0
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
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * User Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_UserController extends Core_IndexController
{
    /**
     * Return a list of all the users except the current user
     *
     * @return void
     */
    public function jsonGetUsersAction()
    {
        $db      = Zend_Registry::get('db');
        $where   = array();
        $where   = "status = 'A' AND id != ". (int)Phprojekt_Auth::getUserId();
        $user    = new Phprojekt_User_User($db);
        $records = $user->fetchAll($where);

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the detail for a Settings in JSON.
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $user = new Phprojekt_User_User();
        $id       = $this->getRequest()->getParam("id");

        $user->find($id);
        $data = array();

        $data['id']        = $user->id;
        $data['username']  = (empty($user->username))?"":$user->username;
        $data['firstname'] = (empty($user->firstname))?"":$user->firstname;
        $data['lastname']  = (empty($user->lastname))?"":$user->lastname;
        $data['status']    = (empty($user->status))?"":$user->status;

        $setting = new Setting_Models_Setting();
        $setting->setModule('User');

        $tmp = $setting->getList(0, $setting->getModel()->getFieldDefinition());

        foreach ($tmp as $values) {
            foreach ($values as $key => $value) {
                if ($key != 'id') {
                    if (!empty($data["id"])) {
                        $data[$key] = $value;
                    } else {
                        $data[$key] = "";
                    }
                }
            }
        }

        $records = array($data);

        $metadata = $user->getInformation(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $metadata = $metadata->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        $data     = array("metadata"=> $metadata,
                          "data"    => $records,
                          "numRows" => count($records));

        echo Phprojekt_Converter_Json::convert($data);
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
        $translate = Zend_Registry::get('translate');
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $model   = $this->getModelObject();
            $message = $translate->translate(self::ADD_TRUE_TEXT);
        } else {
            $model   = $this->getModelObject()->find($id);
            $message = $translate->translate(self::EDIT_TRUE_TEXT);
        }

        Default_Helpers_Save::save($model, $this->getRequest()->getParams());

        // Saving the settings
        $setting = new Setting_Models_Setting();
        $setting->setModule('User');
        $setting->setSettings($this->getRequest()->getParams(), $id);

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
    }
}
