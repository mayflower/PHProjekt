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
 * @version    $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Default.SubModule");
dojo.provide("phpr.Default.SubModule.Grid");
dojo.provide("phpr.Default.SubModule.Form");

dojo.declare("phpr.Default.SubModule", phpr.Component, {
    // Internal vars
    gridBox:      null,
    detailsBox:   null,
    subForm:      null,
    subGrid:      null,
    module:       null,
    parentId:     null,
    gridWidget:   null,
    formWidget:   null,
    sortPosition: 1,

    constructor:function(parentId) {
        // Summary:
        //    Set some vars to run the sub module
        // Description:
        //    Define the parent id, the current module and the widgets to use
        this.module     = "DefaultSubModule";
        this.gridWidget = phpr.Default.SubModule.Grid;
        this.formWidget = phpr.Default.SubModule.Form;
        this.parentId   = parentId;
    },

    getController:function() {
        // Summary:
        //    Return the controller to use
        // Description:
        //    Return the controller to use
        return 'index';
    },

    setUrl:function(type, id) {
        // Summary:
        //    Set all the urls
        // Description:
        //    Set all the urls
        var url = phpr.webpath + "index.php/" + this.module + "/" + this.getController();
        switch (type) {
            case 'grid':
                url += "/jsonList/";
                break;
            case 'form':
                url += "/jsonDetail/";
                break;
            case 'save':
                url += "/jsonSave/";
                break;
            case 'delete':
                url += "/jsonDelete/";
                break;
        }
        url += phpr.module.toLowerCase() + "Id/" + this.parentId;
        if (type != 'grid') {
            url += '/id/' + id;
        }

        return url;
    },

    fillTab:function(nodeId) {
        // Summary:
        //    Create the sub module tab
        // Description:
        //    Create the divs for contain the grid and the form
        var content = new dijit.layout.ContentPane({
            region: 'center'
        }, document.createElement('div'));

        var borderContainer = new dijit.layout.BorderContainer({
            design: 'sidebar'
        }, document.createElement('div'));

        this.gridBox = new dijit.layout.ContentPane({
            region: 'center'
        }, document.createElement('div'));

        var tmpContent = new dijit.layout.ContentPane({
            region: 'right',
            style:  'width: 50%;'
        }, document.createElement('div'));
        this.detailsBox = new dijit.layout.ContentPane({
            region: 'center',
            style:  'height: auto;'
        }, document.createElement('div'));
        this.buttonsBox = new dijit.layout.ContentPane({
            region:    'bottom',
            style:     'height: auto; padding-left: 15.6%;',
            baseClass: 'footer'
        }, document.createElement('div'));

        tmpContent.domNode.appendChild(this.detailsBox.domNode);
        tmpContent.domNode.appendChild(this.buttonsBox.domNode);

        borderContainer.addChild(this.gridBox);
        borderContainer.addChild(tmpContent);
        content.attr("content", borderContainer.domNode);

        dijit.byId(nodeId).attr('content', content);

        dojo.connect(dijit.byId(nodeId), "onShow", dojo.hitch(this, function() {
            this._renderSubModule();
        }));
    },

    _renderSubModule:function() {
        // Summary:
        //    Render the grid and the form widgets
        // Description:
        //    Render the grid and the form widgets
        this.subGrid = new this.gridWidget('', this, phpr.currentProjectId);
        this.subForm = new this.formWidget(this, 0, phpr.module);
    },

    updateCacheData:function() {
        // Summary:
        //    Update the grid and the form widgets
        // Description:
        //    Update the grid and the form widgets
        //    Render both again
        if (this.subGrid) {
            this.subGrid.updateData();
        }
        if (this.subForm) {
            this.subForm.updateData();
        }
        this._renderSubModule();
    }
});

dojo.declare("phpr.Default.SubModule.Grid", phpr.Default.Grid, {
    // Overwrite functions for use with internal vars
    // This functions can be Rewritten
    updateData:function() {
        phpr.DataStore.deleteData({url: this.url});
    },

    usePencilForEdit:function() {
        return true;
    },

    useIdInGrid:function() {
        return true;
    },

    // Overwrite functions for use with internal vars
    // This functions should not be Rewritten

    setGridLayout:function(meta) {
        // Summary:
        //    Set all the field as not editables
        // Description:
        //    Set all the field as not editables
        this.inherited(arguments);
        for (cell in this.gridLayout) {
            if (typeof(this.gridLayout[cell]['editable']) == 'boolean') {
                this.gridLayout[cell]['editable'] = false;
            } else {
                for (index in this.gridLayout[cell]) {
                    if (typeof(this.gridLayout[cell][index]['editable']) == 'boolean') {
                        this.gridLayout[cell][index]['editable'] = false;
                    }
                }
            }
        }
    },

    setUrl:function() {
        this.url = this.main.setUrl('grid');
    },

    getLinkForEdit:function(id) {
        this.main.subForm = new this.main.formWidget(this.main, id, phpr.module);
    },

    setNode:function() {
        this._node = this.main.gridBox;
    },

    // Set empty functions for avoid them
    // This functions should not be Rewritten

    useCheckbox:function() {
        return false;
    },

    setFilterUrl:function(filters) {
        this.setUrl();
    },

    processActions:function() {
    },

    setExportButton:function(meta) {
    },

    setSaveChangesButton:function(meta) {
    },

    loadGridSorting:function() {
    },

    saveGridSorting:function(e) {
    },

    loadGridScroll:function() {
    },

    saveGridScroll:function() {
    },

    setFilterButton:function(meta) {
    },

    manageFilters:function() {
    },

    showTags:function() {
    }
});

