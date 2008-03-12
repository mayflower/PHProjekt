<?php
/**
 * Project Module Controller for PHProjekt 6
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Default Project Module Controller for PHProjekt 6
 *
 * For make a indexController for your module
 * just extend it to the IndexController
 * and redefine the function getModelsObject
 * for return the object model that you want
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_IndexController extends IndexController
{
    /**
     * Returns the list for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     *
     * @return void
     */
    public function jsonListAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set. This is also used for loading a
        // grid on demand (initially only a part is shown, scrolling down loads what is needed).
        $count     = (int) $this->getRequest()->getParam('count');
        $offset    = (int) $this->getRequest()->getParam('start');
     	$parentId  = (int) $this->getRequest()->getParam('nodeId');
        $itemId    = (int) $this->getRequest()->getParam('id');

        if (!empty($itemId)) {
            $records = $this->getModelObject()->fetchAll('id = ' . $itemId, null, $count, $offset);
        } else if (empty($parentId)) {
     	    $records = $this->getModelObject()->fetchAll('parent is null', null, $count, $offset);
     	} else {
        	$records = $this->getModelObject()->fetchAll('parent = ' . $parentId, null, $count, $offset);
     	}

        echo Phprojekt_Converter_Json::convert($records);
    }

    /**
     * Save Action
     *
     * The save is redefined for use with tree in the project module
     *
     * @return void
     */
    public function jsonSaveAction()
    {
     	$id = (int) $this->getRequest()->getParam('id');

     	try {
     	    if (empty($id)) {
                $model = $this->getModelObject();
            } else {
                $model = $this->getModelObject()->find($this->getRequest()->getParam('id'));
     	    }
     	    $node = new Phprojekt_Tree_Node_Database($model, $id);
            Default_Helpers_Save::save($node, $this->getRequest()->getParams(), (int) $this->getRequest()->getParam('parent', null));
        } catch (Exception $saveError) {
            $data = array();
            $data['error'] = $this->getModelObject()->getError();
            echo Phprojekt_Converter_Json::covertValue($data);
     	}
    }
}