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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Tree");

phpr.treeLastProjectSelected = null;

dojo.declare("phpr.Tree", null, {
    // Summary:
    //    This class is responsible for rendering the Tree of a default module.
    _idName:    null,
    _tree:      null,
    _url:       null,
    _treePaths: [],

    constructor:function() {
        // Summary:
        //    Create a new tree.
        this._setUrl();
        this._setId(null);
    },

    loadTree:function() {
        // Summary:
        //    Init the tree.
        // Description:
        //    Init the tree if not exists,
        //    in the other case, select the current project,
        //    draw the breadcrum and fix the size.
        if (!this._idName) {
            // Data of the tree
            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
                if (!this._tree) {
                    this._tree = this._getTree();
                    this._tree.startup();
                    this._getNode().set('content', this._tree.domNode);
                    dojo.connect(this._tree, 'onClick', dojo.hitch(this, '_onItemClick'));
                    dojo.byId('navigation-container-title').innerHTML = phpr.nls.get('Projects');
                } else {
                    this._processDataDiff();
                }
                this._setId(this._tree.id);
                this._finishDraw();
            })});
        } else {
            this._finishDraw();
        }
    },

    getUrl:function() {
        // Summary:
        //    Return the url for get the tree.
        return this._url;
    },

    fadeOut:function() {
        // Summary:
        //     Manage the visibility of the tree panel.
        if (dojo.style('treeBox', 'opacity') != 0.5) {
            dojo.style('treeBox', 'opacity', 0.5);
        }
    },

    fadeIn:function() {
        // Summary:
        //     Manage the visibility of the tree panel.
        if (dojo.style('treeBox', 'opacity') != 1) {
            dojo.style('treeBox', 'opacity', 1);
        }
    },

    getParentId:function(id) {
        // Summary:
        //    Return the parent id of one project.
        if (this._treePaths[id]) {
            var paths = this._treePaths[id].toString().split("\/").reverse();
            for (i in paths) {
                if (paths[i] > 0) {
                    return paths[i];
                }
            }
        }

        return 1;
    },

    updateData:function() {
        // Summary:
        //    Destroy the id and update the data for refresh the tree.
        this._setId(null);
        phpr.DataStore.deleteData({url: this._url});
    },

    /************* Private functions *************/

    _setUrl:function() {
        // Summary:
        //    Set the url for get the tree.
        this._url = phpr.webpath + 'index.php/Project/index/jsonTree';
    },

    _setId:function(id) {
        // Summary:
        //    Set the id of the widget.
        this._idName = id;
    },

    _getTree:function() {
        // Summary:
        //    Create a new dijit.tree.
        return new dijit.Tree({
            model:    this._getModel(),
            showRoot: false,
            persist:  false,
            _onNodeMouseEnter: function(node) {
                if (node.item.cut == 'true') {
                    dijit.showTooltip(node.item.longName, node.domNode);
                }
            },
            _onNodeMouseLeave: function(node) {
                if (node.item.cut == 'true') {
                    dijit.hideTooltip(node.domNode);
                }
            }
        }, document.createElement('div'));
    },

    _getNode:function() {
        // Summary:
        //    Set the node to put the tree.
        return dijit.byId('treeBox');
    },

    _getModel:function() {
        // Summary:
        //    Create a new tree model with a new store.
        // Create the store
        var store          = new dojo.data.ItemFileWriteStore({});
        store.clearOnClose = true;
        store.data         = this._processData(phpr.clone(phpr.DataStore.getData({url: this._url})));

        // Create the model
        return new dijit.tree.ForestStoreModel({
            store: store,
            query: {parent: '1'}
        });
    },

    _processData:function(data) {
        // Summary:
        //    Process the data for the tree.
        // Description:
        //    Collect path and change the long names.
        for(var i in data.items) {
            data.items[i]['id']     = parseInt(data.items[i]['id']);
            data.items[i]['parent'] = parseInt(data.items[i]['parent']);
            this._cutName(data.items[i]);
            this._treePaths[data.items[i]['id']] = data.items[i]['path'];
        }

        return data;
    },

    _cutName:function(item) {
        // Summary:
        //    Cut the name of the item if is too long.
        var width = dojo.byId('navigation-container').style.width.replace(/px/, "");
        var name  = item['name'].toString();
        var depth = item['path'].match(/\//g).length;
        if (depth > 5) {
            depth = 5;
        }
        var maxLength = Math.round((width / 11) - (depth - 1));
        item['cut']      = false;
        item['longName'] = (item['longName']) ? item['longName'] : name;
        if (name.length > maxLength) {
            item['name'] = name.substr(0, maxLength) + '...';
            item['cut']  = true;
        }
    },

    _finishDraw:function() {
        // Summary:
        //    Finish the draw process.
        // Description:
        //    Fix width and select the current project.
        this._checkTreeSize();
        this._drawBreadCrumb();
        this._selecteCurrent(phpr.currentProjectId);
    },

    _checkTreeSize:function() {
        // Summary
        //    This avoids unwanted vertical scrollbar in the tree when general height is not too much.
        var treeHeight = dojo.byId('treeBox').offsetHeight;
        if (treeHeight < 300) {
            dojo.byId('tree-navigation').style.height = '90%';
        }
    },

    _drawBreadCrumb:function() {
        // Summary:
        //    Set the Breadcrumb with all the projects and the module.
        var projects = new Array();
        if (!phpr.isGlobalModule(phpr.module)) {
            if (phpr.treeLastProjectSelected != phpr.currentProjectId || phpr.currentProjectId == 1) {
                var storeData = this._tree.model.store._itemsByIdentity;
                var item      = storeData[phpr.currentProjectId];
                if (item) {
                    var paths = this._treePaths[phpr.currentProjectId].toString().split("\/");
                    for (var i in paths) {
                        if (paths[i] > 0 && paths[i] != phpr.currentProjectId) {
                            var subItem = storeData[paths[i]];
                            if (subItem) {
                                projects.push({id:   subItem.id,
                                               name: subItem.longName});
                            }
                        }
                    }
                    projects.push({id:   item.id,
                                   name: item.longName});
                }
                phpr.BreadCrumb.setProjects(projects);
            }
        } else {
            phpr.BreadCrumb.setProjects(projects);
        }
        phpr.BreadCrumb.setModule();
        phpr.BreadCrumb.draw();
    },

    _selecteCurrent:function(id) {
        // Summary:
        //    Select the current projects and open all the parents.
        if (phpr.treeLastProjectSelected != id) {
            // Remove last bold
            var node = this._getNodeByidentity(phpr.treeLastProjectSelected);
            if (node) {
                dojo.removeClass(node.rowNode, 'selected');
            }

            if (id > 1) {
                // Expan the parents
                var item = this._tree.model.store._itemsByIdentity[id];
                if (item) {
                    var paths = item.path.toString().split("\/");
                    for (var i in paths) {
                        if (parseInt(paths[i]) > 1) {
                            this._tree._expandNode(this._getNodeByidentity(paths[i]));
                        }
                    }
                }

                // Add new bold
                var node = this._getNodeByidentity(id);
                if (node) {
                    this._tree.focusNode(node);
                    dojo.addClass(node.rowNode, 'selected');
                    phpr.treeLastProjectSelected = id;
                }
            }
        }
    },

    _getNodeByidentity:function(identity) {
        // Summary:
        //    Return the node by identity.
        var nodes = this._tree._itemNodesMap[identity];
        if (nodes && nodes.length){
            // Select the first item
            node = nodes[0];
        } else {
            node = nodes;
        }

        return node;
    },

    _processDataDiff:function() {
        // Summary:
        //    Process the new data for the tree.
        // Description:
        //    Check for changes between the store and the new data.
        //    - Add new nodes.
        //    - Edit existing nodes.
        //    - Move nodes.
        //    - Delete nodes.
        var newDataArray = this._processData(phpr.clone(phpr.DataStore.getData({url: this._url})));
        var newData = newDataArray['items'];
        var store   = this._tree.model.store;
        var oldData = store._itemsByIdentity;
        var toKeep  = [];

        for (var j = 0; j < newData.length; j++) {
            var item = oldData[newData[j]['id']];
            if (null == item) {
                // Add a new item
                this._tree.model.newItem({
                    id:     newData[j]['id'],
                    name:   newData[j]['name'],
                    parent: newData[j]['parent'],
                    path:   newData[j]['path'],
                    cut:    newData[j]['cut']
                }, oldData[newData[j]['parent']]);

                // Mark for keep it
                toKeep[newData[j]['id']] = true;
            } else {
                // Mark for keep it
                toKeep[item.id] = true;

                if (newData[j]['longName'] != item.longName) {
                    // The name was changed
                    this._cutName(newData[j]);
                    store.setValue(item, 'longName', newData[j]['longName']);
                    store.setValue(item, 'name', newData[j]['name']);
                    store.setValue(item, 'cut', newData[j]['cut']);
                }

                if (newData[j]['id'] > 1 && newData[j]['parent'] != item.parent) {
                    // The parent was changed
                    this._tree.model.pasteItem(item, oldData[item.parent], oldData[newData[j]['parent']], false, 0);
                    store.setValue(item, 'parent', newData[j]['parent']);
                    store.setValue(item, 'path', newData[j]['path']);
                }
            }
        }

        // Search for deleted items
        for (var i in oldData) {
            if (oldData[i] && !toKeep[oldData[i].id]) {
                store.deleteItem(oldData[i]);
            }
        }

        // Save the changes
        store.save({});

        // Delete vars
        newData = [];
        oldData = [];
        toKeep  = [];
    },

    _onItemClick:function(item) {
        // Summary:
        //    Publishes "changeProject" as soon as a tree Node is clicked.
        if (!item) {
          item = [];
        }
        dojo.publish(phpr.module + '.changeProject', [item.id]);
    }
});
