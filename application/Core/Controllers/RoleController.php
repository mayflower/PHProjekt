<?php
/**
 * Role Module Controller for PHProjekt 6.0
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
 * Role Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_RoleController extends Core_IndexController
{
    /**
     * Return all the modules in an array and the access if exists
     *
     * @requestparam integer $roleId The role id
     *
     * @return void
     */
    public function jsonGetModulesAccessAction()
    {
        $role    = new Phprojekt_Role_RoleModulePermissions();
        $roleId  = (int) $this->getRequest()->getParam('id', null);
        $modules = $role->getRoleModulePermissionsById($roleId);

        echo Phprojekt_Converter_Json::convert($modules);
    }
}