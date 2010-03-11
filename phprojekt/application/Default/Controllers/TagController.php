<?php
/**
 * Tag Controller.
 * The controller will get all the actions for manage tags.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * Tag Controller.
 * The controller will get all the actions for manage tags.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class TagController extends IndexController
{
    /**
     * Returns an array with tags order by number of ocurrences.
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
     *  - integer <b>limit</b> Number of results.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetTagsAction()
    {
        $tagObj = Phprojekt_Tags::getInstance();
        $limit  = (int) $this->getRequest()->getParam('limit', 0);
        $tags   = $tagObj->getTags($limit);
        $fields = $tagObj->getFieldDefinition();

        Phprojekt_Converter_Json::echoConvert($tags, $fields);
    }

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
        $tagObj = Phprojekt_Tags::getInstance();

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
     * Search for a tag.
     *
     * Returns an array with all the modules with the tag, with:
     * <pre>
     *  - id            => id of the item found.
     *  - moduleId      => id of the module.
     *  - moduleName    => Name of the module.
     *  - moduleLabel   => Display for the module.
     *  - firstDisplay  => Firts display for the item (Ej. title).
     *  - secondDisplay => Second display for the item (Ej. notes).
     *  - projectId     => Parent project id of the item.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>tag</b>   Tag to search.
     *  - integer <b>limit</b> Number of results.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetModulesByTagAction()
    {
        $tagObj = Phprojekt_Tags::getInstance();
        $tag    = (string) $this->getRequest()->getParam('tag', '');
        $limit  = (int) $this->getRequest()->getParam('limit', 0);
        $tags   = $tagObj->getModulesByTag($tag, $limit);

        Phprojekt_Converter_Json::echoConvert($tags);
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
     * If there is an error, the save will return a Phprojekt_PublishedException,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0.
     *  - id      => 0.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On missing or wrong id.
     *
     * @return void
     */
    public function jsonSaveTagsAction()
    {
        $tagObj = Phprojekt_Tags::getInstance();
        $id     = (int) $this->getRequest()->getParam('id');
        $string = (string) $this->getRequest()->getParam('string', '');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $module   = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', 'Project'));
        $moduleId = (int) Phprojekt_Module::getId($module);

        $tagObj->saveTags($moduleId, $id, $string);

        $message = Phprojekt::getInstance()->translate('The Tags were added correctly');

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
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
     * If there is an error, the delete will return a Phprojekt_PublishedException,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0.
     *  - id      => 0.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On missing or wrong id.
     *
     * @return void
     */
    public function jsonDeleteTagsAction()
    {
        $tagObj = Phprojekt_Tags::getInstance();
        $id     = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $module   = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', 'Project'));
        $moduleId = (int) Phprojekt_Module::getId($module);

        $tagObj->deleteTagsByItem($moduleId, $id);

        $message = Phprojekt::getInstance()->translate('The Tags were deleted correctly');

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => 0);

        Phprojekt_Converter_Json::echoConvert($return);
    }
}
