/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Module.Form");

dojo.declare("phpr.Module.Form", phpr.Core.Form, {
    initData:function() {
        // Get all the active users
        this._moduleDesignerUrl  = phpr.webpath + 'index.php/Core/moduleDesigner/jsonDetail/nodeId/1/id/' + this.id;
        this._initData.push({'url': this._moduleDesignerUrl});
    },

    addBasicFields:function() {
        var designerData = new Object();
        designerData = phpr.DataStore.getData({url: this._moduleDesignerUrl});

        // Button for open the dialog
        if (designerData && (typeof designerData === 'object')) {
            this.formdata[1] += this.fieldTemplate.buttonActionRender(phpr.nls.get('Designer'), 'designerButton',
                phpr.nls.get('Editor'), '', 'dojo.publish(\'Module.openDialog\');',
                phpr.nls.get('Open a dialog where you can drag and drop many fields for create the form as you want.'));
        }

        // Hidden field for the MD data
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
            designerData[0]['tableField']    = 'project_id';
            designerData[0]['selectType']    = 'project';
            designerData[0]['tableType']     = 'int';
            designerData[0]['tableLength']   = 11;
            designerData[0]['formLabel']     = 'Project';
            designerData[0]['formType']      = 'selectValues';
            designerData[0]['formRange']     = 'Project # id # title';
            designerData[0]['defaultValue']  = 1;
            designerData[0]['listPosition']  = 0;
            designerData[0]['status']        = 1;
            designerData[0]['isRequired']    = 1;
        }
        var jsonDesignerData = dojo.toJson(designerData);
        this.formdata[1] += this.fieldTemplate.hiddenFieldRender('Designer Data', 'designerData', jsonDesignerData,
            true, false);
    },

    postRenderForm:function() {
        // Add onBlur to the label field for update the tableName
        dojo.connect(dojo.byId('label'), "onchange",  dojo.hitch(this, "updateDesignerData"));
    },

    openDialog:function() {
        // create the dialog
        phpr.destroyWidget('moduleManagerDialog');
        var dialog = new dijit.Dialog({
            title: "created",
            id:    "moduleManagerDialog",
            style: "width:95%; height:" + (getMaxHeight() - 10) + "px; background: #fff;"
        });
        dojo.body().appendChild(dialog.domNode);

        // Disconnect onExecute
        for (var i = 0; i < dialog._connects.length; i++) {
            var handle = dialog._connects[i];
            var event = handle[0][1];
            if (event == 'onExecute') {
                dialog.disconnect(handle);
                break;
            }
        }
        dialog.startup();

        // Add translations
        var tabs = this.tabStore.getList();
        for (t in tabs) {
            tabs[t].name = phpr.nls.get(tabs[t].name);
        }
        this.render(["phpr.Core.Module.template", "moduleDesigner.html"], dialog.domNode, {
            webpath:     phpr.webpath,
            tableText:   phpr.nls.get('Database'),
            formText:    phpr.nls.get('Form'),
            listText:    phpr.nls.get('Grid'),
            generalText: phpr.nls.get('General'),
            saveText:    phpr.nls.get('Close'),
            tabs:        tabs
        });
        dojo.style(dojo.byId('moduleDesignerEditor'), "display", "none");
        phpr.makeModuleDesignerSource();
        phpr.makeModuleDesignerTarget(dijit.byId('designerData').attr('value'), this.tabStore.getList());

        // Select the first tab, since the tabs in the dialog don't work on dojo 1.4
        dijit.byId('moduleDesignerEditor').startup();
        var parent = dijit.byId('moduleDesignerTarget');
        parent._showChild(dijit.byId(parent.containerNode.children[0].id));

        dijit.byId('moduleDesignerTarget').startup();
        var parent = dijit.byId('moduleDesignerEditor');
        parent._showChild(dijit.byId(parent.containerNode.children[0].id));

        dialog.show();

        dojo.connect(dijit.byId('moduleManagerDialog'), "hide",  dojo.hitch(this, "processDialogData"));
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

                if (dijit.byId('name').attr('value') != '') {
                    data[i]['tableName'] = dijit.byId('name').attr('value');
                } else {
                    data[i]['tableName'] = self.convertLabelIntoTableName(dijit.byId('label').attr('value'));
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
        dijit.byId('designerData').attr('value', json);
    },

    updateDesignerData:function(event) {
        // summary:
        //    Update the field "name" with the value of the name or label
        // description:
        //    Update the field "name" with the value of the name or label
        //    Change the value into all the data array
        var self = this;
        var data = dojo.fromJson(dijit.byId('designerData').attr('value'));

        if (self.id > 0) {
            var tableName = self.convertLabelIntoTableName(dijit.byId('name').attr('value'));
        } else {
            var tableName = self.convertLabelIntoTableName(dijit.byId('label').attr('value'));
            dijit.byId('name').attr('value', tableName);
        }

        for (var i in data) {
            data[i]['tableName'] = self.convertLabelIntoTableName(tableName);
        }
        data = dojo.toJson(data);
        dijit.byId('designerData').attr('value', data);

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
                                    phpr.loadJsFile(phpr.webpath + 'index.php/js/module/name/' + this.sendData['name']);
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
    }
});
