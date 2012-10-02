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
 * @since      File available since Release 6.1
 * @author     Reno Reckling <exi@wthack.de>
 */

dojo.provide("phpr.Default.System.ViewManager");

dojo.provide("phpr.Default.System.TemplatedLayoutContainer");
dojo.provide("phpr.Default.System.ViewContentMixin");
dojo.provide("phpr.Default.System.View");
dojo.provide("phpr.Default.System.DefaultView");
dojo.provide("phpr.Default.System.TemplateWrapper");

dojo.declare("phpr.Default.System.TemplatedLayoutContainer", [dijit.layout.ContentPane, dijit._Templated],
    {
        templateString: '',
        widgetsInTemplate: true,
        constructor: function() {
            this.templateString = "";
        },
        destroy: function() {
            if (this._beingDestroyed) {
                return;
            }
            this.inherited(arguments);
            dojo.forEach(dijit.findWidgets(this.domNode),
                    function(w) {
                        if (dojo.isFunction(w.destroyRecursive)) {
                            w.destroyRecursive();
                        }
                    });
        },
        postCreate: function() {
            this.inherited(arguments);
            if (this.dataNode && this.dataNode.getAttribute('phprTemplateData')) {
                try {
                    dojo.mixin(this, dojo.fromJson(this.dataNode.getAttribute('phprTemplateData')));
                } catch (e) {
                    console.error("malformated data");
                }
            }
            dojo.addClass(this.domNode, "phprTemplatedLayoutContainer");
        }
    }
);

dojo.declare("phpr.Default.System.ViewContentMixin", null, {
    view: null,
    _mixedIn: false,
    constructor: function(view) {
        this.view = view;
    },
    mixin: function() {

    },
    destroyMixin: function() {

    },
    update: function() {
        if (this._mixedIn === false) {
            this.mixin();
            this._mixedIn = true;
        }
    },
    destroy: function() {
        this.destroyMixin();
    }
});

dojo.declare("phpr.Default.System.DefaultViewContentMixin", phpr.Default.System.ViewContentMixin, {
    _blank: true,
    mixin: function() {
        this.inherited(arguments);
        this.view.clear = dojo.hitch(this, "clear");
        this.view.clearDetails = dojo.hitch(this, "clearDetails");
        this.view.clearOverview = dojo.hitch(this, "clearOverview");
    },
    update: function(options) {
        this.inherited(arguments);
        if (options.blank === true) {
            if (this._blank === false) {
                this._clearCenterMainContent();
            }
        } else {
            if (this._blank === true) {
                this._renderBorderContainer();
            }
        }
    },
    destroyMixin: function() {
        this.inherited(arguments);
        this.clear();
        this._clearCenterMainContent();
        delete this.view.clear;
        delete this.view.clearDetails;
        delete this.view.clearOverview;
        delete this.view.clear;
        delete this.view.overviewBox;
        delete this.view.detailsBox;
        delete this.view.defaultMainContent;
    },
    clearDetails: function() {
        if (!this._blank) {
            this.view.detailsBox.destroyDescendants();
        }

        return this.view;
    },
    clearOverview: function() {
        if (!this._blank) {
            this.view.overviewBox.destroyDescendants();
        }

        return this.view;
    },
    clear: function() {
        if (!this._blank) {
            this.clearDetails();
            this.clearOverview();
        } else {
            this._clearCenterMainContent();
        }

        return this.view;
    },
    _clearCenterMainContent: function() {
        this.view.centerMainContent.destroyDescendants();
        this.view.defaultMainContent = null;
        this._blank = true;
    },
    _renderBorderContainer: function() {
        if (this._blank === false) {
            this._clearCenterMainContent();
        }

        var mainContent = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.mainContent.html"
        });

        this.view.defaultMainContent = mainContent.mainContent;

        var overview = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.OverviewBox.html"
        });

        this._overviewBox = overview;
        this.view.overviewBox = overview.overviewBox;
        this.view.defaultMainContent.addChild(overview, 0);

        var details = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.DetailsBox.html"
        });

        this._detailsBox = details;
        this.view.detailsBox = details.detailsBox;
        this.view.defaultMainContent.addChild(details, 2);

        this.view.centerMainContent.set('content', this.view.defaultMainContent);
        this.view.defaultMainContent.startup();

        this._blank = false;
    }
});

