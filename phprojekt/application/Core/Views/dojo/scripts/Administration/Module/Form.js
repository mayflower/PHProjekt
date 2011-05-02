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
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Module.Form");

dojo.declare("phpr.Module.Form", phpr.Core.Form, {
    _moduleDesignerUrl: null,

    // Events Buttons
    _eventForDesignerData: null,

    processDialogData:function() {
        // Summary:
        //    Collect all the data from the fields and make a json array for the server.
        var tabs         = phpr.TabStore.getList();
        var data         = {};
        var i            = -1;
        var formPosition = 0;
        var self         = this;
        for (var j in tabs) {
            var targetId = 'target-ModuleDesigner-' + tabs[j]['id'] + '-' + this._id;

            // Move the deleted items to garbage, for don't show it again
            dojo.query('.deleted', dojo.byId(targetId)).forEach(function(node) {
                dojo.place(node, 'garbage');
            });

            // Remove the newItem class on save
            dojo.query('.newItem', dojo.byId(targetId)).forEach(function(node) {
                dojo.removeClass(node, 'newItem');
            });

            // Process the items
            dojo.query('.dojoDndItem', dojo.byId(targetId)).forEach(function(node) {
                i++;
                data[i] = {};

                if (dijit.byId('name-' + self._module).get('value') != '') {
                    data[i]['tableName'] = dijit.byId('name-' + self._module).get('value');
                } else {
                    data[i]['tableName'] =
                        self._convertLabelIntoTableName(dijit.byId('label-' + self._module).get('value'));
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

                dojo.query('.hiddenValue', node).forEach(function(ele) {
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
        dijit.byId('designerData-' + this._module).set('value', json);
    },

    cancelDialogData:function() {
        // Summary:
        //    Restore the deleted nodes and move to garbage the newItems.
        var tabs = phpr.TabStore.getList();
        for (var j in tabs) {
            var targetId = 'target-ModuleDesigner-' + tabs[j]['id'] + '-' + this._id;
            dojo.query('.deleted', dojo.byId(targetId)).forEach(function(node) {
                node.style.display = 'block';
                node.className     = 'dojoDndItem';
            });
            dojo.query('.newItem', dojo.byId(targetId)).forEach(function(node) {
                node.style.display = 'none';
                dojo.place(node, 'garbage');
            });
        }
    },

    /************* Private functions *************/

    _constructor:function(module, subModules) {
        // Summary:
        //    Construct the form only one time.
        this.inherited(arguments);

        // Create a new global instance of phpr.ModuleDesigner
        phpr.ModuleDesigner = new phpr.ModuleDesigner();
    },

    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
        this._moduleDesignerUrl  = phpr.webpath + 'index.php/Core/moduleDesigner/jsonDetail/nodeId/1/id/' + this._id;
        this._initDataArray.push({'url': this._moduleDesignerUrl});
    },

    _setPermissions:function(data) {
        // Summary:
        //    Get the permission for the current user on the item.
        this.inherited(arguments);

        // Show delete ?
        var designerData = phpr.DataStore.getData({url: this._moduleDesignerUrl});
        if (designerData && designerData['isUserModule']) {
            this._deletePermissions = true;
        } else {
            this._deletePermissions = false;
        }
    },

    _addBasicFields:function() {
        // Summary:
        //    Add some special fields.
        var designerData = phpr.DataStore.getData({url: this._moduleDesignerUrl});

        // Button for open the dialog
        var fieldValues = {
            type:     'buttonAction',
            id:       'designerButton',
            label:    phpr.nls.get('Form'),
            disabled: false,
            required: false,
            value:    '',
            tab:      1,
            text:     phpr.nls.get('Open Editor'),
            icon:     '',
            action:   dojo.hitch(this, '_openDialog'),
            hint:     phpr.nls.get('Open a dialog where you can drag and drop many fields for create the form '
                      + 'as you want.')
        };
        this._fieldTemplate.addRow(fieldValues);

        // Hidden field for the MD data
        if (designerData && (!designerData['definition'] || !designerData['definition'].length)) {
            designerData                  = {};
            designerData['definition']    = {};
            designerData['definition'][0] = {};

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

        var fieldValues = {
            type:     'hidden',
            id:       'designerData',
            label:    phpr.nls.get('Designer Data'),
            disabled: false,
            required: true,
            value:    jsonDesignerData,
            tab:      1,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);
    },

    _postRenderForm:function() {
        // Summary:
        //    User functions after render the form.
        var designerData = phpr.DataStore.getData({url: this._moduleDesignerUrl});

        // Hidde Button for open the dialog?
        if (!designerData || (typeof designerData['definition'] !== 'object')) {
            dojo.byId('row_designerButton-' + this._module).style.display = 'none';
        } else {
            dojo.byId('row_designerButton-' + this._module).style.display = (dojo.isIE) ? 'block' : 'table-row';
        }

        // Add onBlur to the label field for update the tableName
        if (!this._eventForDesignerData) {
            this._eventForDesignerData = dojo.connect(dijit.byId('label-' + this._module), 'onChange',
                dojo.hitch(this, '_updateDesignerData')
            );
            this._events.push('_eventForDesignerData');
        };
    },

    _openDialog:function() {
        // Summary:
        //    Create the dialog only one time.
        // Description:
        //    Create the dialog and the source fields only one time.
        //    Create a new target field for this module is not exists.
        var dialog = dijit.byId('moduleDesignerDialog');
        if (!dialog) {
            var dialog = new dijit.Dialog({
                id:        'moduleDesignerDialog',
                style:     'width:95%; height:' + (getMaxHeight() - 28) + 'px;',
                baseClass: 'moduleDesignerDialog'
            });
            dojo.body().appendChild(dialog.domNode);
            dialog.startup();

            phpr.Render.render(['phpr.Core.Module.template', 'moduleDesigner.html'], dialog.containerNode, {
                webpath:     phpr.webpath,
                tableText:   phpr.nls.get('Database'),
                formText:    phpr.nls.get('Form'),
                listText:    phpr.nls.get('Grid'),
                generalText: phpr.nls.get('General'),
                saveText:    phpr.nls.get('Save'),
                cancelText:  phpr.nls.get('Cancel')
            });

            phpr.ModuleDesigner.createSourceFields();

            // Select the first tab, since the tabs in the dialog don't work on dojo 1.4
            dijit.byId('editor-ModuleDesigner').startup();
            var parent = dijit.byId('editor-ModuleDesigner');
            parent._showChild(dijit.byId(parent.containerNode.children[0].id));
        }

        dialog.set('title', phpr.nls.get('Module Designer') + ' [' + dijit.byId('label-' + this._module).value + ']');

        var moduleData = dijit.byId('designerData-' + this._module).get('value');
        phpr.ModuleDesigner.createTargetFields(this._id, moduleData, phpr.TabStore.getList());

        dialog.show();
        dojo.style(dojo.byId('editor-ModuleDesigner'), 'display', 'none');
    },

    _convertLabelIntoTableName:function(value) {
        // Summary:
        //    Trnasform the label into a valid DB name.
        value     = value.replace(/\W+/g, '');
        value     = value.replace(/[_]/g, '');
        var first = value.charAt(0).toUpperCase();

        return first + value.substr(1, value.length-1);
    },

    _updateDesignerData:function(event) {
        // Summary:
        //    Update the field "name" with the value of the name or label.
        // Description:
        //    Change the value into all the data array.
        var data = dojo.fromJson(dijit.byId('designerData-' + this._module).get('value'));

        if (this._id > 0) {
            var tableName = this._convertLabelIntoTableName(dijit.byId('name-' + this._module).get('value'));
        } else {
            var tableName = this._convertLabelIntoTableName(dijit.byId('label-' + this._module).get('value'));
            dijit.byId('name-' + this._module).set('value', tableName);
        }

        for (var i in data) {
            data[i]['tableName'] = this._convertLabelIntoTableName(tableName);
        }
        data = dojo.toJson(data);
        dijit.byId('designerData-' + this._module).set('value', data);
    },

    _submitForm:function() {
        // Summary:
        //    Submit the forms.
        if (!this._prepareSubmission()) {
            return false;
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/Core/moduleDesigner/jsonSave/nodeId/1/id/' + this._id,
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Core/module/jsonSave/nodeId/1/id/' + this._id,
                        content:   this._sendData,
                        onSuccess: dojo.hitch(this, function(data) {
                            new phpr.handleResponse('serverFeedback', data);
                            if (data.type == 'success') {
                                if (!this._id) {
                                    // Load the new module
                                    phpr.loadJsFile(phpr.webpath + 'index.php/js/module/name/' + this._sendData['name']
                                     + '/csrfToken/' + phpr.csrfToken);
                                } else {
                                    // Destroy all the layout of this module
                                    dojo.publish(this._sendData['name'] + '.destroyLayout');
                                }
                                dojo.publish('Module.updateCacheData');
                                // Reload global modules
                                phpr.DataStore.deleteData({url: phpr.globalModuleUrl});
                                phpr.DataStore.addStore({url: phpr.globalModuleUrl});
                                phpr.DataStore.requestData({
                                    url:         phpr.globalModuleUrl,
                                    processData: dojo.hitch(this, function() {
                                        dojo.publish('Module.setGlobalModulesNavigation');
                                        dojo.publish('Module.setUrlHash', [phpr.parentmodule, null, [phpr.module]]);
                                    })
                                });
                            }
                        })
                    });
               }
            })
        });
    },

    _deleteForm:function() {
        // Summary:
        //    Delete a module.
        phpr.send({
            url:       phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonDelete/id/' + this._id,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    dojo.publish('Module.updateCacheData');
                    // Reload global modules
                    phpr.DataStore.deleteData({url: phpr.globalModuleUrl});
                    phpr.DataStore.addStore({url: phpr.globalModuleUrl});
                    phpr.DataStore.requestData({
                        url:         phpr.globalModuleUrl,
                        processData: dojo.hitch(this, function() {
                            dojo.publish('Module.setGlobalModulesNavigation');
                            dojo.publish('Module.setUrlHash', [phpr.parentmodule, null, [phpr.module]]);
                        })
                    });
                }
            })
        });
    }
});
