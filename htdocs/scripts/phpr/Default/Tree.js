dojo.provide("phpr.Default.Tree");

dojo.require("phpr.Component");
dojo.require("dijit.Tree");

dojo.declare("phpr.Default.Tree", phpr.Component, {
    // summary: This class is responsible for rendering the Tree of a default module
    _treeNode:  null,
    _url:       null,
    _idName:    null,

    constructor:function(main) {
        // summary: The tree is rendere on construction
        this.main  = main;
        this.setUrl();
        this.setId();
        this.setNode();
        this.loadTree();
    },

    loadTree:function() {
        if (!dijit.byId(this._idName)) {
            // Data of the tree
            store     = this.getStore();
            model     = this.getModel(store);
            this.tree = this.getTree(model);

            this._treeNode.setContent(this.tree.domNode);
            this.tree.startup();

            dojo.connect(this.tree, "onClick", dojo.hitch(this, "onItemClick"));
        }
    },

    getStore:function() {
        return new dojo.data.ItemFileWriteStore({url: this._url});
    },

    getModel:function(store) {
        return new dijit.tree.ForestStoreModel({
            store: store,
            query: {parent:'1'},
        });
    },

    getTree:function(model) {
        return new dijit.Tree({
            id:       this._idName,
            model:    model,
            showRoot: false
        }, document.createElement('div'));
    },

    setUrl:function() {
        // summary:
        //    Set the url for get the tree
        // description:
        //    Set the url for get the tree
        this._url = phpr.webpath + "index.php/Project/index/jsonTree";
    },

    setNode:function() {
        // summary:
        //    Set the node to put the tree
        // description:
        //    Set the node to put the tree
        this._treeNode = dijit.byId("treeBox");
    },

    setId:function() {
        // summary:
        //    Set the id of the widget
        // description:
        //    Set the id of the widget
        this._idName = 'treeNode';
    },

    updateData: function() {
        phpr.destroySimpleWidget(this._idName);
    },

    onItemClick: function(item) {
        // summary: publishes "changeProject" as soon as a tree Node is clicked
        if(!item) {
          item = [];
        }
        this.publish("changeProject", [item]);
    },
});