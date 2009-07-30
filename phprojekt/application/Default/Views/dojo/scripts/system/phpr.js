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

dojo.provide("phpr");

// Dojo Base
dojo.require("dojo._base.Color");
dojo.require("dojo._base.connect");
dojo.require("dojo._base.declare");
dojo.require("dojo._base.fx");
dojo.require("dojo._base.html");
dojo.require("dojo._base.lang");
dojo.require("dojo.AdapterRegistry");
dojo.require("dojo.cldr.monetary");
dojo.require("dojo.cldr.supplemental");
dojo.require("dojo.colors");
dojo.require("dojo.cookie");
dojo.require("dojo.currency");
dojo.require("dojo.fx");
dojo.require("dojo.html");
dojo.require("dojo.i18n");
dojo.require("dojo.number");
dojo.require("dojo.parser");
dojo.require("dojo.regexp");
dojo.require("dojo.string");

// Dojo Date
dojo.require("dojo.date");
dojo.require("dojo.date.locale");
dojo.require("dojo.date.stamp");

// Dojo Dnd
dojo.require("dojo.dnd.autoscroll");
dojo.require("dojo.dnd.Avatar");
dojo.require("dojo.dnd.Container");
dojo.require("dojo.dnd.common");
dojo.require("dojo.dnd.Manager");
dojo.require("dojo.dnd.move");
dojo.require("dojo.dnd.Moveable");
dojo.require("dojo.dnd.Mover");
dojo.require("dojo.dnd.Selector");
dojo.require("dojo.dnd.Source");
dojo.require("dojo.dnd.TimedMoveable");

// Dojo Data
dojo.require("dojo.data.ItemFileReadStore");
dojo.require("dojo.data.ItemFileWriteStore");
dojo.require("dojo.data.util.filter");
dojo.require("dojo.data.util.simpleFetch");
dojo.require("dojo.data.util.sorter");

// Dijit Base
dojo.require("dijit._base");
dojo.require("dijit._base.focus");
dojo.require("dijit._base.manager");
dojo.require("dijit._base.place");
dojo.require("dijit._base.popup");
dojo.require("dijit._base.scroll");
dojo.require("dijit._base.sniff");
dojo.require("dijit._base.typematic");
dojo.require("dijit._base.wai");
dojo.require("dijit._base.window");
dojo.require("dijit._Calendar");
dojo.require("dijit._Container");
dojo.require("dijit._Templated");
dojo.require("dijit._TimePicker");
dojo.require("dijit._Widget");

// Dijit Misc
dojo.require("dijit.Dialog");
dojo.require("dijit.Menu");
dojo.require("dijit.TitlePane");
dojo.require("dijit.Toolbar");
dojo.require("dijit.Tooltip");
dojo.require("dijit.Tree");

// Dijit Layout
dojo.require("dijit.layout._LayoutWidget");
dojo.require("dijit.layout.BorderContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.layout.StackContainer");
dojo.require("dijit.layout.TabContainer");

// Dijit Form
dojo.require("dijit.form._DateTimeTextBox");
dojo.require("dijit.form._FormWidget");
dojo.require("dijit.form._Spinner");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.CheckBox");
dojo.require("dijit.form.ComboBox");
dojo.require("dijit.form.CurrencyTextBox");
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.form.Form");
dojo.require("dijit.form.MultiSelect");
dojo.require("dijit.form.NumberSpinner");
dojo.require("dijit.form.NumberTextBox");
dojo.require("dijit.form.Slider");
dojo.require("dijit.form.Textarea");
dojo.require("dijit.form.TextBox");
dojo.require("dijit.form.TimeTextBox");
dojo.require("dijit.form.ValidationTextBox");

// Dijit Editor
dojo.require("dijit.ColorPalette");
dojo.require("dijit._editor._Plugin");
dojo.require("dijit._editor.html");
dojo.require("dijit._editor.plugins.EnterKeyHandling");
dojo.require("dijit._editor.plugins.FontChoice");
dojo.require("dijit._editor.plugins.LinkDialog");
dojo.require("dijit._editor.plugins.TextColor");
dojo.require("dijit._editor.range");
dojo.require("dijit._editor.RichText");
dojo.require("dijit._editor.selection");
dojo.require("dijit.Editor");

// Dojox base
dojo.require("dojox.data.QueryReadStore");

