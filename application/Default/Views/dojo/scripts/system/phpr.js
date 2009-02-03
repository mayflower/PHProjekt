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
dojo.require("dijit._editor._Plugin");
dojo.require("dijit._editor.html");
dojo.require("dijit._editor.plugins.EnterKeyHandling");
dojo.require("dijit._editor.selection");
dojo.require("dijit._editor.range");
dojo.require("dijit._editor.RichText");
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
dojo.require("dojox.gfx");
dojo.require("dojox.gfx.shape");
dojo.require("dojox.gfx.path");

// Dojox layout
dojo.require("dojox.form.RangeSlider");
dojo.require("dojox.layout.ExpandoPane");
dojo.require("dojox.layout.ScrollPane");
dojo.require("dojox.widget.Toaster");

// global vars
var module = null;
var webpath = null;
var currentProjectId = null;
var rootProjectId = null;
var userTags = null;
var currentTags = null;
var serverFeedback = null;
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
        url:"",
        content:"",
        handleAs: "json",
        onSuccess:null,
        onError:null,
        onEnd:null,
        sync:false,
        chunkMap:{}
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
            new phpr.handleResponse('serverFeedback',response);
            _onEnd();
        }
    }

    if (params.onSuccess) {
        // If you define onSuccess, make sure to also show the error, for ret=False!!!!!!!
        _onSuccess = function(data, ioArgs) {
            try {
                // 500 is the error code for logut
                if (data.code && data.code == 500) {
                    location = phpr.webpath+"index.php/Login/logout";
                    return;
                } else {
                    params.onSuccess(data, ioArgs);
                    _onEnd();
                    phpr.loading.hide();
                }
            } catch(e) {
                var response = {};
                response.type ='error';
                response.message = e;
                new phpr.handleResponse('serverFeedback',response);
            }
        };
    } else {
        _onSuccess = function(data) {
            new phpr.handleResponse('serverFeedback',data);
            _onEnd();
        };
    }
    dojo.xhrPost({
        url        :    params.url,
        content    :    params.content,
        handleAs:   params.handleAs,
        sync    :   params.sync,
        error    :    _onError,
        load    :    _onSuccess
    });
};

phpr.handleResponse = function(resultArea,result)
{
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

phpr.getCurrent = function(data, identifier, value){
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
    // summary:
    //    Center and make bold an error message
    // description:
    //    Center and make bold an error message
    var output = '';
    output += '<div style="text-align: center; margin: 10px 10px 10px 10px; font-weight: bold;">';
    output += phpr.nls.get(message);
    output += '</div>';
    return output;
};

phpr.isValidInputKey = function(key) {
    // summary:
    //    Return if a key is a valid input key
    // description:
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
    // summary:
    //    Get and return data from the server
    // description:
    //    The data is request to the server
    //    and then is cached for the future used.
    _internalCache: new Array(),

    addStore:function(params) {
        // summary:
        //    Set a new store for save the data
        // description:
        //    Set a new store for save the data
        if (!this._internalCache[params.url]) {
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
        // summary:
        //    Request the data
        // description:
        //    If the data is not cached, request to the server.
        //    Then return to the processData function
        if (typeof params.processData == "undefined") {
            params.processData = null;
        }
        if (this._internalCache[params.url]['data'].length == 0) {
            phpr.loading.show();
            this._internalCache[params.url]['store'].fetch({
                onComplete: dojo.hitch(this, "saveData", {
                    url:         params.url,
                    processData: params.processData
                })}
            );
        } else if (params.processData) {
            params.processData.call();
        }
    },

    saveData:function(params, data) {
        // summary:
        //    Store the data in the cache
        // description:
        //    Store the data in the cache
        //    Then return to the processData function
        this._internalCache[params.url]['data'] = data;
        phpr.loading.hide();
        if (params.processData) {
            params.processData.call();
        }
    },

    getData:function(params) {
        // summary:
        //    Return the "data" tag from the server
        // description:
        //    Return the "data" tag from the server
        return this.getStore(params).getValue(this._internalCache[params.url]['data'][0], "data") || Array();
    },

    getMetaData:function(params) {
        // summary:
        //    Return the "metadata" tag from the server
        // description:
        //    Return the "metadata" tag from the server
        return this.getStore(params).getValue(this._internalCache[params.url]['data'][1], "metadata") || Array();
    },

    deleteData:function(params) {
        // summary:
        //    Delete the cache
        // description:
        //    Delete the cache
        if (this._internalCache[params.url]) {
           this._internalCache[params.url]['data'] = new Array();
        }
    },

    getStore:function(params) {
        // summary:
        //    Return the current data.store
        // description:
        //    Return the current data.store
        return this._internalCache[params.url]['store'];
    },

    deleteAllCache:function() {
        // summary:
        //    Delete all the cache
        // description:
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
    // summary:
    //    Request to the server
    // description:
    //    Request to the server and return an array with
    //    data and metadata values
    requestMethod:"post",
    doClientPaging:false,

    _assertIsItem:function(item) {
    },

    _filterResponse:function(data) {

        // 500 is the error code for logut
        if (data.code && data.code == 500) {
            location = phpr.webpath + "index.php/Login/logout";
            return;
        }

        if (typeof data.data == 'undefined') {
            data.data = new Array();
        }
        if (data.data.length == 0 && typeof data.metadata == 'undefined') {
            var retData     = data;
            var retMetaData = new Array();
        } else {
            var retData     = data.data;
            var retMetaData = data.metadata;
        }
        ret = {
            items: [
                {"data":     retData},
                {"metadata": retMetaData}]
        }
        return ret;
    }
});

dojo.declare("phpr.DateTextBox",[dijit.form.DateTextBox], {
    serialize:function(d, options) {
        // summary:
        //     This function overwrites the dijit.form.DateTextBox display
        //     description:
        //     Make sure that the date is not only displayed localized, but also
        //     the value which is returned is set to this date format
        return dojo.date.locale.format(d, {selector:'date', datePattern:'dd-MMM-yyyy'}).toLowerCase();
    }
});

dojo.declare("phpr.ServerFeedback", [dijit._Widget], {
    // summary:
    //     A class for displaying the ServerFeedback
    // description:
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
    // summary:
    //     Simple class for show or hide the loading icon
    // description:
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
    // summary:
    //     Trasnlation class
    // description:
    //     Collect all the trasnlated strings into an array
    //     and return the request string translateds
    _strings: {},

    constructor:function(translatedStrings) {
       this._strings = translatedStrings;
    },

    get:function(string, module) {
        // Special module
        if (module && this._strings[module] && this._strings[module][string]) {
            //return module + " - " + this._strings[module][string];
            return this._strings[module][string];
        // Current module
        } else if (this._strings[phpr.module] && this._strings[phpr.module][string]) {
            //return phpr.module + " - " + this._strings[phpr.module][string];
            return this._strings[phpr.module][string];
        // Default module
        } else if (this._strings['Default'] && this._strings['Default'][string]) {
            //return "Default - " + this._strings['Default'][string];
            return this._strings['Default'][string];
        } else {
            // Check if the string is in other module
            for (var module in this._strings) {
                if (this._strings[module] && this._strings[module][string]) {
                    //return module + " - " + this._strings[module][string];
                    return this._strings[module][string];
                }
            }
            // Unstranslated string
            return string;
        }
    }
});
