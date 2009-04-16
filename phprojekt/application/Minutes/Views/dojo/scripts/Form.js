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

        var tabs = this.form; //dijit.byId('tabItems');
        dojo.connect(tabs, "selectChild", dojo.hitch(this, function(child) {
            if (child.id == 'tabItems') {
                dojo.byId('minutesDetailsRight').style.display = 'inline';
                dojo.byId('itemsFormTab').style.display = 'none';
                this.loadSubForm();
                this.url  = phpr.webpath + "index.php/Minutes/item/jsonList/minutesId/" + this.id;
                phpr.DataStore.addStore({"url": this.url});
                phpr.DataStore.requestData({"url": this.url, processData: dojo.hitch(this, "_buildGrid")});
            } else {
                dojo.byId('minutesDetailsRight').style.display = 'none';
            }
        })); 
    },
    
    url: null,
    
    _buildGrid: function() {
        console.debug('Building grid...');
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
        
        var content = dojo.clone(phpr.DataStore.getData({"url": this.url}));
        var gridData = { items: new Array() };
        for (var i in content) {
            gridData.items.push(content[i]);
        }

        var store = new dojo.data.ItemFileWriteStore({data: gridData});
        
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
            console.debug('Row doubleclicked:');
            console.debug(data);
        }));
    
        dojo.byId('tabItems').appendChild(grid.domNode);
        grid.startup();
    },
    
    
    _itemFormData: null,
    _invitedList:  [{id:1,name:'test1'},{id:2,name:'test2'}],
    
    getSubFormData: function(itemId) {
    	var responseHandler = dojo.hitch(this, function(responseObject, ioArgs) {
            console.debug('getSubFormData('+itemId+'): Got XHR response:');
            console.debug(responseObject.data);
            this._itemFormData = responseObject.data[0];
            this.loadSubForm();
            return responseObject;
        });
    	dojo.xhrGet({
            // The following URL must match that used to test the server.
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
            console.debug('getInvitedList: Got XHR response:');
            console.debug(responseObject.data);
            this._invitedList = responseObject.data;
            return responseObject;
        });
    	dojo.xhrGet( {
            // The following URL must match that used to test the server.
    		url: "index.php/Minutes/index/jsonListUser/id/"+this.id,
            handleAs: "json",
            load: responseHandler,
            error: function(e) {
    			console.debug('getInvitedList has encountered an error: ' + e);
    		}
    	});
    },
    
    loadSubForm: function() {
    	console.debug('Loading subform...');
 	
    	if (!this._itemFormData) {
    		// Use default empty dataset
	    	this._itemFormData = {
				id:         '',
				ownerId:    '',
				minutesId:  '',
				projectId:  '',
				topicId:    '',
				topicType:  '',
				userId:     '',
				sortOrder:  '',
				title:      '',
				comment:	'',
				topicDate:  '2009-05-01'
	    	};
    	}
    	// @todo Add server data to placeholder object
    	var placeholders = {
			lblTitle:     'Title',
			lblComment:   'Comment',
			lblOwnerId:   'Owner',
			lblTopicType: 'Type',
			lblTopicDate: 'Date',
			users:		  this._invitedList,
			types:        [{id:1,name:'Topic'},{id:2,name:'Task'}]
    	};
    	placeholders = dojo.mixin(placeholders, this._itemFormData);
    	// Render the template  
    	var subForm = this.render(["phpr.Minutes.template", "minutesItemForm.html"], 
    			                  dojo.byId('minutesDetailsRight'), placeholders);
    	
    	// Attach to minutesItemForm's onsubmit event
    	var formWidget = dijit.byId('minutesItemForm');
    	dojo.connect(formWidget, 'onSubmit', dojo.hitch(this, function(e) {
    		this.itemFormData = null; // reset form data
    		console.debug('Submitted item form');
    		console.debug(formWidget.getValues());
    		// @todo Implement writing data back to server
    	}));
    	// Populate form
    	console.debug('Populating form...');
    	console.debug(this._itemFormData);
    	for(var i in this._itemFormData) {
    		console.debug('Setting form element "'+i+'" to: ' + this._itemFormData[i]);
    		formWidget.attr(i, this._itemFormData[i]);
    	}
    	//formWidget.setValues(this._itemFormData);
    	
    	// @todo Attach to general save button's onclick event, 
    	//       submit minutesItemsForm as well when triggered.
    	//       Also reload _invitedList, in case something has
    	//       changed.
    }
});
