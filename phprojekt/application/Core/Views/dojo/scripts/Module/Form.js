dojo.provide("phpr.Module.Form");

dojo.declare("phpr.Module.Form", phpr.Core.Form, {
    setPermissions:function (data) {
        this._writePermissions = true;
        this._deletePermissions = false;
        this._accessPermissions = true;
    },
    
    initData: function() {
        // Get all the tabs
        this.tabStore = new phpr.Store.Tab();
        this.tabStore.fetch();
    },
        
    addBasicFields: function(){
        this.formdata += this.fieldTemplate.buttonActionRender('Designer', 'designerButton', 'Open Dialog', '', 'dojo.publish(\'Module.openDialog\');');
        this.formdata += this.fieldTemplate.textFieldRender('Designer', 'designerData', '', true, false);
    },
    
    openDialog: function() {
        // create the dialog
        phpr.destroyWidget('moduleManagerDialog');
        var dialog = new dijit.Dialog({
            title: "created",
            id:    "moduleManagerDialog",
            style: "width:95%; height:95%; background: #fff;"
        });
        dojo.body().appendChild(dialog.domNode);
        dialog.startup();
        this.render(["phpr.Core.Module.template", "moduleDesigner.html"], dialog.domNode, {
            webpath:  phpr.webpath,
            saveText: phpr.nls.get('Save'),
            tabs:     this.tabStore.getList()
        });
        phpr.makeModuleDesignerSource();
        phpr.makeModuleDesignerTarget(dijit.byId('designerData').attr('value'), this.tabStore.getList());
        dialog.show();
        
        dojo.connect(dijit.byId('moduleManagerDialog'), "hide",  dojo.hitch(this, "processDialogData"));
    },
     
    processDialogData: function() {
        var tabs = this.tabStore.getList();
        var data = new Object();
        var i = -1;
        for (var j in tabs) {
            var tab = eval("moduleDesignerTarget" + tabs[j]['nameId']);
            tab.getAllNodes().forEach(function(node) {
                var t = tab._normalizedCreator(node);
                i++;
                data[i] = new Object();
                data[i]['formTab'] = tabs[j]['id'];
                dojo.query('.hiddenValue', t.node).forEach(function(ele){
                    switch (ele.name) {
                        case 'selectType':
                            data[i]['selectType'] = ele.value;
                            break;
                        case 'tableType':
                            data[i]['tableType'] = ele.value;
                            break;
                        case 'tableLenght':
                            data[i]['tableLenght'] = ele.value;
                            break;
                        case 'tableField':
                            data[i]['tableField'] = ele.value;
                            break;
                        case 'formLabel':
                            data[i]['formLabel'] = ele.value;
                            break;
                        case 'formType':
                            data[i]['formType'] = ele.value;
                            break;
                        case 'formRange':
                            data[i]['formRange'] = ele.value;
                            break;
                        case 'defaultValue':
                            data[i]['defaultValue'] = ele.value;
                            break;
                        case 'listPosition':
                            data[i]['listPosition'] = ele.value;
                            break;
                        case 'status':
                            data[i]['status'] = ele.value;
                            break;
                        case 'isRequired':
                            data[i]['isRequired'] = ele.value;
                            break;
                    }
                });
            });
        }
        var json = dojo.toJson(data);
        dijit.byId('designerData').attr('value', json);
    }
});
