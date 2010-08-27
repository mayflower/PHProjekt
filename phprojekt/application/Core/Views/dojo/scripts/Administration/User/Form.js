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
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.User.Form");

dojo.declare("phpr.User.Form", phpr.Core.Form, {
    setPermissions:function(data) {
        this._writePermissions = true;
        // users can't be deleted
        this._deletePermissions = false;
        this._accessPermissions = true;
    },

    useCache:function() {
        return false;
    },

    customActionOnSuccess:function() {
        var result     = Array();
        result.type    = 'warning';
        result.message = phpr.nls.get('You need to log out and log in again in order to let changes have effect');
        new phpr.handleResponse('serverFeedback', result);
    },

    prepareSubmission:function() {
        // Check the admin value
        if (dijit.byId('admin').get('value') == "") {
            dijit.byId('admin').set('value', 0);
        }
        // Check the status value
        if (dijit.byId('status').get('value') == "") {
            dijit.byId('status').set('value', "A");
        }

        return this.inherited(arguments);
    }
});
