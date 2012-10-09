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

dojo.provide("phpr");

/* template deps */

dojo.require("dojo.hash");
dojo.require("dijit.form.Form");
dojo.require("dijit.form.Textarea");
dojo.require("dijit.form.HorizontalRuleLabels");
dojo.require("dojox.widget.Toaster");

/* phpr deps */

dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.form.Button");
dojo.require("dojox.data.QueryReadStore");
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit._Widget");
dojo.require("dijit.Dialog");
dojo.require("dijit.layout._LayoutWidget");
dojo.require("dijit._Templated");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.layout.ContentPane");
dojo.require("dojox.dtl.Inline");

// Global vars
var module           = null;
var currentProjectId = null;
var rootProjectId    = null;
var userTags         = null;
var currentTags      = null;
var serverFeedback   = null;

phpr.initWidgets = function(el) {
    // This parses the given node and inits the widgets found in there.
    if (dojo.isString(el)) {
        el = dojo.byId(el);
    }
    dojo.parser.parse(el);
};

phpr.destroySubWidgets = function(el) {
    // Destroy all the old widgets, so dojo can init the new ones with the same IDs again.
    if (dijit.byId(el) && dijit.byId(el).destroyDescendants) { // dijit widget id?
        dijit.byId(el).destroyDescendants();
    } else if (dojo.byId(el)) { // dom node id?
        try {
            var widget = dijit.byNode(dojo.byId(el));
            if (widget && widget.destroyDescendants) {
                widget.destroyDescendants();
            } else {
                throw new Error("");
            }
        } catch (e) {
            dojo.forEach(dijit.findWidgets(dojo.byId(el)), function(w) {
                w.destroyRecursive();
            });
            dojo.byId(el).innerHTML = '';
        }
    }
};

phpr.destroyWidget = function(el) {
    // Destroy only one widgwt using the id
    if (dijit.byId(el)) {
        dijit.byId(el).destroyRecursive();
    }
};

phpr.fillTemplate = function(templateName, data) {
    // Summary:
    //  fills a template with the data
    // Description:
    //  The template is fetched from the templateCache and filled with the data.
    //  The resulting string is then returned
    data = data || {};
    var context = new dojox.dtl.Context(data);
    // Use the cached template
    var tplContent = __phpr_templateCache[templateName];
    var tpl        = new dojox.dtl.Template(tplContent);
    var content    = tpl.render(context);
    tpl = null;
    tplContent = null;
    context = null;
    return content;
};

phpr.send = function(/*Object*/paramsIn) {
    // Send the given content to the server using the Default values,
    // if you need something special dont use this function.
    //
    //  Example call:
    //      phpr.send({url:"/live-save/", content:{data:1}, chunkMap:{tags:"tagsEl"}});

    phpr.loading.show();

    var deferred = new dojo.Deferred();
    var params = {
        url:       "",
        content:   "",
        handleAs:  "json",
        sync:      false,
        chunkMap:  {}
    };

    if (dojo.isObject(paramsIn)) {
        dojo.mixin(params, paramsIn);
    }

    // Add a token
    if (params.content) {
        dojo.mixin(params.content, {'csrfToken': phpr.csrfToken});
    } else {
        params.content = {'csrfToken': phpr.csrfToken};
    }

    var deferred = dojo.xhrPost({
        url:      params.url,
        content:  params.content,
        handleAs: params.handleAs,
        sync:     params.sync
    });


    deferred = deferred.then(
        function(data, ioArgs) {
            phpr.loading.hide();
            return data;
        },
        function(err) {
            // try to parse json from the error message
            try {
                var data = dojo.fromJson(err.responseText);
                return data;
            } catch (e) {
                // unexpected error, return no data
                phpr.handleError(params.url, 'php');
                return {};
            }
        }
    );

    return deferred;
};

phpr.handleResponse = function(resultArea, result) {
    phpr.loading.hide();
    var css = 'error';
    if (result.type == 'success') {
        css = 'success';
    } else if (result.type == 'warning') {
        css = 'warning';
    }
    var message = result.message;
    if (!message) {
        return;
    }
    phpr.serverFeedback.addMessage({cssClass: css, output: message});
};

phpr.getCurrent = function(data, identifier, value) {
    var current = null;
    for (var i = 0; i < data.length; i++) {
        if (value == data[i][identifier]) {
            current = data[i];
            break;
        }
    }
    return current;
};


