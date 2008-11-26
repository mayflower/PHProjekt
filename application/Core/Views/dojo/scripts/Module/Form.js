dojo.provide("phpr.Module.Form");

dojo.declare("phpr.Module.Form", phpr.Core.Form, {
    
    initData: function() {
        // Get all the active users
        this._moduleDesignerUrl  = phpr.webpath + 'index.php/Core/moduleDesigner/jsonDetail/id/' + this.id;
        this._initData.push({'url': this._moduleDesignerUrl});
    },

    addBasicFields:function() {
        // Button for open the dialog
        this.formdata[1] += this.fieldTemplate.buttonActionRender('Designer', 'designerButton', 'Open Dialog', '', 'dojo.publish(\'Module.openDialog\');');

        // Hidden field for the MD data
        var designerData = new Object();
        designerData = phpr.DataStore.getData({url: this._moduleDesignerUrl});
        if (!designerData.length) {
            designerData = new Object();
            designerData[0] = new Object();
            designerData[0]['id']            = 0;
            designerData[0]['tableName']     = '';
            designerData[0]['formPosition']  = 1;
            designerData[0]['formTab']       = 1;
            designerData[0]['formColumns']   = 1;
            designerData[0]['formRegexp']    = null;
            designerData[0]['listAlign']     = 'center';
            designerData[0]['listUseFilter'] = 1;
            designerData[0]['altPosition']   = 0;
            designerData[0]['isInteger']     = 0;
            designerData[0]['isUnique']      = 0;
            designerData[0]['tableField']    = 'projectId';
            designerData[0]['selectType']    = 'project';
            designerData[0]['tableType']     = 'int';
            designerData[0]['tableLength']   = 11;
            designerData[0]['formLabel']     = 'Project';
            designerData[0]['formTooltip']   = 'Project';
            designerData[0]['formType']      = 'selectValues';
            designerData[0]['formRange']     = 'Project # id # title';
            designerData[0]['defaultValue']  = 1;
            designerData[0]['listPosition']  = 1;
            designerData[0]['status']        = 1;
            designerData[0]['isRequired']    = 1;
        }
        var jsonDesignerData = dojo.toJson(designerData);
        this.formdata[1] += this.fieldTemplate.hiddenFieldRender('Designer Data', 'designerData', jsonDesignerData, true, false);
        
        // Add onBlur to the label field for update the tableName
        dojo.connect(dijit.byId('label'), "onchange",  dojo.hitch(this, "updateDedignerData"));
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
                    if (dijit.byId('name').attr('value') != '') {
                        data[i]['tableName'] = dijit.byId('name').attr('value');
                    } else {
                        data[i]['tableName'] = this.convertLabelIntoTableName(dijit.byId('label').attr('value'));
                    }
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
                        case 'tableLength':
                            data[i]['tableLength'] = ele.value;
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
                        case 'id':
                            data[i]['id'] = ele.value;
                            break;
                    }
                });
            });
        }
        var json = dojo.toJson(data);
        dijit.byId('designerData').attr('value', json);
    },

    updateDedignerData: function(event) {
        var data = dojo.fromJson(dijit.byId('designerData').attr('value'));
        if (this.id > 0) {
            var tableName = this.convertLabelIntoTableName(dijit.byId('name').attr('value'));
        } else {
            var tableName = this.convertLabelIntoTableName(dijit.byId('label').attr('value'));
            dijit.byId('name').attr('value', tableName);
        }
        for (var i in data) {
            data[i]['tableName'] = this.convertLabelIntoTableName(tableName);
        }
        data = dojo.toJson(data);
        dijit.byId('designerData').attr('value', data);
        event.stopPropagation();
        event.preventDefault();
        dojo.stopEvent(event);
    },
    
    convertLabelIntoTableName: function(value) {
        value     = value.replace(/\s+/g, '');
        var first = value.charAt(0).toUpperCase();
        
        return first + value.substr(1, value.length-1);
    },
    
    submitForm: function() {
        for(var i = 0; i < this.formsWidget.length; i++) {
            this.sendData = dojo.mixin(this.sendData, this.formsWidget[i].attr('value'));
        }

        this.prepareSubmission();

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
                            if (data.type == 'success') {
                                if (!this.id) {
                                    var response     = {};
                                    response.type    = 'notice';
                                    response.message = phpr.nls.get('YOU MUST TO REFRESH THE PAGE TO WORK WITH THE NEW MODULE');
                                    new phpr.handleResponse('serverFeedback', response);
                                }   
                                this.publish("updateCacheData");
                                this.publish("reload");
                            }         
                        })
                    });
               }
            })
        });
    },

    deleteForm: function() {
        phpr.send({
            url:       phpr.webpath + 'index.php/Core/module/jsonDelete/id/' + this.id,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                    this.publish("updateCacheData");
                    this.publish("reload");
               }
            })
        });
    },
        
    updateData:function() {
        phpr.DataStore.deleteAllCache();        
    }
});
