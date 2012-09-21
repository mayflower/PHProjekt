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
 * Role Module Controller.
 */
class Core_RoleController extends Core_IndexController
{
    /**
     * Returns all the modules and the access for one roleId.
     *
     * Returns a list of all the modules with:
     * <pre>
     *  - id       => id of the module.
     *  - name     => Name of the module.
     *  - label    => Display for the module.
     *  - none     => True or false for none access.
     *  - read     => True or false for read access.
     *  - write    => True or false for write access.
     *  - access   => True or false for access access.
     *  - create   => True or false for create access.
     *  - copy     => True or false for copy access.
     *  - delete   => True or false for delete access.
     *  - download => True or false for download access.
     *  - admin    => True or false for admin access.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b> The role id for consult.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetModulesAccessAction()
    {
        $role    = new Phprojekt_Role_RoleModulePermissions();
        $roleId  = (int) $this->getRequest()->getParam('id', null);
        $modules = $role->getRoleModulePermissionsById($roleId);

        Phprojekt_Converter_Json::echoConvert($modules);
    }
}
