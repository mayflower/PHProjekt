dojo.provide("phpr.admin.Main");
// We need the dtl for rendering the template.
dojo.require("dojox.dtl");
dojo.require("dojo.dnd.Source");

dojo.require("phpr.Component");
dojo.require("phpr.Admin.Elements");

// Load the widgets the template uses.
dojo.require("dijit.layout.SplitContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.Toolbar");
dojo.require("dijit.form.Button");
dojo.require("dijit.TitlePane");
dojo.require("dijit.form.TextBox");

dojo.declare("phpr.Admin.Main", phpr.Component, {

    constructor:function() {
        this.render(["phpr.Admin.template", "main.html"], dojo.body());

        // Do this after complete page load (= window.onload).
        dojo.addOnLoad(dojo.hitch(this, function() {
            // Load the elements
			//initialize data store for elements:
			//this.elementsStore = new dojo.data.ItemFileReadStore({url:'display_elements.json'});
            this.elements = new phpr.Admin.Elements(this);
        })
        );
    }

});