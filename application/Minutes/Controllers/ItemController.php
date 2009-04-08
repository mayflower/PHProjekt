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
    
}
