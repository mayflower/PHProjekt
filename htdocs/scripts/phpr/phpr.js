dojo.provide("phpr");

dojo.registerModulePath("phpr", "../../phpr");

dojo.require("dojo.parser");
dojo.require("dojox.data.QueryReadStore");

// global vars
var module = null;
var webpath = null;
var currentProjectId = null;

phpr.initWidgets = function(el) {
    // This parses the given node and inits the widgets found in there.
    if (dojo.isString(el)) {
        el = dojo.byId(el);
    }
    dojo.parser.parse(el);
};

phpr.destroyWidgets = function(el) {
    // Destroy all the old widgets, so dojo can init the new ones with the same IDs again.
    var oldWidgetNodes = dojo.query("[widgetId]", dojo.byId(el));
    for (var i = 0; i < oldWidgetNodes.length; i++) {
        if (dijit.byNode(oldWidgetNodes[i])) {
            dijit.byNode(oldWidgetNodes[i]).destroy();
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
        handleAs:"text",
        onSuccess:null,
        onError:null,
        onEnd:null,
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
                alert("Please inform the admin, this error should not occur.\n\n"+e);
            }
        };
    } else {
        _onSuccess = function(data) {
            new phpr.handleResponse('serverFeedback',data);
            _onEnd();
            if (!data.ret && (data.error || data.errors)) {
                alert(data.error || data.errors);
            }
        };
    }
    dojo.xhrPost({
        url		:	params.url,
        content	:	params.content,
        handleAs:   params.handleAs,
        error	:	_onError,
        load	:	_onSuccess
    });
};
phpr.handleResponse = function(resultArea,result)
{
    if (dijit.byId(resultArea)) {
        phpr.destroyWidgets(resultArea);
    }	
    var css = result.__className;
    if(!css){
        css = 'error'; 
    } 
    var message= result.message
    if (!message) {
        message = "";
    }
    new phpr.ServerFeedback({cssClass: css, output:message},dojo.byId(resultArea))
    
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

dojo.declare("phpr.ReadStore", dojox.data.QueryReadStore, {
    // We need the store explicitly here, since we have to pass it to the grid model.
    requestMethod:"post",
    doClientPaging:false,
    
    _filterResponse: function(data){
        ret = {
            items: [{
                "metadata": data.metadata},
                {"data": data.data}]
        };
        return ret;
    }

});
dojo.require("dijit.form.DateTextBox");
dojo.declare("phpr.DateTextBox",[dijit.form.DateTextBox], {
    serialize: function(d, options) {
                    return dojo.date.locale.format(d, {selector:'date', datePattern:'dd-MMM-yyyy'}).toLowerCase();
                }
});
dojo.declare("phpr.ServerFeedback",
    [dijit._Widget, dijit._Templated],
    {
        // summary:
        // A class for displaying the ServerFeedback
        // This class receives the Server Feedback and displays it to the User
        
        cssClass:     null,
        output:       null,		
        templatePath: dojo.moduleUrl("phpr.Default", "template/ServerFeedback.html"),
    }
);