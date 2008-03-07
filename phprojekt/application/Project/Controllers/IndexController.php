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
     * a list of submodules
     *
     * @var array
     */
    protected $_submodules = array();

    /**
     * Set various session data.
     *
     * @todo Check if we have to move this part of the code, as lastProjectId
     *       is used everywhere.
     *
     * @return void
     */
    public function init() {
        parent::init();

        $this->_submodules = $this->_getSubmodules();
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
        if (null === $this->getRequest()->getParam('id', null)) {
            throw new InvalidArgumentException('Id not found');    
        }
        
        $model = $this->getModelObject()->find($this->getRequest()->getParam('id'));
        /* Validate and save if is all ok */
        $node = new Phprojekt_Tree_Node_Database($model);
        Default_Helpers_Save::save($node, $this->getRequest()->getParams(), (int) $this->getRequest()->getParam('parent', null));
    }

    /**
     * Get a list of submodules and check for the users right on them
     *
     * @return array
     */
    protected function _getSubmodules()
    {
        //select all sobmodules with read rights from  db
        $session = new Zend_Session_Namespace();

        $modulesArray    = array();
        $allModulesArray = array('Todo','Note','Timecard');

        $rights = new Phprojekt_RoleRights($session->currentProjectId, 'Project');
        foreach ($allModulesArray as $module) {
            $right = $rights->hasRight('read', $module) ? true : $rights->hasRight('write', $module);
            if ($right) {
                $modulesArray[] = $module;
            }
        }

        return $modulesArray;
    }
}