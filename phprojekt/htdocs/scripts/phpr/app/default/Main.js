dojo.provide("phpr.app.default.Main");

// We need the dtl for rendering the template (default.html).
dojo.require("dojox.dtl");

dojo.require("phpr.Component");

// Load the widgets the template uses.
dojo.require("dijit.layout.SplitContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.Toolbar");
dojo.require("dijit.form.Button");

// app specific files
dojo.require("phpr.app.default.Tree");
dojo.require("phpr.app.default.Grid");
dojo.require("phpr.app.default.Form");
//dojo.require("phpr.app.default.Details");


dojo.declare("phpr.app.default.Main", phpr.Component, {
    
    tree:null,
    grid:null,
	webpath:'',
    
    constructor:function(webpath, availmodules) {
		this.webpath=webpath;
        this.render(["phpr.app.default.template", "main.html"], dojo.body(),{webpath:this.webpath});
        
        // Do this after complete page load (= window.onload).
        dojo.addOnLoad(dojo.hitch(this, function() {
                // Load the components, tree, list and details.
                this.tree = new phpr.app.default.Tree(this,'Project');
                this.grid = new phpr.app.default.Grid(this,null,'Project');
            })
        );
		dojo.subscribe("grid.RowClick", dojo.hitch(this, function(id,module){
			this.form = new phpr.app.default.Form(this,id,module);
			}
		));
		dojo.subscribe("tree.nodeClick", dojo.hitch(this, function(project,module){
			this.grid = new phpr.app.default.Grid(this,project.id,module);
			if (dijit.byId("detailsBox")) {
				phpr.destroyWidgets("detailsBox");
			}		
        }));
		dojo.subscribe("form.Submitted", dojo.hitch(this, function(id,module){
			//this.tree = new phpr.app.default.Tree(this,'Project');
			this.grid = new phpr.app.default.Grid(this,id,module);
			
		}
		));
    }

    
});
