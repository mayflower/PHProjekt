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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @author     Reno Reckling <reno.reckling@mayflower.de>
 */

dojo.require("dojox.layout.FloatingPane");

dojo.provide("phpr.Default.TutorialOverlay");

dojo.declare("phpr.Default.TutorialOverlay", phpr.Default.System.Component, {
    _mainWindow: null,
    _mainWindowContent: null,
    _tabContainer: null,

    show: function() {
        //ensure the proper window state for the tutorial
        var state = phpr.pageManager.getState();
        if (state.moduleName !== "Project" || state.projectId !== "1") {
            phpr.pageManager.changeState({moduleName: 'Project', projectId: "1"});
        }

        this._mainWindow = new dojox.layout.FloatingPane({
            title: phpr.nls.get("Tutorial"),
            dockable: false,
            style: "position: absolute; width: 600px; height: 500px; visibility: hidden; z-index: 1000;"
        }, dojo.create("div"));

        phpr.viewManager.getView().completeContent.containerNode.appendChild(this._mainWindow.domNode);
        this._mainWindow.startup();
        this._mainWindow.show();

        this._mainWindowContent = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.tutorialOverlayContent.html"
        });

        this._mainWindow.set('content', this._mainWindowContent);

        this._tabContainer = new dijit.layout.TabContainer({
                region: "center",
                tabPosition: "left-h"
            },
            dojo.create("div")
        );

        this._mainWindowContent.borderContainer.addChild(this._tabContainer);
        this._mainWindowContent.borderContainer.resize();

        dojo.style(this._mainWindow.domNode, "top", "300px");
        dojo.style(this._mainWindow.domNode, "left", "300px");

        this._setCheckBox();
        this._setHeader();

        this._addTreeTutorial();
        this._addGridTutorial();
        this._addProjectModulesTutorial();
        this._addGlobalModulesTutorial();
        this._addActionsTutorial();
        this._addHelpTutorial();
    },

    _setCheckBox: function() {
        this._mainWindowContent.checkboxLabel.innerHTML = phpr.nls.get("Show tutorial on login?");
        this._mainWindowContent.checkbox.set('checked', phpr.config.tutorialDisplayed === "true" ? false : true);
        this.garbageCollector.addEvent(
            dojo.connect(this._mainWindowContent.checkbox, "onChange", dojo.hitch(this,
                function() {
                    var value = this._mainWindowContent.checkbox.get('checked');
                    this._setTutorialDisplayed(!value);
                })
            )
        );
    },

    _setHeader: function() {
        this._mainWindowContent.header.set('content', phpr.nls.get("Tutorial Header"));
    },

    _setTutorialDisplayed: function(value) {
        phpr.send({
            url: 'index.php/Default/index/jsonSetTutorialDisplayed',
            content: { displayed: (value ? "true" : "false") }
        });
    },

    _addTreeTutorial: function() {
        var domNode = phpr.viewManager.getView().treeNavigation;
        this._addTutorialEntry("Tree", "Tree tutorial text", domNode);
    },

    _addGridTutorial: function() {
        var domNode = function() {
            return phpr.tutorialAnchors.grid;
        };

        this._addTutorialEntry("Grid", "Grid tutorial text", domNode);
    },

    _addProjectModulesTutorial: function() {
        var domNode = phpr.viewManager.getView().containSubModuleNavigation;
        this._addTutorialEntry("Project Modules", "Project Modules tutorial text", domNode);
    },

    _addGlobalModulesTutorial: function() {
        var domNode = phpr.viewManager.getView().mainNavigation.domNode;
        this._addTutorialEntry("Global Modules", "Global Modules tutorial text", domNode);
    },

    _addActionsTutorial: function() {
        var domNode = phpr.viewManager.getView().buttonRow.domNode;
        this._addTutorialEntry("Actions", "Actions tutorial text", domNode);
    },

    _addHelpTutorial: function() {
        var domNode = phpr.tutorialAnchors.helpButton.domNode;
        this._addTutorialEntry("Help", "Help tutorial text", domNode);
    },

    _addTutorialEntry: function(title, text, domNode) {
        var tab = this._createTabContent(title, text);

        this._setHighlights(tab, domNode);

        this._tabContainer.addChild(tab);
    },

    _createTabContent: function(title, text) {
        var content = phpr.nls.get(text);
        var tab = new dijit.layout.ContentPane({
            title: phpr.nls.get(title),
            content: content,
            style: "margin: 5px;"
        });

        return tab;
    },

    _setHighlights: function(tab, domNode) {
        var that = this;
        this.garbageCollector.addEvent(dojo.connect(tab, 'onShow', function() {
            var node = that._resolveNode(domNode);

            if (node) {
                that._highlightNode(node);
            }
        }));

    },

    _resolveNode: function(node) {
        if (dojo.isFunction(node)) {
            node = node();
        }

        return node;
    },

    _highlightNode: function(domNode) {
        var domBox = dojo.marginBox(domNode);
        var overlay = dojo.create("div");
        dojo.style(
            overlay,
            {
                "position": "absolute",
                "height": domBox.h + "px",
                "width": domBox.w + "px",
                "backgroundColor": "red",
                "top": domBox.t + "px",
                "left": domBox.l + "px",
                "zIndex": 99999,
                "opacity": 0
            }
        );

        dojo.place(overlay, domNode, "before");
        dojo.animateProperty({
            node: overlay,
            duration: 1000,
            properties: {
                opacity: {
                    start: 0,
                    end: 0.7
                }
            },
            onEnd: function() {
                dojo.animateProperty({
                    node: overlay,
                    duration: 1000,
                    properties: {
                        opacity: {
                            start: 0.7,
                            end: 0
                        }
                    },
                    onEnd: function() {
                        dojo.destroy(overlay);
                    }
                }).play();
            }
        }).play();
    }
});
