<?php
/**
 * Administration for the Role module
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007, 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id: AdminController.php 635 2008-04-02 19:32:05Z david $
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Administration for the Role module
 *
 * @copyright  2007, 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Role_AdminController extends AdminController
{
    public static $name = 'Role Administration';
    public static $configuration = array();

    /**
     * Return all the modules in an array and the access if exists
     *
     * @requestparam integer $roleId The role id
     *
     * @return void
     */
    public function jsonGetModulesAccessAction()
    {
        $role    = Phprojekt_Loader::getModel('Role', 'RoleModulePermissions');
        $roleId  = (int) $this->getRequest()->getParam('id', null);
        $modules = $role->getRoleModulePermissionsById($roleId);

        echo Phprojekt_Converter_Json::convert($modules);
    }
}