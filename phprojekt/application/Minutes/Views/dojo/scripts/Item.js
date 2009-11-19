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

dojo.provide("phpr.Minutes.Item");

dojo.declare("phpr.Minutes.Item", phpr.Default.SubModule, {
    constructor:function(parentId) {
        this.module     = "MinutesItem";
        this.gridWidget = phpr.Minutes.Item.Grid;
        this.formWidget = phpr.Minutes.Item.Form;
        this.parentId   = parentId;
    },

    getController:function() {
        return 'item';
    }
});

dojo.declare("phpr.Minutes.Item.Grid", phpr.Default.SubModule.Grid, {
    useIdInGrid:function() {
        return false;
    },

    customGridLayout:function(meta) {
        // Summary:
        //    Set a different layout for the grid
        // Description:
        //    Set a different layout for the grid
        var deleteFields = new Array('sortOrder', 'minutesId', 'projectId', 'comment');
        this.gridLayout = dojo.filter(this.gridLayout, function(item) {
                return (!phpr.inArray(item['field'], deleteFields));
            }
        );

        for (cell in this.gridLayout) {
            if (this.gridLayout[cell]['field'] == 'topicId') {
                this.gridLayout[cell]['rowSpan'] = 2;
                this.gridLayout[cell]['width']   = '9%';
            } else if (this.gridLayout[cell]['field'] == 'gridEdit') {
                this.gridLayout[cell]['rowSpan'] = 2;
            } else if (this.gridLayout[cell]['field'] == 'title') {
                this.gridLayout[cell]['width'] = '46%';
            } else if (this.gridLayout[cell]['field'] == 'userId') {
                this.gridLayout[cell]['width'] = '20%';
            }
        }

        this.gridLayout = [this.gridLayout, [
            {
                width:    'auto',
                name:     phpr.nls.get('Comment'),
                field:    'comment',
                type:     phpr.grid.cells.Textarea,
                styles:   "text-align: left;",
                editable: false,
                colSpan:  4
            }
        ]];
    }
});

dojo.declare("phpr.Minutes.Item.Form", phpr.Default.SubModule.Form, {
    initData:function() {
        this._itemsUrl = phpr.webpath + "index.php/Minutes/item/jsonListItemSortOrder/minutesId/" + this.main.parentId;
        this._initData.push({'url': this._itemsUrl});
    },

    addBasicFields:function() {
        var data  = phpr.DataStore.getData({url: this._url});
        var range = phpr.DataStore.getData({url: this._itemsUrl});
        var label = phpr.nls.get('Sort after');
        var id    = this.main.module + 'parentOrder';
        var value = data[0]['sortOrder'] - 1 >= 0 ? data[0]['sortOrder'] - 1 : 0;

        this.formdata[this._tabNumber] += this.fieldTemplate.selectRender(range, label, id, value, false, false, '');
    },

    postRenderForm:function() {
        // Have the appropriate input fields appear for each type
        var data = phpr.DataStore.getData({url: this._url});
        this._switchItemFormFields(data[0]['topicType']); // defaults
        dojo.connect(dijit.byId(this.main.module + 'topicType'), 'onChange',
            dojo.hitch(this, this._switchItemFormFields));
    },

    _switchItemFormFields:function(typeValue) {
        // Summary:
        //    Toggle visibility of detail form fields
        // Description:
        //    Hides or shows the appropriate form fields for the currently
        //    selected topicType. Currently registered types are:
        //    1='Topic', 2='Statement', 3='TODO', 4='Decision', 5='Date'
        var display = (dojo.isIE) ? 'block' : 'table-row';
        var trDate  = dojo.byId(this.main.module + 'topicDate').parentNode.parentNode.parentNode.parentNode.parentNode;
        var trUser  = dojo.byId(this.main.module + 'userId').parentNode.parentNode.parentNode.parentNode.parentNode;
        switch(parseInt(typeValue)) {
            case 3:
                dojo.style(trUser, "display", display);
                dojo.style(trDate, "display", display);
                dijit.byId(this.main.module + 'userId').attr("disabled", false);
                dijit.byId(this.main.module + 'topicDate').attr("disabled", false);
                break;
            case 5:
                dojo.style(trUser, "display", "none");
                dojo.style(trDate, "display", display);
                dijit.byId(this.main.module + 'userId').attr("disabled", true);
                dijit.byId(this.main.module + 'topicDate').attr("disabled", false);
                break;
            default:
                dojo.style(trUser, "display", "none");
                dojo.style(trDate, "display", "none");
                dijit.byId(this.main.module + 'userId').attr("disabled", true);
                dijit.byId(this.main.module + 'topicDate').attr("disabled", true);
                break;
        }
    },

    updateData:function() {
        this.inherited(arguments);
        phpr.DataStore.deleteData({url: this._itemsUrl});
    }
});