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

    initData:function() {
        // Summary:
        //    Init all the data before draw the form
        // Description:
        //    This function call all the needed data before the form is drawed
        //    The form will wait for all the data are loaded.
        this.inherited(arguments);
        if (this.id > 0) {
            this._peopleUrl = phpr.webpath + 'index.php/Minutes/index/jsonListUser/id/' + this.id;
            this._initData.push({'url': this._peopleUrl});
        }
    },

    addModuleTabs:function(data) {
        // Summary:
        //    Add default module tabs plus Items and mail tabs
        // Description:
        //    Extends inherited method to add the Items and mail tabs,
        this.inherited(arguments);

        // Render additional tabs only if there is an ID
        // (these tabs don't make sense for unsaved records)
        if (this.id > 0) {
            this.addMailTab(data);
        }
    },

    addMailTab:function(data) {
        // Summary:
        //    Mail tab
        // Description:
        //    Display options for sending Minutes per mail
        var mailForm = this.render(["phpr.Minutes.template", "minutesMailForm.html"], null, {
            'id':            this.id,
            'people':        phpr.DataStore.getData({url: this._peopleUrl}),
            'lblRecipients': phpr.nls.get('Recipients'),
            'lblAdditional': phpr.nls.get('Additional Recipients'),
            'lblTooltip':    phpr.nls.get('Email addresses of unlisted recipients, comma-separated.'),
            'lblOptions':    phpr.nls.get('Options'),
            'lblAttachPdf':  phpr.nls.get('Include PDF attachment'),
            'lblSendMail':   phpr.nls.get('Send mail'),
            'lblPreview':    phpr.nls.get('Preview')
        });

        this.addTab(mailForm, 'tabMail', 'Mail', 'mailFormTab');

        dojo.connect(dijit.byId('minutesMailFormSend'), 'onClick', dojo.hitch(this, function() {
            phpr.send({
                url: phpr.webpath + 'index.php/Minutes/index/jsonSendMail/nodeId/' + phpr.currentProjectId
                    + '/id/' + this.id,
                content:   dijit.byId('mailFormTab').get('value'),
                onSuccess: dojo.hitch(this, function(data) {
                    new phpr.handleResponse('serverFeedback', data);
                })
            })
        }));

        dojo.connect(dijit.byId('minutesMailFormPreview'), 'onClick', dojo.hitch(this, function() {
            window.open(phpr.webpath + 'index.php/Minutes/index/pdf/nodeId/' + phpr.currentProjectId
                + '/id/' + this.id + '/csrfToken/' + phpr.csrfToken, 'pdf');
        }));
    },

    postRenderForm:function() {
        // Summary:
        //    Keep the itemStatus value for future use
        var data         = phpr.DataStore.getData({url: this._url});
        this._itemStatus = data[0].itemStatus;
    },

    prepareSubmission: function() {
        // Summary:
        //    Gathers data for form submission and displays confirm dialogs if needed
        // Description:
        //    Overrides functionality of parent class' method: Checks for itemStatus
        //    property and displays dialogs to the user to confirm his actions whenever
        //    the status changes from/to 4 (finalized). Displays an informal dialog
        //    when a save action is attempted while status remains at 4 (not possible).
        var result = this.inherited(arguments);
        if (result) {
            // Check for status. possible states are:
            // 1#Planned|2#Created|3#Filled|4#Final
            if (this._itemStatus == 4 && this.sendData.itemStatus != 4) {
                // Finalized form is about to be made writeable again,
                // check for write rights and ask permission:
                if (!this._allowSubmit) {
                    this.displayConfirmDialog({
                        title:   phpr.nls.get('Unfinalize Minutes'),
                        message: phpr.nls.get('Are you sure this Minutes entry should no longer be finalized?')
                            + '<br />' + phpr.nls.get('After proceeding, changes to the data will be possible again.')
                    });
                    result = false;
                }
            } else if (this._itemStatus != 4 && this.sendData.itemStatus == 4) {
                // Writeable form is about to be finalized,
                // ask for sanity check and permission:
                if (!this._allowSubmit) {
                    this.displayConfirmDialog({
                        title:   phpr.nls.get('Finalize Minutes'),
                        message: phpr.nls.get('Are you sure this Minutes entry should be finalized?')
                            + '<br />' + phpr.nls.get('Write access will be prohibited!')
                    });
                    result = false;
                }
            } else if (this._itemStatus == 4 && this.sendData.itemStatus == 4) {
                // Form is finalized and settings have not changed, display
                // informal message that form can't be saved.
                this.displayConfirmDialog({
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

    displayConfirmDialog: function(options) {
        // Summary:
        //    Display a modal dialog
        // Description:
        //    Displays a configurable dijit.Dialog. Uses external template
        //    'confirmDialog.html'. No return value as dialog runs asynchronously.
        //    User input must be processed using event handlers "callbackOk" and
        //    "callbackCancel".
        if (!options) {
            options = [];
        }
        var defaults = {
            title:          phpr.nls.get('Confirm'),
            message:        phpr.nls.get('Are you sure?'),
            displayButtons: true,
            labelOK:        phpr.nls.get('OK'),
            labelCancel:    phpr.nls.get('Cancel'),
            callbackOk:     dojo.hitch(this, function(e) {
                this._allowSubmit = true;
                confirmDialog.hide();
                confirmDialog.destroyRecursive();
                this.submitForm();
            }),
            callbackCancel: dojo.hitch(this, function(e) {
                this._allowSubmit = false;
                confirmDialog.hide();
                confirmDialog.destroyRecursive();
            })
        };
        options = dojo.mixin(defaults, options);
        var content = this.render(["phpr.Minutes.template", "confirmDialog.html"], null, {
            message:        options.message,
            displayButtons: options.displayButtons,
            labelOK:        options.labelOK,
            labelCancel:    options.labelCancel
        });

        var confirmDialog = new dijit.Dialog({
            title:   options.title,
            content: content
        });

        dojo.body().appendChild(confirmDialog.domNode);
        confirmDialog.startup();
        dojo.connect(dijit.byId('minutesConfirmDialogButtonOK'), 'onClick', options.callbackOk);
        dojo.connect(dijit.byId('minutesConfirmDialogButtonCancel'), 'onClick', options.callbackCancel);
        confirmDialog.show();
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this form
        // Description:
        //    Delete the cache for this form
        this.inherited(arguments);
        phpr.DataStore.deleteData({url: this._peopleUrl});
    }
});
