dojo.provide("phpr.Default.Tree");

dojo.require("phpr.Component");

dojo.require("dijit.Tree");
dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.Default.Tree", phpr.Component, {
    // summary: This class is responsible for rendering the Tree of a default module
    _treeNode:  null,
    treeWidget: null,
    module:     null,
    
    constructor: function(main) {
        // summary: The tree is rendere on construction
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
        // summary: publishes "changeProject" as soon as a tree Node is clicked
        if(!item) { 
          item = [];
        }
        this.publish("changeProjekt", [item]); 
    }
});