dojo.provide("phpr.Settings.Default.Main");

dojo.require("phpr.Component");
dojo.require("phpr.Settings.Default.Tree");

dojo.declare("phpr.Settings.Default.Main", phpr.Default.Main, {
    // summary: class for initialilzing a default Admin module
    tree: 	          null,
    grid:             null,
    module:           null,
    availableModules: null,
    writePermissions: false,
    treeWidget: phpr.Settings.Default.Tree,

    reload:function() {
        phpr.module   = this.module;

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.destroyWidgets("centerMainContent");
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        this.render(["phpr.Default.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.renderButton();
        var updateUrl = phpr.webpath + 'index.php/User/index/jsonSaveMultipleSetting';
        this.tree     = new this.treeWidget(this);
        this.grid     = new this.gridWidget(updateUrl, this, null, this.module);
	},

    renderButton:function() {
        //render new button
        var newEntry = null;
        phpr.destroyWidgets("subModuleNavigation");
        phpr.destroyWidgets("buttonRow");
        /*
        var params = {
            label:     '',
            id:        'newEntry',
            iconClass: 'add'
        };

        newEntry = new dijit.form.Button(params);
        dojo.byId("buttonRow").appendChild(newEntry.domNode);
        */
        phpr.initWidgets(dojo.byId("subModuleNavigation"));
        // dojo.connect(dijit.byId("newEntry"), "onClick", dojo.hitch(this, "newEntry"));
        
    },

    newEntry: function() {
        // summary:
        //     This function is responsible for displaying the form for a new entry in the
        //     current Module
        this.openForm([null]);
    }
});