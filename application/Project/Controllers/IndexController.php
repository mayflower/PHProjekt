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
 * You can redefine too, the var FORM_COLUMNS
 * for make your module with other number than the default
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
     * How many columns will have the form
     *
     * @var integer
     */
    const FORM_COLUMNS = 1;

    /**
     * Init the Module object
     *
     * @return Zend_Item object
     */
    public function getModelsObject()
    {
        $db = Zend_Registry::get('db');

        return Phprojekt_Loader::getModel('Project', 'Project', array('db' => $db));
    }

    /**
     * Save Action
     *
     * The save is redefined for use with tree in the project module
     *
     * @return void
     */
    public function saveAction()
    {
        $request = $this->_request->getParams();

        $parent = (isset($request['parent'])) ? (int) $request['parent'] : 1;
        $itemid = (isset($request['id'])) ? (int) $request['id'] : null;

        $parentNode = new Phprojekt_Tree_Node_Database($this->models, $parent);
        $newNode    = new Phprojekt_Tree_Node_Database($this->models, $itemid);

        if (null !== $itemid) {
            $newNode->setup();
        }
        $parentNode->setup();

        /* Assign the values */
        foreach ($request as $k => $v) {
            if ($newNode->getActiveRecord()->keyExists($k)) {
                $newNode->$k = $v;
            }
        }

        /* Validate and save if is all ok */
        if ($newNode->getActiveRecord()->recordValidate()) {
            if (null === $itemid || $newNode->parent !== $parentNode->id) {
                $parentNode->appendNode($newNode);
            } else {
                $newNode->getActiveRecord()->save();
            }
            $this->message = 'Saved';
        } else {
            $this->errors = $newNode->getActiveRecord()->getError();
        }

        $this->setTreeView();

        $this->generateOutput();
        $this->render('index');
    }
}