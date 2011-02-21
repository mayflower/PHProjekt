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
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Project.FormBasicData");

dojo.declare("phpr.Project.FormBasicData", phpr.Project.Form, {
    _constructor:function(module, subModules) {
        // Summary:
        //    Overwrite the constructor for set new value to this._module.
        module = 'BasicData'
        this.inherited(arguments);
    },

    _setUrl:function() {
        // Summary:
        //    Set the url for get the data
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/'
            + phpr.Tree.getParentId(this._id) + '/id/' + this._id;
    },

    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
        var projectId = this._id;
        var parentId  = phpr.Tree.getParentId(phpr.currentProjectId);

        // Get the rights for other users
        this._accessUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetUsersRights'
            + '/nodeId/' + parentId + '/id/' + projectId;
        this._initDataArray.push({'url': this._accessUrl});

        // Get all the active users
        this._userStore = new phpr.Store.User(parentId);
        this._initDataArray.push({'store': this._userStore});

        // Get roles
        this._roleStore = new phpr.Store.Role(parentId, projectId);
        this._initDataArray.push({'store': this._roleStore});

        // Get modules
        this._moduleStore = new phpr.Store.Module(parentId, projectId);
        this._initDataArray.push({'store': this._moduleStore});

        // Get the tags
        this._tagUrl = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module
            + '/id/' + projectId;
        this._initDataArray.push({'url': this._tagUrl});
    },

    _postRenderForm:function() {
        // Summary:
        //    User functions after render the form.
        if (dijit.byId('deleteButton-' + this._module)) {
            dijit.byId('deleteButton-' + this._module).domNode.style.display = 'none';
        }
    },

    _submitForm:function() {
        // Summary:
        //    Submit the forms.
        if (!this._prepareSubmission()) {
            return false;
        }

        phpr.send({
            url: phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/nodeId/'
                + phpr.Tree.getParentId(this._id) + '/id/' + this._id,
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (!this._id) {
                   this._id = data['id'];
               }
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonSaveTags/moduleName/' + phpr.module
                            + '/id/' + this._id,
                        content:   this._sendData,
                        onSuccess: dojo.hitch(this, function(data) {
                            if (this._sendData['string']) {
                                new phpr.handleResponse('serverFeedback', data);
                            }
                            if (data.type == 'success') {
                                dojo.publish(phpr.module + '.updateCacheData');
                                dojo.publish(phpr.module + '.changeProject', [this._id]);
                            }
                        })
                    });
                }
            })
        });
    }
});