phpr.drawEmptyMessage = function(message) {
    // Summary:
    //    Center and make bold an error message
    // Description:
    //    Center and make bold an error message
    var output = '';
    output += '<div style="text-align: center; margin: 10px 10px 10px 10px; font-weight: bold;">';
    output += phpr.nls.get(message);
    output += '</div>';
    return output;
};

phpr.isValidInputKey = function(key) {
    // Summary:
    //    Return if a key is a valid input key
    // Description:
    //    Return if a key is a valid input key
    if ((key != dojo.keys.ENTER) &&
       (key != dojo.keys.NUMPAD_ENTER) &&
       (key != dojo.keys.TAB) &&
       (key != dojo.keys.CTRL) &&
       (key != dojo.keys.SHIFT) &&
       (key != dojo.keys.CLEAR) &&
       (key != dojo.keys.ALT) &&
       (key != dojo.keys.PAUSE) &&
       (key != dojo.keys.CAPS_LOCK) &&
       (key != dojo.keys.ESCAPE) &&
       (key != dojo.keys.SPACE) &&
       (key != dojo.keys.PAGE_UP) &&
       (key != dojo.keys.PAGE_DOWN) &&
       (key != dojo.keys.END) &&
       (key != dojo.keys.HOME) &&
       (key != dojo.keys.LEFT_ARROW) &&
       (key != dojo.keys.UP_ARROW) &&
       (key != dojo.keys.RIGHT_ARROW) &&
       (key != dojo.keys.DOWN_ARROW) &&
       (key != dojo.keys.INSERT) &&
       (key != dojo.keys.DELETE) &&
       (key != dojo.keys.HELP) &&
       (key != dojo.keys.LEFT_WINDOW) &&
       (key != dojo.keys.RIGHT_WINDOW) &&
       (key != dojo.keys.SELECT) &&
       (key != dojo.keys.NUMPAD_MULTIPLY) &&
       (key != dojo.keys.NUMPAD_PLUS) &&
       (key != dojo.keys.NUMPAD_DIVIDE) &&
       (key != dojo.keys.F1) &&
       (key != dojo.keys.F2) &&
       (key != dojo.keys.F3) &&
       (key != dojo.keys.F4) &&
       (key != dojo.keys.F5) &&
       (key != dojo.keys.F6) &&
       (key != dojo.keys.F7) &&
       (key != dojo.keys.F8) &&
       (key != dojo.keys.F9) &&
       (key != dojo.keys.F10) &&
       (key != dojo.keys.F11) &&
       (key != dojo.keys.F12) &&
       (key != dojo.keys.F13) &&
       (key != dojo.keys.F14) &&
       (key != dojo.keys.F15) &&
       (key != dojo.keys.NUM_LOCK) &&
       (key != dojo.keys.SCROLL_LOCK)) {
        return true;
    } else {
        return false;
    }
};

