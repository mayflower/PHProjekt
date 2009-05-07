<?php
/**
 * Minutes Module Item Controller for PHProjekt 6.0
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
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Minutes Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */
class Minutes_ItemController extends IndexController
{
    /**
     * Get the model object
     * This function must be redefined in each module
     *
     * @return Phprojekt_Model_Interface
     */
    public function getModelObject()
    {
        return Phprojekt_Loader::getModel('Minutes', 'MinutesItem');
    }
    
    /**
     * This Action currently acts as a mockup
     * 
     * @todo implement this action
     * 
     * @return void
     */
    public function indexAction()
    {
        $itemModel = Phprojekt_Loader::getModel('Minutes', 'MinutesItem');
        $itemModel->init($this->getRequest()->getParam('minutesId', null));
        
        $result = $itemModel->fetchAll();
        
        echo get_class($this) . " indexAction was here!<br><pre>";
        echo "<hr>";
        Phprojekt_Converter_Json::echoConvert($result);
        echo "</pre>";
    }
    
    /**
     * Returns list of minutes items referenced by a minutesId
     * 
     * @requestparam integer minutesId The id of the minutes this list belongs to.
     * @return void
     */
    public function jsonListAction()
    {
        $itemModel = Phprojekt_Loader::getModel('Minutes', 'MinutesItem');
        $itemModel->init($this->getRequest()->getParam('minutesId', null));
        
        $result = $itemModel->fetchAll();
        Phprojekt_Converter_Json::echoConvert($result, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the detail for a single minutes item
     *
     * @requestparam integer id         The id of the item.
     * @requestparam integer minutesId  The id of the minutes.
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $itemModel = Phprojekt_Loader::getModel('Minutes', 'MinutesItem');
        $itemModel->init((int) $this->getRequest()->getParam('minutesId', null));
        
        $id = (int) $this->getRequest()->getParam('id');
        
        if (empty($id)) {
            $record = NULL;
        } else {
            $record = $itemModel->find($id);
        }
        
        Phprojekt_Converter_Json::echoConvert($record, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Saves the current minutesitem
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id         The id of the item.
     * @requestparam integer minutesId  The id of the minutes.
     *
     * @return void
     */
    
    public function jsonSaveAction()
    {
        $minutesId = (int) $this->getRequest()->getParam('minutesId');
        
        $minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes');
        $minutes->find($minutesId);
        
        Phprojekt::getInstance()->getLog()->debug('Minutes is: '.print_r($minutes->_data, true));
        
        if (empty($minutes->id)) {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        } else {
            
            $id = (int) $this->getRequest()->getParam('id');
            
            if (empty($id)) {
                $model   = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')->init($minutesId);
                $message = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);
            } else {
                $model   = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')->init($minutesId)->find($id);
                $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
            }
            
            if ($model instanceof Phprojekt_Model_Interface) {
                //$tmp = Default_Helpers_Save::save($model, $this->getRequest()->getParams());
                $params = $this->getRequest()->getParams();
                
                $params['projectId'] = $minutes->projectId;
                $params['ownerId']   = $minutes->ownerId;
                
                Default_Helpers_Save::save($model, $params);
                
                $return = array('type'    => 'success',
                                'message' => $message,
                                'code'    => 0,
                                'id'      => $model->id);
                
                Phprojekt_Converter_Json::echoConvert($return);
            } else {
                throw new Phprojekt_PublishedException(self::NOT_FOUND);
            }
        }
    }
    
    /*
     * Deletes the minutes item
     * 
     * @requestparam integer minutesId   ID of the minutes being worked on.
     * @requestparam integer id          ID of the item to be deleted.
     * 
     * @return void
     */
    public function jsonDeleteAction()
    {
        $minutesId = (int) $this->getRequest()->getParam('minutesId');
        
        if (empty($minutesId)) {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
        
        $id = (int) $this->getRequest()->getParam('id');
        
        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }
        
        $model = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')->init($minutesId)->find($id);
        
        if ($model instanceof Phprojekt_Model_Interface) {
            $tmp = Default_Helpers_Delete::delete($model);
            if ($tmp === false) {
                $message = Phprojekt::getInstance()->translate(self::DELETE_FALSE_TEXT);
            } else {
                $message = Phprojekt::getInstance()->translate(self::DELETE_TRUE_TEXT);
            }
            $return = array('type'    => 'success',
                            'message' => $message,
                            'code'    => 0,
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }
}
