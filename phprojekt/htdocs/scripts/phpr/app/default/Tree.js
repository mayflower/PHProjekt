dojo.provide("phpr.app.default.Tree");

dojo.require("phpr.Component");
// The dijits the template uses
dojo.require("dijit.Tree");
dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.app.default.Tree", phpr.Component, {
    
    treeWidget:null,
    
    constructor:function(main) {
        this.main = main;
        // Render the tree on the left hand side, from the template.
        this.render(["phpr.app.default.template", "tree.html"], dojo.byId("treeBox"));
        
        this.treeWidget = dijit.byId("treeNode");
        dojo.connect(this.treeWidget, "onClick", dojo.hitch(this, "onItemClick"));
    },
    
    onItemClick:function(item) {
        // Inform the grid about an update ...
        this.main.grid.setProject({
            id:this.treeWidget.store.getValue(item, "id"),
            name:this.treeWidget.store.getValue(item, "name")
        });
    }
    
});