dojo.provide("phpr");

dojo.registerModulePath("phpr", "../../phpr");

dojo.require("dojo.parser");
dojo.require("dojox.data.QueryReadStore");
dojo.require("dojox.dtl._HtmlTemplated");
dojo.require("dojox.fx");
dojo.require("dojo.fx");

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
		url:       phpr.webpath + 'index.php/' + phpr.module + '/Tag/jsonGetTags',
        sync:      true,
		onSuccess: function(data){
             phpr.userTags = data;
             new phpr.handleResponse('serverFeedback', data);
        }
	});
};

phpr.receiveCurrentTags = function(id){
    phpr.send({
		url:       phpr.webpath + 'index.php/' + phpr.module + '/Tag/jsonGetTagsByModule/id/'+id,
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

dojo.declare("phpr.ReadStore", dojox.data.QueryReadStore, {
    // We need the store explicitly here, since we have to pass it to the grid model.
    requestMethod:"post",
    doClientPaging:false,

    _filterResponse: function(data){
        ret = {
            items: [{
                "metadata": data.metadata},
                {"data": data.data},
                {"history": data.history}]
        };
        return ret;
    }
});

dojo.declare("phpr.ReadHistory", dojox.data.QueryReadStore, {
    // We need the store explicitly here, since we have to pass it to the grid model.
    requestMethod:"post",
    doClientPaging:false,

    _filterResponse: function(data){
        ret = {
            items: [{
               "history": data.history}]
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