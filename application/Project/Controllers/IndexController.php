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
    public function jsonSaveAction()
    {
        $translate = Zend_Registry::get('translate');
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $model   = $this->getModelObject();
            $message = $translate->translate('The Item was added correctly');
        } else {
            $model   = $this->getModelObject()->find($id);
            $message = $translate->translate('The Item was edited correctly');
        }
        $node    = new Phprojekt_Tree_Node_Database($model, $id);
        $newNode = Default_Helpers_Save::save($node,
                                              $this->getRequest()->getParams(),
                                              (int) $this->getRequest()->getParam('projectId',
                                              null));

        // Set the id since the Tree save
        // return differents values from insert and update
        if (empty($id)) {
            $showId = $newNode->id;
        } else {
            $showId = $id;
        }

        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $showId);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Save Multiple items Action
     *
     * The save is redefined for use with tree in the project module
     *
     * @return void
     */
    public function jsonSaveMultipleAction()
    {
        $translate = Zend_Registry::get('translate');
        $data      = (array) $this->getRequest()->getParam('data');

        $message = $translate->translate('The Items was added correctly');
        $showId = array();
        foreach ($data as $id => $fields) {
            $model   = $this->getModelObject()->find($id);
            $node    = new Phprojekt_Tree_Node_Database($model, $id);
            $newNode = Default_Helpers_Save::save($node, $fields, (int) $this->getRequest()->getParam('nodeId', null));
            $showId[] = $newNode->id;
        }

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => implode(',', $showId));

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Get all the modules active and their relation with the projectId
     *
     * @requestparam int $id The project Id
     *
     * @return void
     */
    public function jsonGetModulesProjectRelationAction()
    {
        $projectId = (int) $this->getRequest()->getParam('id');
        $project   = Phprojekt_Loader::getModel('Project', 'ProjectModulePermissions');
        $modules   = $project->getProjectModulePermissionsById($projectId);

        echo Phprojekt_Converter_Json::convert($modules);
    }

    /**
     * Get all the role-user relation with the projectId
     *
     * @requestparam int $id The project Id
     *
     * @return void
     */
    public function jsonGetProjectRoleUserRelationAction()
    {
        $projectId = (int) $this->getRequest()->getParam('id');
        $project   = Phprojekt_Loader::getModel('Project', 'ProjectRoleUserPermissions');
        $roles     = $project->getProjectRoleUserPermissions($projectId);

        echo Phprojekt_Converter_Json::convert($roles);
    }
}