dojo.declare("phpr.DataStore", null, {
    // Summary:
    //    Get and return data from the server
    // Description:
    //    The data is request to the server
    //    and then is cached for the future used.
    _internalCache: [],

    _activeDownloads: {},

    addStore: function(params) {
        // Summary:
        //    Set a new store for save the data
        // Description:
        //    Set a new store for save the data
        if (typeof this._internalCache[params.url] == 'undefined' || params.noCache) {
            var store = new phpr.ReadStore({url: params.url});
            this._internalCache[params.url] = {
                data: [],
                store: store,
                deferred: null,
                active: false
            };
        }
    },

    requestData: function(params) {
        // Summary:
        //    Request the data
        // Description:
        //    If the data is not cached, request to the server.
        //    Then return to the processData function

        var deferred;
        var alreadyActive = this._internalCache[params.url].active;

        if (alreadyActive) {
            deferred = this._internalCache[params.url].deferred;
        } else {
            deferred = new dojo.Deferred();
            deferred.then(dojo.hitch(this, function() {
                this._internalCache[params.url].active = false;
                delete this._internalCache[params.url].deferred;
            }));
        }

        if (dojo.isFunction(params.processData)) {
            deferred.then(params.processData);
        }


        if (!alreadyActive) {
            if (this._internalCache[params.url].data.length === 0) {
                phpr.loading.show();
                this._internalCache[params.url].active = true;
                this._internalCache[params.url].deferred = deferred;
                var that = this;
                this._internalCache[params.url].store.fetch({
                    serverQuery: params.serverQuery || {},
                    onComplete:  dojo.hitch(this, "saveData", {
                        url: params.url,
                        processData: function() {
                            deferred.callback(that.getCombinedData(params));
                        }
                    }),
                    onError: dojo.hitch(this, "errorHandler", {
                        url: params.url,
                        processData: function() {
                            deferred.callback();
                        }
                    })
                });
            } else {
                deferred.callback(this.getCombinedData(params));
            }
        }

        return deferred;
    },

    errorHandler: function(scope, error) {
        // Summary:
        //    Display a PHP or JS error
        // Description:
        //    If there is some data before the json
        //    the error is cached and showed
        //    Also is cached the JS error

        // Get the message error
        if ((error.number && (error.number & 0xFFFF == 1002 || error.number & 0xFFFF == 1006)) || // IE
            (error.name && error.name == "SyntaxError")) { // FF
            // PHP Error
            phpr.handleError(scope.url, 'php');
        } else if (error.status === 0) {
            // Lost connection to server
            phpr.handleError(null, 'connection');
        } else {
            var message = null;
            if (error.message) {
                message = error.message;
            } else if (error.description) {
                message = error.description;
            }
            phpr.handleError(scope.url, 'php', message);
        }

        scope.processData();
    },

    saveData: function(params, data) {
        // Summary:
        //    Store the data in the cache
        // Description:
        //    Store the data in the cache
        //    Then return to the processData function
        this._internalCache[params.url].data = data;
        phpr.loading.hide();
        if (params.processData) {
            params.processData(this.getCombinedData(params));
        }
    },

    getData: function(params) {
        // Summary:
        //    Return the "data" tag from the server
        // Description:
        //    Return the "data" tag from the server
        return this.getStore(params).getValue(this._internalCache[params.url].data[0], "data") || [];
    },

    getMetaData: function(params) {
        // Summary:
        //    Return the "metadata" tag from the server
        // Description:
        //    Return the "metadata" tag from the server
        return this.getStore(params).getValue(this._internalCache[params.url].data[1], "metadata") || [];
    },

    getCombinedData: function(params) {
        return {
            data: this.getData(params),
            metaData: this.getMetaData(params)
        };
    },

    deleteData: function(params) {
        // Summary:
        //    Delete the cache
        // Description:
        //    Delete the cache
        if (this._internalCache[params.url]) {
            this._internalCache[params.url].data = [];
        }
    },

    deleteDataPartialString: function(params) {
        // Summary:
        //    Deletes the cache for the urls that start with the received string.
        for (var url in this._internalCache) {
            var urlLeft = url.substring(0, params.url.length);
            if (urlLeft == params.url) {
                this._internalCache[url].data = [];
            }
        }
    },

    getStore: function(params) {
        // Summary:
        //    Return the current data.store
        // Description:
        //    Return the current data.store
        return this._internalCache[params.url].store;
    },

    deleteAllCache: function() {
        // Summary:
        //    Delete all the cache
        // Description:
        //    Delete all the cache
        for (var i in this._internalCache) {
            // Special case for global modules since are not reloaded
            if (this._internalCache[i] && i != 'index.php/Core/module/jsonGetGlobalModules') {
                this._internalCache[i].data = [];
            }
        }
    }
});

dojo.declare("phpr.ReadStore", dojox.data.QueryReadStore, {
    // Summary:
    //    Request to the server
    // Description:
    //    Request to the server and return an array with
    //    data and metadata values
    requestMethod:  "post",
    doClientPaging: false,

    _assertIsItem: function(item) {
    },

    _fetchItems: function(request, fetchHandler, errorHandler) {
        if (request.serverQuery) {
            request.serverQuery.csrfToken = phpr.csrfToken;
        } else if (request.query) {
            request.query.csrfToken = phpr.csrfToken;
        } else {
            request.serverQuery = {};
            request.serverQuery.csrfToken = phpr.csrfToken;
        }
        this.inherited(arguments);
    },

    _filterResponse: function(data) {
        var retData     = [];
        var retMetaData = [];

        if (!data) {
            phpr.handleError(this.url, 'exception');
        } else if (data.code && data.code == 500) {
            // 500 is the error code for logut
            location = 'index.php/Login/logout';
        } else if (data.type && data.type == "error") {
            phpr.handleError(this.url, 'error', data.message);
        } else {
            var customData = false;
            if (typeof data.data == 'undefined') {
                customData = true;
                data.data  = [];
            }

            if (true === customData && data.data.length === 0 && typeof data.metadata == 'undefined') {
                retData     = data;
            } else {
                retData     = data.data;
                retMetaData = data.metadata;
            }
        }

        var ret = {
            items: [
                {"data":     retData},
                {"metadata": retMetaData}
            ]
        };

        return ret;
    }
});

