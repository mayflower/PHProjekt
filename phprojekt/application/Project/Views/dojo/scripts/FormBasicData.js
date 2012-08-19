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
 * @subpackage Project
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Project.FormBasicData");

dojo.declare("phpr.Project.FormBasicData", phpr.Project.Form, {
    setUrl: function() {
        // Summary:
        //    Set the url for get the data
        this._url = 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/' +
                    phpr.tree.getParentId(this.id) + '/id/' + this.id;
    },

    initData: function() {
        // Get the rights for other users
        this._accessUrl = 'index.php/' + phpr.module + '/index/jsonGetUsersRights' +
                        '/nodeId/' + phpr.tree.getParentId(phpr.currentProjectId) + '/id/' + this.id;
        this._initData.push({'url': this._accessUrl});

        // Get all the active users
        this.userStore = new phpr.Default.System.Store.User(phpr.tree.getParentId(this.id));
        this._initData.push({'store': this.userStore});

        // Get roles
        this.roleStore = new phpr.Default.System.Store.Role(phpr.tree.getParentId(phpr.currentProjectId), this.id);
        this._initData.push({'store': this.roleStore});

        // Get modules
        this.moduleStore = new phpr.Default.System.Store.Module(phpr.tree.getParentId(phpr.currentProjectId), this.id);
        this._initData.push({'store': this.moduleStore});

        // Get the tags
        this._tagUrl = 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module + '/id/' + this.id;
        this._initData.push({'url': this._tagUrl});
    },

    postRenderForm: function() {
        if (dijit.byId("deleteButton")) {
            dijit.byId("deleteButton").destroy();
        }
    },

    submitForm: function() {
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url: 'index.php/' + phpr.module + '/index/jsonSave/nodeId/' +
                    phpr.tree.getParentId(this.id) + '/id/' + this.id,
            content:   this.sendData
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (!this.id) {
                    this.id = data.id;
                }
                if (data.type == 'success') {
                    return phpr.send({
                        url: 'index.php/Default/Tag/jsonSaveTags/moduleName/' + phpr.module +
                            '/id/' + this.id,
                        content: this.sendData
                    });
                }
            }
        })).then(dojo.hitch(this, function(data) {
            if (data) {
                if (this.sendData.string) {
                    new phpr.handleResponse('serverFeedback', data);
                }
                if (data.type == 'success') {
                    this.publish("updateCacheData");
                    this.publish("changeProject", [this.id]);
                }
            }
        }));
    }
});
