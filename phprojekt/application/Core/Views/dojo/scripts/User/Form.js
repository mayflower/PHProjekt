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
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

dojo.provide("phpr.User.Form");

dojo.declare("phpr.User.Form", phpr.Core.Form, {
    setPermissions:function (data) {
        this._writePermissions = true;
        
        // users can't be deleted
        this._deletePermissions = false;
        this._accessPermissions = true;
    },
        
    addModuleTabs:function(data) {
    },

    useCache:function() {
        return false;
    },
            
    updateData: function(){
        phpr.DataStore.deleteData({url: this._url});

        // Delete User Cache
        this.userStore = new phpr.Store.User();
        this.userStore.update();
        
        // Delete settings for the user
        var url = phpr.webpath+"index.php/Core/user/jsonGetSettingList/nodeId/" + this.id;
        phpr.DataStore.deleteData({url: url});
    }
});