dojo.declare("phpr.DateTextBox", [dijit.form.DateTextBox], {
    _blankValue: '', // used by filter() when the textbox is blank

    parse: function(value, constraints) {
        // Summary:
        //    Parses as string as a Date, according to constraints
        // Date

        var date = this.dateLocaleModule.parse(value, constraints);
        return date || undefined;
    },

    serialize: function(d, options) {
        // Summary:
        //     This function overwrites the dijit.form.DateTextBox display
        // Description:
        //     Make sure that the date is not only displayed localized, but also
        //     the value which is returned is set to this date format
        return dojo.date.locale.format(d, {selector: 'date', datePattern: 'yyyy-MM-dd'}).toLowerCase();
    }
});

dojo.declare("phpr.ServerFeedback", [dijit._Widget], {
    // Summary:
    //     A class for displaying the ServerFeedback
    // Description:
    //     This class receives the Server Feedback and displays it to the User
    messages: [],
    displayedMessages: [],

    addMessage: function(message) {
        this.messages.push(message);
        this.displayMessage(message);
    },

    deleteLastMessage: function(message) {
        this.messages.pop();
    },

    displayMessage: function(message) {
        this.displayedMessages = [message];
        for (var i in this.displayedMessages) {
            out = this.displayedMessages[i];
            dojo.publish("ServerFeedback", [{
                    message: out.output,
                    type:    out.cssClass
                }]
            );
        }
    }
});

dojo.declare("phpr.loading", null, {
    // Summary:
    //     Simple class for show or hide the loading icon
    // Description:
    //     Simple class for show or hide the loading icon
    hide: function() {
        var view = phpr.viewManager.getView();
        if (view && view.loadingIcon) {
            view.loadingIcon.style.display = 'none';
        }
    },

    show: function() {
        var view = phpr.viewManager.getView();
        if (view && view.loadingIcon) {
            view.loadingIcon.style.display = 'inline';
        }
    }
});

dojo.declare("phpr.translator", null, {
    // Summary:
    //     Trasnlation class
    // Description:
    //     Collect all the trasnlated strings into an array
    //     and return the request string translateds
    _currentLanguage: null,
    _firstLoad: true,
    _strings: null,
    _fallbackStrings: null,
    _listener: null,

    constructor: function() {
        this._listener = dojo.subscribe("phpr.moduleSettingsChanged", this, "_onSettingsChange");
        this._strings = {};
        this._fallbackStrings = {};
    },

    destroy: function() {
        dojo.unsubscribed(this._listener);
    },

    _onSettingsChange: function(module, data) {
        if (module === "User" && data && data.language) {
            this.loadTranslation(data.language);
        }
    },

    loadTranslation: function(language)  {
        var self = this;
        return this._loadLanguage(language).then(function(data) {
            self._strings = data;

            if (!self._firstLoad && self._currentLanguage !== language) {
                dojo.publish("phpr.languageChanged", [language]);
            } else {
                self._firstLoad = false;
            }

            self._currentLanguage = language;
        });
    },

    loadFallback: function(language)  {
        var self = this;
        return this._loadLanguage(language).then(function(data) {
            self._fallbackStrings = data;
        });
    },

    _loadLanguage: function(lang) {
        var url = 'index.php/Default/index/jsonGetTranslatedStrings/language/' + lang;
        var param = { url: url };
        phpr.DataStore.addStore(param);
        return phpr.DataStore.requestData(param).then(function () {
            return phpr.DataStore.getData(param);
        });
    },

    get: function(string, module) {
        var returnValue;

        returnValue = this._getStringsFromObject(this._strings, string, module);
        if (returnValue === null) {
            returnValue = this._getStringsFromObject(this._fallbackStrings, string, module);
        }

        if (returnValue === null) {
            // Unstranslated string
            returnValue = string;
        }

        return returnValue;
    },

    _getStringsFromObject: function(stringObject, string, module) {
        if (module && stringObject[module] && stringObject[module][string]) {
            return stringObject[module][string];
        // Current module
        } else if (stringObject[phpr.module] && stringObject[phpr.module][string]) {
            return stringObject[phpr.module][string];
        // Core module
        } else if (stringObject.Core && stringObject.Core[string]) {
            return stringObject.Core[string];
        // Default module
        } else if (stringObject.Default && stringObject.Default[string]) {
            return stringObject.Default[string];
        } else {
            return null;
        }
    }
});

