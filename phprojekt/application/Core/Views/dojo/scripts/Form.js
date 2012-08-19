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

dojo.provide("phpr.Core.Form");

dojo.declare("phpr.Core.Form", phpr.Default.Form, {
    setUrl: function() {
        // Summary:
        //    Rewritten the function for work like a system module and like a form
        // Description:
        //    Rewritten the function for work like a system module and like a form
        if (
            (this.main.action && this.main.isSystemModule(this.main.action)) ||
            (!this.main.action && this.main.isSystemModule(this.main.module))
           ) {
            this._url = 'index.php/Core/' + this.main.module.toLowerCase() + '/jsonDetail/nodeId/1/id/' +
                this.id;
        } else {
            this._url = 'index.php/Core/' + this.main.module.toLowerCase() + '/jsonDetail/nodeId/1/' +
                'moduleName/' + this.main.action;
        }
    },

    initData: function() {
    },

    setPermissions: function(data) {
        this._writePermissions  = true;
        this._deletePermissions = false;
        if (this.id > 0) {
            this._deletePermissions = true;
        }
        this._accessPermissions = true;
    },

    addBasicFields: function() {
    },

    addModuleTabs: function(data) {
    },

    useCache: function() {
        if (this.main.isSystemModule(this.main.module)) {
            return true;
        } else {
            return false;
        }
    },

    submitForm: function() {
        // Summary:
        //    Rewritten the function for work like a system module and like a form
        // Description:
        //    Rewritten the function for work like a system module and like a form
        if (!this.prepareSubmission()) {
            return false;
        }

        var moduleName, url;
        if ((this.main.action && this.main.isSystemModule(this.main.action)) ||
            (!this.main.action && this.main.isSystemModule(this.main.module))
           ) {
            url = 'index.php/Core/' + this.main.module.toLowerCase() + '/jsonSave/nodeId/1/id/' + this.id;

            moduleName = this.main.module;
        } else {
            url = 'index.php/Core/' + this.main.module.toLowerCase() +
                '/jsonSave/nodeId/1/moduleName/' + this.main.action;

            moduleName = this.main.action;
        }

        this.setSubmitInProgress(true);
        phpr.send({
            url: url,
            content: this.sendData
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                this.setSubmitInProgress(false);
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.customActionOnSuccess(moduleName, this.sendData);
                    this.publish("updateCacheData");
                    phpr.pageManager.modifyCurrentState(
                        { id: undefined },
                        { forceModuleReload: true }
                    );
                }
            }
        }));
    },

    customActionOnSuccess: function() {
        // Summary:
        //    Function for be rewritten
        // Description:
        //    Function for be rewritten
    },

    deleteForm: function() {
        phpr.send({
            url: 'index.php/Core/' + this.main.action.toLowerCase() + '/jsonDelete/id/' + this.id
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");

                    phpr.pageManager.modifyCurrentState(
                        { id: undefined },
                        { forceModuleReload: true }
                    );
                }
            }
        }));
    },

    updateData: function() {
        phpr.DataStore.deleteData({url: this._url});
    },

    setBreadCrumbItem: function(itemValue) {
        phpr.BreadCrumb.setItem(itemValue);
    }
});

dojo.declare("phpr.Core.DialogForm", [phpr.Core.Form, phpr.Default.DialogForm], {
});
