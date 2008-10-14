dojo.provide("phpr.Administration.Main");

dojo.declare("phpr.Administration.Main", phpr.Default.Main, {
    constructor:function() {
		this.module = "Administration";
		this.loadFunctions(this.module);

		this.gridWidget = phpr.Administration.Grid;
		this.formWidget = phpr.Administration.Form;
		this.treeWidget = phpr.Administration.Tree;
		
		dojo.subscribe("Administration.loadSubModule", this, "loadSubModule");
	},

    reload:function() {
        phpr.module    = this.module;
        phpr.submodule = '';
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroySimpleWidget("exportGrid");
        phpr.destroySimpleWidget("saveChanges");
        phpr.destroySimpleWidget("gridNode");
        phpr.destroyWidgets("detailsBox");
        this.render(["phpr.Administration.template", "mainContent.html"],dojo.byId('centerMainContent'));
        this.setSubGlobalModulesNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
    },

    loadSubModule:function(/*String*/module) {
        //summary: this function opens a new Detail View
        if (!dojo.byId('detailsBox')) {
            this.reload();
        }
        phpr.submodule = module;
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroySimpleWidget("exportGrid");
        phpr.destroySimpleWidget("saveChanges");
        phpr.destroySimpleWidget("gridNode");
        phpr.destroyWidgets("detailsBox");
        this.render(["phpr.Administration.template", "mainContent.html"],dojo.byId('centerMainContent'));		
        this.setSubGlobalModulesNavigation();
        this.form = new this.formWidget(this,0,this.module);
    },
	  
    setSubGlobalModulesNavigation:function(currentModule) {
        phpr.destroyWidgets("buttonRow");
        dojo.byId("subModuleNavigation").innerHTML = '';
        var subModuleUrl = phpr.webpath + 'index.php/Administration/index/jsonGetModules';
        var self = this;
        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({
            url: subModuleUrl,
            processData: dojo.hitch(this,function() {
                var modules = new Array();				
				modules.push({"name":"User", "label": phpr.nls.get("User"), "moduleFunction": "reload", "module": "User"});
                modules.push({"name":"Role", "label": phpr.nls.get("Role"), "moduleFunction": "reload", "module": "Role"});
                modules.push({"name":"Module", "label": phpr.nls.get("Module"), "moduleFunction": "reload", "module": "Module"});
				tmp = phpr.DataStore.getData({url: subModuleUrl});
				for (var i = 0; i < tmp.length; i++) {
                    modules.push({"name": tmp[i].name, "label": tmp[i].label, "moduleFunction": "loadSubModule", "module": "Administration"});
				}                		
                var navigation ='<ul id="nav_main">';
                for (var i = 0; i < modules.length; i++) {
                    var liclass        = '';
                    var moduleName     = modules[i].name;
                    var moduleLabel    = modules[i].label;
                    var moduleFunction = modules[i].moduleFunction;
					var module         = modules[i].module;
                    if (moduleName == phpr.submodule) {
                        liclass   = 'class = active';
                    }
                    navigation += self.render(["phpr.Administration.template", "navigation.html"], null, {
                        moduleName :    moduleName,
                        moduleLabel:    moduleLabel,
						module:         module,
                        liclass:        liclass,
                        moduleFunction: moduleFunction
                    });
                }
                navigation += "</ul>";
                dojo.byId("subModuleNavigation").innerHTML = navigation;
                phpr.initWidgets(dojo.byId("subModuleNavigation"));
                this.customSetSubmoduleNavigation();
            })
        })
    },	
	
    updateCacheData:function() {
        this.form.updateData();
    }	
});