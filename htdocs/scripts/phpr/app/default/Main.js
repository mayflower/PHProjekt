dojo.provide("phpr.app.default.Main");

// We need the dtl for rendering the template (default.html).
dojo.require("dojox.dtl");

dojo.require("phpr.Component");

// Load the widgets the template uses.
dojo.require("dijit.layout.SplitContainer");
dojo.require("dijit.layout.TabContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.Toolbar");
dojo.require("dijit.form.Button");

// app specific files
dojo.require("phpr.app.default.Tree");
dojo.require("phpr.app.default.Grid");
//dojo.require("phpr.app.default.Details");


dojo.declare("phpr.app.default.Main", phpr.Component, {
    
    tree:null,
    grid:null,
    
    constructor:function() {
        this.render(["phpr.app.default.template", "main.html"], dojo.body());
        
        // Do this after complete page load (= window.onload).
        dojo.addOnLoad(dojo.hitch(this, function() {
                // Load the components, tree, list and details.
                this.tree = new phpr.app.default.Tree(this);
//                this.grid = new phpr.app.default.Grid(this);
            })
        );
        
        dojo.subscribe("tree.onNodeClick", null, dojo.hitch(this, "openProject"))
    },
    
    openProject:function(data) {
        //console.debug(data);
        var el = dojo.byId("contentBox");
        phpr.destroyWidgets(el);
        this.render(["phpr.app.default.template", "tabs.html"], el);
        
        // Load first tab. Is this code right here???? thinking ...
        var w = dijit.byId("tabBox");
        var content = this.render(["phpr.app.default.template", "content.html"]);
        w.getChildren()[0].setContent(content);
        this.grid = new phpr.app.default.Grid(this);
    }
    
});
/*
{"metadata":[
             {"key":"title","label":"title","type":"textfield","hint":"title","order":0,"position":2,"fieldset":"","range":null,"required":true,"readOnly":false},
             {"key":"projectId","label":"project","type":"tree","hint":"project","order":0,"position":1,"fieldset":"","range":"Project","required":false,"readOnly":false},
             {"key":"startDate","label":"startDate","type":"date","hint":"startDate","order":0,"position":4,"fieldset":"","range":null,"required":true,"readOnly":false},
             {"key":"endDate","label":"endDate","type":"date","hint":"endDate","order":0,"position":5,"fieldset":"","range":null,"required":true,"readOnly":false},
             {"key":"priority","label":"priority","type":"selectbox","hint":"priority","order":0,"position":6,"fieldset":"",
                "range":{"1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10"},"required":true,"readOnly":false},
            {"key":"currentStatus","label":"currentStatus","type":"selectbox","hint":"currentStatus","order":0,"position":7,"fieldset":"",
                "range":{"1":"Accepted","2":"Working","4":"Ended","5":"Stopped","7":"Waiting"},"required":false,"readOnly":false}],
"data":[
        {"id":"1","title":"Todo of Test Project","notes":"","ownerId":"1","projectId":"1","startDate":"2007-12-12",
            "endDate":"2007-12-31","priority":"0","currentStatus":"working","read":"","write":"","admin":""}
        ]
}
*/