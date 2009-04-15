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
        
        var itemsData = this.render(["phpr.Minutes.template", "minutesItemGrid.html"], null, {
            // no placeholders used atm.
        });
        
        this.addTab(itemsData, 'tabItems', 'Items', 'itemsFormTab');
        
        console.debug('my id is: ' + this.id);
    },
    
    postRenderForm: function() {
        // summary:
        //    Render grid
        // description:
        //    Render the datagrid after the rest of the form has been 
        //    processed. Neccessary because the datagrid won't render
        //    unless dimensions of all surrounding elements are known.
        // I used this for testing purposes. Also renders only in gridBox
        try {
            console.debug('postRenderForm is called');
            var tabs = this.form; //dijit.byId('tabItems');
            /*
            dojo.connect(tabs,'onShow',dojo.hitch(this, function() {
                // create a new grid:
                //var url  = phpr.webpath + "index.php/Minutes/index/jsonGetMinuteItems/id/" + this.id;
                //var url  = phpr.webpath + "index.php/Minutes/item/jsonList/minutesId/" + this.id;
                //var grid = new phpr.Minutes.ItemGrid(url, phpr.Default.Main, this.id);
                console.debug('Items tab is shown, getting grid data...');
                this.url  = phpr.webpath + "index.php/Minutes/item/jsonList/minutesId/" + this.id;
                phpr.DataStore.addStore({"url": this.url});
                phpr.DataStore.requestData({"url": this.url, processData: dojo.hitch(this, "_buildGrid")});
            }));
             */
            dojo.connect(tabs, "selectChild", dojo.hitch(this, function(child) {
                try {
                    console.debug("a tab was selected: " + child.id + ' / ' + child);
                    if (child.id == 'tabItems') {
                        dojo.byId('minutesDetailsRight').style.display = 'inline';
                        console.debug('Items tab is shown, getting grid data...');
                        this.url  = phpr.webpath + "index.php/Minutes/item/jsonList/minutesId/" + this.id;
                        phpr.DataStore.addStore({"url": this.url});
                        phpr.DataStore.requestData({"url": this.url, processData: dojo.hitch(this, "_buildGrid")});
                    } else {
                        dojo.byId('minutesDetailsRight').style.display = 'none';
                    }
                } catch (ex) {
                    console.debug(ex);
                }
            })); 
        } catch(ex) {
            console.debug('Datastore: ' + ex);
        }
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
        try {
            var content = dojo.clone(phpr.DataStore.getData({"url": this.url}));
        } catch(ex) {
            console.debug('Content: ' + ex);
        }
        try {
            var gridData = { items: new Array() };
            for (var i in content) {
                gridData.items.push(content[i]);
            }
        } catch(ex) {
            console.debug('Array: ' + ex);
        }
        try {
            var store = new dojo.data.ItemFileWriteStore({data: gridData});
        } catch(ex) {
            console.debug('Store: ' + ex);
        }
        try {
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
        } catch(ex) {
            console.debug('Grid: ' + ex);
        }
        try {
            //dojo.byId('minuteItems').appendChild(grid.domNode);
            /*
            // if I use a content pane, I must provide absolute dimensions
            // or the grid won't render. If I don't use a content pane, the grid
            // does render at full width (which is desired), but has a weird
            // top and bottom margin so I have to scroll down to see it.
            // WTF???
            var pane = new dijit.layout.ContentPane({
                title:   'minutesItemsContentPane'
            });
            pane.domNode.appendChild(grid.domNode)
             */
            dojo.byId('tabItems').appendChild(grid.domNode);
            console.debug('Appending child to: ' + dojo.byId('tabItems'));
            grid.startup();
        } catch(ex) {
            console.debug('Append: ' + ex);
        }
    }
   
});

