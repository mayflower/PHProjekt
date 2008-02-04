dojo.provide("phpr");

// This path is relative to the dojo.js (see index.html)
dojo.registerModulePath("phpr", "../../phpr");

dojo.require("dojo.parser");

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
    var l = oldWidgetNodes.length;
    for (var i=0; i<l; i++) {
        var w = dijit.byNode(oldWidgetNodes[i]);
        if (w) {
            w.destroy();
        }
    }
};



phpr.send = function(/*Object*/paramsIn) {
    // Send the given content to the server using the default values,
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
            //console.debug(response, ioArgs);
            alert("Error, please try again.\nError:"+dojo.toJson(response)+"\n\n"+dojo.toJson(ioArgs));
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
            _onEnd();
            if (!data.ret && (data.error || data.errors)) {
                alert(data.error || data.errors);
            }
        };
    }
    dojo.xhrPost({
        url:params.url,
        content:params.content,
        handleAs:"json-comment-filtered",
        error:_onError,
        load:_onSuccess
    });
};

phpr.getData = function(url, callback){
    dojo.xhrPost(
    {
        url         :	url,
        handleAs    :   'json',
        timeout     :   10000,
        load: function(data) {
            callback(data);
        },
        error       :   function(response, ioArgs) {
            alert('Error! No data received! ' + ioArgs);
            return response;
        },
        sync		:	true,
    }
    )
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
}