// Dojox grid
dojo.require("dojox.grid._EditManager");
dojo.require("dojox.grid._Events");
dojo.require("dojox.grid._FocusManager");
dojo.require("dojox.grid._Grid");
dojo.require("dojox.grid._Layout");
dojo.require("dojox.grid._RowManager");
dojo.require("dojox.grid._RowSelector");
dojo.require("dojox.grid._Scroller");
dojo.require("dojox.grid._View");
dojo.require("dojox.grid._ViewManager");
dojo.require("dojox.grid.cells");
dojo.require("dojox.grid.cells._base");
dojo.require("dojox.grid.cells.dijit");
dojo.require("dojox.grid.DataSelection");
dojo.require("dojox.grid.DataGrid");
dojo.require("dojox.grid.Selection");
dojo.require("dojox.grid.util");
dojo.require("dojox.html.metrics");

// Dojox strings
dojo.require("dojox.string.Builder");
dojo.require("dojox.string.sprintf");
dojo.require("dojox.string.tokenize");

// Dojox templates
dojo.require("dojox.dtl");
dojo.require("dojox.dtl._base");
dojo.require("dojox.dtl._Templated");
dojo.require("dojox.dtl.Context");
dojo.require("dojox.dtl.filter.htmlstrings");
dojo.require("dojox.dtl.filter.strings");
dojo.require("dojox.dtl.tag.logic");

// Dojox fx
dojo.require("dojox.fx._base");
dojo.require("dojox.fx._core");
dojo.require("dojox.fx.scroll");
dojo.require("dojox.gfx");
dojo.require("dojox.gfx.shape");
dojo.require("dojox.gfx.path");

// Dojox layout
dojo.require("dojox.form.RangeSlider");
dojo.require("dojox.layout.ExpandoPane");
dojo.require("dojox.layout.ScrollPane");
dojo.require("dojox.widget.Toaster");

// Global vars
var module           = null;
var webpath          = null;
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
    if (dojo.byId(el)) {
        var oldWidgetNodes = dojo.query("[widgetId]", dojo.byId(el));
        for (var i = 0; i < oldWidgetNodes.length; i++) {
            if (dijit.byNode(oldWidgetNodes[i])) {
                dijit.byNode(oldWidgetNodes[i]).destroy();
            }
        }
    } else if (dijit.byId(el)) {
        dijit.byId(el).destroy();
    }
};

phpr.destroyWidget = function(el) {
    // Destroy only one widgwt using the id
    if (dijit.byId(el)) {
        dijit.byId(el).destroy();
    }
};

phpr.send = function(/*Object*/paramsIn) {
    // Send the given content to the server using the Default values,
    // if you need something special dont use this function.
    //
    //  Example call:
    //      phpr.send({url:"/live-save/", content:{data:1}, chunkMap:{tags:"tagsEl"}, onSuccess:function() {...}});

    // onEnd: Is always called after the onSuccess and onError have finished.
    //     This might be used for resetting things that are common for both cases.

    phpr.loading.show();
    var params = {
        url:       "",
        content:   "",
        handleAs:  "json",
        onSuccess: null,
        onError:   null,
        onEnd:     null,
        sync:      false,
        chunkMap:  {}
    }
    if (dojo.isObject(paramsIn)) {
        dojo.mixin(params, paramsIn);
    }
    var _onError, _onSuccess = function() {};
    var _onEnd = params.onEnd || function() {};

    if (params.onError) {
        _onError = function(response, ioArgs) {
            params.onError(response, ioArgs);
            _onEnd();
        }
    } else {
        _onError = function(response, ioArgs) {
            phpr.handleError(params.url, 'php');
            _onEnd();
        }
    }

    _onSuccess = function(data, ioArgs) {
        try {
            // 500 is the error code for logut
            if (data.code && data.code == 500) {
                location = phpr.webpath + "index.php/Login/logout";
                return;
            } else {
                if (params.onSuccess) {
                    params.onSuccess(data, ioArgs);
                } else {
                    new phpr.handleResponse('serverFeedback', data);
                }
                _onEnd();
                phpr.loading.hide();
            }
        } catch(e) {
            phpr.handleError(params.url, 'exception');
            return;
        }
    };

    dojo.xhrPost({
        url:      params.url,
        content:  params.content,
        handleAs: params.handleAs,
        sync:     params.sync,
        error:    _onError,
        load:     _onSuccess
    });
};

