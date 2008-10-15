dojo.provide("feditor");

// This path is relative to the dojo.js (see index.html)
dojo.registerModulePath("feditor", "../../feditor");

dojo.require("dojo.parser");
feditor.initWidgets = function(el) {
    // This parses the given node and inits the widgets found in there.
    if (dojo.isString(el)) {
        el = dojo.byId(el);
    }
    dojo.parser.parse(el);
};
feditor.destroyWidgets = function(el) {
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
feditor.getData = function(url, callback){
    dojo.xhrPost(
    {
        url         :    url,
        handleAs    :   'json',
        timeout     :   10000,
        load: function(data) {
            callback(data);
        },
        error       :   function(response, ioArgs) {
            alert('Error! No data received! ' + ioArgs);
            return response;
        },
        sync        :    true,
    }
    )
};


