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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Tree");

phpr.treePaths               = new Array();
phpr.treeLastProjectSelected = null;

dojo.declare("phpr.Tree", phpr.Component, {
    // Summary: This class is responsible for rendering the Tree of a default module
    _treeNode: null,
    _url:      null,
    _idName:   null,
    _store:    null,
    _model:    null,

    constructor:function() {
        this.setUrl();
        this.setId(null);
    },

    loadTree:function() {
        if (!this._idName || !dijit.byId(this._idName)) {
            this.setNode();
            // Data of the tree
            var _this = this;
            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
                var content = _this.processData(dojo.clone(phpr.DataStore.getData({url: _this._url})));
                _this._store = new dojo.data.ItemFileWriteStore({data: content});
                _this.getModel();
                _this.tree = _this.getTree();
                _this.setId(_this.tree.id);
                _this._treeNode.set('content', _this.tree.domNode);
                _this.tree.startup();
                dojo.connect(_this.tree, "onClick", dojo.hitch(this, "onItemClick"));
                dojo.byId("navigation-container-title").innerHTML = phpr.nls.get('Projects');
                _this.finishDraw();
            })});
        } else {
            this.tree = dijit.byId(this._idName);
            this.finishDraw();
        }
    },

    finishDraw:function() {
        // Summary:
        //    Finish the draw process
        // Description:
        //    Fix width and select the current project
        this.checkTreeSize();
        this.drawBreadCrumb();
        this.selecteCurrent(phpr.currentProjectId);
    },

    getModel:function() {
        this._model = new dijit.tree.ForestStoreModel({
            store: this._store,
            query: {parent:'1'}
        });
    },

    getTree:function() {
        return new dijit.Tree({
            model:    this._model,
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

    getUrl:function() {
        // Summary:
        //    Return the url for get the tree
        // Description:
        //    Return the url for get the tree
        return this._url;
    },

    setUrl:function() {
        // Summary:
        //    Set the url for get the tree
        // Description:
        //    Set the url for get the tree
        this._url = phpr.webpath + 'index.php/Project/index/jsonTree';
    },

    setNode:function() {
        // Summary:
        //    Set the node to put the tree
        // Description:
        //    Set the node to put the tree
        this._treeNode = dijit.byId("treeBox");
    },

    setId:function(id) {
        // Summary:
        //    Set the id of the widget
        // Description:
        //    Set the id of the widget
        this._idName = id;
    },

    updateData:function() {
        phpr.destroyWidget(this._idName);
        phpr.DataStore.deleteData({url: this._url});
    },

    onItemClick:function(item) {
        // Summary: publishes "changeProject" as soon as a tree Node is clicked
        if (!item) {
          item = [];
        }
        this.publish("changeProject", [item.id]);
    },

    selecteCurrent:function(id) {
        // Summary:
        //    Select the current projects and all the parents
        // Description:
        //    Select the current projects and all the parents
        var _tree = this.tree;
        var _this = this;

        // Remove last bold
        var node = _this.getNodeByidentity(phpr.treeLastProjectSelected);
        if (node) {
            dojo.removeClass(node.rowNode, "selected");
        }

        if (id > 1) {
            // Expan the parents
            this.tree.model.store.fetchItemByIdentity({identity: id,
                onItem:function(item) {
                    if (item) {
                        var paths = item.path.toString().split("\/");
                        for (i in paths) {
                            if (Math.abs(paths[i]) > 1) {
                                _tree._expandNode(_this.getNodeByidentity(paths[i]));
                            }
                        }
                    }
            }});

            // Add new bold
            var node = _this.getNodeByidentity(id);
            if (node) {
                _tree.focusNode(node);
                dojo.addClass(node.rowNode, "selected");
                phpr.treeLastProjectSelected = id;
            }
        }
    },

    getNodeByidentity:function(identity) {
        // Summary:
        //    Return the node by identity
        // Description:
        //    Return the node by identity
        var nodes = this.tree._itemNodesMap[identity];
        if (nodes && nodes.length){
            // Select the first item
            node = nodes[0];
        } else {
            node = nodes;
        }

        return node;
    },

    processData:function(data) {
        // Summary:
        //    Process the data for the tree
        // Description:
        //    Collect path and change the long names
        var width = dojo.byId('navigation-container').style.width.replace(/px/, "");
        for(var i in data.items) {
            var name  = data.items[i]['name'].toString();
            var depth = data.items[i]['path'].match(/\//g).length;
                if (depth > 5) {
                depth = 5;
            }
            var maxLength = Math.round((width / 11) - (depth - 1));
            data.items[i]['cut'] = false;
            if (name.length > maxLength) {
                data.items[i]['longName'] = name;
                data.items[i]['name']     = name.substr(0, maxLength) + '...';
                data.items[i]['cut']      = true;
            }
            phpr.treePaths[data.items[i]['id']] = data.items[i]['path'];
        }

        return data;
    },

    getParentId:function(id) {
        // Summary:
        //    Return the parent id of one project
        // Description:
        //    Return the parent id of one project
        if (phpr.treePaths[id]) {
            var paths = phpr.treePaths[id].toString().split("\/").reverse();
            for (i in paths) {
                if (paths[i] > 0) {
                    return paths[i];
                }
            }
        }
        return 1;
    },

    checkTreeSize:function() {
        // Summary
        //    This avoids unwanted vertical scrollbar in the tree when general height is not too much
        var treeHeight = dojo.byId('treeBox').offsetHeight;
        if (treeHeight < 300) {
            dojo.byId('tree-navigation').style.height = '90%';
        }
    },

    drawBreadCrumb:function() {
        var projects = new Array();
        var _this    = this;

        if (!phpr.isGlobalModule(phpr.module)) {
            if (phpr.treeLastProjectSelected != phpr.currentProjectId || phpr.currentProjectId == 1) {
                this.tree.model.store.fetchItemByIdentity({identity: phpr.currentProjectId,
                    onItem:function(item) {
                        if (item) {
                            var paths = phpr.treePaths[phpr.currentProjectId].toString().split("\/");
                            for (i in paths) {
                                if (paths[i] > 0 && paths[i] != phpr.currentProjectId) {
                                    _this.tree.model.store.fetchItemByIdentity({identity: paths[i],
                                        onItem:function(item) {
                                            if (item) {
                                                projects.push({"id":   item.id,
                                                               "name": item.name});
                                            }
                                    }});
                                }
                            }
                            projects.push({"id":   item.id,
                                           "name": item.name});
                        }
                }});
                phpr.BreadCrumb.setProjects(projects);
            }
        } else {
            phpr.BreadCrumb.setProjects(projects);
        }
        phpr.BreadCrumb.setModule();
        phpr.BreadCrumb.draw();
    },

    fadeOut:function() {
        // Summary:
        //     Manage the visibility of the tree panel
        if (dojo.style("treeBox", "opacity") != 0.5) {
            dojo.style("treeBox", "opacity", 0.5);
        }
    },

    fadeIn:function() {
        // Summary:
        //     Manage the visibility of the tree panel
        if (dojo.style("treeBox", "opacity") != 1) {
            dojo.style("treeBox", "opacity", 1);
        }
    }
});