dojo.declare("phpr.Dialog", [dijit.Dialog], {
    // Summary:
    //     Provide a dialog with some changes
    // Description:
    //     Allow dialog into other dialog and fix the key input
    _onKey: function(/*Event*/ evt) {
    // Summary: handles the keyboard events for accessibility reasons
        if (evt.charOrCode) {
            var dk   = dojo.keys;
            var node = evt.target;
            if (evt.charOrCode === dk.TAB) {
                this._getFocusItems(this.domNode);
            }
            var singleFocusItem = (this._firstFocusItem == this._lastFocusItem);
            // see if we are shift-tabbing from first focusable item on dialog
            if (node == this._firstFocusItem && evt.shiftKey && evt.charOrCode === dk.TAB) {
                if (!singleFocusItem) {
                    dijit.focus(this._lastFocusItem); // send focus to last item in dialog
                }
                dojo.stopEvent(evt);
            } else if (node == this._lastFocusItem && evt.charOrCode === dk.TAB && !evt.shiftKey) {
                if (!singleFocusItem) {
                    dijit.focus(this._firstFocusItem); // send focus to first item in dialog
                }
                dojo.stopEvent(evt);
            } else {
                // see if the key is for the dialog
                while (node) {
                    if (node == this.domNode || node == this.domNode.parentNode) {
                        if (evt.charOrCode == dk.ESCAPE) {
                            this.onCancel();
                        } else {
                            return; // just let it go
                        }
                    }
                    node = node.parentNode;
                }
                // this key is for the disabled document window
                if (evt.charOrCode !== dk.TAB) {
                    // allow tabbing into the dialog for a11y
                    dojo.stopEvent(evt);
                // opera won't tab to a div
                } else if (!dojo.isOpera) {
                    try {
                        this._firstFocusItem.focus();
                    } catch (e) {
                        /*squelch*/
                    }
                }
            }
        }
    }
});

dojo.declare("phpr.InitialScreen", null, {
    // Summary:
    //     Manage the visibility of the page on init
    // Description:
    //     Manage the visibility of the page on init
    start: function() {
        dojo.style(phpr.viewManager.getView().completeContent.domNode, "opacity", 0);
    },

    end: function() {
        var view = phpr.viewManager.getView();
        dojo.style(view.completeContent.domNode, "opacity", 1);
        dojo.style(view.initLoading, "display", "none");
    }
});

phpr.loadJsFile = function(fileName) {
    // Load a js and insert into the head
    var fileRef = document.createElement('script');
    var def;
    fileRef.setAttribute("type", "text/javascript");
    fileRef.setAttribute("src", fileName);
    if (typeof fileRef != "undefined") {
        def = new dojo.Deferred();
        fileRef.onload = function() {
            def.callback();
        };
        document.getElementsByTagName("head")[0].appendChild(fileRef);
    }

    return def;
};

phpr.loadCssFile = function(fileName) {
    // Load a css and insert into the head
    var fileRef = document.createElement("link");
    fileRef.setAttribute("rel", "stylesheet");
    fileRef.setAttribute("type", "text/css");
    fileRef.setAttribute("href", fileName);
    if (typeof fileRef != "undefined") {
        document.getElementsByTagName("head")[0].appendChild(fileRef);
    }
};

phpr.handleError = function(url, type, message) {
    // Process and return an error message
    var response  = {};
    response.type = 'error';

    if (url) {
        response.message = url + ': ';
    } else {
        response.message = '';
    }

    switch (type) {
        case 'exception':
            response.message += phpr.nls.get('Internal exception') + '<br />';
            response.message += phpr.nls.get('Please contact the administrator and check the error logs');
            break;
        case 'php':
            response.message += phpr.nls.get('Invalid json format') + '<br />';
            response.message += phpr.nls.get('Please contact the administrator and check the error logs');
            break;
        case 'error':
            response.message += phpr.nls.get('User error') + '<br />';
            response.message += message;
            break;
        case 'js':
            response.message += phpr.nls.get('Internal javascript error') + '<br />';
            response.message += message;
            break;
        case 'silence':
            console.log(phpr.nls.get('Server unreachable! ') + message);
            return;
        case 'connection':
            response.message += phpr.nls.get('Server unreachable! ') + '<br />';
            break;
        default:
            response.message += phpr.nls.get('Unexpected error');
            break;
    }

    // Show support address?
    if (phpr.config.supportAddress !== undefined && phpr.config.supportAddress !== '') {
        response.message += '<br /> ' + phpr.nls.get('Support address:') + ' ' + phpr.config.supportAddress;
    }

    new phpr.handleResponse('serverFeedback', response);
};

