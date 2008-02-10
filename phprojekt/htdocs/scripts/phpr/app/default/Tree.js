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
        
        var store = new dojo.data.ItemFileReadStore({url:"/index.php/Project/index/jsonList/view/tree"});
        this.treeWidget = new dijit.Tree({
            //id:"treeNode",
            store:store
        }, dojo.byId("treeNode"));
        dojo.connect(this.treeWidget, "onClick", dojo.hitch(this, "onItemClick"));
    },
    
    onItemClick:function(item) {
        // Inform the grid about an update ...
        //this.main.grid.setProject({
        //    id:this.treeWidget.store.getValue(item, "id"),
        //    name:this.treeWidget.store.getValue(item, "name")
        //});
        var s = this.treeWidget.store;
        var data = {
            id:s.getValue(item, "id"),
            name:s.getValue(item, "name")
        };
// TODO should the topic be named "tree.*" or rather "project.set" ... or should we map the one into the other ...
// i am not sure what is best ... thinking :-) (wolfram)
        dojo.publish("tree.onNodeClick", [data])
    }
    
});