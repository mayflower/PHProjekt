dojo.provide("phpr.Default.Tree");

dojo.require("phpr.Component");

dojo.require("dijit.Tree");
dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.Default.Tree", phpr.Component, {
    
   	_treeNode:  null,
	treeWidget: null,
	module:     null,
    
    constructor: function(main) {
        var treepath = phpr.webpath + "index.php/Project/index/jsonTree";
        this.main    = main;

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
		this.publish("changeProjekt", [item]); 
    }
});