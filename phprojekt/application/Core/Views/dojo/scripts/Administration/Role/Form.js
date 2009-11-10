/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Role.Form");

dojo.declare("phpr.Role.Form", phpr.Core.Form, {
    roleModuleAccessStore: null,

    initData:function() {
        // Get modules
        this.roleModuleAccessStore = new phpr.Store.RoleModuleAccess(this.id);
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
        this.formdata[1] += this.render(["phpr.Core.Role.template", "formAccess.html"], null, {
            accessModuleText: phpr.nls.get('Module'),
            accessReadText:   phpr.nls.get('Read'),
            accessWriteText:  phpr.nls.get('Write'),
            accessCreateText: phpr.nls.get('Create'),
            accessAdminText:  phpr.nls.get('Admin'),
            labelfor:         phpr.nls.get('Access'),
            label:            phpr.nls.get('Access'),
            modules:          this.roleModuleAccessStore.getList()
        });
    }
});
