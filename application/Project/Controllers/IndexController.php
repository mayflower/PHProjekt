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
     * Get a list of submodules
     * and check for the users right on them
     *
     * @return array
     */
    public function jsonGetSubmodulesAction()
    {
        $session           = new Zend_Session_Namespace();
        $allowedSubModules = array();
        $subModules        = Phprojekt_SubModules::getInstance()->getSubModules();

        $rights = new Phprojekt_RoleRights($session->currentProjectId, 'Project');
        foreach ($subModules as $subModuleData) {
            $right = $rights->hasRight('read', $subModuleData['name']) ? true : $rights->hasRight('write', $subModuleData['name']);
            if ($right) {
                $allowedSubModules[] = $subModuleData;
            }
        }

        echo Zend_Json_Encoder::encode($allowedSubModules);
    }
}