dojo.declare("phpr.BreadCrumb", null, {
    // Summary:
    //     Manage the Breadcrumb
    // Description:
    //     Manage the Breadcrumb
    _module:       '',
    _projects:     [],
    _item:         '',
    _lastModule:   null,
    _lastParent:   null,
    _separatorOne: ' / ',
    _separatorTwo: ' > ',

    setProjects: function(projects) {
        // Summary:
        //     Set the projects tree as one string
        // Description:
        //     Set the projects tree as one string
        this._projects = projects;
        this._module   = null;
        this._item     = null;
    },

    setModule: function() {
        // Summary:
        //     Set the module and sub-module
        // Description:
        //     Change the module and sub-module only if these change
        if (phpr.module != this._lastModule || phpr.parentmodule != this._lastParent || !this._module) {
            if (phpr.parentmodule && phpr.parentmodule != phpr.module) {
                this._module     = phpr.nls.get(phpr.parentmodule) + this._separatorOne + phpr.nls.get(phpr.module);
                this._lastParent = phpr.parentmodule;
            } else {
                this._module = phpr.nls.get(phpr.module);
            }
            this._item       = null;
            this._lastModule = phpr.module;
        }
    },

    setItem: function(item) {
        // Summary:
        //     Set the item value
        // Description:
        //     Display the item info (By default, the first field value)
        //     Show "New" if is a new item
        if (!item) {
            item = phpr.nls.get('New');
        }
        this._item = item;
    },

    draw: function() {
        // Summary:
        //     Draw the breadcrumb
        // Description:
        //     Show the breadcrumb in the title
        var breadCrumb, breadCrumbTitle;
        if (this._projects.length > 0) {
            var titleArray    = [];
            var projectsArray = [];
            for (var i in this._projects) {
                var link = '<a href="javascript: dojo.publish(\'' + phpr.module +
                    '.changeProject\', [' + this._projects[i].id + ']);">' +
                    this._projects[i].name + '</a>';
                projectsArray.push(link);
                titleArray.push(this._projects[i].name);
            }
            breadCrumb      = projectsArray.join(this._separatorTwo.toString()) + this._separatorOne + this._module;
            breadCrumbTitle = titleArray.join(this._separatorTwo.toString()) + this._separatorOne + this._module;
        } else {
            breadCrumb      = this._module;
            breadCrumbTitle = this._module;
        }
        if (this._item) {
            breadCrumb      += this._separatorOne + this._item;
            breadCrumbTitle += this._separatorOne + this._item;
        }
        document.title                    = breadCrumbTitle;
        phpr.viewManager.getView().breadCrumb.innerHTML = breadCrumb;
    }

});

phpr.inArray = function(needle, haystack) {
    // Summary:
    //    Checks whether the given needle is in the haystack
    // Description:
    //    Checks whether the given needle is in the haystack

    // we need to check for this, because for some reason, the function is
    // called with undefined as haystack very often
    if (dojo.isArray(haystack) || "Object" == typeof haystack) {
        return dojo.indexOf(haystack, needle) != -1;
    }

    return false;
};

