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

dojo.provide("phpr.Core.Form");

dojo.declare("phpr.Core.Form", phpr.Default.Form, {
    _isSystemModule: null,

    init:function(id, params, isSystemModule) {
        // Summary:
        //    Init the form for a new render.
        this._isSystemModule = isSystemModule;

        this.inherited(arguments);
    },

    updateData:function() {
        phpr.DataStore.deleteData({url: this._url});
    },

    /************* Private functions *************/

    _setUrl:function() {
        // Summary:
        //    Set the url for get the data.
        // Description:
        //    Rewritten the function for work like a system module and like a form.
        if (this._isSystemModule) {
            this._url = phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonDetail/nodeId/1/id/'
                + this._id;
        } else {
            this._url = phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonDetail/nodeId/1/'
                + 'moduleName/' + phpr.submodule;
        }
    },

    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
    },

    _setPermissions:function(data) {
        // Summary:
        //    Get the permission for the current user on the item.
        this._writePermissions  = true;
        this._deletePermissions = false;
        if (this._id > 0) {
            this._deletePermissions = true;
        }
        this._accessPermissions = true;
    },

    _setBreadCrumbItem:function(itemValue) {
        // Summary:
        //    Set the Breadcrumb with the first item value.
        phpr.BreadCrumb.setItem(itemValue);
    },

    _addBasicFields:function() {
        // Summary:
        //    Add some special fields.
    },

    _addModuleTabs:function(data) {
        // Summary:
        //    Add extra tabs.
    },

    _useCache:function() {
        // Summary:
        //    Return true or false if the cache is used.
        if (this._isSystemModule) {
            return true;
        } else {
            return false;
        }
    },

    _submitForm:function() {
        // Summary:
        //    Submit the forms.
        // Description:
        //    Rewritten the function for work like a system module and like a form
        if (!this._prepareSubmission()) {
            return false;
        }
        if (this._isSystemModule) {
            var url = phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonSave/nodeId/1/id/'
                + this._id;
        } else {
            var url = phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonSave/nodeId/1/moduleName/'
                + phpr.submodule;
        }
        phpr.send({
            url:       url,
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this._customActionOnSuccess();
                    dojo.publish(phpr.module + '.updateCacheData');
                    if (this._isSystemModule) {
                        dojo.publish(phpr.module + '.setUrlHash', [phpr.parentmodule, null, [phpr.module]]);
                    } else {
                        dojo.publish(phpr.module + '.setUrlHash', [phpr.parentmodule]);
                    }
                }
            })
        });
    },

    _customActionOnSuccess:function() {
        // Summary:
        //    Function for be rewritten.
    },

    _deleteForm:function() {
        // Summary:
        //    Delete an item.
        phpr.send({
            url:       phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonDelete/id/' + this._id,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    dojo.publish(phpr.module + '.updateCacheData');
                    dojo.publish(phpr.module + '.setUrlHash', [phpr.parentmodule, null, [phpr.module]]);
                }
            })
        });
    }
});
