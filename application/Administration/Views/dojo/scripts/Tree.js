dojo.provide("phpr.Administration.Tree");

dojo.declare("phpr.Administration.Tree", phpr.Default.Tree, {

	getStore:function() {
        this._store = new dojo.data.ItemFileWriteStore({data:
            {
                "identifier":"id",
                "label":"name",
                "items":[
                    {"name":"Admin","id":"1","parent":null,"children":[]},
                    {"name":"Users","id":"User","parent":"1","children":[]},
                    {"name":"Modules","id":"Module","parent":"1","children":[]},
                    {"name":"Roles","id":"Role","parent":"1","children":[]},
                ]
            }
        });
    },

    setId:function() {
        phpr.destroySimpleWidget("treeNode");
        this._idName = 'treeAdminNode';
    },

    getModel:function() {
        this._model = new dijit.tree.ForestStoreModel({
            store:     this._store,
            query:     {parent:'1'},
            rootId:    phpr.nls.administration,
            rootLabel: phpr.nls.administration,
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