dojo.declare("phpr.FilteringSelect", dijit.form.FilteringSelect, {
    // Summary:
    //    Extend the dojo FilteringSelect for fix some bugs.
    // Description:
    //    The dojo select do not allow two or more labels with the same name,
    //    for select users that is a problem (users with the same name),
    //    See: http://trac.dojotoolkit.org/ticket/7279
    //    Also change the query options and highlight for work with trees in select.

    // Highlight any occurrence
    highlightMatch: "all",

    // `${0}*` means "starts with", `*${0}*` means "contains", `${0}` means "is"
    queryExpr: "*${0}*",

    // Internal var for fix the bug of items with the same display
    _lastSelectedId: null,

    _doSelect: function(/*Event*/ tgt) {
        // Summary:
        //    Overrides ComboBox._doSelect(), the method called when an item in the menu is selected.
        // Description:
        //    FilteringSelect overrides this to set both the visible and
        //    hidden value from the information stored in the menu.
        //    Also mark the last selected item.
        this._setValueFromItem(tgt.item, true);
        this._lastSelectedId = this.get('value');
    },

    _setDisplayedValueAttr: function(/*String*/ label, /*Boolean?*/ priorityChange) {
        // Summary:
        //    Overrides dijit.form.FilteringSelect._setDisplayedValueAttr().
        // Description:
        //    Change the query for search the id if an item is select,
        //    or by the name is not (normal case)

        // When this is called during initialization it'll ping the datastore
        // for reverse lookup, and when that completes (after an XHR request)
        // will call setValueAttr()... but that shouldn't trigger an onChange()
        // event, even when it happens after creation has finished
        if (!this._created) {
            priorityChange = false;
        }

        if (this.store) {
            var query = dojo.clone(this.query); // #6196: populate query with user-specifics
            // Escape meta characters of dojo.data.util.filter.patternToRegExp().
            if (this._lastSelectedId !== null) {
                this._lastQuery = query.value = this._lastSelectedId;
            } else {
                this._lastQuery = query[this.searchAttr] = label.replace(/([\\\*\?])/g, "\\$1");
            }
            this._lastSelectedId = null;

            // If the label is not valid, the callback will never set it,
            // so the last valid value will get the warning textbox set the
            // textbox value now so that the impending warning will make
            // sense to the user
            this.textbox.value = label;
            this._lastDisplayedValue = label;
            var _this = this;
            var fetch = {
                query:        query,
                queryOptions: {
                    ignoreCase: this.ignoreCase,
                    deep:       true
                },
                onComplete: function(result, dataObject) {
                    dojo.hitch(_this, "_callbackSetLabel")(result, dataObject, priorityChange);
                },
                onError: function(errText) {
                    dojo.hitch(_this, "_setValue")("", label, false);
                }
            };
            dojo.mixin(fetch, this.fetchProperties);
            this.store.fetch(fetch);
        }
    },

    doHighlight: function(/*String*/label, /*String*/find) {
        // Summary:
        //    Highlights the string entered by the user in the menu.
        //    Change the function for Highlights all the occurences

        // Add greedy when this.highlightMatch=="all"
        var modifiers = "i" + (this.highlightMatch == "all" ? "g" : "");
        var escapedLabel = this._escapeHtml(label);
        find = dojo.regexp.escapeString(find); // escape regexp special chars
        var ret = escapedLabel.replace(new RegExp("(^|\\s|\\w)(" + find + ")", modifiers),
            '$1<span class="dijitComboBoxHighlightMatch">$2</span>');
        return ret; // Returns String, (almost) valid HTML (entities encoded)
    }
});

phpr.isGlobalModule = function(module) {
    // Summary:
    //    Return if the module is global or per project
    // Description:
    //    Return if the module is global or per project
    var globalModules = phpr.DataStore.getData({url: phpr.globalModuleUrl});

    // System Global Modules
    if (module == 'Administration' || module == 'Setting') {
        return true;
    } else if (phpr.parentmodule == 'Administration' || phpr.parentmodule == 'Setting') {
        return true;
    } else {
        for (var index in globalModules) {
            if (globalModules[index].name == module) {
                return true;
            }
        }
    }
    return false;
};

dojo.declare("phpr.regExpForFilter", null, {
    // Summary:
    //    Return the regular expresion used for parse filter values
    // Description:
    //    Reject all the characters except letters, numbers, dash, underscore and colon
    getExp: function() {
        return '[^\\x21\\x22\\x23\\x24\\x25\\x26\\x27\\x28\\x29\\x2A\\x2B\\x2C\\x2E\\x2F\\x3B' +
            '\\x3C\\x3D\\x3E\\x3F\\x5B\\x5C\\x5D\\x5E\\x60\\x7B\\x7C\\x7D\\x7E\\x82\\x83\\x84\\x85' +
            '\\x86\\x87\\x88\\x89\\x8B\\x91\\x92\\x93\\x94\\x95\\x98\\x99\\x9B\\xA1\\xA6\\xAC\\xAE' +
            '\\xAF\xA8\\xB0\\xB1\\xB2\\xB3\\xB4\\xB6\\xB7\\xB8\\xB9\\xBA\\xBB\\xBC\\xBD\\xBE\\xBF]*';
    },

    // Summary:
    //    Return the message used for invalid values
    getMsg: function() {
        return '<b>' + phpr.nls.get('Invalid string') + '</b><br />' +
            phpr.nls.get('Allowed values are: Letters, numbers, space, dash, underscore and colon');
    }
});

