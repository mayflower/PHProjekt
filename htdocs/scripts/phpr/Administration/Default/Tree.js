dojo.provide("phpr.Administration.Default.Tree");

dojo.require("phpr.Component");

dojo.declare("phpr.Administration.Default.Tree", phpr.Component, {
   	_treeNode:  null,
	treeWidget: null,
	module:     "Administration",

    constructor: function(main) {
        var treepath = phpr.webpath + "scripts/phpr/Administration/Default/admintree.json";
        this.main    = main;

		this._treeNode = dojo.byId("treeBox");

		if (dijit.byId(this._treeNode)) {
			phpr.destroyWidgets("treeBox");
		}

        this.render(["phpr.Administration.Default.template", "tree.html"], this._treeNode, {
            url: treepath,
            administrationText: phpr.nls.administration,
        });

		this.treeWidget = dijit.byId("treeNode");
        dojo.connect(this.treeWidget, "onClick", dojo.hitch(this, "onItemClick"));
    },

    onItemClick: function(item) {
        dojo.publish("Administration."+item.id+".load");
    }
});
