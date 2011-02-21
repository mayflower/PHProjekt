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
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Default.SubModule");
dojo.provide("phpr.Default.SubModule.Grid");
dojo.provide("phpr.Default.SubModule.Form");

dojo.declare("phpr.Default.SubModule", null, {
    // Internal vars
    gridBox:      null,
    //detailsBox:   null,
    subForm:      null,
    subGrid:      null,
    module:       null,
    parentId:     null,
    gridWidget:   null,
    formWidget:   null,
    sortPosition: 1,

    constructor:function() {
        // Summary:
        //    Set some vars to run the sub module.
        // Description:
        //    Define the current module and the widgets to use.
        this._module = 'DefaultSubModule';

        this._loadFunctions();

        this._gridWidget = phpr.Default.SubModule.Grid;
        this._formWidget = phpr.Default.SubModule.Form;
    },

    _loadFunctions:function() {
        // Summary:
        //    Add all the functions for the current module.
        dojo.subscribe(this._module + '.updateCacheData', this, 'updateCacheData');
        dojo.subscribe(this._module + '.openForm', this, 'openForm');
        dojo.subscribe(this._module + '.gridProxy', this, 'gridProxy');
    },

    fillTab:function(nodeId) {
        // Summary:
        //    Create the sub module tab.
        // Description:
        //    Create the divs for contain the grid and the form.
        var content = new dijit.layout.ContentPane({
            region: 'center'
        }, document.createElement('div'));

        var borderContainer = new dijit.layout.BorderContainer({
            design: 'sidebar'
        }, document.createElement('div'));

        var gridBox = new dijit.layout.ContentPane({
            id:     'gridBox-' + this._module,
            region: 'center'
        }, document.createElement('div'));

        var detailsBox = new dijit.layout.ContentPane({
            id:     'detailsBox-' + this._module,
            region: 'right',
            style:  'width: 50%; height: 100%;'
        }, document.createElement('div'));

        borderContainer.addChild(gridBox);
        borderContainer.addChild(detailsBox);
        content.set('content', borderContainer.domNode);

        dijit.byId(nodeId).set('content', content);
    },

    renderSubModule:function(parentId) {
        // Summary:
        //    Render the grid and the form widgets.
        this.parentId = parentId;

        if (!this.subGrid) {
            this.subGrid = new this._gridWidget(this._module);
        }
        this.subGrid.init(this.parentId);

        if (!this.subForm) {
            this.subForm = new this._formWidget(this._module);
        }
        this.subForm.init(0, [], this.parentId);
    },

    updateCacheData:function() {
        // Summary:
        //    Update the grid and the form widgets.
        if (this.subGrid) {
            this.subGrid.updateData();
        }
        if (this.subForm) {
            this.subForm.updateData();
        }
        this.renderSubModule(this.parentId);
    },

    gridProxy:function(functionName, params) {
        // Summary:
        //    Proxy for run grid functions.
        if (this.subGrid) {
            dojo.hitch(this.subGrid, functionName).apply(this, [params]);
        }
    },

    openForm:function(id) {
        // Summary:
        //    Open a form for edit.
        this.subForm.init(id, [], this.parentId);
    }
});

dojo.declare("phpr.Default.SubModule.Grid", phpr.Default.Grid, {
    _parentId:  0,

    init:function(id) {
        // Summary:
        //    Init the form for a new render.
        this._parentId = id;

        this.inherited(arguments);
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this grid.
        this._cached[this._id] = false;
        phpr.DataStore.deleteData({url: this._url});
    },

    _setUrl:function() {
        // Summary:
        //    Set the url for getting the data.
        this._url = phpr.webpath + 'index.php/' + this._module + '/' + this._getController();
        this._url += '/jsonList/';
        this._url += 'nodeId/' + phpr.currentProjectId + '/';
        this._url += phpr.module.toLowerCase() + 'Id/' + this._parentId;
    },

    _getController:function() {
        // Summary:
        //    Return the controller to use.
        return 'index';
    },

    _processActions:function() {
        // Summary:
        //    Processes the info of the grid actions and fills the appropriate arrays.
    },

    _usePencilForEdit:function() {
        // Summary:
        //    Draw the pencil icon for edit the row.
        return false;
    },

    _useCheckbox:function() {
        // Summary:
        //    Whether to show or not the checkbox in the grid list.
        return false;
    },

    _useIdInGrid:function() {
        // Summary:
        //    Draw the ID on the grid.
        return true;
    },

    _customGridLayout:function(gridLayout) {
        // Summary:
        //    Custom functions for the layout.
        for (cell in gridLayout) {
            if (typeof(gridLayout[cell]['editable']) == 'boolean') {
                gridLayout[cell]['editable'] = false;
            } else {
                for (index in gridLayout[cell]) {
                    if (typeof(gridLayout[cell][index]['editable']) == 'boolean') {
                        gridLayout[cell][index]['editable'] = false;
                    }
                }
            }
        }

        return gridLayout;
    },

    _loadGridSorting:function() {
        // Summary:
        //    Retrieves from cookies the sorting criterion for the current grid if any.
        //    Use the hash for identify the cookie
    },

    _loadGridScroll:function() {
        // Summary:
        //    Retrieves from cookies the scroll position for the current grid, if there is one.
        //    Use the hash for identify the module grid
    },

    _setExportButton:function(meta) {
        // Summary:
        //    If there is any row, render an export Button.
    },

    _setFilterButton:function(meta) {
        // Summary:
        //    If there is any row, render a filter Button.
    },

    _renderFilters:function() {
        // Summary:
        //    Prepare the filter form.
    },

    _showTags:function() {
        // Summary:
        //    Draw the tags.
    },

    _getLinkForEdit:function(id) {
        // Summary:
        //    Return the link for open the form.
        dojo.publish(this._module + '.openForm', [id]);
    },

    _saveGridSorting:function(e) {
        // Summary:
        //    Stores in cookies the new sorting criterion for the current grid.
        //    Use the hash for identify the cookie.
    },

    _saveGridScroll:function() {
        // Summary:
        //    Stores in cookies the new scroll position for the current grid.
        //    Use the hash for identify the cookie.
    }
});

