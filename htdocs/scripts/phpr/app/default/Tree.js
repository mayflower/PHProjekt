dojo.provide("phpr.app.default.Tree");

dojo.require("phpr.Component");
// The dijits the template uses
dojo.require("dijit.Tree");
dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.app.default.Tree", phpr.Component, {
    
    treeWidget:null,
	module:'Project',
    
    constructor:function(main,module) {
        this.main = main;
		this.module = module;
        // Render the tree on the left hand side, from the template.
		//destroy if already exists
		if (dijit.byId("treeNode")) {
			phpr.destroyWidgets("treeNode");
		}		
		var treepath =  this.main.webpath+"index.php/Project/index/jsonList/view/tree";
        this.render(["phpr.app.default.template", "tree.html"], dojo.byId("treeBox"),{url:treepath});
        this.treeWidget = dijit.byId("treeNode");
        dojo.connect(this.treeWidget, "onClick", dojo.hitch(this, "onItemClick"));
    },
    
    onItemClick:function(item) {
		dojo.publish("tree.nodeClick", [item, this.module]); 
   		dojo.publish("tree.leaveClick", [item, this.module]);
    }
    
});