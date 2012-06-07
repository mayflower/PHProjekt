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
 * @category   PHProjekt
 * @package    Application
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Role.Form");

dojo.declare("phpr.Role.Form", phpr.Core.DialogForm, {
    roleModuleAccessStore: null,

    initData:function() {
        // Get modules
        this.roleModuleAccessStore = new phpr.Default.System.Store.RoleModuleAccess(this.id);
        this._initData.push({'store': this.roleModuleAccessStore});
    },

    setPermissions:function(data) {
        this._writePermissions  = true;
        this._deletePermissions = false;
        if (this.id > 1) {
            this._deletePermissions = true;
        }
        this._accessPermissions = true;
    },

    addBasicFields:function() {
        this.formdata[1].push(
            new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Core.Role.template.formAccess.html",
                templateData: {
                    accessModuleText: phpr.nls.get('Module'),
                    accessReadText:   phpr.nls.get('Read'),
                    accessWriteText:  phpr.nls.get('Write'),
                    accessCreateText: phpr.nls.get('Create'),
                    accessAdminText:  phpr.nls.get('Admin'),
                    labelfor:         phpr.nls.get('Access'),
                    label:            phpr.nls.get('Access'),
                    modules:          this.roleModuleAccessStore.getList()
                }
        }));
    }
});
