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
    const MINUTES_READ_ONLY = 'This minutes is final and cannot be edited.';

    /**
     * Returns list of minutes items referenced by a minutesId
     *
     * @requestparam integer minutesId The id of the minutes this list belongs to.
     *
     * @return void
     */
    public function jsonListAction()
    {
        $minutesId = (int) $this->getRequest()->getParam('minutesId', 0);
        if (!empty($minutesId)) {
            $itemModel = Phprojekt_Loader::getModel('Minutes', 'MinutesItem');
            $itemModel->init($minutesId);
            $result = $itemModel->fetchAll();
        } else {
            $result = array();
        }

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
        $itemModel->init((int) $this->getRequest()->getParam('minutesId', 0));
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
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

        if (empty($minutes->id)) {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        } elseif (4 == $minutes->itemStatus) {
            throw new Phprojekt_PublishedException(self::MINUTES_READ_ONLY);
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
                $params = $this->getRequest()->getParams();

                $params['projectId'] = $minutes->projectId;
                $params['ownerId']   = $minutes->ownerId;

                if (isset($params['parentOrder']) && is_numeric($params['parentOrder']) && $params['parentOrder'] > 0) {
                    // This item is supposed to be sorted after the given order
                    $params['sortOrder'] = $params['parentOrder'] + 1;
                    unset($params['parentOrder']);
                }

                Phprojekt::getInstance()->getLog()->debug('Saving Item model with params: '.print_r($params, true));

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
     * @requestparam integer minutesId Id of the minutes being worked on.
     * @requestparam integer id        Id of the item to be deleted.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $minutesId = (int) $this->getRequest()->getParam('minutesId');

        $minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes');
        $minutes->find($minutesId);

        if (empty($minutesId)) {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        } elseif (4 == $minutes->itemStatus) {
            throw new Phprojekt_PublishedException(self::MINUTES_READ_ONLY);
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

    /**
     * Provide list of items for sort ordering
     */
    public function jsonListItemSortOrderAction()
    {
        $minutesId = (int) $this->getRequest()->getParam('minutesId');

        $items = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')->init($minutesId)->fetchAll();

        $return = array();
        foreach ($items as $item) {
            // @var item MinutesItem
            $return[] = array('sortOrder' => $item->sortOrder, 'title' => $item->title);
        }

        Phprojekt_Converter_Json::echoConvert($return);
    }
}
