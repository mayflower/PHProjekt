dojo.provide("phpr.Default.Tree");

dojo.require("phpr.Component");
dojo.require("dijit.Tree");
dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.Default.Tree", phpr.Component, {
    
   	_treeNode:null,
	treeWidget:null,
	module:'Project',
    
    constructor:function(main,module) {
		this._treeNode = dojo.byId("treeBox");
        this.main = main;
		this.module = module;
		if (dijit.byId(this._treeNode)) {
			phpr.destroyWidgets(this._treeNode);
		}
		var treepath =  this.main.webpath+"index.php/Project/index/jsonList/view/tree";
        this.render(["phpr.Default.template", "tree.html"], this._treeNode,{url:treepath});
		this.treeWidget = dijit.byId("treeNode");
        dojo.connect(this.treeWidget, "onClick", dojo.hitch(this, "onItemClick"));
    },
    
    onItemClick:function(item) {
		if(!item)item=[];
		dojo.publish("Project.tree.nodeClick", [item, this.module]); 
      }
    
});