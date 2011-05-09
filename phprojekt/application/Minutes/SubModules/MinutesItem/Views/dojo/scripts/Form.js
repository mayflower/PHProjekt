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
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.MinutesItem.Form");

dojo.declare("phpr.MinutesItem.Form", phpr.Default.SubModule.Form, {
    // Events Buttons
    _eventForParent: null,

    updateData:function() {
        // Summary:
        //    Delete the cache for this form.
        this.inherited(arguments);
        phpr.DataStore.deleteData({url: this._itemsUrl});
    },

    /************* Private functions *************/

    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
        this._itemsUrl = phpr.webpath + 'index.php/MinutesItem/index/jsonListItemSortOrder/minutesId/'
            + this._parentId;
        this._initDataArray.push({'url': this._itemsUrl});
    },

    _addBasicFields:function() {
        // Summary:
        //    Add some special fields.
        var data  = phpr.DataStore.getData({url: this._url});

        var fieldValues = {
            id:       'parentOrder',
            type:     'selectbox',
            tab:      this._tabNumber,
            label:    phpr.nls.get('Sort after', this._module),
            value:    (data[0]['sortOrder'] - 1 >= 0) ? data[0]['sortOrder'] - 1 : 0,
            range:    phpr.DataStore.getData({url: this._itemsUrl}),
            disabled: false,
            required: false,
            hint:     ''
        };

        this._fieldTemplate.addRow(fieldValues);
    },

    _postRenderForm:function() {
        // Have the appropriate input fields appear for each type
        var data = phpr.DataStore.getData({url: this._url});
        this._switchItemFormFields(data[0]['topicType']); // defaults

        if (!this._eventForParent) {
            this._eventForParent = dojo.connect(dijit.byId('topicType-' + this._module), 'onChange',
                dojo.hitch(this, this._switchItemFormFields)
            );
            this._events.push('_eventForParent');
        };
    },

    _switchItemFormFields:function(typeValue) {
        // Summary:
        //    Toggle visibility of detail form fields.
        // Description:
        //    Hides or shows the appropriate form fields for the currently
        //    selected topicType. Currently registered types are:
        //    1='Topic', 2='Statement', 3='TODO', 4='Decision', 5='Date'
        var display = (dojo.isIE) ? 'block' : 'table-row';
        var trDate  = dojo.byId('topicDate-' + this._module).parentNode.parentNode.parentNode.parentNode;
        var trUser  = dojo.byId('userId-' + this._module).parentNode.parentNode.parentNode.parentNode;
        switch(parseInt(typeValue)) {
            case 3:
                dojo.style(trUser, 'display', display);
                dojo.style(trDate, 'display', display);
                dijit.byId('userId-' + this._module).set('disabled', false);
                dijit.byId('topicDate-' + this._module).set('disabled', false);
                if (dojo.isIE) {
                    // Fix the display of the selectBox for IE
                    dojo.style(dojo.byId('userId-' + this._module), 'display', 'inline');
                    dojo.style(dojo.byId('topicDate-' + this._module), 'display', 'inline');
                }
                break;
            case 5:
                dojo.style(trUser, 'display', 'none');
                dojo.style(trDate, 'display', display);
                dijit.byId('userId-' + this._module).set('disabled', true);
                dijit.byId('topicDate-' + this._module).set('disabled', false);
                if (dojo.isIE) {
                    // Fix the display of the selectBox for IE
                    dojo.style(dojo.byId('userId-' + this._module), 'display', 'none');
                    dojo.style(dojo.byId('topicDate-' + this._module), 'display', 'inline');
                }
                break;
            default:
                dojo.style(trUser, 'display', 'none');
                dojo.style(trDate, 'display', 'none');
                dijit.byId('userId-' + this._module).set('disabled', true);
                dijit.byId('topicDate-' + this._module).set('disabled', true);
                if (dojo.isIE) {
                    // Fix the display of the selectBox for IE
                    dojo.style(dojo.byId('userId-' + this._module), 'display', 'none');
                    dojo.style(dojo.byId('topicDate-' + this._module), 'display', 'none');
                }
                break;
        }
    }
});
