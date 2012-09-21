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
 * Tag Controller.
 * The controller will get all the actions for manage tags.
 */
class TagController extends IndexController
{
    /**
     * Returns an array with tags for one item.
     *
     * Returns the metadata of the fieds and the data itself with:
     * <pre>
     *  - string => The tag.
     *  - count  => Number of ocurrences.
     * </pre>
     * Also the number of rows is returned.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>         id of the item.
     *  - integer <b>limit</b>      Number of results.
     *  - integer <b>moduleName</b> Name of the module.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetTagsByModuleAction()
    {
        $tagObj = new Phprojekt_Tags();

        $id        = (int) $this->getRequest()->getParam('id', 0);
        $limit     = (int) $this->getRequest()->getParam('limit', 0);
        $module    = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', 'Project'));
        $moduleId  = (int) Phprojekt_Module::getId($module);

        if (!empty($id)) {
            $tags = $tagObj->getTagsByModule($moduleId, $id, $limit);
        } else {
            $tags = array();
        }
        $fields = $tagObj->getFieldDefinition();

        Phprojekt_Converter_Json::echoConvert($tags, $fields);
    }

    /**
     * Saves the tags for the current item.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the item.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>string</b>     An string of words (Will be separated by the spaces).
     *  - string <b>moduleName</b> Name of the module.
     * </pre>
     *
     * If there is an error, the save will return a Zend_Controller_Action_Exception,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - id      => 0.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On missing or wrong id.
     *
     * @return void
     */
    public function jsonSaveTagsAction()
    {
        $tagObj = new Phprojekt_Tags();
        $id     = (int) $this->getRequest()->getParam('id');
        $string = (string) $this->getRequest()->getParam('string', '');

        if (empty($id)) {
            throw new Zend_Controller_Action_Exception(self::ID_REQUIRED_TEXT, 400);
        }

        $module   = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', 'Project'));
        $moduleId = (int) Phprojekt_Module::getId($module);

        $tagObj->saveTags($moduleId, $id, $string);

        $message = Phprojekt::getInstance()->translate('The Tags were added correctly');

        $return = array('type'    => 'success',
                        'message' => $message,
                        'id'      => 0);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Delete the tags for one item.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the item.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>moduleName</b> Name of the module.
     * </pre>
     *
     * If there is an error, the delete will return a Zend_Controller_Action_Exception,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - id      => 0.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On missing or wrong id.
     *
     * @return void
     */
    public function jsonDeleteTagsAction()
    {
        $tagObj = new Phprojekt_Tags();
        $id     = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Zend_Controller_Action_Exception(self::ID_REQUIRED_TEXT, 400);
        }

        $module   = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', 'Project'));
        $moduleId = (int) Phprojekt_Module::getId($module);

        $tagObj->deleteTagsByItem($moduleId, $id);

        $message = Phprojekt::getInstance()->translate('The Tags were deleted correctly');

        $return = array('type'    => 'success',
                        'message' => $message,
                        'id'      => 0);

        Phprojekt_Converter_Json::echoConvert($return);
    }
}
