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
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

dojo.provide("phpr.Default.Tree");

dojo.declare("phpr.Default.Tree", phpr.Component, {
    // summary: This class is responsible for rendering the Tree of a default module
    _treeNode:  null,
    _url:       null,
    _idName:    null,
    _store:     null,
    _model:     null,
    _paths:     new Array(),

    constructor:function(main) {
        // summary: The tree is rendere on construction
        this.main  = main;
        this.setUrl();
        this.setId();
        this.setNode();
        this.loadTree();
    },

    loadTree:function() {
        if (!dijit.byId(this._idName)) {
            // Data of the tree
            this.getStore();
            this.getModel();
            this.tree = this.getTree();

            this._treeNode.attr('content', this.tree.domNode);
            this.tree.startup();
            dojo.connect(this.tree, "onClick", dojo.hitch(this, "onItemClick"));
        } else {
            this.tree = dijit.byId(this._idName);
        }
        this.initTree(1);
        this.selecteCurrent(phpr.currentProjectId);
        this.closeTree(phpr.currentProjectId);
    },

    getStore:function() {
        this._store = new dojo.data.ItemFileWriteStore({url: this._url});
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
        // summary:
        //    Set the url for get the tree
        // description:
        //    Set the url for get the tree
        this._url = phpr.webpath + "index.php/Project/index/jsonTree";
    },

    setNode:function() {
        // summary:
        //    Set the node to put the tree
        // description:
        //    Set the node to put the tree
        this._treeNode = dijit.byId("treeBox");
    },

    setId:function() {
        // summary:
        //    Set the id of the widget
        // description:
        //    Set the id of the widget
        this._idName = 'treeNode';
    },

    updateData:function() {
        phpr.destroyWidget(this._idName);
    },

    onItemClick:function(item) {
        // summary: publishes "changeProject" as soon as a tree Node is clicked
        if(!item) {
          item = [];
        }
        this.publish("changeProject", [item.id]);
    },

    selecteCurrent:function(id) {
        // summary:
        //    Select the current projects and all the parents
        // description:
        //    Select the current projects and all the parents
        if (id > 1) {
            var _tree = this.tree;
            var _this = this;
            this.tree.model.store.fetchItemByIdentity({identity: id,
                onItem:function(item) {
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
                        node.labelNode.style.fontWeight = "bold";
                    }
            }});
        }
    },

    initTree:function(id) {
        // summary:
        //    Add the path of every project into an array
        // description:
        //    Add the path of every project into an array
        var _tree  = this.tree;
        var _this  = this;
        var _paths = this._paths;
        this.tree.model.store.fetch({
            query: {parent: id.toString()},
            onItem: function(item) {
                var name = item.name.toString();
                if (name.length > 20) {
                    var shortName = name.substr(0, 20) + '...';
                    _tree.model.store.setValue(item, "name", shortName);
                }
                _paths[item.id] = item.path;
                _this.initTree(item.id);
                node = _tree._itemNodeMap[item.id];
                if (node) {
                    node.labelNode.style.fontWeight = "normal";
                }
            }
        });
    },

    closeTree:function(id) {
        // summary:
        //    Close all the projects exept the current branch
        // description:
        //    Close all the projects exept the current branch
        if (id > 1) {
            var _tree = this.tree;
            var _this = this;
            if (this._paths[id]) {
                var usedPath = this._paths[id].toString().split("\/");
                for (i in this._paths) {
                    if (id != i) {
                        path = this._paths[i].toString().split("\/");
                        for (j in path) {
                            if (path[j] != usedPath[j]) {
                                node = _tree._itemNodeMap[path[j]];
                                if (node) {
                                    _tree._collapseNode(node);
                                }
                            }
                        }
                    }
                }
            }
        }
    },

    getParentId: function(id) {
        // summary:
        //    Return the parent id of one project
        // description:
        //    Return the parent id of one project
        if (this._paths[id]) {
            var paths = this._paths[id].toString().split("\/").reverse();
            for (i in paths) {
                if (paths[i] > 0) {
                    return paths[i];
                }
            }
        }
        return 1;
    }
});
