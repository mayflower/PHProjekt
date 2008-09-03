dojo.provide("phpr");

dojo.require("dojo.parser");
dojo.require("dojo.fx");
dojo.require("dojo.date.locale");
dojo.require("dojo.dnd.Mover");
dojo.require("dojo.dnd.Moveable");
dojo.require("dojo.dnd.move");
dojo.require("dojo.dnd.TimedMoveable");
dojo.require("dojo.data.ItemFileReadStore");
dojo.require("dojo.data.ItemFileWriteStore");

dojo.require("dijit.dijit");
dojo.require("dijit.form.MultiSelect");
dojo.require("dijit.form.Textarea");
dojo.require("dijit.Editor");
dojo.require("dijit.layout.BorderContainer");
dojo.require("dijit.layout.TabContainer");
dojo.require("dijit.layout.SplitContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.Toolbar");
dojo.require("dijit.form.Button");
dojo.require("dijit.Dialog");
dojo.require("dijit.layout.BorderContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.TitlePane");
dojo.require("dijit.Toolbar");
dojo.require("dijit.Menu");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.Form");
dojo.require("dijit.form.Textarea");
dojo.require("dijit.Editor");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit._Templated");
dojo.require("dijit.Tree");
dojo.require("dijit._Calendar");

dojo.require("dojox.grid.DataGrid");
dojo.require("dojox.gfx");
dojo.require("dojox.data.QueryReadStore");
dojo.require("dojox.dtl._HtmlTemplated");
dojo.require("dojox.fx");
dojo.require("dojox.layout.ExpandoPane");
dojo.require("dojox.dtl");
dojo.require("dojox.dtl.Context");
dojo.require("dojox.grid.cells.dijit");

// global vars
var module = null;
var webpath = null;
var currentProjectId = null;
var rootProjectId = null;
var userTags = null;
var currentTags = null;
phpr.initWidgets = function(el) {
    // This parses the given node and inits the widgets found in there.
    if (dojo.isString(el)) {
        el = dojo.byId(el);
    }
    dojo.parser.parse(el);
};

phpr.destroyWidgets = function(el) {
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

phpr.destroySimpleWidget = function(el) {
    // Destroy only one widgwt using the id
    if (dojo.byId(el)) {
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

    //
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
                params.onSuccess(data, ioArgs);
                _onEnd();
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
        url		:	params.url,
        content	:	params.content,
        handleAs:   params.handleAs,
        sync    :   params.sync,
        error	:	_onError,
        load	:	_onSuccess
    });
};

phpr.handleResponse = function(resultArea,result)
{
    var css = 'error';
    if(result.type =='success'){
        css = 'success';
    }
    var message= result.message
    if (!message) {
        css = '';
        message = "";
    }
    dijit.byId(resultArea).addMessage({cssClass: css, output:message});
};

phpr.getCurrent = function(data, identifier, value){
    var current = null;
    for (i=0; i < data.length; i++) {
        if(value == data[i][identifier]){
            current = data[i];
            break;
        }
    }
    return current;
};

phpr.receiveUserTags = function(){
    phpr.send({
		url:       phpr.webpath + 'index.php/Default/Tag/jsonGetTags',
        sync:      true,
		onSuccess: function(data){
             phpr.userTags = data;
             new phpr.handleResponse('serverFeedback', data);
        }
	});
};

phpr.receiveCurrentTags = function(id){
    phpr.send({
		url:       phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module + '/id/'+id,
        sync:      true,
		onSuccess: function(data){
             phpr.currentTags = data;
             new phpr.handleResponse('serverFeedback', data);
        }
	});
};

phpr.getCurrentTags = function(){
    return phpr.currentTags;
};

phpr.getUserTags = function(){
    return phpr.userTags;
};

dojo.declare("phpr.DataStore", null, {
    // summary:
    //    Get and return data from the server
    // description:
    //    The data is request to the server
    //    and then is cached for the future used.
    _internalCache:  new Array(),
    _onComplete:     null,

    addStore:function(params) {
        // summary:
        //    Set a new store for save the data
        // description:
        //    Set a new store for save the data
        if (!this._internalCache[params.url]) {
            store = new phpr.ReadStore({url: params.url});
            this._internalCache[params.url] = {
                data: new Array(),
                store: store,
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
            this._internalCache[params.url]['store'].fetch({onComplete: dojo.hitch(this, "saveData", {url: params.url, processData: params.processData})});
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
        if(this._internalCache[params.url]) {
           this._internalCache[params.url]['data'] = new Array();
        }
    },

    getStore:function(params) {
        // summary:
        //    Return the current data.store
        // description:
        //    Return the current data.store
        return this._internalCache[params.url]['store'];
    }
});

phpr.DataStore = new phpr.DataStore();

dojo.declare("phpr.ReadStore", dojox.data.QueryReadStore, {
    // summary:
    //    Request to the server
    // description:
    //    Request to the server and return an array with
    //    data and metadata values
    requestMethod:"post",
    doClientPaging:false,

    _filterResponse:function(data) {
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

dojo.declare("phpr.ReadHistory", dojox.data.QueryReadStore, {
    // We need the store explicitly here, since we have to pass it to the grid model.
    requestMethod:"post",
    doClientPaging:false,

    _filterResponse: function(data) {
        if (!data.history) {
            data.history = {};
        }
        ret = {
            items: [{
               "history": data.history}]
        };
        return ret;
    }
});

dojo.declare("phpr.ReadData", dojox.data.QueryReadStore, {
    // We need the store explicitly here, since we have to pass it to the grid model.
    requestMethod:"post",
    doClientPaging:false,

    _filterResponse: function(data) {
        if (!data.data) {
            data.data = {};
        }
        ret = {
            items: [{
               "data": data.data}]
        };
        return ret;
    }
});

dojo.require("dijit.form.DateTextBox");
dojo.declare("phpr.DateTextBox",[dijit.form.DateTextBox], {
    serialize: function(d, options) {
        // summary:
        //     This function overwrites the dijit.form.DateTextBox display
        //     description:
        //     Make sure that the date is not only displayed localized, but also
        //     the value which is returned is set to this date format
        return dojo.date.locale.format(d, {selector:'date', datePattern:'dd-MMM-yyyy'}).toLowerCase();
    }
});

dojo.declare("phpr.ServerFeedback",[dijit._Widget, dojox.dtl._HtmlTemplated], {
    // summary:
    //     A class for displaying the ServerFeedback
    // description:
    //     This class receives the Server Feedback and displays it to the User
    widgetsInTemplate: true,
    messages:[],
    displayedMessages:[],

    templatePath: dojo.moduleUrl("phpr.Default", "template/ServerFeedback.html"),
        base: {
            url: dojo.moduleUrl("phpr.Default", "template/serverFeedbackContent.html"),
            shared: true
        },

        addMessage: function(message){
            this.messages.push(message);
            this.displayMessage(message);
        },

        deleteLastMessage: function(message){
            this.messages.pop();
        },

        displayMessage: function(message){
            this.displayedMessages = [message];
            this.setTemplate(dojo.moduleUrl("phpr.Default", "template/ServerFeedback.html"));
            this.render();
            var fadeIn = dojo.fadeIn({
                node:this.serverFeedbackContainer,
                duration: 500
            });

            var fadeOut = dojo.fadeOut({
                node: this.serverFeedbackContainer,
                duration: 5000
            });

            fadeIn.play();
            dojo.connect(fadeIn, "onEnd", function(){
                fadeIn.stop();
                fadeOut.play();
            });
        },

        postCreate: function(){
            this.render();
        }
    }
);