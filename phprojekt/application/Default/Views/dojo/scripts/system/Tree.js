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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Default.System.Tree");

dojo.require("dojo.data.ItemFileWriteStore");
dojo.require("dijit.Tree");
dojo.require("dijit.tree.ForestStoreModel");

phpr.treePaths               = [];
phpr.treeLastProjectSelected = null;

dojo.declare("phpr.Default.System.Tree", phpr.Default.System.Component, {
    // Summary: This class is responsible for rendering the Tree of a default module
    _treeNode: null,
    _url:      null,
    _idName:   null,
    _store:    null,
    _model:    null,
    tree:      null,

    constructor: function() {
        this.setUrl();
        this.setId(null);
        dojo.subscribe("phpr.activeModuleChanged", this, "drawBreadCrumb");
    },

    loadTree: function() {
        var tree = phpr.viewManager.getView().treeBox;
        // Data of the tree
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({
            url: this._url,
            processData: dojo.hitch(this, function(treeNode) {
                var content = this.processData(dojo.clone(phpr.DataStore.getData({url: this._url})));
                this._store = new dojo.data.ItemFileWriteStore({data: content});
                this.getModel();
                if (this.tree && dojo.isFunction(this.tree.destroyRecursive)) {
                    this.tree.destroyRecursive();
                    this.tree = null;
                }
                this.tree = this.getTree();
                this.setId(this.tree.id);
                treeNode.set('content', this.tree.domNode);
                this.tree.startup();
                dojo.connect(this.tree, "onClick", dojo.hitch(this, "onItemClick"));
                this.finishDraw();
            }, tree)
        });
        tree = null;
    },

    finishDraw: function() {
        // Summary:
        //    Finish the draw process
        // Description:
        //    Fix width and select the current project
        this.checkTreeSize();
        this.drawBreadCrumb();
        this.selecteCurrent(phpr.currentProjectId);
    },

    getModel: function() {
        this._model = new dijit.tree.ForestStoreModel({
            store: this._store,
            query: {parent: '1'}
        });
    },

    _treeOnNodeMouseEnter: function(node) {
        if (node.item.cut == 'true') {
            dijit.showTooltip(node.item.longName, node.domNode);
        }
    },

    _treeOnNodeMouseLeave: function(node) {
        if (node.item.cut == 'true') {
            dijit.hideTooltip(node.domNode);
        }
    },

    getTree: function() {
        var treeWidget = new dijit.Tree({
                model:    this._model,
                showRoot: false,
                persist:  true,
                _onNodeMouseEnter: dojo.hitch(this, "_treeOnNodeMouseEnter"),
                _onNodeMouseLeave: dojo.hitch(this, "_treeOnNodeMouseLeave")
            },
            document.createElement('div'));
        return treeWidget;
    },

    getUrl: function() {
        // Summary:
        //    Return the url for get the tree
        // Description:
        //    Return the url for get the tree
        return this._url;
    },

    setUrl: function() {
        // Summary:
        //    Set the url for get the tree
        // Description:
        //    Set the url for get the tree
        this._url = 'index.php/Project/index/jsonTree';
    },

    setId: function(id) {
        // Summary:
        //    Set the id of the widget
        // Description:
        //    Set the id of the widget
        this._idName = id;
    },

    updateData: function() {
        phpr.DataStore.deleteData({url: this._url});
        this.loadTree();
    },

    onItemClick: function(item) {
        // Summary: publishes "changeProject" as soon as a tree Node is clicked
        if (!item) {
            item = [];
        }
        this.publish("changeProject", [item.id]);
    },

    selecteCurrent: function(id) {
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
            this.tree.model.store.fetchItemByIdentity({
                identity: id,
                onItem: function(item) {
                    if (item) {
                        var paths = item.path.toString().split("\/");
                        for (var i in paths) {
                            if (Math.abs(paths[i]) > 1) {
                                _tree._expandNode(_this.getNodeByidentity(paths[i]));
                            }
                        }
                    }
                }
            });

            // Add new bold
            var node = _this.getNodeByidentity(id);
            if (node) {
                _tree.focusNode(node);
                dojo.addClass(node.rowNode, "selected");
                phpr.treeLastProjectSelected = id;
            }
        }
    },

    getNodeByidentity: function(identity) {
        // Summary:
        //    Return the node by identity
        // Description:
        //    Return the node by identity
        var nodes = this.tree._itemNodesMap[identity];
        if (nodes && nodes.length) {
            // Select the first item
            node = nodes[0];
        } else {
            node = nodes;
        }

        return node;
    },

    processData: function(data) {
        // Summary:
        //    Process the data for the tree
        // Description:
        //    Collect path and change the long names
        var node = phpr.viewManager.getView().navigationContainer.domNode;
        var width = node.style.width.replace(/px/, "");
        for (var i in data.items) {
            var name  = data.items[i].name.toString();
            var depth = data.items[i].path.match(/\//g).length;
            if (depth > 5) {
                depth = 5;
            }
            var maxLength = Math.round((width / 11) - (depth - 1));
            data.items[i].cut = false;
            if (name.length > maxLength) {
                data.items[i].longName = name;
                data.items[i].name     = name.substr(0, maxLength) + '...';
                data.items[i].cut      = true;
            }
            phpr.treePaths[data.items[i].id] = data.items[i].path;
        }

        return data;
    },

    getParentId: function(id) {
        // Summary:
        //    Return the parent id of one project
        // Description:
        //    Return the parent id of one project
        if (phpr.treePaths[id]) {
            var paths = phpr.treePaths[id].toString().split("\/").reverse();
            for (var i in paths) {
                if (paths[i] > 0) {
                    return paths[i];
                }
            }
        }
        return 1;
    },

    checkTreeSize: function() {
        // Summary
        //    This avoids unwanted vertical scrollbar in the tree when general height is not too much
        var treeHeight = phpr.viewManager.getView().treeBox.offsetHeight;
        if (treeHeight < 300) {
            phpr.viewManager.getView().treeBox.style.height = '90%';
        }
    },

    drawBreadCrumb: function() {
        var projects = [];

        if (!phpr.isGlobalModule(phpr.module) && (phpr.treeLastProjectSelected != phpr.currentProjectId || phpr.currentProjectId == 1)) {
            var projects = this.getProjectHierarchyArray(phpr.currentProjectId);
        }

        phpr.BreadCrumb.setProjects(projects);
        phpr.BreadCrumb.setModule();
        phpr.BreadCrumb.draw();
    },

    getProjectHierarchyArray: function(itemId) {
        var ret = [];
        var item;

        this._store.fetchItemByIdentity({
            identity: itemId,
            onItem: function(titem) {
                if (titem) {
                    item = titem;
                }
            }
        });

        if (!item) {
            return [];
        }

        if (item.parent[0]) {
            ret = this.getProjectHierarchyArray(item.parent[0]);
        }

        ret.push({
            "id":   item.id,
            "name": item.name
        });

        return ret;
    },

    fadeOut: function() {
        // Summary:
        //     Manage the visibility of the tree panel
        var treeBox = phpr.viewManager.getView().treeBox.domNode;
        if (dojo.style(treeBox, "opacity") != 0.5) {
            dojo.style(treeBox, "opacity", 0.5);
        }
    },

    fadeIn: function() {
        // Summary:
        //     Manage the visibility of the tree panel
        var treeBox = phpr.viewManager.getView().treeBox.domNode;
        if (dojo.style(treeBox, "opacity") != 1) {
            dojo.style(treeBox, "opacity", 1);
        }
    }
});
