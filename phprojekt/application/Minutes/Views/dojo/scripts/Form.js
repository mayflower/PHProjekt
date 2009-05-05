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
        // I used this for testing purposes. Also renders only in gridBox
        
        var tabs = this.form;
        dojo.connect(tabs, "selectChild", dojo.hitch(this, function(child) {
            if (child.id == 'tabItems') {
                //dojo.byId('minutesDetailsRight').style.display = 'inline';
                this.loadSubForm();
                this.url  = phpr.webpath + "index.php/Minutes/item/jsonList/minutesId/" + this.id;
                phpr.DataStore.addStore({"url": this.url});
                phpr.DataStore.requestData({"url": this.url, processData: dojo.hitch(this, "_buildGrid")});
                //dojo.connect(dijit.byId('submitButton'), 'onClick', this.saveSubFormData);
            } else {
                //dojo.byId('minutesDetailsRight').style.display = 'none';
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
        var schema = [{
                name:     'Topic',
                field:    'topicId',
                styles:   "text-align: center;",
                width:    '30px'
            },
            {
                name:     'Title',
                field:    'title',
                styles:   "text-align: left;",
                width:    '100px'
            },
            {
                name:     'Comment',
                field:    'comment',
                styles:   "text-align: left;",
                width:    '270px'
            },
            {
                name:     'Type',
                field:    'topicType',
                styles:   "text-align: center;",
                width:    '50px',
                options:    [1,2],
                values:     ['Topic','Comment']
            }
        ];
        
        var store = this._getItemGridStore();
        
        var grid = new dojox.grid.DataGrid({
            store: store,
            structure: [{
                        defaultCell: {
                            type:     dojox.grid.cells.Input,
                            styles:   'text-align: left;'
                        },
                        rows: [schema]
            }]
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
        var content = dojo.clone(phpr.DataStore.getData({url: this.url}));
        var gridData = { items: new Array() };
        for (var i in content) {
            gridData.items.push(content[i]);
        }
        return new dojo.data.ItemFileWriteStore({"data": gridData});
    },
    
    updateGrid: function() {
        phpr.DataStore.deleteData({"url": this.url});
        phpr.DataStore.requestData({"url": this.url, processData: dojo.hitch(this, "_buildGrid")});
    },
    
    _itemGrid:     null,
    _itemFormData: null,
    _invitedList:  [{id:1,name:'test1'},{id:2,name:'test2'}],
    
    saveSubFormData: function() {
        var responseHandler = dojo.hitch(this, function(responseObject, ioArgs) {
            this._itemFormData = null;
            this.loadSubForm();
            //this._buildGrid();
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
    
    getSubFormData: function(itemId) {
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
    
    loadSubForm: function() {
        if (!this._itemFormData) {
            // Use default empty dataset
            // @todo FIXME
            this._itemFormData = {
                id:         '',
                ownerId:    '',
                minutesId:  this.id,
                projectId:  '',
                topicId:    '',
                topicType:  '',
                userId:     '',
                sortOrder:  '1',
                title:      '',
                comment:	'',
                topicDate:  '2009-05-01'
            };
        }
        // @todo Add server data to placeholder object
        var placeholders = {
            lblTitle:     'Title',
            lblComment:   'Comment',
            lblUserId:    'Who',
            lblTopicType: 'Type',
            lblTopicDate: 'Date',
            lblSubmit:    'Save',
            users:        this._invitedList,
            types:        [{id:1,name:'Topic'},{id:2,name:'Task'}]
        };
        placeholders = dojo.mixin(placeholders, this._itemFormData);
        
        // Render the template
        this.render(["phpr.Minutes.template", "minutesItemForm.html"], 
                     dojo.byId('minutesDetailsRight'), placeholders);
        dojo.connect(dijit.byId('minutesItemFormSubmit'), 'onClick', 
                     dojo.hitch(this, this.saveSubFormData));
    }
});
