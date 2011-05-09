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
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */
dojo.provide("phpr.User.Form");

dojo.declare("phpr.User.Form", phpr.Core.Form, {
    _setPermissions:function(data) {
        // Summary:
        //    Get the permission for the current user on the item.
        this._writePermissions = true;
        // users can't be deleted
        this._deletePermissions = false;
        this._accessPermissions = true;
    },

    _useCache:function() {
        // Summary:
        //    Return true or false if the cache is used.
        return false;
    },

    _prepareSubmission:function() {
        // Summary:
        //    Prepares the data for submission.
        // Check the admin value
        if (dijit.byId('admin-Administration-User').get('value') == "") {
            dijit.byId('admin-Administration-User').set('value', 0);
        }
        // Check the status value
        if (dijit.byId('status-Administration-User').get('value') == "") {
            dijit.byId('status-Administration-User').set('value', 'A');
        }

        return this.inherited(arguments);
    },

    _customActionOnSuccess:function() {
        // Summary:
        //    Show a warning for user sub-module.
        var result     = {};
        result.type    = 'warning';
        result.message = phpr.nls.get('You need to log out and log in again in order to let changes have effect');
        new phpr.handleResponse('serverFeedback', result);
    }
});