phpr.confirmDialog = function(callbackOk, message) {
    // Summary:
    //    Open a dialog for confirm the delete action
    // Description:
    //    Open a dialog and call the callback function on "OK" clicked
    var callback = function(confirm) {
        confirmDialog.hide();
        confirmDialog.destroyRecursive();
        if (confirm) {
            callbackOk.call();
        }
    };

    var content = new dijit.layout.ContentPane({
        region: 'center',
        style:  'text-align: center;'
    }, document.createElement('div'));
    var question = new dijit.layout.ContentPane({
        region:  'center',
        content: '<p>' + message + '</p>'
    }, document.createElement('div'));
    var buttonContent = new dijit.layout.ContentPane({
        region: 'bottom',
        gutter: 'yes'
    }, document.createElement('div'));
    var buttonOK = new dijit.form.Button({
        baseClass: 'negative',
        iconClass: 'tick',
        style:     'float: left;',
        label:     phpr.nls.get('OK'),
        onClick:   dojo.hitch(this, function() {
            callback(true);
        })
    });
    var buttonCancel = new dijit.form.Button({
        baseClass: 'positive',
        iconClass: 'cross',
        style:     'float: right;',
        label:     phpr.nls.get('Cancel'),
        onClick:   dojo.hitch(this, function() {
            callback(false);
        })
    });
    var confirmDialog = new phpr.Dialog({
        title:     phpr.nls.get('Confirmation'),
        draggable: false,
        style:     "width: 300px;"
    });

    buttonContent.domNode.appendChild(buttonOK.domNode);
    buttonContent.domNode.appendChild(buttonCancel.domNode);

    content.domNode.appendChild(question.domNode);
    content.domNode.appendChild(dojo.create('br'));
    content.domNode.appendChild(buttonContent.domNode);

    confirmDialog.containerNode.appendChild(content.domNode);
    confirmDialog.show();
    // avoid cyclic refs
    content = null;
    question = null;
    buttonContent = null;
    buttonOK = null;
    buttonCancel = null;
    return confirmDialog;
};

phpr.getAbsoluteUrl = function(suffix) {
    if (location.href.indexOf('index.php') === -1) {
        return location.href.substring(0, location.href.lastIndex.Of('/') + 1) + (suffix || '');
    } else {
        return location.href.substring(0, location.href.indexOf('index.php')) + (suffix || '');
    }
};

dojo.provide("phpr.Default.System.TabController");
dojo.declare("phpr.Default.System.TabController", [dijit.layout.TabController], {
    "class": "dijitTabContainerTop-tabs",
    tabStripClass: null,
    doLayout: true,
    garbageCollector: null,
    templateString: "<div role='tablist' dojoAttachEvent='onkeypress:onkeypress'></div>",
    constructor: function() {
        this.garbageCollector = new phpr.Default.System.GarbageCollector();
    },
    destroy: function() {
        this.inherited(arguments);
        this.garbageCollector.collect();
    },
    onButtonClick: function(page) {
        this.onSelectChild(page);

        page.callback();
    },
    onSelectChild: function(page) {
        if (!page) {
            return;
        }

        if (this._currentChild) {
            var oldButton = this.pane2button[this._currentChild.id];
            oldButton.set('checked', false);
            dijit.setWaiState(oldButton.focusNode, "selected", "false");
            oldButton.focusNode.setAttribute("tabIndex", "-1");
        }

        var newButton = this.pane2button[page.id];
        newButton.set('checked', true);
        dijit.setWaiState(newButton.focusNode, "selected", "true");
        this._currentChild = page;
        newButton.focusNode.setAttribute("tabIndex", "0");
    },
    getEntryFromOptions: function(options) {
        options = options || {};
        // this is just a dummy, as the tabcontroller needs "pages" but we don't need or want them
        var entry = new dijit._Widget({
            title: options.moduleLabel,
            showTitle: options.moduleLabel,
            tooltip: options.moduleLabel,
            watch: function() {
                return { unwatch: function() {} };
            },
            dir: "",
            lang: "",
            callback: options.callback || function() {}
        }, dojo.create('div'));

        this.garbageCollector.addNode(entry);

        return entry;
    }
});