dojo.declare("phpr.Default.SubModule.Form", phpr.Default.Form, {
    _tabNumber: 99,

    // Overwrite functions for use with internal vars
    // This functions can be Rewritten

    initData:function() {
    },

    addBasicFields:function() {
    },

    updateData:function() {
        if (this.id > 0) {
            phpr.DataStore.deleteData({url: this._url});
        }
    },

    // Set empty functions for avoid them
    // This functions should not be Rewritten

    setUrl:function() {
        this._url = this.main.setUrl('form', this.id);
    },

    setNode:function() {
        this._formNode = this.main.detailsBox;
    },

    setPermissions:function(data) {
        this._writePermissions  = true;
        this._deletePermissions = true;
        this._accessPermissions = false;
    },

    getTabs:function() {
        // Summary:
        //    Change the tab number for don't overwrite the module tab
        // Description:
        //    Change the tab number for don't overwrite the module tab
        while (dijit.byId('tabBasicData' + this._tabNumber)) {
            this._tabNumber++;
        }

        return new Array({"id":     this._tabNumber,
                          "name":   phpr.nls.get('Basic Data'),
                          "nameId": 'subModuleTab' + this._tabNumber})
    },

    setFormButtons:function() {
        // Summary:
        //    Display buttons for the sub module instead of the default
        // Description:
        //    Display buttons for the sub module instead of the default
        phpr.destroySubWidgets(this.main.buttonsBox.domNode.id);
        this.main.buttonsBox.domNode.innerHTML = '';

        var params = {
            label:     phpr.nls.get('Save'),
            showLabel: true,
            baseClass: 'positive',
            iconClass: 'tick',
            style:     'padding-right: 7px',
            disabled:  false
        };
        this._submoduleSaveButton = new dijit.form.Button(params);
        this.main.buttonsBox.domNode.appendChild(this._submoduleSaveButton.domNode);
        dojo.connect(this._submoduleSaveButton, "onClick", dojo.hitch(this, "submitForm"));

        if (this.id > 0) {
            var params = {
                label:     phpr.nls.get('Delete'),
                showLabel: true,
                baseClass: 'positive',
                iconClass: 'cross',
                style:     'padding-right: 7px',
                disabled:  false
            };
            this._submoduleDeleteButton = new dijit.form.Button(params);
            this.main.buttonsBox.domNode.appendChild(this._submoduleDeleteButton.domNode);
            dojo.connect(this._submoduleDeleteButton, 'onClick', dojo.hitch(this, "deleteForm"));
        }

        var params = {
            label:     phpr.nls.get('New'),
            showLabel: true,
            baseClass: 'positive',
            iconClass: 'add',
            style:     'padding-right: 7px',
            disabled:  false
        };
        this._submoduleNewButton = new dijit.form.Button(params);
        this.main.buttonsBox.domNode.appendChild(this._submoduleNewButton.domNode);
        dojo.connect(this._submoduleNewButton, 'onClick', dojo.hitch(this, function() {
            this.main.subForm = new this.main.formWidget(this.main, 0, phpr.module);
        }));
    },

    setCustomFieldValues:function(fieldValues) {
        // Summary:
        //    Change the name of the fields for don't overwrite the module fields
        // Description:
        //    Change the name of the fields for don't overwrite the module fields
        //    Also change the tab for the same reason
        fieldValues['id']  = this.main.module + fieldValues['id'];
        fieldValues['tab'] = fieldValues['tab'] * this._tabNumber;

        return fieldValues;
    },

    prepareSubmission:function() {
        // Summary:
        //    Return the field names with the original name
        // Description:
        //    Return the field names with the original name
        this.sendData = new Array();
        for (var i = 0; i < this.formsWidget.length; i++) {
            if (!this.formsWidget[i].isValid()) {
                var parent = this.formsWidget[i].containerNode.parentNode.id;
                this.form.selectChild(parent);
                this.formsWidget[i].validate();
                return false;
            }
            var data = this.formsWidget[i].attr('value');
            for (var index in data) {
                if (index.indexOf(this.main.module) == 0) {
                    var newIndex   = index.substr(this.main.module.length);
                    data[newIndex] = data[index];
                    delete data[index];
                }
            }
            if (typeof(data) != 'object') {
                data = new Array(data);
            }
            this.sendData = dojo.mixin(this.sendData, data);
        }

        return true;
    },

    submitForm:function() {
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url:       this.main.setUrl('save', this.id),
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.main.updateCacheData();
                }
            })
        });
    },

    deleteForm:function() {
        phpr.send({
            url:       this.main.setUrl('delete', this.id),
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.main.updateCacheData();
                }
            })
        });
    },

    // Set empty functions for avoid them
    // This functions should not be Rewritten
    addModuleTabs:function(data) {
    },

    useHistoryTab:function() {
        return false;
    }
});
