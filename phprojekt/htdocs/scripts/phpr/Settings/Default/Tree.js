dojo.provide("phpr.Settings.Default.Tree");

dojo.require("phpr.Default.Tree");

dojo.declare("phpr.Settings.Default.Tree", phpr.Default.Tree, {

    setUrl:function() {
        this._url = phpr.webpath + "scripts/phpr/Settings/Default/settingstree.json";
	},

    setId:function() {
        phpr.destroySimpleWidget("treeNode");
        this._idName = 'treeSettingsNode';
    },

    getModel:function(store) {
        return new dijit.tree.ForestStoreModel({
            store: store,
            query: {parent:'1'},
            rootId:    phpr.nls.settings,
            rootLabel: phpr.nls.settings,
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
