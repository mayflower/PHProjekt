/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @author     Markus Wolff <markus.wolff@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Minutes.Form");
dojo.provide("phpr.Minutes.ItemGrid");

dojo.declare("phpr.Minutes.Form", phpr.Default.Form, {
    // Current item id
    itemId: null,

    // Request url for get the data
    _peopleUrl:   null,
    _itemGridUrl: null,
    _itemUrl:     null,

    // List of item types. used as cache.
    _itemTypes: [],

    // Global flag needed for confirm dialogs
    _allowSubmit: false,

    initData:function() {
        // Summary:
        //    Init all the data before draw the form
        // Description:
        //    This function call all the needed data before the form is drawed
        //    The form will wait for all the data are loaded.
        this.inherited(arguments);
        if (this.id > 0) {
            this._peopleUrl = phpr.webpath + "index.php/Minutes/index/jsonListUser/id/" + this.id;
            this._initData.push({'url': this._peopleUrl});

            this._itemsUrl = phpr.webpath + "index.php/Minutes/item/jsonListItemSortOrder/minutesId/" + this.id;
            this._initData.push({'url': this._itemsUrl, 'noCache': true});
        }
    },

    addModuleTabs:function(data) {
        // Summary:
        //    Add default module tabs plus Items tab
        // Description:
        //    Extends inherited method to add the Items tab,
        this.inherited(arguments);

        // render additional tabs only if there is an ID
        // (these tabs don't make sense for unsaved records)
        if (this.id > 0) {
            this.addItemsTab(data);
            this.addMailTab(data);
        }
    },

    addItemsTab:function(data) {
        // Summary:
        //    Items tab
        // Description:
        //    Display minute items grid and input form.
        //    See Default/Form.js, method addAccessTab for
        //    a more detailed example of adding tabs.
        this.addTab('', 'tabItems', phpr.nls.get('Items'), 'itemsFormTab');

        dojo.byId('itemsFormTab').style.display = 'none';

        if (!dijit.byId('minutesBox')) {
            var minutesBox = new dijit.layout.ContentPane({
                region: 'center',
                id:     'minutesBox'
            }, dojo.doc.createElement('div'));
        } else {
            var minutesBox = dijit.byId('minutesBox');
        }

        if (!dijit.byId('minutesLayout')) {
            var minutesLayout = new dijit.layout.BorderContainer({
                design: 'sidebar',
                id:     'minutesLayout'
            }, dojo.doc.createElement('div'));
        } else {
            var minutesLayout = dijit.byId('minutesLayout');
        }

        if (!dijit.byId('minutesGridBox')) {
            var minutesGridBox = new dijit.layout.ContentPane({
                region: 'center',
                id:     'minutesGridBox'
            }, dojo.doc.createElement('div'));
        } else {
            var minutesGridBox = dijit.byId('minutesGridBox');
        }

        if (!dijit.byId('minutesDetailsRight')) {
            var minutesDetailsRight = new dijit.layout.ContentPane({
                region: 'right',
                id:     'minutesDetailsRight',
                style:  'width: 50%;'
            }, dojo.doc.createElement('div'));
        } else {
            var minutesDetailsRight = dijit.byId('minutesDetailsRight');
        }

        minutesLayout.addChild(minutesGridBox);
        minutesLayout.addChild(minutesDetailsRight);
        minutesBox.attr("content", minutesLayout.domNode);
        dijit.byId('tabItems').attr('content', minutesBox.domNode);
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
            'lblComment':    phpr.nls.get('Comment'),
            'lblOptions':    phpr.nls.get('Options'),
            'lblAttachPdf':  phpr.nls.get('Include PDF attachment'),
            'lblSendMail':   phpr.nls.get('Send mail'),
            'lblPreview':    phpr.nls.get('Preview')
        });

        this.addTab(mailForm, 'tabMail', phpr.nls.get('Mail'), 'mailFormTab');

        new dijit.Tooltip({
            connectId:  ['minutesMailFormAdditionalRecipientsTooltip'],
            label:      phpr.nls.get('Email addresses of unlisted recipients, comma-separated.')
        });

        dojo.connect(dijit.byId('minutesMailFormSend'), 'onClick', function() {
            phpr.send({
                url:       phpr.webpath + 'index.php/Minutes/index/jsonSendMail/',
                content:   dojo.formToObject('mailFormTab'),
                onSuccess: dojo.hitch(this, function(data) {
                    new phpr.handleResponse('serverFeedback', data);
                })
            })
        });

        dojo.connect(dijit.byId('minutesMailFormPreview'), 'onClick', dojo.hitch(this, function() {
            window.open(phpr.webpath + 'index.php/Minutes/index/pdf/id/' + this.id, 'pdf');
        }));
    },

    postRenderForm: function() {
        // Summary:
        //    Render grid
        // Description:
        //    Render the datagrid after the rest of the form has been
        //    processed. Neccessary because the datagrid won't render
        //    unless dimensions of all surrounding elements are known.
        var tabs = this.form;
        dojo.connect(tabs, "selectChild", dojo.hitch(this, function(child) {
            if (child.id == 'tabItems') {
                this._itemGridUrl = phpr.webpath + "index.php/Minutes/item/jsonList/minutesId/" + this.id;
                phpr.DataStore.addStore({"url": this._itemGridUrl});
                phpr.DataStore.requestData({"url": this._itemGridUrl, processData: dojo.hitch(this, "_buildGrid")});
            }
        }));
    },

    _buildGrid: function() {
        // Summary:
        //    Return grid object instance
        // Description:
        //    Internal method for creating and configuring a grid object for MinutesItems.
        //    Row configuration is defined here.
        this.loadSubForm();
        var self   = this;
        var layout = [{
            cells: [[
                     {
                         name:    phpr.nls.get('Topic'),
                         field:   'topicId',
                         styles:  "text-align: center;",
                         width:   '5%',
                         rowSpan: 2
                     },
                     {
                         name:   phpr.nls.get('Title'),
                         field:  'title',
                         styles: "text-align: left;",
                         width:  '50%'
                     },
                     {
                         name:   phpr.nls.get('Type'),
                         field:  'topicType',
                         styles: "text-align: center;",
                         width:  '10%',
                         type:   dojox.grid.cells.Select,
                         formatter:function(value) {
                             var typeList = self.getItemTypes();
                             for (var i = 0; i < typeList.length; i++) {
                                 if (typeList[i].id && typeList[i].id == value) {
                                     return typeList[i].name;
                                 }
                             }
                             return value;
                         }
                     },
                     {
                         name:   phpr.nls.get('Date'),
                         field:  'topicDate',
                         styles: "text-align: center;",
                         width:  '15%',
                         formatter:function(value) {
                             if (value == "0000-00-00") {
                                 return '';
                             }
                             return value;
                         }
                     },
                     {
                         name:   phpr.nls.get('Who'),
                         field:  'userId',
                         styles: "text-align: center;",
                         width:  '20%',
                         formatter:function(value) {
                             var userList = phpr.DataStore.getData({url: self._peopleUrl});
                             for(var i=0; i < userList.length; i++) {
                                 if (userList[i].id && userList[i].id == value) {
                                     return userList[i].display;
                                 }
                             }
                             return '';
                         }
                     },
                 ],[
                     {
                         name:    phpr.nls.get('Comment'),
                         field:   'comment',
                         styles:  "text-align: left;",
                         width:   '100%',
                         colSpan: 4
                     }
                 ]]
            }];

        var store = this._getItemGridStore();
        var grid  = new dojox.grid.DataGrid({
            store:     store,
            structure: layout
        }, document.createElement('div'));

        dojo.connect(grid, 'onRowClick', dojo.hitch(this, function(e) {
            var data      = e.grid.getItem(e.rowIndex);
            this.itemId   = data.id;
            this._itemUrl = phpr.webpath + 'index.php/Minutes/item/jsonDetail/minutesId/' + this.id + '/id/' + data.id,
            phpr.DataStore.addStore({url: this._itemUrl});
            phpr.DataStore.requestData({url: this._itemUrl, processData: dojo.hitch(this, function() {
                this.loadSubForm();
            })});
        }));

        dijit.byId('minutesGridBox').attr('content', grid.domNode);
        grid.startup();
    },

    _getItemGridStore: function() {
        // Summary:
        //    Return data store for the grid
        // Description:
        //    Creates Dojo.Data.ItemFileWriteStore instance for
        //    displaying MinutesItems in the grid.
        var content  = dojo.clone(phpr.DataStore.getData({url: this._itemGridUrl}));
        var gridData = {items: new Array()};
        for (var i in content) {
            gridData.items.push(content[i]);
        }
        return new dojo.data.ItemFileWriteStore({"data": gridData});
    },

    updateGrid: function() {
        // Summary:
        //    Refreshes the grid's data source
        // Description:
        //    Recreated the data store for the grid, which automatically
        //    updates the view
        phpr.DataStore.deleteData({"url": this._itemGridUrl});
        phpr.DataStore.requestData({"url": this._itemGridUrl, processData: dojo.hitch(this, "_buildGrid")});
    },

    saveSubFormData: function() {
        // Summary:
        //    Save the detail form for a MinutesItem
        // Description:
        //    Retrieves the data from the detail form, posts it to
        //    the sever and refreshes the grid to reflect changes.
        var sendData    = new Array();
        var formsWidget = dijit.byId("minutesItemForm");
        if (!formsWidget.isValid()) {
            formsWidget.validate();
            return false;
        }
        sendData = dojo.mixin(sendData, formsWidget.attr('value'));

        phpr.send({
            url:       phpr.webpath + 'index.php/Minutes/item/jsonSave/minutesId/' + this.id + '/id/' + this.itemId,
            content:   sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    var itemUrl = phpr.webpath + 'index.php/Minutes/item/jsonDetail/minutesId/' + this.id + '/id/'
                        + this.itemId;
                    phpr.DataStore.deleteData({url: itemUrl});
                    this.itemId = null;
                    this.updateGrid();
                }
            })
        });
    },

    deleteSubFormData: function() {
        // Summary:
        //    Deletes the currently active MinutesItem
        // Description:
        //    Posts current form data to the server.
        //    Resets detail form to default values, allowing to enter a new record.
        phpr.send({
            url:       phpr.webpath + 'index.php/Minutes/item/jsonDelete/minutesId/' + this.id + '/id/' + this.itemId,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                    var itemUrl = phpr.webpath + 'index.php/Minutes/item/jsonDetail/minutesId/' + this.id + '/id/'
                        + this.itemId;
                    phpr.DataStore.deleteData({url: itemUrl});
                    this.itemId = null;
                    this.updateGrid();
                }
            })
        });
    },

    getItemTypes: function() {
        // Summary:
        //    Returns a list of item types
        // Description:
        //    Will return a list of valid item types for MinutesItems from metadata.
        //    Uses cached property if available.
        if (this._itemTypes.length == 0) {
            meta = dojo.clone(phpr.DataStore.getMetaData({url: this._itemGridUrl}));
            for (var i = 0; i < meta.length; i++) {
                if (meta[i]['key'] == 'topicType') {
                    this._itemTypes = meta[i]['range'];
                }
            }
        }
        return this._itemTypes;
    },

    loadSubForm: function() {
        // Summary:
        //    Load detail form and populate it
        // Description:
        //    Loads the detail form template for MinutesItems and populates it with
        //    either default data or data from a loaded MinutesItem. Also registers
        //    event listeners for various detail form behaviours.
        if (this.itemId && this.itemId > 0) {
            var itemFormData = dojo.clone(phpr.DataStore.getData({url: this._itemUrl})).shift();
        } else {
            // Use default empty dataset
            var itemFormData = {
                topicType:  1,
                userId:     0,
                sortOrder:  0,
                title:      '',
                comment:    '',
                topicDate:  ''
            };
        }

        var placeholders = {
            lblTitle:       phpr.nls.get('Title'),
            lblComment:     phpr.nls.get('Comment'),
            lblUserId:      phpr.nls.get('Who'),
            lblTopicType:   phpr.nls.get('Type'),
            lblTopicDate:   phpr.nls.get('Date'),
            lblParentOrder: phpr.nls.get('Sort'),
            lblSubmit:      phpr.nls.get('Save'),
            lblDelete:      phpr.nls.get('Delete'),
            lblClear:	    phpr.nls.get('New'),
            parentOrder:    itemFormData.sortOrder - 1 >= 0 ? itemFormData.sortOrder - 1 : 0,
            users:          phpr.DataStore.getData({url: this._peopleUrl}),
            items:          phpr.DataStore.getData({url: this._itemsUrl}),
            types:          this.getItemTypes(),
            editItem:       this.itemId > 0,
            lblRequired:    phpr.nls.get('Required Field')
        };
        placeholders = dojo.mixin(placeholders, itemFormData);

        // Render the template
        this.render(["phpr.Minutes.template", "minutesItemForm.html"], dojo.byId('minutesDetailsRight'),
            placeholders);

        // Connect save/delete events to buttons
        dojo.connect(dijit.byId('minutesItemFormSubmit'), 'onClick', dojo.hitch(this, this.saveSubFormData));
        dojo.connect(dijit.byId('minutesItemFormDelete'), 'onClick', dojo.hitch(this, this.deleteSubFormData));

        // Have reset button reload the form using defaults
        dojo.connect(dijit.byId('minutesItemFormClear'), 'onClick', dojo.hitch(this, function() {
            this.itemId = null;
            this.loadSubForm();
        }));

        // Have the appropriate input fields appear for each type
        this._switchItemFormFields(placeholders.topicType); // defaults
        dojo.connect(dijit.byId('minutesItemFormTopicType'), 'onChange', dojo.hitch(this, this._switchItemFormFields));

        // Set cursor to title field when all is done
        dojo.byId("minutesItemFormTitle").focus();
    },

    _switchItemFormFields: function(typeValue) {
        // Summary:
        //    Toggle visibility of detail form fields
        // Description:
        //    Hides or shows the appropriate form fields for the currently
        //    selected topicType. Currently registered types are:
        //    1='Topic', 2='Statement',3='TODO',4='Decision',5='Date'
        var display = (dojo.isIE) ? 'block' : 'table-row';
        switch(parseInt(typeValue)) {
            case 3:
                dojo.style(dojo.byId('minutesItemFormRowUser'), "display", display);
                dojo.style(dojo.byId('minutesItemFormRowDate'), "display", display);
                dijit.byId('minutesItemFormUserId').attr("disabled", false);
                dijit.byId('topicDate').attr("disabled", false);
                break;
            case 5:
                dojo.style(dojo.byId('minutesItemFormRowUser'), "display", "none");
                dojo.style(dojo.byId('minutesItemFormRowDate'), "display", display);
                dijit.byId('minutesItemFormUserId').attr("disabled", true);
                dijit.byId('topicDate').attr("disabled", false);
                break;
            default:
                dojo.style(dojo.byId('minutesItemFormRowUser'), "display", "none");
                dojo.style(dojo.byId('minutesItemFormRowDate'), "display", "none");
                dijit.byId('minutesItemFormUserId').attr("disabled", true);
                dijit.byId('topicDate').attr("disabled", true);
                break;
        }
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
            var data = phpr.DataStore.getData({url: this._url});
            // check for status. possible states are:
            // 1#Planned|2#Created|3#Filled|4#Final
            if (data[0].itemStatus == 4 && this.sendData.itemStatus != 4) {
                // finalized form is about to be made writeable again,
                // check for write rights and ask permission:
                if (!this._allowSubmit) {
                    this.displayConfirmDialog({
                        title:   phpr.nls.get('Unfinalize Minutes'),
                        message: phpr.nls.get('Are you sure this Minutes entry should no longer be finalized?')
                            + '<br />' + phpr.nls.get('After proceeding, changes to the data will be possible again.')
                    });
                    result = false;
                }
            } else if (data[0].itemStatus != 4 && this.sendData.itemStatus == 4) {
                // writeable form is about to be finalized,
                // ask for sanity check and permission:
                if (!this._allowSubmit) {
                    this.displayConfirmDialog({
                        title:   phpr.nls.get('Finalize Minutes'),
                        message: phpr.nls.get('Are you sure this Minutes entry should be finalized?')
                            + '<br />' + phpr.nls.get('Write access will be prohibited!')
                    });
                    result = false;
                }
            } else if (data[0].itemStatus == 4 && this.sendData.itemStatus == 4) {
                // form is finalized and settings have not changed, display
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
                // all is well, proceed with submission
                result = true;
            }
        }
        // reset flag to initial value of false for next run
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
