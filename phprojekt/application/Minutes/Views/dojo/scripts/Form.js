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
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Markus Wolff <markus.wolff@mayflower.de>
 */

dojo.provide("phpr.Minutes.Form");

dojo.declare("phpr.Minutes.Form", phpr.Default.Form, {
    // Request url for get the data
    _peopleUrl: null,

    // Global flag needed for confirm dialogs
    _allowSubmit: false,

    // Internal var for keep the itemStatus value
    _itemStatus: 0,

    updateData:function() {
        // Summary:
        //    Delete the cache for this form.
        this.inherited(arguments);
        phpr.DataStore.deleteData({url: this._peopleUrl});
    },

    /************* Private functions *************/

    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
        this.inherited(arguments);
        if (this._id > 0) {
            this._peopleUrl = phpr.webpath + 'index.php/Minutes/index/jsonListUser/id/' + this._id;
            this._initDataArray.push({'url': this._peopleUrl});
        }
    },

    _addModuleTabs:function(data) {
       // Summary:
        //    Add extra tabs.
        // Description:
        //    Extends inherited method to add the Mail tab.
        this.inherited(arguments);

        // Render additional tabs only if there is an Id
        // (these tabs don't make sense for unsaved records)
        if (this._id > 0) {
            this._addMailTab(data);
        }
    },

    _addMailTab:function(data) {
        // Summary:
        //    Mail tab.
        // Description:
        //    Display options for sending Minutes per mail.
        var tableTabId = 'mail';

        // Init the table
        this._fieldTemplate.createTable(tableTabId);

        // Recipients
        var fieldValues = {
            type:     'multipleselectbox',
            id:       'recipients',
            label:    phpr.nls.get('Recipients'),
            tab:      tableTabId,
            disabled: false,
            required: false,
            range:    phpr.DataStore.getData({url: this._peopleUrl}),
            value:    '',
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // Additional recipients
        var fieldValues = {
            type:     'text',
            id:       'additional',
            label:    phpr.nls.get('Additional Recipients'),
            tab:      tableTabId,
            disabled: false,
            required: false,
            value:    '',
            hint:     phpr.nls.get('Email addresses of unlisted recipients, comma-separated.')
        };
        this._fieldTemplate.addRow(fieldValues);

        // Pdf check
        var fieldValues = {
            type:     'checkbox',
            id:       'pdf',
            label:    phpr.nls.get('Include PDF attachment'),
            tab:      tableTabId,
            disabled: false,
            required: false,
            value:    false,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // Preview button
        var fieldValues = {
            type:   'buttonAction',
            id:     'mailPreview',
            label:  phpr.nls.get('Preview'),
            text:   phpr.nls.get('View'),
            tab:    tableTabId,
            icon:   '',
            action: dojo.hitch(this, function() {
                window.open(phpr.webpath + 'index.php/Minutes/index/pdf/nodeId/' + phpr.currentProjectId
                    + '/id/' + this._id + '/csrfToken/' + phpr.csrfToken, 'pdf');
            }),
            hint: ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // Send Mail button
        var fieldValues = {
            type:   'buttonAction',
            id:     'mailSend',
            label:  phpr.nls.get('Send mail'),
            text:   phpr.nls.get('Send'),
            tab:    tableTabId,
            icon:   '',
            action: dojo.hitch(this, function() {
                var content = dijit.byId('mailFormTab-' + this._module).get('value');
                // Delete the module string
                for (var index in content) {
                    var newIndex       = index.substr(0, index.length - 1 - this._module.length);
                    content[newIndex] = content[index];
                    delete content[index];
                }
                phpr.send({
                    url: phpr.webpath + 'index.php/Minutes/index/jsonSendMail/nodeId/' + phpr.currentProjectId
                        + '/id/' + this._id,
                    content:   content,
                    onSuccess: dojo.hitch(this, function(data) {
                        new phpr.handleResponse('serverFeedback', data);
                    })
                })
            }),
            hint: ''
        };

        this._fieldTemplate.addRow(fieldValues);

        // Add the tab to the form
        var tabId  = 'tabMail-' + this._module;
        var formId = 'mailFormTab-' + this._module;
        this._addTab(this._fieldTemplate.getTable(tableTabId), tabId, 'Mail', formId);
    },

    _postRenderForm:function() {
        // Summary:
        //    Keep the itemStatus value for future use.
        var data         = phpr.DataStore.getData({url: this._url});
        this._itemStatus = data[0].itemStatus;
    },

    _prepareSubmission: function() {
        // Summary:
        //    Gathers data for form submission and displays confirm dialogs if needed.
        // Description:
        //    Overrides functionality of parent class' method:
        //    Checks for itemStatus property and displays dialogs to the user to confirm his actions
        //    whenever the status changes from/to 4 (finalized).
        //    Displays an informal dialog when a save action is attempted while status remains at 4 (not possible).
        var result = this.inherited(arguments);
        if (result) {
            // Check for status. Possible states are:
            // 1#Planned|2#Created|3#Filled|4#Final
            if (this._itemStatus == 4 && this._sendData.itemStatus != 4) {
                // Finalized form is about to be made writeable again,
                // check for write rights and ask permission:
                if (!this._allowSubmit) {
                    this._displayConfirmDialog({
                        title:   phpr.nls.get('Unfinalize Minutes'),
                        message: phpr.nls.get('Are you sure this Minutes entry should no longer be finalized?')
                            + '<br />' + phpr.nls.get('After proceeding, changes to the data will be possible again.'),
                        displayButtons: true
                    });
                    result = false;
                }
            } else if (this._itemStatus != 4 && this._sendData.itemStatus == 4) {
                // Writeable form is about to be finalized,
                // ask for sanity check and permission:
                if (!this._allowSubmit) {
                    this._displayConfirmDialog({
                        title:   phpr.nls.get('Finalize Minutes'),
                        message: phpr.nls.get('Are you sure this Minutes entry should be finalized?')
                            + '<br />' + phpr.nls.get('Write access will be prohibited!'),
                        displayButtons: true
                    });
                    result = false;
                }
            } else if (this._itemStatus == 4 && this._sendData.itemStatus == 4) {
                // Form is finalized and settings have not changed,
                // display informal message that form can't be saved.
                this._displayConfirmDialog({
                    title:   phpr.nls.get('Minutes are finalized'),
                    message: phpr.nls.get('This Minutes entry is finalized.')
                        + '<br />' + phpr.nls.get('Editing data is no longer possible.')
                        + '<br />' + phpr.nls.get('Your changes have not been saved.'),
                    displayButtons: false
                });
                result = false;
            } else {
                // All is well, proceed with submission
                result = true;
            }
        }

        // Reset flag to initial value of false for next run
        this._allowSubmit = false;

        return result;
    },

    _getFieldForDelete:function() {
        // Summary:
        //    Return an array of fields for delete.
        var fields = this.inherited(arguments);

        // Mail tab
        fields.push('recipients[]');
        fields.push('additional');
        fields.push('pdf');

        return fields;
    },

    _displayConfirmDialog: function(options) {
        // Summary:
        //    Display a modal dialog.
        // Description:
        //    Displays a configurable dijit.Dialog.
        //    No return value as dialog runs asynchronously.
        var confirmDialog = dijit.byId('dialogContainer-' + this._module);
        if (!confirmDialog) {
            // Create the dialog
            var container = new dijit.layout.ContentPane({
                region: 'center'
            }, document.createElement('div'));

            var content = new dijit.layout.ContentPane({
                id:    'confirmDialogContent-' + this._module,
                region: 'center'
            }, document.createElement('div'));

            var buttonsDiv = new dijit.layout.ContentPane({
                id:    'confirmDialogButtonContent-' + this._module,
                region: 'bottom',
                gutter: 'yes',
                style:  'display: none;'
            }, document.createElement('div'));
            var okButton = new dijit.form.Button({
                label:     phpr.nls.get('OK'),
                baseClass: 'positive',
                iconClass: 'tick',
                style:     'float: left;',
                onClick:   dojo.hitch(this, function(e) {
                    this._allowSubmit = true;
                    dijit.byId('dialogContainer-' + this._module).hide();
                    this._submitForm();
                })
            });
            var canecelButton = new dijit.form.Button({
                label:     phpr.nls.get('Cancel'),
                baseClass: 'negative',
                iconClass: 'cross',
                style:     'float: right;',
                onClick:   dojo.hitch(this, function(e) {
                    this._allowSubmit = false;
                    dijit.byId('dialogContainer-' + this._module).hide();
                })
            });
            buttonsDiv.domNode.appendChild(okButton.domNode);
            buttonsDiv.domNode.appendChild(canecelButton.domNode);

            container.domNode.appendChild(content.domNode);
            container.domNode.appendChild(dojo.create('br'));
            container.domNode.appendChild(buttonsDiv.domNode);

            var confirmDialog = new dijit.Dialog({
                id:      'dialogContainer-' + this._module,
                content: container
            });

        } else {
            var content    = dijit.byId('confirmDialogContent-' + this._module);
            var buttonsDiv = dijit.byId('confirmDialogButtonContent-' + this._module);
        }

        // Set Title
        confirmDialog.set('title', (options.title) ? options.title : phpr.nls.get('Confirm'));

        // Set message
        var message = (options.message) ? options.message : phpr.nls.get('Are you sure?');
        content.set('content', message);

        if (options.displayButtons) {
            buttonsDiv.domNode.style.display = 'inline';
        } else {
            buttonsDiv.domNode.style.display = 'none';
        }

        confirmDialog.show();
    }
});
