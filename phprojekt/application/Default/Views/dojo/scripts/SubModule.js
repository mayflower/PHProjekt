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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Default.SubModule");
dojo.provide("phpr.Default.SubModule.Grid");
dojo.provide("phpr.Default.SubModule.Form");

dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.layout.BorderContainer");

dojo.declare("phpr.Default.SubModule", phpr.Default.System.Component, {
    // Internal vars
    overviewBox:  null,
    detailsBox:   null,
    subForm:      null,
    subGrid:      null,
    module:       null,
    parentId:     null,
    gridWidget:   null,
    formWidget:   null,
    sortPosition: 1,

    constructor: function(parentId) {
        // Summary:
        //    Set some vars to run the sub module
        // Description:
        //    Define the parent id, the current module and the widgets to use
        this.module     = "DefaultSubModule";
        this.gridWidget = phpr.Default.SubModule.Grid;
        this.formWidget = phpr.Default.SubModule.Form;
        this.parentId   = parentId;
    },

    destroy: function() {
        this.destroySubForm();
        this.inherited(arguments);
    },

    getController: function() {
        // Summary:
        //    Return the controller to use
        // Description:
        //    Return the controller to use
        return 'index';
    },

    setUrl: function(type, id) {
        // Summary:
        //    Set all the urls
        // Description:
        //    Set all the urls
        var url = 'index.php/' + this.module + '/' + this.getController();
        switch (type) {
            case 'grid':
                url += '/jsonList/';
                break;
            case 'form':
                url += '/jsonDetail/';
                break;
            case 'save':
                url += '/jsonSave/';
                break;
            case 'delete':
                url += '/jsonDelete/';
                break;
            default:
                break;
        }
        if (type != 'delete') {
            url += 'nodeId/' + phpr.currentProjectId + '/';
        }
        if (type != 'grid') {
            url += 'id/' + id + '/';
        }
        url += phpr.module.toLowerCase() + 'Id/' + this.parentId;

        return url;
    },

    createTab: function(main) {
        // Summary:
        //    Create the sub module tab
        // Description:
        //    Create the divs for contain the grid and the form

        var container = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.singleTableRow.html"
        });

        // we need a fixed height here because the form rendering flow is still inherently broken
        // subforms cannot determine it's height because their rendering is triggered after the outer form
        // is build.
        this.borderContainer = new dijit.layout.BorderContainer({
            design: 'sidebar',
            style: 'height: 400px; width: 100%;'
        });

        this._buildForm();
        this._renderSubModule();

        container.containerNode.appendChild(this.borderContainer.domNode);

        return main.addTab(
            [container],
            'tab' + this.module,
            phpr.nls.get(this.module, this.module),
            this.module + 'FormTab'
        );
    },

    _buildForm: function() {
        this.overviewBox = new dijit.layout.ContentPane({
            region: 'center'
        }, document.createElement('div'));

        this.detailsBox = new dijit.layout.ContentPane({
            region: 'right',
            style:  'width: 50%;'
        }, document.createElement('div'));

        this.borderContainer.addChild(this.overviewBox);
        this.borderContainer.addChild(this.detailsBox);

        dojo.addClass(this.borderContainer.domNode, 'subModuleDiv');
    },

    _renderSubModule: function() {
        // Summary:
        //    Render the grid and the form widgets
        // Description:
        //    Render the grid and the form widgets
        if (this.subGrid && dojo.isFunction(this.subGrid.destroy)) {
            this.subGrid.destroy();
        }
        this.subGrid = new this.gridWidget('', this, phpr.currentProjectId, this.overviewBox);
        this.destroySubForm();
        this.subForm = new this.formWidget(this, 0, phpr.module, {}, this.detailsBox);
    },

    updateCacheData: function() {
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
    },

    destroySubForm: function() {
        if (this.subForm && dojo.isFunction(this.subForm.destroy)) {
            this.subForm.destroy();
        }
        this.subForm = null;
    }
});

dojo.declare("phpr.Default.SubModule.Grid", phpr.Default.LegacyGrid, {
    // Overwrite functions for use with internal vars
    // This functions can be Rewritten
    updateData: function() {
        phpr.DataStore.deleteData({url: this.url});
    },

    usePencilForEdit: function() {
        return false;
    },

    useIdInGrid: function() {
        return true;
    },

    // Overwrite functions for use with internal vars
    // This functions should not be Rewritten

    setGridLayout: function(meta) {
        // Summary:
        //    Set all the field as not editables
        // Description:
        //    Set all the field as not editables
        this.inherited(arguments);
        for (var cell in this.gridLayout) {
            if (typeof(this.gridLayout[cell].editable) == 'boolean') {
                this.gridLayout[cell].editable = false;
            } else {
                for (var index in this.gridLayout[cell]) {
                    if (typeof(this.gridLayout[cell][index].editable) == 'boolean') {
                        this.gridLayout[cell][index].editable = false;
                    }
                }
            }
        }
    },

    setUrl: function() {
        this.url = this.main.setUrl('grid');
    },

    editItemWithId: function(id) {
        this.main.destroySubForm();
        this.main.subForm = new this.main.formWidget(this.main, id, phpr.module, {}, this.main.detailsBox);
    },

    // Set empty functions for avoid them
    // This functions should not be Rewritten

    useCheckbox: function() {
        return false;
    },

    setFilterQuery: function(filters) {
        this.setUrl();
    },

    processActions: function() {
    },

    setExportButton: function(meta) {
    },

    loadGridSorting: function() {
    },

    saveGridSorting: function(e) {
    },

    loadGridScroll: function() {
    },

    saveGridScroll: function() {
    },

    setFilterButton: function(meta) {
    },

    manageFilters: function() {
    }
});

