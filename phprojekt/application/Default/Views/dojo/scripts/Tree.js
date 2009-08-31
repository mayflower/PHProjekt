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
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Default.Tree");

phpr.treePaths               = new Array();
phpr.treeLastProjectSelected = null;

dojo.declare("phpr.Default.Tree", phpr.Component, {
    // Summary: This class is responsible for rendering the Tree of a default module
    _treeNode: null,
    _url:      null,
    _idName:   null,
    _store:    null,
    _model:    null,

    constructor:function(main) {
        // Summary: The tree is rendere on construction
        this.main  = main;
        this.setUrl();
        this.setId();
        this.setNode();
        this.loadTree();
    },

    loadTree:function() {
        if (!dijit.byId(this._idName)) {
            // Data of the tree
            var _this = this;
            phpr.DataStore.addStore({url: this._url, noCache: true});
            phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
                var content = _this.processData(dojo.clone(phpr.DataStore.getData({url: _this._url})));
                _this._store = new dojo.data.ItemFileWriteStore({data: content});
                _this.getModel();
                _this.tree = _this.getTree();

                _this._treeNode.attr('content', _this.tree.domNode);
                _this.tree.startup();
                dojo.connect(_this.tree, "onClick", dojo.hitch(this, "onItemClick"));
                dojo.byId("navigation-container-title").innerHTML = phpr.nls.get('Projects');
                phpr.InitialScreen.end();
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
        this.drawBreadScrum();
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
            id:       this._idName,
            model:    this._model,
            showRoot: false,
            persist:  false
        }, document.createElement('div'));
    },

    setUrl:function() {
        // Summary:
        //    Set the url for get the tree
        // Description:
        //    Set the url for get the tree
        this._url = phpr.webpath + "index.php/Project/index/jsonTree";
    },

    setNode:function() {
        // Summary:
        //    Set the node to put the tree
        // Description:
        //    Set the node to put the tree
        this._treeNode = dijit.byId("treeBox");
    },

    setId:function() {
        // Summary:
        //    Set the id of the widget
        // Description:
        //    Set the id of the widget
        this._idName = 'treeNode';
    },

    updateData:function() {
        phpr.destroyWidget(this._idName);
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
        this.tree.model.store.fetchItemByIdentity({identity: phpr.treeLastProjectSelected,
            onItem:function(item) {
                if (item) {
                    var node = _tree._itemNodeMap[item.id];
                    if (node) {
                        dojo.removeClass(node.labelNode, "selected");
                    }
                }
        }});

        if (id > 1) {
            // Add new bold
            this.tree.model.store.fetchItemByIdentity({identity: id,
                onItem:function(item) {
                    if (item) {
                        var paths = item.path.toString().split("\/");
                        for (i in paths) {
                            if (Math.abs(paths[i]) > 1) {
                                node = _tree._itemNodeMap[paths[i]];
                                _tree._expandNode(node);
                            }
                        }
                        var node = _tree._itemNodeMap[item.id];
                        if (node) {
                            _tree.focusNode(node);
                            dojo.addClass(node.labelNode, "selected");
                            phpr.treeLastProjectSelected = item.id;
                        }
                    }
            }});
        } else {
            phpr.treeLastProjectSelected = id;
        }
    },

    processData:function(data) {
        // Summary:
        //    Process the data for the tree
        // Description:
        //    Collect path and change the long names
        var width = dojo.byId('navigation-container').style.width.replace(/px/, "");
        for(var i in data.items) {
            for(var j in data.items[i]) {
                var name  = data.items[i]['name'].toString();
                var depth = data.items[i]['path'].match(/\//g).length;
                if (depth > 5) {
                    depth = 5;
                }
                var maxLength = Math.round((width / 11) - (depth - 1));
                if (name.length > maxLength) {
                    var shortName = name.substr(0, maxLength) + '...';
                    data.items[i]['name'] = shortName;
                }
                phpr.treePaths[data.items[i]['id']] = data.items[i]['path'];
            }
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

    drawBreadScrum:function() {
        var projectsNames = new Array();
        var _this         = this;
        if (phpr.treeLastProjectSelected != phpr.currentProjectId) {
            this.tree.model.store.fetchItemByIdentity({identity: phpr.currentProjectId,
                onItem:function(item) {
                    if (item) {
                        var paths = phpr.treePaths[phpr.currentProjectId].toString().split("\/");
                        for (i in paths) {
                            /* do not display the invidisble root node, so we discard the first one */
                            if (paths[i] > 1 && paths[i] != phpr.currentProjectId) {
                                _this.tree.model.store.fetchItemByIdentity({identity: paths[i],
                                    onItem:function(item) {
                                        if (item) {
                                            projectsNames.push(item.name);
                                        }
                                }});
                            }
                        }
                        if (i > 1) {
                            projectsNames.push(item.name);
                        }
                    }
            }});

            phpr.BreadCrumb.setProjects(projectsNames);
        }
        phpr.BreadCrumb.setModule();
        phpr.BreadCrumb.draw();
    }
});