phpr.handleResponse = function(resultArea, result) {
    phpr.loading.hide();
    var css = 'error';
    if (result.type == 'success') {
        css = 'success';
    } else if (result.type == 'notice') {
        css = 'notice';
    }
    var message= result.message
    if (!message) {
        return;
    }
    phpr.serverFeedback.addMessage({cssClass: css, output:message});
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
    _internalCache: new Array(),

    _active: false,

    addStore:function(params) {
        // Summary:
        //    Set a new store for save the data
        // Description:
        //    Set a new store for save the data
        if (typeof this._internalCache[params.url] == 'undefined') {
            store = new phpr.ReadStore({url: params.url});
            this._internalCache[params.url] = {
                data:  new Array(),
                store: store
            };
        } else if (params.noCache) {
            store = new phpr.ReadStore({url: params.url});
            this._internalCache[params.url] = {
                data:  new Array(),
                store: store
            };
        }
    },

    requestData:function(params) {
        // Summary:
        //    Request the data
        // Description:
        //    If the data is not cached, request to the server.
        //    Then return to the processData function
        if (typeof params.processData == "undefined") {
            params.processData = null;
        }
        if (this._internalCache[params.url]['data'].length == 0) {
            phpr.loading.show();
            if (this._active == true) {
                setTimeout(dojo.hitch(this, "requestData", params), 500);
            } else {
                this._active = true;
                this._internalCache[params.url]['store'].fetch({
                    onComplete: dojo.hitch(this, "saveData", {
                        url:         params.url,
                        processData: params.processData
                    }),
                    onError: dojo.hitch(this, "errorHandler", {
                        url:         params.url,
                        processData: params.processData
                    })}
                );
            }
        } else if (params.processData) {
            params.processData.call();
        }
    },

    errorHandler:function(scope, error) {
        // Summary:
        //    Display a PHP or JS error
        // Description:
        //    If there is some data before the json
        //    the error is cached and showed
        //    Also is cached the JS error

        // Get the message error
        if ((error.number && (error.number & 0xFFFF == 1002 || error.number & 0xFFFF == 1006)) // IE
            || (error.name && error.name == "SyntaxError")) { // FF
            // PHP Error
            phpr.handleError(scope.url, 'php');
        } else {
            // Js error
            var message = null;
            if (error.message) {
                message = error.message;
            } else if (error.description) {
                message = error.description;
            }
            phpr.handleError(scope.url, 'js', message);
        }
    },

    saveData:function(params, data) {
        // Summary:
        //    Store the data in the cache
        // Description:
        //    Store the data in the cache
        //    Then return to the processData function
        this._active = false;
        this._internalCache[params.url]['data'] = data;
        phpr.loading.hide();
        if (params.processData) {
            params.processData.call();
        }
    },

    getData:function(params) {
        // Summary:
        //    Return the "data" tag from the server
        // Description:
        //    Return the "data" tag from the server
        return this.getStore(params).getValue(this._internalCache[params.url]['data'][0], "data") || Array();
    },

    getMetaData:function(params) {
        // Summary:
        //    Return the "metadata" tag from the server
        // Description:
        //    Return the "metadata" tag from the server
        return this.getStore(params).getValue(this._internalCache[params.url]['data'][1], "metadata") || Array();
    },

    deleteData:function(params) {
        // Summary:
        //    Delete the cache
        // Description:
        //    Delete the cache
        if (this._internalCache[params.url]) {
           this._internalCache[params.url]['data'] = new Array();
        }
    },

    deleteDataPartialString:function(params) {
        // Summary:
        //    Deletes the cache for the urls that start with the received string.
        for (url in this._internalCache) {
            var urlLeft = url.substring(0, params.url.length);
            if (urlLeft == params.url) {
                this._internalCache[url]['data'] = new Array();
            }
        }
    },

    getStore:function(params) {
        // Summary:
        //    Return the current data.store
        // Description:
        //    Return the current data.store
        return this._internalCache[params.url]['store'];
    },

    deleteAllCache:function() {
        // Summary:
        //    Delete all the cache
        // Description:
        //    Delete all the cache
        for (var i in this._internalCache) {
            // Special case for global modules since are not reloaded
            if (this._internalCache[i] && i != phpr.webpath + "index.php/Core/module/jsonGetGlobalModules") {
                this._internalCache[i]['data'] = new Array();
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

    _assertIsItem:function(item) {
    },

    _filterResponse:function(data) {
        var retData     = new Array();
        var retMetaData = new Array();

        if (!data) {
            phpr.handleError(this.url, 'exception');
        } else if (data.code && data.code == 500) {
            // 500 is the error code for logut
            location = phpr.webpath + "index.php/Login/logout";
        } else if (data.type && data.type == "error") {
            phpr.handleError(this.url, 'error', data.message);
        } else {
            var customData = false;
            if (typeof data.data == 'undefined') {
                customData = true;
                data.data  = new Array();
            }

            if (true == customData && data.data.length == 0 && typeof data.metadata == 'undefined') {
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
        }

        return ret;
    }
});

dojo.declare("phpr.DateTextBox", [dijit.form.DateTextBox], {
    serialize:function(d, options) {
        // Summary:
        //     This function overwrites the dijit.form.DateTextBox display
        // Description:
        //     Make sure that the date is not only displayed localized, but also
        //     the value which is returned is set to this date format
        return dojo.date.locale.format(d, {selector:'date', datePattern:'yyyy-MM-dd'}).toLowerCase();
    }
});

dojo.declare("phpr.ServerFeedback", [dijit._Widget], {
    // Summary:
    //     A class for displaying the ServerFeedback
    // Description:
    //     This class receives the Server Feedback and displays it to the User
    messages:[],
    displayedMessages:[],

    addMessage:function(message) {
        this.messages.push(message);
        this.displayMessage(message);
    },

    deleteLastMessage:function(message) {
        this.messages.pop();
    },

    displayMessage:function(message) {
        this.displayedMessages = [message];
        for (i in this.displayedMessages) {
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
    hide:function() {
        if (dojo.byId('loadingIcon')) {
            dojo.byId('loadingIcon').style.display = 'none';
        }
    },

    show:function() {
        if (dojo.byId('loadingIcon')) {
            dojo.byId('loadingIcon').style.display = 'inline';
        }
    }
});

dojo.declare("phpr.translator", null, {
    // Summary:
    //     Trasnlation class
    // Description:
    //     Collect all the trasnlated strings into an array
    //     and return the request string translateds
    _strings: {},

    constructor:function(translatedStrings) {
       this._strings = translatedStrings;
    },

    get:function(string, module) {
        var returnValue;

        // Special module
        if (module && this._strings[module] && this._strings[module][string]) {
            returnValue = this._strings[module][string];
        // Current module
        } else if (this._strings[phpr.module] && this._strings[phpr.module][string]) {
            returnValue = this._strings[phpr.module][string];
        // Default module
        } else if (this._strings['Default'] && this._strings['Default'][string]) {
            returnValue = this._strings['Default'][string];
        } else {
            // Unstranslated string
            returnValue = string;
        }
        return returnValue;
    }
});

dojo.declare("phpr.Dialog", [dijit.Dialog], {
    // Summary:
    //     Provide a dialog with some changes
    // Description:
    //     Allow dialog into other dialog and fix the key input
    _onKey:function(/*Event*/ evt) {
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
                    } catch(e) {
                        /*squelch*/
                    }
                }
            }
        }
    }
});

dojo.declare("phpr.TreeContent", null, {
    // Summary:
    //     Manage the visibility of the tree panel
    // Description:
    //     Manage the visibility of the tree panel
    fadeOut:function() {
        if (dojo.style("treeBox", "opacity") != 0.5) {
            dojo.style("treeBox", "opacity", 0.5);
        }
    },

    fadeIn:function() {
        if (dojo.style("treeBox", "opacity") != 1) {
            dojo.style("treeBox", "opacity", 1);
        }
    }
});

dojo.declare("phpr.InitialScreen", null, {
    // Summary:
    //     Manage the visibility of the page on init
    // Description:
    //     Manage the visibility of the page on init
    start:function() {
        dojo.style("completeContent", "opacity", 0);
    },

    end:function() {
        dojo.style("completeContent", "opacity", 1);
        dojo.style("initLoading", "display", "none");
    }
});

phpr.loadJsFile = function(fileName) {
    // Load a js and insert into the head
    var fileRef = document.createElement('script')
    fileRef.setAttribute("type" ,"text/javascript");
    fileRef.setAttribute("src", fileName);
    if (typeof fileRef != "undefined") {
        document.getElementsByTagName("head")[0].appendChild(fileRef)
    }
};

phpr.loadCssFile = function(fileName) {
    // Load a css and insert into the head
    var fileRef = document.createElement("link")
    fileRef.setAttribute("rel", "stylesheet");
    fileRef.setAttribute("type", "text/css");
    fileRef.setAttribute("href", fileName);
    if (typeof fileRef!="undefined") {
        document.getElementsByTagName("head")[0].appendChild(fileRef)
    }
};

phpr.handleError = function(url, type, message) {
    // Process and return an error message
    var response  = {};
    response.type = 'error';

    if (url) {
        response.message = url.replace(phpr.webpath, "") + ': ';
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
        default:
            response.message += phpr.nls.get('Unexpected error');
            break;
    }

    new phpr.handleResponse('serverFeedback', response);
};

dojo.declare("phpr.BreadCrumb", null, {
    // Summary:
    //     Manage the Breadcrumb
    // Description:
    //     Manage the Breadcrumb
    _module:       '',
    _projects:     '',
    _item:         '',
    _lastModule:   null,
    _lastParent:   null,
    _separatorOne: ' / ',
    _separatorTwo: '-',

    setProjects:function(projectsNames) {
        // Summary:
        //     Set the projects tree as one string
        // Description:
        //     Set the projects tree as one string
        this._projects = projectsNames.join(this._separatorTwo.toString());
        this._module   = null;
        this._item     = null;
    },

    setModule:function() {
        // Summary:
        //     Set the module and sub-module
        // Description:
        //     Change the module and sub-module only if these change
        if (phpr.module != this._lastModule || phpr.parentmodule != this._lastParent) {
            if (phpr.parentmodule) {
                this._module = phpr.parentmodule + this._separatorTho + phpr.module;;
                this._lastParent = phpr.parentmodule;
            } else {
                this._module = phpr.module;
            }
            this._item       = null;
            this._lastModule = phpr.module;
        }
    },

    setItem:function(item) {
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

    draw:function() {
        // Summary:
        //     Draw the breadcrumb
        // Description:
        //     Show the breadcrumb in the title
        var breadCrumb = this._projects + this._separatorOne + this._module;
        if (this._item) {
            breadCrumb += this._separatorOne + this._item;
        }
        document.title                    = breadCrumb;
        dojo.byId("breadCrumb").innerHTML = breadCrumb;
    }

});

dojo.declare("phpr.ScrollPane", [dijit.layout._LayoutWidget, dijit._Templated], {
    // Summary:
    //    Scroll widget for manage the module tabs
    // Description:
    //    Scroll widget for manage the module tabs

    // How much incress/decress the left on arrow mouse over
    _scrollRatio: 20,

    // Internal var for stop the propagation
    _allow: true,

    // Current left
    _left: 0,

    // Array with modules and left positions
    _positions: new Array(),

    // Template
    templateString: "<div class=\"phprScrollWindow\" dojoAttachEvent=\"onmouseenter: _enter, ondijitclick: _leave, "
        + "onmouseleave: _leave\">\r\n\t<div class=\"phprScrollArrowLeft\" dojoAttachPoint=\"scrollArrowLeft\" "
        + "dojoAttachEvent=\"onmouseenter: _enterLeft, onmouseleave: _leave\">\r\n\t&nbsp;\r\n\t</div>\r\n\t"
        + "<div class=\"phprScrollWrapper\" style=\"${style}\" dojoAttachPoint=\"wrapper\" "
        + "dojoAttachEvent=\"onmousemove: _calc\">\r\n\t<div class=\"phprScrollPane\" "
        + "dojoAttachPoint=\"containerNode\"></div>\r\n\t</div>\r\n\t<div class=\"phprScrollArrowRight\" "
        + "dojoAttachPoint=\"scrollArrowRight\" dojoAttachEvent=\"onmouseenter: _enterRight, onmouseleave: _leave\">"
        + "\r\n\t&nbsp;\r\n\t</div>\r\n\t</div>\r\n",

    layout:function() {
        // Summary:
        //    Initial the widget
        // Description:
        //    Set style and make the positions array
        dojo.style(this.wrapper, 'width', this.domNode.style['width']);

        var node    = this.containerNode.firstChild.firstChild.firstChild.childNodes;
        var width   = 0;
        var curleft = 0;
        var medium  = Math.floor(this.wrapper["offsetWidth"] / 2);
        var max     = this.getMaxLeft();
        for (var i in node) {
            var offsetWidth = node[i].offsetWidth || 0;
            // Find left
            width += offsetWidth;
            if (width < medium) {
                curleft = 0;
            } else if (width > max) {
                curleft = max;
            } else {
                curleft += offsetWidth;
            }

            if (node[i].id) {
                var id = node[i].id.toString().replace(/navigation_/, "");
                this._positions.push({"id": id, "left": curleft});
            }
        }

        var pos = this.getPosition(phpr.module);
        if (pos) {
            this._left = pos;
        }
        this._set()
    },

    postCreate:function() {
        // Summary:
        //    Initial the widget
        // Description:
        //    Initial the widget
        this.inherited(arguments);
        dojo.style(this.wrapper, "overflow", "hidden");
    },

    _set:function() {
        // Summary:
        //    Set the left scroll
        // Description:
        //    Set the left scroll
        this.wrapper["scrollLeft"] = this._left;
        this._calcButtons();
    },

    _calc:function(e) {
        // Summary:
        //    Move the scroll depending on the mouse movement
        // Description:
        //    If the last position of the mouse and the current one exceeed 100,
        //    move the scroll
        //    100 is for a normal "speed"
        if (this._allow) {
            if ((this._lastPageX - e.pageX) > 100) {
                this._lastPageX = e.pageX;
                this._enterLeft();
            } else if ((this._lastPageX - e.pageX) < -100) {
                this._lastPageX = e.pageX;
                this._enterRight();
           }
        }
    },

    _enter:function(e) {
        // Summary:
        //    Enter to the widget
        // Description:
        //    Set the current mouse position
        this._lastPageX = e.pageX;
    },

    _enterLeft:function() {
        // Summary:
        //    Move the scroll to the left
        // Description:
        //    Move the scroll on left arrow over
        if (this._allow) {
            if (this._left > 1) {
                this._left -= this._scrollRatio;
                if (this._left < 0) {
                    this._left = 0;
                }
                this._set();
                setTimeout(dojo.hitch(this, "_enterLeft"), 50);
            }
        }
    },

    _enterRight:function() {
        // Summary:
        //    Move the scroll to the right
        // Description:
        //    Move the scroll on right arrow over
        if (this._allow) {
            var maxLeft = this.getMaxLeft();
            if (this._left < maxLeft) {
                this._left += this._scrollRatio;
                if (this._left > this.maxLeft) {
                    this._left = this.maxLeft;
                }
                this._set();
                setTimeout(dojo.hitch(this, "_enterRight"), 50);
            }
        }
    },

    _leave:function() {
        // Summary:
        //    Leave the widget
        // Description:
        //    Stop events
        this._allow = false;
        setTimeout(dojo.hitch(this, function() {this._allow = true}), 50);
    },

    _calcButtons:function() {
        // Summary:
        //    Show or hide the arrows
        // Description:
        //    Change the style of the arrow buttons depending on the current scroll
        if (this.wrapper["scrollLeft"] == 0) {
            this.scrollArrowLeft.className = "phprScrollArrowLeftHide";
            this._leave();
        } else {
            this.scrollArrowLeft.className = "phprScrollArrowLeft";
        }

        if (this.wrapper["scrollLeft"] == this.getMaxLeft()) {
            this.scrollArrowRight.className = "phprScrollArrowRightHide";
            this._leave();
        } else {
            this.scrollArrowRight.className = "phprScrollArrowRight";
        }
    },

    getPosition:function(module) {
        // Summary:
        //    Return the module position
        // Description:
        //    Return the left for a module
        for (var i in this._positions) {
            if (this._positions[i].id == module) {
                return this._positions[i].left;
            }
        }

        return null;
    },

    getMaxLeft:function() {
        // Summary:
        //    Get max left available for the scroll
        // Description:
        //    Get max left available for the scroll
        return this.wrapper["scrollWidth"] - this.wrapper["offsetWidth"];
    }
});
