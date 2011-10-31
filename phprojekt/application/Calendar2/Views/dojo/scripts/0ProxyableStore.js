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
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Reno Reckling <reno.reckling@mayflower.de>
 */

dojo.provide("phpr.Calendar2.ProxyableStore");
dojo.declare("phpr.Calendar2.ProxyableStore", phpr.Default.System.Store, {
    constructor: function(projectId) {
        if (!projectId) {
            projectId = phpr.currentProjectId;
        }
        this._url = phpr.webpath + 'index.php/Calendar2/index/jsonGetProxyableUsers/nodeId/' + projectId;
    },

    makeSelect: function() {
        // Summary:
        //    This function get all the users the current user has proxy rights on
        // Description:
        //    This function get all the users the current user has proxy rights on
        var users  = phpr.DataStore.getData({url: this._url});
        this._list = [];
        for (var i in users) {
            this._list.push({"id":      users[i].id,
                             "display": users[i].display,
                             "current": users[i].current});
        }
    }
});

