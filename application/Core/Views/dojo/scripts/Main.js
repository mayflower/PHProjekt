dojo.provide("phpr.Core.Main");

dojo.declare("phpr.Core.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Core";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Core.Grid;
        this.formWidget = phpr.Core.Form;
        this.treeWidget = phpr.Core.Tree;
    },
    
    reload:function() {
        phpr.module    = this.module;
        phpr.submodule = this.module;
        this.render(["phpr.Default.template", "mainContent.html"], dojo.byId('centerMainContent'));
        this.cleanPage();
        this.setSubGlobalModulesNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
        var updateUrl = phpr.webpath + 'index.php/Core/'+phpr.module.toLowerCase()+'/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },

    setSubGlobalModulesNavigation:function(currentModule) {
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
    }
});