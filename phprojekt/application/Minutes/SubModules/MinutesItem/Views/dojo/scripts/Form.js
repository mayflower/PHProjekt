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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.MinutesItem.Form");

dojo.declare("phpr.MinutesItem.Form", phpr.Default.SubModule.Form, {
    initData:function() {
        this._itemsUrl = phpr.webpath + 'index.php/MinutesItem/index/jsonListItemSortOrder/minutesId/'
            + this.main.parentId;
        this._initData.push({'url': this._itemsUrl});
    },

    addBasicFields:function() {
        var data  = phpr.DataStore.getData({url: this._url});
        var range = phpr.DataStore.getData({url: this._itemsUrl});
        var label = phpr.nls.get('Sort after', this.main.module);
        var id    = this.main.module + 'parentOrder';
        var value = (data[0]['sortOrder'] - 1 >= 0) ? data[0]['sortOrder'] - 1 : 0;

        this.formdata[this._tabNumber] += this.fieldTemplate.selectRender(range, label, id, value, false, false);
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
