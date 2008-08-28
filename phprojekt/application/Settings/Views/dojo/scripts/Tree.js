dojo.provide("phpr.Settings.Tree");

dojo.declare("phpr.Settings.Tree", phpr.Default.Tree, {

	getStore:function() {
        this._store = new dojo.data.ItemFileWriteStore({data:
            {
                "identifier":"id",
                "label":"name",
                "items":[
                    {"name":"Admin","id":"1","parent":null,"children":[]},
                    {"name":"Settings","id":"Settings","parent":"1","children":[]},
                ]
            }
        });
    },

    setId:function() {
        phpr.destroySimpleWidget("treeNode");
        this._idName = 'treeSettingsNode';
    },

    getModel:function() {
        this._model = new dijit.tree.ForestStoreModel({
            store: this._store,
            query: {parent:'1'},
            rootId:    phpr.nls.settings,
            rootLabel: phpr.nls.settings,
        });
    },

    getTree:function() {
        return new dijit.Tree({
            id:        this._idName,
            model:     this._model,
        }, document.createElement('div'));
    },

    onItemClick: function(item) {
        dojo.publish(item.id+".reload");
    },
});
