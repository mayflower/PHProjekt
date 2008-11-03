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

        // Get the module Designer data
        this._moduleDesignerUrl  = phpr.webpath + 'index.php/Core/moduleDesigner/jsonDetail/id/' + this.id;
        phpr.DataStore.addStore({url: this._moduleDesignerUrl});
        phpr.DataStore.requestData({url: this._moduleDesignerUrl});
    },
        
    addBasicFields:function() {
        this.formdata += this.fieldTemplate.buttonActionRender('Designer', 'designerButton', 'Open Dialog', '', 'dojo.publish(\'Module.openDialog\');');
        var designerData = new Object();
        designerData = phpr.DataStore.getData({url: this._moduleDesignerUrl});
        var jsonDesignerData = dojo.toJson(designerData);
        this.formdata += this.fieldTemplate.textFieldRender('Designer Data', 'designerData', jsonDesignerData, true, false);
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
            saveText: phpr.nls.get('Close'),
            tabs:     this.tabStore.getList()
        });
        phpr.makeModuleDesignerSource();
        phpr.makeModuleDesignerTarget(dijit.byId('designerData').attr('value'), this.tabStore.getList());
        dialog.show();
        
        dojo.connect(dijit.byId('moduleManagerDialog'), "hide",  dojo.hitch(this, "processDialogData"));
    },
     
    processDialogData: function() {
        var tabs         = this.tabStore.getList();
        var data         = new Object();
        var i            = -1;
        var formPosition = 0;
        for (var j in tabs) {
            var tab = eval("moduleDesignerTarget" + tabs[j]['nameId']);
            tab.getAllNodes().forEach(function(node) {
                var t = tab._normalizedCreator(node);
                i++;
                data[i] = new Object();
                if (!this.id) {
                    data[i]['tableName'] = dijit.byId('name').attr('value');
                }
                formPosition++;
                data[i]['formPosition']  = formPosition;
                data[i]['formTab']       = tabs[j]['id'];
                data[i]['formColumns']   = 1;
                data[i]['formRegexp']    = null;
                data[i]['listAlign']     = 'center';
                data[i]['listUseFilter'] = 1;
                data[i]['altPosition']   = 0;
                data[i]['isInteger']     = 0;
                data[i]['isUnique']      = 0;

                dojo.query('.hiddenValue', t.node).forEach(function(ele){
                    switch (ele.name) {
                        case 'tableField':
                            data[i]['tableField'] = ele.value;
                            break;
                        case 'selectType':
                            data[i]['selectType'] = ele.value;
                            break;
                        case 'tableType':
                            data[i]['tableType'] = ele.value;
                            break;
                        case 'tableLenght':
                            data[i]['tableLenght'] = ele.value;
                            break;
                        case 'formLabel':
                            data[i]['formLabel']   = ele.value;
                            data[i]['formTooltip'] = ele.value;
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
    },

    submitForm: function() {
        for(var i = 0; i < this.formsWidget.length; i++) {
            this.sendData = dojo.mixin(this.sendData, this.formsWidget[i].attr('value'));
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/Core/moduleDesigner/jsonSave/id/' + this.id,
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Core/module/jsonSave/id/' + this.id,
                        content:   this.sendData,
                        onSuccess: dojo.hitch(this, function(data) {
                            new phpr.handleResponse('serverFeedback', data);
                            if (data.type =='success') {
                                this.publish("updateCacheData");
                                this.publish("reload");
                            }
                        })
                    });
               }
            })
        });
    },

    updateData:function() {
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._moduleDesignerUrl});
    }
});
