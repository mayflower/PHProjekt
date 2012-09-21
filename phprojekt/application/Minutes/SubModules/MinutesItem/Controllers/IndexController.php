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
 * MinutesItem Module Controller.
 */
class MinutesItem_IndexController extends IndexController
{
    /**
     * String to use on error in the action save and delete when the item is read only.
     */
    const MINUTES_READ_ONLY = 'This minutes is final and cannot be edited.';

    /**
     * Return the model name for construct the class.
     *
     * @return string The path to the model in the class format.
     */
    public function getModelName()
    {
        return 'Minutes_SubModules_MinutesItem';
    }

    /**
     * Gets the class model of the module and set the parent.
     *
     * @return Phprojekt_Model_Interface An instance of Phprojekt_Model_Interface.
     */
    public function getModelObject()
    {
        $minutesId = (int) $this->getRequest()->getParam('minutesId', 0);
        if ($minutesId == 0) {
            // Try with parentId
            $minutesId = (int) $this->getRequest()->getParam('parentId', 0);
        }

        $object = parent::getModelObject();
        $object->setParent($minutesId);

        return $object;
    }

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
        $this->setCurrentProjectId();

        if (!empty($minutesId)) {
            $where = sprintf('minutes_id = %d', (int) $minutesId);
        } else {
            $where = null;
        }

        $itemModel = $this->getModelObject();
        $result    = $itemModel->fetchAll($where);

        if (array() === $result) {
            // Inject metadata for draw an empty grid
            $ordering = Phprojekt_ModelInformation_Default::ORDERING_LIST;
            $result   = array('metadata' => $itemModel->getInformation()->getFieldDefinition($ordering),
                              'numRows'  => 0);
        }

        Phprojekt_Converter_Json::echoConvert($result, Phprojekt_ModelInformation_Default::ORDERING_LIST);
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
     * If there is an error, the save will return a Zend_Controller_Action_Exception,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - id      => Id of the minute item.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On error in the action save or wrong id.
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $this->setCurrentProjectId();
        $model = $this->getModelObject();

        if (empty($model->getParent()->id)) {
            throw new Zend_Controller_Action_Exception(self::NOT_FOUND, 404);
        } elseif (4 == $model->getParent()->itemStatus) {
            throw new Zend_Controller_Action_Exception(self::MINUTES_READ_ONLY, 403);
        } else {
            $id = (int) $this->getRequest()->getParam('id');

            if (empty($id)) {
                $message = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);
            } else {
                $model->find($id);
                $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
            }

            if ($model instanceof Phprojekt_Model_Interface) {
                $params = $this->setParams($this->getRequest()->getParams(), $model->getParent());
                Default_Helpers_Save::save($model, $params);

                $return = array('type'    => 'success',
                                'message' => $message,
                                'id'      => $model->id);

                Phprojekt_Converter_Json::echoConvert($return);
            } else {
                throw new Zend_Controller_Action_Exception(self::NOT_FOUND, 404);
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
     *  - id      => id of the deleted item.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On missing or wrong id, or on error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        $model = $this->getModelObject();

        if (empty($model->getParent()->id)) {
            throw new Zend_Controller_Action_Exception(self::NOT_FOUND, 404);
        } elseif (4 == $model->getParent()->itemStatus) {
            throw new Zend_Controller_Action_Exception(self::MINUTES_READ_ONLY, 403);
        }

        if (empty($id)) {
            throw new Zend_Controller_Action_Exception(self::ID_REQUIRED_TEXT, 400);
        }

        $model->find($id);

        if ($model instanceof Phprojekt_ActiveRecord_Abstract && !empty($model->id)) {
            $tmp = Default_Helpers_Delete::delete($model);
            if ($tmp === false) {
                $message = Phprojekt::getInstance()->translate(self::DELETE_FALSE_TEXT);
                $type    = 'error';
            } else {
                $message = Phprojekt::getInstance()->translate(self::DELETE_TRUE_TEXT);
                $type    = 'success';
            }

            $return = array('type'    => $type,
                            'message' => $message,
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Zend_Controller_Action_Exception(self::NOT_FOUND, 404);
        }
    }

    /**
     * Returns a list of items for sort ordering.
     *
     * The return data have:
     * <pre>
     *  - id:   Order number.
     *  - name: Title of the item.
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
        $minutesId = (int) $this->getRequest()->getParam('minutesId', 0);
        $where     = sprintf('minutes_id = %d', (int) $minutesId);

        $items = $this->getModelObject()->fetchAll($where);

        $return = array('data' => array(array('id'   => 0,
                                              'name' => '')));
        foreach ($items as $item) {
            $return['data'][] = array('id'   => (int) $item->sortOrder,
                                      'name' => $item->title);
        }

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Set some values deppend on the params.
     *
     * @return array POST values with some changes.
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
