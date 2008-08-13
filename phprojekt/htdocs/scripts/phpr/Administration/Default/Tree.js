dojo.provide("phpr.Administration.Default.Tree");

dojo.require("phpr.Default.Tree");

dojo.declare("phpr.Administration.Default.Tree", phpr.Default.Tree, {

    setUrl:function() {
        this._url = phpr.webpath + "scripts/phpr/Administration/Default/admintree.json";
	},

    setId:function() {
        phpr.destroySimpleWidget("treeNode");
        this._idName = 'treeAdminNode';
    },

    getModel:function(store) {
        return new dijit.tree.ForestStoreModel({
            store: store,
            query: {parent:'1'},
            rootId:    phpr.nls.administration,
            rootLabel: phpr.nls.administration,
        });
    },

    getTree:function(model) {
        return new dijit.Tree({
            id:        this._idName,
            model:     model,
        }, document.createElement('div'));
    },

    onItemClick: function(item) {
        dojo.publish(item.id+".reload");
    },
});