dojo.declare("phpr.Default.SubModule.Form", phpr.Default.Form, {
    _tabNumber: 99,

    // Overwrite functions for use with internal vars
    // This functions can be Rewritten

    initData: function() {
    },

    addBasicFields: function() {
    },

    updateData: function() {
        if (this.id > 0) {
            phpr.DataStore.deleteData({url: this._url});
        }
    },

    // Set empty functions for avoid them
    // This functions should not be Rewritten

    setUrl: function() {
        this._url = this.main.setUrl('form', this.id);
    },

    setPermissions: function(data) {
        this._writePermissions  = true;
        this._deletePermissions = true;
        this._accessPermissions = false;
    },

    getTabs: function() {
        // Summary:
        //    Change the tab number for don't overwrite the module tab
        // Description:
        //    Change the tab number for don't overwrite the module tab
        while (dijit.byId('tabBasicData' + this._tabNumber)) {
            this._tabNumber++;
        }

        return new Array({"id":     this._tabNumber,
                          "name":   phpr.nls.get('Basic Data'),
                          "nameId": 'subModuleTab' + this._tabNumber});
    },

    setFormButtons: function(tabId) {
        // Summary:
        //    Display buttons for the sub module instead of the default
        // Description:
        //    Display buttons for the sub module instead of the default
        this.formdata[tabId].push(new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.subModuleButtons.html",
            templateData: {
                saveText:   phpr.nls.get('Save'),
                deleteText: phpr.nls.get('Delete'),
                newText:    phpr.nls.get('New'),
                id:         this.id
            }
        }));
    },

    setActionFormButtons: function() {
        // Summary:
        //    Connect the buttons to the actions

        if (this.id > 0) {
            dojo.connect(dijit.byId("subModuleDeleteButton"), "onClick", dojo.hitch(this, function() {
                phpr.confirmDialog(dojo.hitch(this, "deleteForm"), phpr.nls.get('Are you sure you want to delete?'));
            }));
        }

        dojo.connect(dijit.byId("subModuleNewButton"), 'onClick', dojo.hitch(this, function() {
            this.main.destroySubForm();
            this.main.subForm = new this.main.formWidget(this.main, 0, phpr.module, {}, this.main.detailsBox);
        }));
    },

    setCustomFieldValues: function(fieldValues) {
        // Summary:
        //    Change the name of the fields for don't overwrite the module fields
        // Description:
        //    Change the name of the fields for don't overwrite the module fields
        //    Also change the tab for the same reason
        if (fieldValues.type != 'upload') {
            fieldValues.id = this.main.module + fieldValues.id;
        }
        fieldValues.tab = fieldValues.tab * this._tabNumber;

        return fieldValues;
    },

    prepareSubmission: function() {
        // Summary:
        //    Return the field names with the original name
        // Description:
        //    Return the field names with the original name
        this.sendData = {};
        for (var i = 0; i < this.formsWidget.length; i++) {
            if (!this.formsWidget[i].isValid()) {
                var parent = this.formsWidget[i].containerNode.parentNode.id;
                this.form.selectChild(parent);
                this.formsWidget[i].validate();
                return false;
            }
            var data = this.formsWidget[i].get('value');
            for (var index in data) {
                if (index.indexOf(this.main.module) === 0) {
                    var newIndex   = index.substr(this.main.module.length);
                    data[newIndex] = data[index];
                    delete data[index];
                }
            }
            if (typeof(data) != 'object') {
                data = new Array(data);
            }
            dojo.mixin(this.sendData, data);
        }

        return true;
    },

    submitForm: function() {
        if (!this.prepareSubmission()) {
            return false;
        }

        this.setSubmitInProgress(true);
        phpr.send({
            url:       this.main.setUrl('save', this.id),
            content:   this.sendData
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                this.setSubmitInProgress(false);
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.main.updateCacheData();
                }
            }
        }));

        return false;
    },

    deleteForm: function() {
        phpr.send({
            url:       this.main.setUrl('delete', this.id)
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.main.updateCacheData();
                }
            }
        }));
    },

    // Set empty functions for avoid them
    // This functions should not be Rewritten
    addModuleTabs: function(data) {
    },

    useHistoryTab: function() {
        return false;
    },

    getUploadIframePath: function(itemid) {
        return 'index.php/' +
            this.main.module + '/index/fileForm' +
            '/nodeId/' + phpr.currentProjectId +
            '/id/' + this.id + '/field/' + itemid +
            '/parentId/'  + this.main.parentId +
            '/csrfToken/' + phpr.csrfToken;
    }
});
