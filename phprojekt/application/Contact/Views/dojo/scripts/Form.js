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
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Contact.Form");

dojo.declare("phpr.Contact.Form", phpr.Default.Form, {
    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
        // Get all the active users
        this._userStore = new phpr.Store.User();
        this._initDataArray.push({'store': this._userStore});
    },

    _addModuleTabs:function(data) {
        // Summary:
        //    Add extra tabs.
        // Description:
        //    Show only history tab for Contacts.
        this._addHistoryTab();
    },

    _addBasicFields:function() {
        // Summary:
        //    Add some special fields.
        // Description:
        //    Remove tag field for Contacts.
    },

    _submitForm:function() {
        // Summary:
        //    Submit the forms.
        // Description:
        //    Remove save tags for Contacts.
        if (!this._prepareSubmission()) {
            return false;
        }

        phpr.send({
            url: phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/nodeId/' + phpr.currentProjectId
                + '/id/' + this._id,
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (!this._id) {
                    this._id = data['id'];
                }
                if (data.type == 'success') {
                    dojo.publish(phpr.module + '.updateCacheData');
                    dojo.publish(phpr.module + '.setUrlHash', [phpr.module]);
                }
            })
        });
    },

    _deleteForm:function() {
        // Summary:
        //    Delete an item.
        // Description:
        //    Remove delete tags for Contacts.
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this._id,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    dojo.publish(phpr.module + '.updateCacheData');
                    dojo.publish(phpr.module + '.setUrlHash', [phpr.module]);
                }
            })
        });
    }
});