dojo.declare("phpr.Default.System.ViewManager", null, {
    _currentView: null,
    _currentViewType: null,
    _container: null,
    _loadingInProgress: false,
    constructor: function() {
        this._container = new dijit.layout.ContentPane({}, dojo.create('div', null, dojo.body()));
        window.onresize = dojo.hitch(this, 'onResize');
        dojo.addOnLoad(dojo.hitch(this, 'onResize'));
    },
    getMaxHeight: function() {
        var availHeight = 0;

        if (document.layers) {
            availHeight = window.innerHeight + window.pageYOffset;
        } else if (document.all) {
            availHeight = document.documentElement.clientHeight + document.documentElement.scrollTop;
        } else if (document.getElementById) {
            availHeight = window.innerHeight + window.pageYOffset;
        }
        return availHeight;
    },
    onResize: function() {
        if (!this._loadingInProgress) {
            availHeight = this.getMaxHeight();
            var cont = this._container;
            if (cont) {
                dojo.style(cont.domNode, "height", availHeight + "px");
                cont.resize();
            }

            if (!phpr.viewManager.getView()) {
                phpr.viewManager.useDefaultView();
            }
            cont = phpr.viewManager.getView().completeCenterContent;
            if (cont) {
                dojo.style(cont.domNode, "height", (availHeight - 60) + "px");
                cont.resize();
            }
        }
        dojo.publish("phpr.resize", []);
    },
    useDefaultView: function(options) {
        return this._updateView(phpr.Default.System.DefaultView,
                [phpr.Default.System.DefaultViewContentMixin, options || {}]);
    },
    _updateView: function(viewType, params) {
        this._loadingInProgress = true;

        if (this._currentViewType === viewType && this._currentView !== null) {
            this._currentView.update.apply(this._currentView, params);
        } else {

            if (this._currentView) {
                this._currentView.destroyRecursive();
            }

            this._currentViewType = viewType;
            this._currentView = viewType.apply(null);
            this._container.set('content', this._currentView);
            this._currentView.update.apply(this._currentView, params);
            this._currentView.startup();
        }

        this._loadingInProgress = false;
        return this.getView();
    },
    setView: function(viewType, mixinType, config) {
        return this._updateView(viewType, [mixinType, config || {}]);
    },
    getView: function() {
        return this._currentView;
    }
});

dojo.declare("phpr.Default.System.View", phpr.Default.System.TemplatedLayoutContainer, {
    name: null,
    _contentMixin: null,
    _contentMixinType: null,
    destroy: function() {
        this._destroyMixin();
    },
    update: function(mixinType, config) {
        if (mixinType) {
            this._mixin(mixinType, config || {});
        } else {
            this._destroyMixin();
        }
        return this;
    },
    _mixin: function(mixinType, config) {
        if (this._contentMixinType !== mixinType) {
            this._destroyMixin();
            this._contentMixin = mixinType.apply(null, [this]);
            this._contentMixin.update(config);
            this._contentMixinType = mixinType;
        } else if (this._contentMixinType === mixinType) {
            this._contentMixin.update(config);
        } else {
            this._destroyMixin();
        }
    },
    _destroyMixin: function() {
        if (this._contentMixin && dojo.isFunction(this._contentMixin.destroy)) {
            this._contentMixin.destroy();
        }

        this._contentMixin = null;
        this._contentMixinType = null;
        this._contentMixinConfig = null;
    }
});

dojo.declare("phpr.Default.System.DefaultView", phpr.Default.System.View, {
    name: "Default",
    widgetsInTemplate: true,
    constructor: function() {
        this.templateString = phpr.fillTemplate("phpr.Default.template.main.html",
            {
                currentModule: phpr.module
            });
    },
    clearButtonRow: function() {
        this.buttonRow.destroyDescendants();
    },
    clearRightButtonRow: function() {
        this.rightButtonRow.destroyDescendants();
    },
    clearSubModuleNavigation: function() {
        this.subModuleNavigation.destroyDescendants();
    }
});

dojo.declare("phpr.Default.System.TemplateWrapper", phpr.Default.System.TemplatedLayoutContainer,
    {
        templateName: "",
        templateData: {},
        constructor: function() {
            this.templateName = "";
            this.templateData = {};
        },
        postMixInProperties: function() {
            this.templateString = phpr.fillTemplate(
                this.templateName, this.templateData);
            this.inherited(arguments);
        },
        postCreate: function() {
            dojo.attr(this.domNode, 'templateName', this.templateName);
            this.inherited(arguments);
        }
    }
);
