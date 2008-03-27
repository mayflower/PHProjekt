dojo.provide("phpr.Administration.Tree");

dojo.require("phpr.Component");

dojo.declare("phpr.Administration.Tree", phpr.Component, {
   	_treeNode:  null,
	treeWidget: null,
	module:     "Administration",
    
    constructor: function(main) {
        var treepath = main.webpath + "scripts/phpr/Administration/admintree.json";
        this.main    = main;

		this._treeNode = dojo.byId("treeBox");

		if (dijit.byId(this._treeNode)) {
			phpr.destroyWidgets(this._treeNode);
		}
		
        this.render(["phpr.Administration.template", "tree.html"], this._treeNode, {url: treepath});
                    
		this.treeWidget = dijit.byId("treeNode");
        dojo.connect(this.treeWidget, "onClick", dojo.hitch(this, "onItemClick"));
    },
    
    onItemClick: function(item) {
		if(!item) { 
		  item = [];
	    }
		  
		dojo.publish("Administration.tree.nodeClick", [item]); 
    }
});
