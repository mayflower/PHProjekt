/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Module.Form");

dojo.declare("phpr.Module.Form", phpr.Core.Form, {
    _dialog: null,

    initData:function() {
        // Get all the active users
        this._moduleDesignerUrl  = phpr.webpath + 'index.php/Core/moduleDesigner/jsonDetail/nodeId/1/id/' + this.id;
        this._initData.push({'url': this._moduleDesignerUrl});
    },

    addBasicFields:function() {
        var designerData = new Object();
        designerData = phpr.DataStore.getData({url: this._moduleDesignerUrl});

        // Button for open the dialog
        if (designerData && (typeof designerData['definition'] === 'object')) {
            this.formdata[1] += this.fieldTemplate.buttonActionRender(phpr.nls.get('Form'), 'designerButton',
                phpr.nls.get('Open Editor'), '', 'dojo.publish(\'Module.openDialog\');',
                phpr.nls.get('Open a dialog where you can drag and drop many fields for create the form as you want.'));
        }

        // Hidden field for the MD data
        if (designerData && (!designerData['definition'] || !designerData['definition'].length)) {
            designerData                  = new Object();
            designerData['definition']    = new Object();
            designerData['definition'][0] = new Object();

            designerData['definition'][0]['id']            = 0;
            designerData['definition'][0]['tableName']     = '';
            designerData['definition'][0]['formPosition']  = 1;
            designerData['definition'][0]['formTab']       = 1;
            designerData['definition'][0]['formColumns']   = 1;
            designerData['definition'][0]['formRegexp']    = null;
            designerData['definition'][0]['listAlign']     = 'center';
            designerData['definition'][0]['listUseFilter'] = 1;
            designerData['definition'][0]['altPosition']   = 0;
            designerData['definition'][0]['isInteger']     = 0;
            designerData['definition'][0]['isUnique']      = 0;
            designerData['definition'][0]['tableField']    = 'project_id';
            designerData['definition'][0]['selectType']    = 'project';
            designerData['definition'][0]['tableType']     = 'int';
            designerData['definition'][0]['tableLength']   = 11;
            designerData['definition'][0]['formLabel']     = 'Project';
            designerData['definition'][0]['formType']      = 'selectValues';
            designerData['definition'][0]['formRange']     = 'Project # id # title';
            designerData['definition'][0]['defaultValue']  = null;
            designerData['definition'][0]['listPosition']  = 0;
            designerData['definition'][0]['status']        = 1;
            designerData['definition'][0]['isRequired']    = 1;
        }
        var jsonDesignerData = dojo.toJson(designerData['definition']);

        this.formdata[1] += this.fieldTemplate.hiddenFieldRender('Designer Data', 'designerData', jsonDesignerData,
            true, false);
    },

    setPermissions:function(data) {
        this.inherited(arguments);

        // Show delete ?
        designerData = phpr.DataStore.getData({url: this._moduleDesignerUrl});
        if (designerData && designerData['isUserModule']) {
            this._deletePermissions = true;
        } else {
            this._deletePermissions = false;
        }
    },

    postRenderForm:function() {
        // Add onBlur to the label field for update the tableName
        dojo.connect(dojo.byId('label'), "onchange",  dojo.hitch(this, "updateDesignerData"));
    },

    openDialog:function() {
        // Create the dialog
        phpr.destroyWidget('moduleManagerDialog');
        this._dialog = new dijit.Dialog({
            title:     phpr.nls.get('Module Designer') + ' [' + dijit.byId('label').value + ']',
            id:        "moduleManagerDialog",
            style:     "width:95%; height:" + (getMaxHeight() - 28) + "px;",
            baseClass: 'moduleManagerDialog'
        });
        dojo.body().appendChild(this._dialog.domNode);
        this._dialog.startup();

        // Add translations
        var tabs = this.tabStore.getList();
        for (t in tabs) {
            tabs[t].name = phpr.nls.get(tabs[t].name);
        }
        this.render(["phpr.Core.Module.template", "moduleDesigner.html"], this._dialog.containerNode, {
            webpath:     phpr.webpath,
            tableText:   phpr.nls.get('Database'),
            formText:    phpr.nls.get('Form'),
            listText:    phpr.nls.get('Grid'),
            generalText: phpr.nls.get('General'),
            saveText:    phpr.nls.get('Save'),
            cancelText:  phpr.nls.get('Cancel'),
            tabs:        tabs
        });
        phpr.makeModuleDesignerSource();
        phpr.makeModuleDesignerTarget(dijit.byId('designerData').get('value'), this.tabStore.getList());

        // Select the first tab, since the tabs in the dialog don't work on dojo 1.4
        dijit.byId('moduleDesignerEditor').startup();
        var parent = dijit.byId('moduleDesignerTarget');
        parent._showChild(dijit.byId(parent.containerNode.children[0].id));

        dijit.byId('moduleDesignerTarget').startup();
        var parent = dijit.byId('moduleDesignerEditor');
        parent._showChild(dijit.byId(parent.containerNode.children[0].id));

        this._dialog.show();
        dojo.style(dojo.byId('moduleDesignerEditor'), "display", "none");
    },

    processDialogData:function() {
        // summary:
        //    Collect all the data from the fields
        // description:
        //    Collect all the data from the fields and make a json array for the server
        var tabs         = this.tabStore.getList();
        var data         = new Object();
        var i            = -1;
        var formPosition = 0;
        var self         = this;
        for (var j in tabs) {
            var tab = eval("moduleDesignerTarget" + tabs[j]['nameId']);
            tab.getAllNodes().forEach(function(node) {
                var t = tab._normalizedCreator(node);
                i++;
                data[i] = new Object();

                if (dijit.byId('name').get('value') != '') {
                    data[i]['tableName'] = dijit.byId('name').get('value');
                } else {
                    data[i]['tableName'] = self.convertLabelIntoTableName(dijit.byId('label').get('value'));
                }

                formPosition++;
                data[i]['formPosition']  = formPosition;
                data[i]['formTab']       = parseInt(tabs[j]['id']);
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
                        case 'id':
                            data[i]['id'] = ele.value;
                            break;
                    }
                });
            });
        }

        var json = dojo.toJson(data);
        dijit.byId('designerData').set('value', json);
    },

    updateDesignerData:function(event) {
        // summary:
        //    Update the field "name" with the value of the name or label
        // description:
        //    Update the field "name" with the value of the name or label
        //    Change the value into all the data array
        var self = this;
        var data = dojo.fromJson(dijit.byId('designerData').get('value'));

        if (self.id > 0) {
            var tableName = self.convertLabelIntoTableName(dijit.byId('name').get('value'));
        } else {
            var tableName = self.convertLabelIntoTableName(dijit.byId('label').get('value'));
            dijit.byId('name').set('value', tableName);
        }

        for (var i in data) {
            data[i]['tableName'] = self.convertLabelIntoTableName(tableName);
        }
        data = dojo.toJson(data);
        dijit.byId('designerData').set('value', data);

        if (event) {
            dojo.stopEvent(event);
        }
    },

    convertLabelIntoTableName:function(value) {
        // summary:
        //    Trnasform the label into a valid DB name
        // description:
        //    Trnasform the label into a valid DB name
        value     = value.replace(/\W+/g, '');
        value     = value.replace(/[_]/g, '');
        var first = value.charAt(0).toUpperCase();

        return first + value.substr(1, value.length-1);
    },

    submitForm:function() {
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/Core/moduleDesigner/jsonSave/nodeId/1/id/' + this.id,
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Core/module/jsonSave/nodeId/1/id/' + this.id,
                        content:   this.sendData,
                        onSuccess: dojo.hitch(this, function(data) {
                            new phpr.handleResponse('serverFeedback', data);
                            if (data.type == 'success') {
                                if (!this.id) {
                                    phpr.loadJsFile(phpr.webpath + 'index.php/js/module/name/' + this.sendData['name']
                                     + '/csrfToken/' + phpr.csrfToken);
                                }
                                this.publish("updateCacheData");
                                phpr.DataStore.deleteData({url: phpr.globalModuleUrl});
                                phpr.DataStore.addStore({url: phpr.globalModuleUrl});
                                phpr.DataStore.requestData({
                                    url:         phpr.globalModuleUrl,
                                    processData: dojo.hitch(this, function() {
                                        this.main.setGlobalModulesNavigation();
                                        this.publish("setUrlHash", [phpr.parentmodule, null, [phpr.module]]);
                                    })
                                });
                            }
                        })
                    });
               }
            })
        });
    },

    deleteForm:function() {
        phpr.send({
            url:       phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonDelete/id/' + this.id,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");
                    phpr.DataStore.deleteData({url: phpr.globalModuleUrl});
                    phpr.DataStore.addStore({url: phpr.globalModuleUrl});
                    phpr.DataStore.requestData({
                        url:         phpr.globalModuleUrl,
                        processData: dojo.hitch(this, function() {
                            this.main.setGlobalModulesNavigation();
                            this.publish("setUrlHash", [phpr.parentmodule, null, [phpr.module]]);
                        })
                    });
                }
            })
        });
    }
});
