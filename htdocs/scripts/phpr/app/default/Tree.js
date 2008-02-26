dojo.provide("phpr.app.default.Tree");

dojo.require("phpr.Component");
// The dijits the template uses
dojo.require("dijit.Tree");
dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.app.default.Tree", phpr.Component, {
    
   	_treeNode:null,
	treeWidget:null,
	module:'Project',
    
    constructor:function(main,module) {
		this._treeNode = dojo.byId("treeBox");
        this.main = main;
		this.module = module;
        // Render the tree on the left hand side, from the template.
		//destroy if already exists
		if (dijit.byId(this._treeNode)) {
			phpr.destroyWidgets(this._treeNode);
		}
		var treepath =  this.main.webpath+"index.php/Project/index/jsonList/view/tree";
        this.render(["phpr.app.default.template", "tree.html"], this._treeNode,{url:treepath});
		this.treeWidget = dijit.byId("treeNode");
        dojo.connect(this.treeWidget, "onClick", dojo.hitch(this, "onItemClick"));
    },
    
    onItemClick:function(item) {
		if(!item)item=[];
		dojo.publish("tree.nodeClick", [item, this.module]); 
      }
    
});