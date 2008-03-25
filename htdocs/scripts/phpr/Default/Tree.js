dojo.provide("phpr.Default.Tree");

dojo.require("phpr.Component");

dojo.require("dijit.Tree");
dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.Default.Tree", phpr.Component, {
    
   	_treeNode:  null,
	treeWidget: null,
	module:     null,
    
    constructor: function(main, module) {
        var treepath = main.webpath + "index.php/Project/index/jsonTree";
        this.main    = main;
        this.module  = module;

		this._treeNode = dojo.byId("treeBox");

		if (dijit.byId(this._treeNode)) {
			phpr.destroyWidgets(this._treeNode);
		}
		
        this.render(["phpr.Default.template", "tree.html"], this._treeNode, {url: treepath});
                    
		this.treeWidget = dijit.byId("treeNode");
        dojo.connect(this.treeWidget, "onClick", dojo.hitch(this, "onItemClick"));
    },
    
    onItemClick: function(item) {
		if(!item) { 
		  item = [];
	    }
		  
		dojo.publish("Project.tree.nodeClick", [item, this.module]); 
    }
});