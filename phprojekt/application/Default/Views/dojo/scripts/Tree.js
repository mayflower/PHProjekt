dojo.provide("phpr.Default.Tree");

dojo.declare("phpr.Default.Tree", phpr.Component, {
    // summary: This class is responsible for rendering the Tree of a default module
    _treeNode:  null,
    _url:       null,
    _idName:    null,
    _store:     null,
    _model:     null,

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
            this.getStore();
            this.getModel(store);
            this.tree = this.getTree();

            this._treeNode.attr('content', this.tree.domNode);
            this.tree.startup();

            dojo.connect(this.tree, "onClick", dojo.hitch(this, "onItemClick"));
        } else {
            this.tree = dijit.byId(this._idName);
        }
    },

    getStore:function() {
        this._store = new dojo.data.ItemFileWriteStore({url: this._url});
    },

    getModel:function() {
        this._model = new dijit.tree.ForestStoreModel({
            store: this._store,
            query: {parent:'1'}
        });
    },

    getTree:function() {
        return new dijit.Tree({
            id:       this._idName,
            model:    this._model,
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
    }
});
