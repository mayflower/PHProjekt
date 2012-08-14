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
 * @subpackage Contact
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Contact.Form");

dojo.declare("phpr.Contact.Form", phpr.Default.DialogForm, {

    initData: function() {
        // Get all the active users
        this.userStore = new phpr.Default.System.Store.User();
        this._initData.push({'store': this.userStore});
    },

    addModuleTabs: function(data) {
        return this.addHistoryTab();
    },

    addBasicFields: function() {
    },

    submitForm: function() {
        if (!this.prepareSubmission()) {
            return false;
        }

        this.setSubmitInProgress(true);
        phpr.send({
            url: 'index.php/' + phpr.module + '/index/jsonSave/nodeId/' + phpr.currentProjectId +
                '/id/' + this.id,
            content:   this.sendData
        }).then(dojo.hitch(this, function(data) {
            this.setSubmitInProgress(false);
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (!this.id) {
                    this.id = data.id;
                }
                if (data.type == 'success') {
                    this.publish("updateCacheData");
                    this.publish("setUrlHash", [phpr.module]);
                }
            }
        }));
    },

    deleteForm: function() {
        phpr.send({
            url: 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this.id
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");
                    this.publish("setUrlHash", [phpr.module]);
                }
            }
        }));
    },

    updateData: function() {
        phpr.DataStore.deleteData({url: this._url});
    }
});
