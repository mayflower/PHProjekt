dojo.provide("phpr.Settings.Default.Tree");
dojo.require("phpr.Component");

dojo.declare("phpr.Settings.Default.Tree", phpr.Component, {
   	_treeNode:  null,
	treeWidget: null,
	module:     "Settings",

    constructor: function(main) {
        var treepath = phpr.webpath + "scripts/phpr/Settings/Default/settingstree.json";
        this.main    = main;

		this._treeNode = dojo.byId("treeBox");

		if (dijit.byId(this._treeNode)) {
			phpr.destroyWidgets("treeBox");
		}

        this.render(["phpr.Settings.Default.template", "tree.html"], this._treeNode, {
            url: treepath,
            settingsText: phpr.nls.settings,
        });

		this.treeWidget = dijit.byId("treeNode");
        dojo.connect(this.treeWidget, "onClick", dojo.hitch(this, "onItemClick"));
    },

    onItemClick: function(item) {
        dojo.publish(item.id+".reload");
    }
});
