<?php
/**
 * Tag Controller for PHProjekt 6
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id: IndexController.php 635 2008-04-02 19:32:05Z david $
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Tag Controller for PHProjekt 6
 *
 * The controller will get all the actions for manage tags
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class TagController extends IndexController
{
    /**
     * Phprojekt_Tags_Default class
     *
     * @var Phprojekt_Tags_Default
     */
    private $_tags = null;

    /**
     * Init function
     *
     * @return void
     */
    public function init ()
    {
        $this->_tags = Phprojekt_Tags_Default::getInstance();
        parent::init();
    }

    /**
     * Get an array with tags
     * order by number of ocurrences
     *
     * @requestparam integer $limit Limit the number of tags for return
     *
     * @return void
     */
    public function jsonGetTagsAction()
    {
        $limit  = (int) $this->getRequest()->getParam('limit', 0);
        $tags   = $this->_tags->getTags($limit);
        $fields = $this->_tags->getFieldDefinition();

        echo Phprojekt_Converter_Json::convertTag($tags, $fields);
    }

    /**
     * Get an array with tags for the $module and $id
     * order by number of ocurrences
     *
     * @requestparam integer $id    Item id
     * @requestparam integer $limit Limit the number of tags for return
     *
     * @return void
     */
    public function jsonGetTagsByModuleAction()
    {
        $id       = (int) $this->getRequest()->getParam('id', 0);
        $moduleId = (int) Phprojekt_Module::getId($this->getRequest()->getModuleName());
        $limit    = (int) $this->getRequest()->getParam('limit', 0);

        if (empty($id)) {
            throw new Phprojekt_PublishedException('ID parameter required');
        }

        $tags   = $this->_tags->getTagsByModule($moduleId, $id, $limit);
        $fields = $this->_tags->getFieldDefinition();

        echo Phprojekt_Converter_Json::convertTag($tags, $fields);
    }

    /**
     * Get an array with all the modules with a tag
     *
     * @requestparam string  $tag   Tag to search
     * @requestparam integer $limit Limit the number of tags for return
     *
     * @return void
     */
    public function jsonGetModulesByTagAction()
    {
        $tag   = $this->getRequest()->getParam('tag', '');
        $limit = (int) $this->getRequest()->getParam('limit', 0);

        $tags   = $this->_tags->getModulesByTag($tag, $limit);
        $fields = $this->_tags->getModuleFieldDefinition();

        echo Phprojekt_Converter_Json::convertTag($tags, $fields);
    }

    /**
     * Saves the tags for the current item
     *
     * @requestparam integer $id     Item id
     * @requestparam string  $string All the tags separated by space
     *
     * @return void
     */
    public function jsonSaveTagsAction()
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $string = $this->getRequest()->getParam('string','');

        if (empty($id)) {
            throw new Phprojekt_PublishedException('ID parameter required');
        }

        $moduleId = Phprojekt_Module::getId($this->getRequest()->getModuleName());
        $this->_tags->saveTags($moduleId, $id, $string);

        $translate = Zend_Registry::get('translate');

        $message = $translate->translate('The Tags was added correctly');

        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => 0);
        echo Phprojekt_Converter_Json::convertValue($return);
    }
}