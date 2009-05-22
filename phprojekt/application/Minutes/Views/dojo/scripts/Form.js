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
    addModuleTabs:function(data) {
        // summary:
        //    Add default module tabs plus Items tab
        // description:
        //    Extends inherited method to add the Items tab,
        //    inherited code is executed afterwards
        this.addItemsTab(data);
        this.inherited(arguments);
    },
    
    addItemsTab:function(data) {
        // summary:
        //    Access tab
        // description:
        //    Display minute items grid and input form.
        //    See Default/Form.js, method addAccessTab for
        //    a more detailed example of adding tabs.
        
        this.getInvitedList(); // preload invited users list to populate subform selectbox
        this.getItemList(); // preload item sort list
        
        var itemsData = this.render(["phpr.Minutes.template", "minutesItemGrid.html"], null, {
            // no placeholders used atm.
        });
        
        this.addTab(itemsData, 'tabItems', 'Items', 'itemsFormTab');        
    },
    
    postRenderForm: function() {
        // summary:
        //    Render grid
        // description:
        //    Render the datagrid after the rest of the form has been 
        //    processed. Neccessary because the datagrid won't render
        //    unless dimensions of all surrounding elements are known.
        
        var tabs = this.form;
        dojo.connect(tabs, "selectChild", dojo.hitch(this, function(child) {
            if (child.id == 'tabItems') {
                //dojo.byId('minutesDetailsRight').style.display = 'inline';
                this.loadSubForm();
                this.url  = phpr.webpath + "index.php/Minutes/item/jsonList/minutesId/" + this.id;
                phpr.DataStore.addStore({"url": this.url});
                phpr.DataStore.requestData({"url": this.url, processData: dojo.hitch(this, "_buildGrid")});
                //dojo.connect(dijit.byId('submitButton'), 'onClick', this.saveSubFormData);
            }
        }));
        dojo.byId('itemsFormTab').style.display = 'none';
        if (undefined == dijit.byId('minutesBox')) {
            var minutesBox = new dijit.layout.ContentPane({
                                     region: 'center',
                                     id: 'minutesBox'
                             }, 
                             dojo.doc.createElement('div'));
        } else {
            var minutesBox = dijit.byId('minutesBox');
        }
        if (undefined == dijit.byId('minutesLayout')) {
            var minutesLayout = new dijit.layout.BorderContainer({
                                        design: 'sidebar',
                                        id: 'minutesLayout'
                                }, 
                                dojo.doc.createElement('div'));
        } else {
            var minutesLayout = dijit.byId('minutesLayout');
        }
        if (undefined == dijit.byId('minutesGridBox')) {
            var minutesGridBox = new dijit.layout.ContentPane({
                                        region: 'center',
                                        id: 'minutesGridBox'
                                 }, 
                                 dojo.doc.createElement('div'));
        } else {
            var minutesGridBox = dijit.byId('minutesGridBox');
        }
        if (undefined == dijit.byId('minutesDetailsRight')) {
            var minutesDetailsRight = new dijit.layout.ContentPane({
                                        region: 'right',
                                        id: 'minutesDetailsRight',
                                        style: 'width: 50%;'
                                 }, 
                                 dojo.doc.createElement('div'));
        } else {
            var minutesDetailsRight = dijit.byId('minutesDetailsRight');
        }
        minutesLayout.addChild(minutesGridBox);
        minutesLayout.addChild(minutesDetailsRight);
        //minutesBox.addChild(minutesLayout);
        minutesBox.attr("content", minutesLayout.domNode);
        dijit.byId('tabItems').attr('content', minutesBox.domNode);
    },
    
    url: null,
    
    _buildGrid: function() {
        // summary:
        //    Return grid object instance
        // description:
        //    Internal method for creating and configuring a
        //    grid object for MinutesItems. Row configuration
        //    is defined here.
        var me = this; // variable scope workaround, needed for formatter
        var layout = [{
            cells: [[
                     {
                         name:     'Topic',
                         field:    'topicId',
                         styles:   "text-align: center;",
                         width:    '30px',
                         rowSpan:  2
                     },
                     {
                         name:     'Title',
                         field:    'title',
                         styles:   "text-align: left;",
                         width:    '270px'
                     },
                     {
                         name:     'Type',
                         field:    'topicType',
                         styles:   "text-align: center;",
                         width:    '50px',
                         type:     dojox.grid.cells.Select,
                         formatter:function(value) {
                             var typeList = me.getItemTypes();
                             for(var i=0; i < typeList.length; i++) {
                                 if (typeList[i].id && typeList[i].id == value) {
                                     return typeList[i].name;
                                 }
                             }
                             return value;
                         }
                     },
                     {
                         name:     'Date',
                         field:    'topicDate',
                         styles:   "text-align: center;",
                         width:    '65px'
                     },
                     {
                         name:     'Who',
                         field:    'userId',
                         styles:   "text-align: center;",
                         width:    '50px',
                         formatter:function(value) {
                             var userList = me._invitedList;
                             for(var i=0; i < userList.length; i++) {
                                 if (userList[i].id && userList[i].id == value) {
                                     return userList[i].username;
                                 }
                             }
                             console.log('Investigate! Value not in user array: ' + value);
                             return '';
                       	 }
                     },
                 ],[
                     {
                         name:     'Comment',
                         field:    'comment',
                         styles:   "text-align: left;",
                         width:    '465px',
                         colSpan:  4
                     }
                 ]]
            }];
        
        var store = this._getItemGridStore();
        
        var grid = new dojox.grid.DataGrid({
            store: store,
            structure: layout
            /*[{
                        defaultCell: {
                            type:     dojox.grid.cells.Input,
                            styles:   'text-align: left;'
                        },
                        cells: [schema]
            }]*/
        }, document.createElement('div'));
        
        dojo.connect(grid, 'onRowDblClick', dojo.hitch(this, function(e) {
            var data = e.grid.getItem(e.rowIndex);
            this.getSubFormData(data.id);
        }));
        
        //dojo.byId('tabItems').appendChild(grid.domNode);
        dijit.byId('minutesGridBox').attr('content', grid.domNode);
        grid.startup();
        this._itemGrid = grid;
    },
    
    _getItemGridStore: function() {
        // summary:
        //    Return data store for the grid
        // description:
        //    Creates Dojo.Data.ItemFileWriteStore instance for
        //    displaying MinutesItems in the grid.
        var content = dojo.clone(phpr.DataStore.getData({url: this.url}));
        var gridData = { items: new Array() };
        for (var i in content) {
            gridData.items.push(content[i]);
        }
        return new dojo.data.ItemFileWriteStore({"data": gridData});
    },
    
    updateGrid: function() {
        // summary:
        //    Refreshes the grid's data source
        // description:
        //    Recreated the data store for the grid, which automatically
        //    updates the view
        phpr.DataStore.deleteData({"url": this.url});
        phpr.DataStore.requestData({"url": this.url, processData: dojo.hitch(this, "_buildGrid")});
    },
    
    // private property: Grid for MinutesItems
    _itemGrid:     null,
    
    // private property: Data to be used in the detail form for MinutesItems
    _itemFormData: null,
    
    // private property: List of participants. Used as cache.
    _invitedList:  [],
    
    saveSubFormData: function() {
        // summary:
        //    Save the detail form for a MinutesItem
        // description:
        //    Retrieves the data from the detail form, posts it to
        //    the sever and refreshes the grid to reflect changes.
        var responseHandler = dojo.hitch(this, function(responseObject, ioArgs) {
            this._itemFormData = null;
            this.getItemList(dojo.hitch(this, this.loadSubForm)); // refresh sort orders
            //this.loadSubForm();
            this.updateGrid();
            return responseObject;
        });
        dojo.xhrPost({
            // The following URL must match that used to test the server.
            url: "index.php/Minutes/item/jsonSave/",
            handleAs: "text",
            load: responseHandler,
            error: function(e) {
                console.debug('saveSubFormData() has encountered an error: ' + e);
            },
            form: "minutesItemForm"
        });
    },
    
    deleteSubFormData: function() {
        // summary:
        //    Deletes the currently active MinutesItem
        // description:
        //    Posts current form data to the server.
        //    Resets detail form to default values, allowing to enter 
        //    a new record.
        var responseHandler = dojo.hitch(this, function(responseObject, ioArgs) {
            this._itemFormData = null;
            this.loadSubForm();
            //this._buildGrid();
            this.updateGrid();
            return responseObject;
        });
        dojo.xhrPost({
            // The following URL must match that used to test the server.
            url: "index.php/Minutes/item/jsonDelete/",
            handleAs: "text",
            load: responseHandler,
            error: function(e) {
                console.debug('deleteSubFormData() has encountered an error: ' + e);
            },
            form: "minutesItemForm"
        });
    },
    
    getSubFormData: function(itemId) {
        // summary:
        //    Reads data for a given MinutesItem from the server
        // description:
        //    Requests data for the MinutesItem having the given ID from
        //    the server and then reloads the detail form with the new
        //    data for editing.
        var responseHandler = dojo.hitch(this, function(responseObject, ioArgs) {
            this._itemFormData = responseObject.data[0];
            this.loadSubForm();
            return responseObject;
        });
        dojo.xhrGet({
            url: "index.php/Minutes/item/jsonDetail/minutesId/"+this.id+'/id/'+itemId,
            handleAs: "json",
            load: responseHandler,
            error: function(e) {
                console.debug('getSubFormData has encountered an error: ' + e);
            }
        });
    },
    
    getInvitedList: function() {
        // summary:
        //    Fetches list of invited participants from server
        // description:
        //    Requests list of invited persons from the server, then
        //    stores it in the _invitedList property.
        var responseHandler = dojo.hitch(this, function(responseObject, ioArgs) {
            this._invitedList = responseObject.data;
            return responseObject;
        });
        dojo.xhrGet( {
            url: "index.php/Minutes/index/jsonListUser/id/"+this.id,
            handleAs: "json",
            load: responseHandler,
            error: function(e) {
                console.debug('getInvitedList has encountered an error: ' + e);
            }
        });
    },
    
    // private property: list of item types. used as cache.
    _itemTypes: [],
    
    getItemTypes: function() {
        // summary:
        //    Returns a list of item types
        // description:
        //    Will return a list of valid item types for MinutesItems. Uses cached
        //    property if available. May do server request in the future.
        if (this._itemTypes.length == 0) {
            // @todo request these from server, needs thinking about asynchronous behaviour
            this._itemTypes = [{id:1,name:'TOPIC'},{id:2,name:'STATEMENT'},{id:3,name:'TODO'},{id:4,name:'DECISION'},{id:5,name:'DATE'}]; 
        }
        return this._itemTypes;
    },
    
    // private property: list of MinutesItems by sort order. used as cache.
    _itemList: [],
    
    getItemList: function(callback) {
        // summary:
        //    Returns a list of items indexed by sort oder
        // description:
        //    Will return a list of valid MinutesItems. Uses cached
        //    property if available. Items are indexed by their sort order
        //    instead of their ID.
        //    Calls optional callback hook if provided.
        var responseHandler = dojo.hitch(this, function(responseObject, ioArgs) {
            this._itemList = responseObject;
            if (this._itemList.metadata) {
                this._itemList = [];
            }
            console.log('Item list is ');
            console.log(this._itemList);
            if (callback) {
                callback();
            }
            return responseObject;
        });
        var errorHandler = dojo.hitch(this, function(e) {
            console.debug('getItemList has encountered an error: ' + e);
            this._itemList = []; // reset values
        });
        dojo.xhrGet( {
            url: "index.php/Minutes/item/jsonListItemSortOrder/minutesId/"+this.id,
            handleAs: "json",
            load: responseHandler,
            error: errorHandler
        });
    },
    
    loadSubForm: function() {
        // summary:
        //    Load detail form and populate it
        // description:
        //    Loads the detail form template for MinutesItems and populates it with
        //    either default data or data from a loaded MinutesItem. Also registers
        //    event listeners for various detail form behaviours.
        if (!this._itemFormData) {
            // Use default empty dataset
            this._itemFormData = {
                id:         '',
                ownerId:    '',
                minutesId:  this.id,
                projectId:  '',
                topicId:    '',
                topicType:  '1',
                userId:     '',
                sortOrder:  '',
                title:      '',
                comment:	'',
                topicDate:  ''
            };
        }
        
        var placeholders = {
            lblTitle:       'Title',
            lblComment:     'Comment',
            lblUserId:      'Who',
            lblTopicType:   'Type',
            lblTopicDate:   'Date',
            lblParentOrder: 'Sort after',
            lblSubmit:      'Save',
            lblDelete:      'Delete',
            lblClear:	    'Clear',
            parentOrder:    this._itemFormData.sortOrder - 1 >= 0 ? this._itemFormData.sortOrder - 1 : '',
            users:          this._invitedList,
            items:          this._itemList,
            types:          this.getItemTypes()
        };
        placeholders = dojo.mixin(placeholders, this._itemFormData);
        
        // Render the template
        this.render(["phpr.Minutes.template", "minutesItemForm.html"], 
                     dojo.byId('minutesDetailsRight'), placeholders);
        
        // connect save/delete events to buttons
        dojo.connect(dijit.byId('minutesItemFormSubmit'), 'onClick', 
                     dojo.hitch(this, this.saveSubFormData));
        dojo.connect(dijit.byId('minutesItemFormDelete'), 'onClick', 
                dojo.hitch(this, this.deleteSubFormData));
        
        // have reset button reload the form using defaults
        dojo.connect(dijit.byId('minutesItemFormClear'), 'onClick', dojo.hitch(this, function() {
            this._itemFormData = null; // reset data
            this.loadSubForm();
        }));
        
        // have delete button disabled when creating a new item,
        // and save button enabled by default when updating a new one
        if (!placeholders.id) {
            dijit.byId('minutesItemFormDelete').attr("disabled", true);
        } else {
            dijit.byId('minutesItemFormSubmit').attr("disabled", false);
        }
        
        // disable/enable submit button according to form validation state 
        dojo.connect(dijit.byId('minutesItemForm'), "onValidStateChange", 
            function(state) {
                dijit.byId('minutesItemFormSubmit').attr("disabled", !state);
            }
        );
        
        // have the appropriate input fields appear for each type
        this._switchItemFormFields(placeholders.topicType); // defaults
        dojo.connect(dijit.byId('minutesItemFormTopicType'), 'onChange',
                dojo.hitch(this, this._switchItemFormFields));
        
        // set cursor to title field when all is done
        dojo.byId("minutesItemFormTitle").focus();
    },
    
    _switchItemFormFields: function(typeValue) {
        // summary:
        //    Toggle visibility of detail form fields
        // description:
        //    Hides or shows the appropriate form fields for the currently
        //    selected topicType. Currently registered types are: 
        //    1='Topic', 2='Statement',3='TODO',4='Decision',5='Date'
        switch(typeValue) {
            case "3":
                dojo.byId('minutesItemFormRowUser').style.visibility = 'visible';
                dojo.byId('minutesItemFormRowDate').style.visibility = 'visible';
                break;
            case "5":
                dojo.byId('minutesItemFormRowUser').style.visibility = 'collapse';
                dojo.byId('minutesItemFormRowDate').style.visibility = 'visible';
                break;
            default:
                dojo.byId('minutesItemFormRowUser').style.visibility = 'collapse';
            dojo.byId('minutesItemFormRowDate').style.visibility = 'collapse';
        }
    }
});