dojo.declare("phpr.Default.SubModule.Form", phpr.Default.Form, {
    _parentId:  0,
    _tabNumber: 99,

    // Events Buttons
    _eventForNew: null,

    init:function(id, params, parentId) {
        // Summary:
        //    Init the form for a new render.
        this._parentId = parentId;

        this.inherited(arguments);
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this form.
        if (this._id > 0) {
            phpr.DataStore.deleteData({url: this._url});
        }
    },

    /************* Private functions *************/

    _setUrl:function() {
        // Summary:
        //    Set the url for get the data.
        this._url = this._setFormUrl('form', this._id);
    },

    _setFormUrl:function(type, id) {
        // Summary:
        //    Set all the urls for the form.
        var url = phpr.webpath + 'index.php/' + this._module + '/' + this._getController();
        switch (type) {
            case 'form':
                url += '/jsonDetail/';
                break;
            case 'save':
                url += '/jsonSave/';
                break;
            case 'delete':
                url += '/jsonDelete/';
                break;
        }
        if (type != 'delete') {
            url += 'nodeId/' + phpr.currentProjectId + '/';
        }
        url += 'id/' + id + '/';
        url += phpr.module.toLowerCase() + 'Id/' + this._parentId;

        return url;
    },

    _getController:function() {
        // Summary:
        //    Return the controller to use.
        return 'index';
    },

    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
    },

    _getTabs:function() {
        // Summary:
        //    Change the tab number for don't overwrite the module tab.
        while (dijit.byId('tabBasicData' + this._tabNumber + '-' + phpr.module)) {
            this._tabNumber++;
        }

        return [{
            id:     this._tabNumber,
            name:   phpr.nls.get('Basic Data'),
            nameId: 'subModuleTab' + this._tabNumber}];
    },

    _setPermissions:function(data) {
        // Summary:
        //    Get the permission for the current user on the item.
        this._writePermissions  = true;
        this._deletePermissions = true;
        this._accessPermissions = false;
    },

    _setCustomFieldValues:function(fieldValues) {
        // Summary:
        //    Change the tab of the fields for don't overwrite the module tab.
        fieldValues['tab'] = fieldValues['tab'] * this._tabNumber;

        return fieldValues;
    },

    _getUploadIframePath:function(itemid) {
        // Summary:
        //    Set the URL for request the upload file.
        return phpr.webpath + 'index.php/' + this._module + '/index/fileForm'
            + '/nodeId/' + phpr.currentProjectId + '/id/' + this._id + '/field/' + itemid
            + '/parentId/'  + this._parentId + '/csrfToken/' + phpr.csrfToken;
    },

    _addBasicFields:function() {
        // Summary:
        //    Add some special fields.
    },

    _addModuleTabs:function(data) {
        // Summary:
        //    Add extra tabs.
    },

    _useHistoryTab:function() {
        // Summary:
        //    Return true or false if the history tab is used.
        return false;
    },

    _postRenderForm:function() {
        // Summary:
        //    User functions after render the form.
        // Description:
        //    Add a "new" buttom and hide the "delete" on new items.
        var newButton = dijit.byId('newButton-' + this._module);
        if (!newButton) {
            var newButton = new dijit.form.Button({
                id:        'newButton-' + this._module,
                label:     phpr.nls.get('New'),
                iconClass: 'add',
                type:      'button',
                style:     'display: inline;',
                disabled:  false
            });

            dojo.byId('buttons-' + this._module + '_div').firstChild.appendChild(newButton.domNode);
        }

        if (!this._eventForNew) {
            this._eventForNew = dojo.connect(newButton, 'onClick',
                dojo.hitch(this, function() {
                    this.init(0, [], this._parentId);
                })
            );
            this._events.push('_eventForNew');
        };

        // Hide delete button on new items
        if (this._id < 1) {
            dijit.byId('deleteButton-' + this._module).domNode.style.display = 'none';
        }
    },

    _submitForm:function() {
        // Summary:
        //    Submit the forms.
        if (!this._prepareSubmission()) {
            return false;
        }

        phpr.send({
            url:       this._setFormUrl('save', this._id),
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    dojo.publish(this._module + '.updateCacheData');
                }
            })
        });
    },

    _deleteForm:function() {
        // Summary:
        //    Delete an item.
        phpr.send({
            url:       this._setFormUrl('delete', this._id),
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    dojo.publish(this._module + '.updateCacheData');
                }
            })
        });
    }
});
