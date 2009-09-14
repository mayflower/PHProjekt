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
 * @subpackage Minutes
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Minutes Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Minutes
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */
class Minutes_ItemController extends IndexController
{
    /**
     * String to use on error in the action save and delete when the item is read only.
     */
    const MINUTES_READ_ONLY = 'This minutes is final and cannot be edited.';

    /**
     * Returns the list of minutes items referenced by a minutesId.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of all the rows.
     *  - The number of rows.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>minutesId</b> The id of the minutes this list belongs to.
     * </pre>
     *
     * The return is in JSON format.
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

        if (array() === $result && isset($itemModel)) {
            // Inject metadata for correct filling of topicType select field
            $result = array('metadata' => $itemModel->getInformation()->getFieldDefinition(),
                            'numRows'  => 0);
        }

        Phprojekt_Converter_Json::echoConvert($result, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the detail (fields and data) of a single minutes item.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of one item.
     *  - The number of rows.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_FORM for get and sort the fields.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b>        The id of the item.
     *  - integer <b>minutesId</b> The id of the minutes.
     * </pre>
     *
     * The return is in JSON format.
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
     * Saves the current minute item.
     *
     * If the request parameter "id" is null or 0, the function will add a new item,
     * if the "id" is an existing item, the function will update it.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>minutesId</b> The id of the minutes.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>                      id of the item to save.
     *  - mixed   <b>all other module fields</b> All the fields values to save.
     * </pre>
     *
     * If there is an error, the save will return a Phprojekt_PublishedException,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0.
     *  - id      => Id of the minute item.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On error in the action save or wrong id.
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
                $newItem = true;
            } else {
                $model   = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')->init($minutesId)->find($id);
                $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
                $newItem = false;
            }

            if ($model instanceof Phprojekt_Model_Interface) {
                $params = $this->setParams($this->getRequest()->getParams(), $minutes);
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

    /**
     * Deletes a certain item.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b>        The id of the item.
     *  - integer <b>minutesId</b> The id of the minutes.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - code    => 0.
     *  - id      => id of the deleted item.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On missing or wrong id, or on error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $minutesId = (int) $this->getRequest()->getParam('minutesId');
        $id        = (int) $this->getRequest()->getParam('id');

        $minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes');
        $minutes->find($minutesId);

        if (empty($minutesId)) {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        } elseif (4 == $minutes->itemStatus) {
            throw new Phprojekt_PublishedException(self::MINUTES_READ_ONLY);
        }

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
     * Returns a list of items for sort ordering.
     *
     * The return data have:
     * <pre>
     *  - sortOrder: Order number.
     *  - title:     Title of the item.
     * </pre>
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>minutesId</b> The id of the minutes.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonListItemSortOrderAction()
    {
        $minutesId = (int) $this->getRequest()->getParam('minutesId');
        $items     = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')->init($minutesId)->fetchAll();

        $return = array('data' => array());
        foreach ($items as $item) {
            $return['data'][] = array('sortOrder' => $item->sortOrder,
                                      'title'     => $item->title);
        }

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Set some values deppend on the params
     *
     * @return array
     */
    public function setParams()
    {
        $args   = func_get_args();
        $params = $args[0];
        $model  = $args[1];

        if (empty($params['topicDate'])) {
            unset($params['topicDate']);
        } else {
            $params['topicDate'] = Cleaner::sanitize('date', $params['topicDate']);
        }

        if (empty($params['userId'])) {
            unset($params['userId']);
        } else {
            $params['userId'] = (int) $params['userId'];
        }

        $params['projectId'] = $model->projectId;
        $params['ownerId']   = $model->ownerId;

        if (isset($params['parentOrder']) && is_numeric($params['parentOrder']) && $params['parentOrder'] > 0) {
            // This item is supposed to be sorted after the given order
            $params['sortOrder'] = $params['parentOrder'] + 1;
            unset($params['parentOrder']);
        }

        return $params;
    }
}
