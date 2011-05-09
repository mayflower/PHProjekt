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

dojo.provide("phpr.TableForm");
dojo.provide("phpr.Field");

dojo.provide("phpr.Field.ButtonActionField");
dojo.provide("phpr.Field.CheckField");
dojo.provide("phpr.Field.DateField");
dojo.provide("phpr.Field.DatetimeField");
dojo.provide("phpr.Field.DisplayField");
dojo.provide("phpr.Field.HiddenField");
dojo.provide("phpr.Field.MultipleselectField");
dojo.provide("phpr.Field.PasswordField");
dojo.provide("phpr.Field.PercentageField");
dojo.provide("phpr.Field.RatingField");
dojo.provide("phpr.Field.SelectField");
dojo.provide("phpr.Field.TextareaField");
dojo.provide("phpr.Field.HtmltextareaField");
dojo.provide("phpr.Field.TimeField");
dojo.provide("phpr.Field.UploadField");
dojo.provide("phpr.Field.TextField");

dojo.declare("phpr.TableForm", null, {
    // Summary:
    //    Class for rendering form fields
    // Description:
    //    This class renders the different form types which are available in a PHProjekt Detail View
    _module:  null,
    _tables:  [],
    _widgets: [],

    constructor:function(module) {
        this._module  = module;
        this._tables  = [];
        this._widgets = [];
    },

    createTable:function(tabId) {
        var tableId = this._getTableId(tabId);

        if (!this._tables[tableId]) {
            this._tables[tableId] = dojo.doc.createElement('table');
            this._tables[tableId].className = 'form';
            var colgroup = document.createElement('colgroup');
    		var col1 = document.createElement('col');
    		col1.className = 'col1';
    		colgroup.appendChild(col1);
    		var col2 = document.createElement('col');
    		col2.className = 'col2';
    		colgroup.appendChild(col2);
    		var col3 = document.createElement('col');
    		col3.className = 'col3';
    		colgroup.appendChild(col3);
    		this._tables[tableId].appendChild(colgroup);
        }
    },

    getTable:function(tabId) {
        var tableId = this._getTableId(tabId);

        return (this._tables[tableId]) ? this._tables[tableId] : null;
    },

    existsTable:function(tabId) {
        var tableId = this._getTableId(tabId);

        return (this._tables[tableId]) ? true : false;
    },

    addRow:function(field) {
        var tableId = this._getTableId(field['tab']);
        var rowId   = 'row_' + field['id'] + '-' + this._module;

        // Create and set the widget
        var widgetClass = this._getFieldClass(field);

        if (!dojo.byId(rowId)) {
            // Add the class for future use
            this._widgets.push(widgetClass);

            // Create row
            var row = this._tables[tableId].insertRow(this._tables[tableId].rows.length);
            row.id  = rowId;
            if (field['type'] == 'hidden') {
                // Hidden field => hidden row
                row.style.display = 'none';
            }

            // Label
            var cell       = row.insertCell(0);
            cell.className = 'label';
            cell.appendChild(widgetClass.getLabel());

            // Field
            var cell = row.insertCell(1);
            cell.appendChild(dijit.byId(widgetClass.fieldId).domNode);

            // Buttons
            var cell = row.insertCell(2);
            if (dijit.byId(widgetClass.buttonsId)) {
                cell.appendChild(dijit.byId(widgetClass.buttonsId).domNode);
            }
        }
    },

    addHighlight:function(field) {
        var widgetClass = this._getFieldClass(field);
        widgetClass.addHighlight();
        widgetClass = null;
    },

    removeHighlight:function() {
        // Call the removeHighlight on each field
        dojo.forEach(this._widgets, function(widgetClass) {
            widgetClass.removeHighlight();
        });
    },

    destroyLayout:function() {
        // Call the destroy on each field
        dojo.forEach(this._widgets, function(widgetClass) {
            widgetClass.destroy();
        });
        this._widgets = [];

        // Remove all the rows for all the tables
        for (var tabId in this._tables) {
            if (this._tables[tabId]) {
                dojo.query('tr', this._tables[tabId]).forEach(function(ele) {
                    dojo.destroy(ele);
                });
            }
        }
    },

    /************* Private functions *************/

    _getTableId:function(tabId) {
        if (!tabId) {
            tabId = 1;
        }
        return 'table_' + tabId + '-' + this._module;
    },

    _getFieldClass:function(field) {
        var widgetClass;
        switch (field['type']) {
            case 'buttonAction':
                widgetClass = new phpr.Field.ButtonActionField(field, this._module);
                break;
            case 'checkbox':
                widgetClass = new phpr.Field.CheckField(field, this._module);
                break;
            case 'date':
                widgetClass = new phpr.Field.DateField(field, this._module);
                break;
            case 'datetime':
                widgetClass = new phpr.Field.DatetimeField(field, this._module);
                break;
            case 'display':
                widgetClass = new phpr.Field.DisplayField(field, this._module);
                break;
            case 'formButtons':
                widgetClass = new phpr.Default.FormButtonsField(field, this._module);
                break;
            case 'hidden':
                widgetClass = new phpr.Field.HiddenField(field, this._module);
                break;
            case 'multipleselectbox':
                widgetClass = new phpr.Field.MultipleselectField(field, this._module);
                break;
            case 'password':
                widgetClass = new phpr.Field.PasswordField(field, this._module);
                break;
            case 'percentage':
                widgetClass = new phpr.Field.PercentageField(field, this._module);
                break;
            case 'rating':
                widgetClass = new phpr.Field.RatingField(field, this._module);
                break;
            case 'selectbox':
                widgetClass = new phpr.Field.SelectField(field, this._module);
                break;
            case 'simpletextarea':
                widgetClass = new phpr.Field.TextareaField(field, this._module);
                break;
            case 'textarea':
                widgetClass = new phpr.Field.HtmltextareaField(field, this._module);
                break;
            case 'time':
                widgetClass = new phpr.Field.TimeField(field, this._module);
                break;
            case 'upload':
                widgetClass = new phpr.Field.UploadField(field, this._module);
                break;
            default:
                widgetClass = new phpr.Field.TextField(field, this._module);
                break;
        }

        return widgetClass;
    }
});

dojo.declare("phpr.Field", null, {
    buttonsId: null,
    fieldId:   null,
    _field:    null,
    _module:   null,

    constructor:function(field, module) {
        this._field  = field;
        this._module = module;

        this._setFieldId();
        this._setButtonsId();

        this._setField();
        this._setButtons();
    },

    getLabel:function() {
        var label = document.createElement('label');
        if (null != this.fieldId) {
            label.setAttribute('for', this.fieldId);
        }

        var txt = document.createTextNode(this._field['label'] + ' ');
        label.appendChild(txt);

        if (this._field['required']) {
            var span = document.createElement('span');
            span.className = 'required';
            var txtPpan = document.createTextNode('(*)');
            span.appendChild(txtPpan);
            label.appendChild(span);
        }

        return label;
    },

    addHighlight:function() {
        if (dijit.byId(this.fieldId)) {
            this._setFieldValue();
            this._updateChangedClass('add');
        }
    },

    removeHighlight:function() {
        if (dijit.byId(this.fieldId)) {
            this._updateChangedClass('remove');
        }
    },

    destroy:function() {
        if (dijit.byId(this.fieldId)) {
            dijit.byId(this.fieldId).destroy();
        }
        if (dijit.byId(this.buttonsId)) {
            dijit.byId(this.buttonsId).destroy();
        }
    },

    /************* Private functions *************/

    _setFieldId:function() {
        this.fieldId = this._field['id'] + '-' + this._module;
    },

    _setButtonsId:function() {
        this.buttonsId = 'tooltip_' + this._field['id'] + '-' + this._module;
    },

    _setField:function() {
        if (!dijit.byId(this.fieldId)) {
            this._createField();
        } else {
            this._setFieldValue();
        }
    },

    _getValue:function() {
        return this._field['value'];
    },

    _createField:function() {
    },

    _setFieldValue:function() {
        dijit.byId(this.fieldId).set('value', this._getValue());

        if (this._field['disabled']) {
            dijit.byId(this.fieldId).set('disabled', true);
        } else {
            dijit.byId(this.fieldId).set('disabled', false);
        }
    },

    _setButtons:function() {
        if (!dijit.byId(this.buttonsId)) {
            this._createButtons();
        } else {
            this._setButtonsValue();
        }
    },

    _createButtons:function() {
        if (this._field['hint'] != '') {
            var container = document.createElement('div');
            container.style.whiteSpace = 'nowrap';
            container.innerHTML = this._field['hint'];

            var dialog = new dijit.TooltipDialog({
                content: container
            });

            var widget = new dijit.form.DropDownButton({
                id:        this.buttonsId,
                showLabel: false,
                baseClass: 'smallIcon',
                iconClass: 'help',
                dropDown:  dialog
            });
        }
    },

    _setButtonsValue:function() {
    },

    _processDataDiff:function() {
        // Summary:
        //    Process the new data for the store.
        // Description:
        //    Check for changes between the store and the new data.
        //    - Add new items.
        //    - Edit existing items.
        //    - Delete old items.
        var newData = phpr.clone(this._field['range']);
        var store   = dijit.byId(this.fieldId).store;
        var oldData = store._itemsByIdentity;
        var toKeep  = [];

        for (var j = 0; j < newData.length; j++) {
            var item = oldData[newData[j]['id']];
            if (null == item) {
                // Add a new item
                store.newItem(newData[j]);
                // Mark for keep it
                toKeep[newData[j]['id']] = true;
            } else {
                if (newData[j]['name'] != item.name) {
                    // The name was changed
                    store.setValue(item, 'name', newData[j]['name']);
                }

                // Mark for keep it
                toKeep[item.id] = true;
            }
        }

        // Search for deleted items
        for (var i in oldData) {
            if (oldData[i] && !toKeep[oldData[i].id]) {
                store.deleteItem(oldData[i]);
            }
        }

        // Save the changes
        store.save({});

        // Delete vars
        newData = [];
        oldData = [];
        toKeep  = [];
    },

    _updateChangedClass:function(action) {
        if (action == 'add') {
            dojo.addClass(dojo.byId(this.fieldId).parentNode.parentNode, 'highlightChanges');
        } else {
            dojo.removeClass(dojo.byId(this.fieldId).parentNode.parentNode, 'highlightChanges');
        }
    }
});

dojo.declare("phpr.Field.ButtonActionField", phpr.Field, {
    _createField:function() {
        return new dijit.form.Button({
            id:        this.fieldId,
            label:     this._field['text'],
            type:      'button',
            iconClass: this._field['icon'],
            disabled:  this._field['disabled'],
            onClick:   this._field['action']
        });
    },

    _updateChangedClass:function(action) {
    }
});

dojo.declare("phpr.Field.CheckField", phpr.Field, {
    _getValue:function() {
        return (this._field['value'] == 1) ? true : false;
    },

    _createField:function() {
        return new phpr.Form.CheckBox({
            id:       this.fieldId,
            name:     this.fieldId,
            required: this._field['required'],
            disabled: this._field['disabled'],
            checked:  this._getValue(),
            value:    1
        });
    },

    _setFieldValue:function() {
        dijit.byId(this.fieldId).set('checked', this._getValue());

        if (this._field['disabled']) {
            dijit.byId(this.fieldId).set('disabled', true);
        } else {
            dijit.byId(this.fieldId).set('disabled', false);
        }
    },

    _updateChangedClass:function(action) {
        if (action == 'add') {
            dojo.addClass(dojo.byId(this.fieldId).parentNode, 'highlightChanges');
        } else {
            dojo.removeClass(dojo.byId(this.fieldId).parentNode, 'highlightChanges');
        }
    }
});

dojo.declare("phpr.Field.DateField", phpr.Field, {
    _getValue:function() {
        return (this._field['value']) ? phpr.Date.isoDateTojsDate(this._field['value']) : new Date();
    },

    _createField:function() {
        return new phpr.Form.DateTextBox({
            id:             this.fieldId,
            name:           this.fieldId,
            required:       this._field['required'],
            disabled:       this._field['disabled'],
            value:          this._getValue(),
            constraints:    {datePattern: 'yyyy-MM-dd'},
            promptMessage:  'yyyy-MM-dd',
            invalidMessage: 'Invalid date. Use yyyy-MM-dd format.'
        });
    }
});

dojo.declare("phpr.Field.DatetimeField", phpr.Field, {
    _fieldWidgetId: null,

    destroy:function() {
        var idForDate = this._field['id'] + '_forDate-' + this._module;
        var idForTime = this._field['id'] + '_forTime-' + this._module;
        if (dijit.byId(idForDate)) {
            dijit.byId(idForDate).destroy();
        }
        if (dijit.byId(idForTime)) {
            dijit.byId(idForTime).destroy();
        }
        if (dijit.byId(this._fieldWidgetId)) {
            dijit.byId(this._fieldWidgetId).destroy();
        }

        this.inherited(arguments);
    },

    _setFieldId:function() {
        this._fieldWidgetId = this._field['id'] + '-' + this._module;
        this.fieldId        = this._fieldWidgetId + '_div';
    },

    _getValue:function() {
        return (this._field['value']) ? phpr.Date.isoDatetimeTojsDate(this._field['value']) : new Date();
    },

    _createField:function() {
        var idForDate = this._field['id'] + '_forDate-' + this._module;
        var idForTime = this._field['id'] + '_forTime-' + this._module;
        var value     = this._getValue();

        var node = new dijit.layout.ContentPane({
            id:        this._fieldWidgetId + '_div',
            baseClass: 'twoFields'
        }, document.createElement('div'));

        var fieldWidgetId = this._fieldWidgetId;
        var widgetDate    = new phpr.Form.DateTextBox({
            id:             idForDate,
            name:           idForDate,
            required:       this._field['required'],
            disabled:       this._field['disabled'],
            value:          value,
            constraints:    {datePattern: 'yyyy-MM-dd'},
            promptMessage:  'yyyy-MM-dd',
            invalidMessage: 'Invalid date. Use yyyy-MM-dd format.',
            onChange:       function() {
                dijit.byId(fieldWidgetId).set('value',
                    phpr.Date.getIsoDatetime(this.value, dijit.byId(idForTime).get('value')));
            }
        });

        // Set Time value
        var hour    = value.getHours();
        var minutes = value.getMinutes();
        if (hour < 10) {
            hour = '0' + hour;
        }
        if (minutes < 10) {
            minutes = '0' + minutes;
        }
        var time = dojo.date.stamp.fromISOString('T' + hour + ':' + minutes + ':00');

        var widgetTime = new phpr.Form.TimeTextBox({
            id:          idForTime,
            name:        idForTime,
            required:    this._field['required'],
            disabled:    this._field['disabled'],
            value:       time,
            constraints: {formatLength: 'short', timePattern: 'HH:mm'},
            onChange:    function() {
                dijit.byId(fieldWidgetId).set('value',
                    phpr.Date.getIsoDatetime(dijit.byId(idForDate).get('value'), this.value));
            }
        });
        var widget = new dijit.form.TextBox({
            id:       this._fieldWidgetId,
            name:     this._fieldWidgetId,
            type:     'hidden',
            value:    value,
            required: this._field['required'],
            disabled: this._field['disabled']
        });

        // Insert the widgets into the div node
        node.domNode.appendChild(widgetDate.domNode);
        node.domNode.appendChild(widgetTime.domNode);
        node.domNode.appendChild(widget.domNode);

        // Set a ISO dateTime value
        dijit.byId(fieldWidgetId).set('value',
            phpr.Date.getIsoDatetime(dijit.byId(idForDate).get('value'), dijit.byId(idForTime).get('value')));
    },

    _setFieldValue:function() {
        var idForDate = this._field['id'] + '_forDate-' + this._module;
        var idForTime = this._field['id'] + '_forTime-' + this._module;
        var value     = this._getValue();

        dijit.byId(this._fieldWidgetId).set('value', value);
        dijit.byId(idForDate).set('value', value);
        dijit.byId(idForTime).set('value', value);

        if (this._field['disabled']) {
            dijit.byId(this._fieldWidgetId).set('disabled', true);
            dijit.byId(idForDate).set('disabled', true);
            dijit.byId(idForTime).set('disabled', true);
        } else {
            dijit.byId(this._fieldWidgetId).set('disabled', false);
            dijit.byId(idForDate).set('disabled', false);
            dijit.byId(idForTime).set('disabled', false);
        }
    },

    _updateChangedClass:function(action) {
        var idForDate = this._field['id'] + '_forDate-' + this._module;
        var idForTime = this._field['id'] + '_forTime-' + this._module;

        if (action == 'add') {
            dojo.addClass(dojo.byId(idForDate).parentNode.parentNode, 'highlightChanges');
            dojo.addClass(dojo.byId(idForTime).parentNode.parentNode, 'highlightChanges');
        } else {
            dojo.removeClass(dojo.byId(idForDate).parentNode.parentNode, 'highlightChanges');
            dojo.removeClass(dojo.byId(idForTime).parentNode.parentNode, 'highlightChanges');
        }
    }
});

dojo.declare("phpr.Field.DisplayField", phpr.Field, {
    _setFieldId:function() {
        this.fieldId = this._field['id'] + '-' + this._module + '_div';
    },

    _getValue:function() {
        if (null !== this._field['range'].id) {
            // The Id must be translated into a descriptive String
            var found = false;
            for (var j in this._field['range']) {
                if (this._field['range'][j]) {
                    if (parseInt(this._field['range'][j].id) == this._field['value']) {
                        this._field['value'] = this._field['range'][j].name;
                        found                = true;
                        break;
                    }
                }
            }

            if (!found) {
                this._field['value'] = '';
            }
        }

        return this._field['value'];
    },

    _createField:function() {
        var node = new dijit.layout.ContentPane({
            id:        this.fieldId
        }, document.createElement('div'));

        var label = document.createElement('label');
        var txt = document.createTextNode(this._getValue() + ' ');
        label.appendChild(txt);

        node.domNode.appendChild(label);
    },

    _setFieldValue:function() {
        dojo.byId(this.fieldId).firstChild.innerHTML = this._getValue();
    },

    _updateChangedClass:function(action) {
        if (action == 'add') {
            dojo.addClass(dojo.byId(this.fieldId), 'highlightChanges');
        } else {
            dojo.removeClass(dojo.byId(this.fieldId), 'highlightChanges');
        }
    }
});

dojo.declare("phpr.Default.FormButtonsField", phpr.Field, {
    destroy:function() {
        if (dijit.byId('submitButton-' + this._module)) {
            dijit.byId('submitButton-' + this._module).destroy();
        }
        if (dijit.byId('deleteButton-' + this._module)) {
            dijit.byId('deleteButton-' + this._module).destroy();
        }

        this.inherited(arguments);
    },

    _setFieldId:function() {
        this.fieldId = this._field['id'] + '-' + this._module + '_div';
    },

    _createField:function() {
        var node = new dijit.layout.ContentPane({
            id:        this.fieldId
        }, document.createElement('div'));

        var saveButton = new dijit.form.Button({
            id:        'submitButton-' + this._module,
            label:     phpr.nls.get('Save'),
            iconClass: 'tick',
            type:      'submit',
            disabled:  this._field['disabled']
        });
        var deleteButton = new dijit.form.Button({
            id:        'deleteButton-' + this._module,
            label:     phpr.nls.get('Delete'),
            iconClass: 'cross',
            type:      'submit',
            disabled:  this._field['disabled']
        });

        var container = document.createElement('div');
        container.appendChild(saveButton.domNode);
        container.appendChild(deleteButton.domNode);
        node.set('content', container);

        this._setFieldValue();
    },

    _setFieldValue:function() {
        var disabled = (this._field['writePermissions']) ? false : true;
        dijit.byId('submitButton' + '-' + this._module).set('disabled', disabled);
        if (disabled) {
            dijit.byId('submitButton' + '-' + this._module).domNode.style.display = 'none';
        } else {
            dijit.byId('submitButton' + '-' + this._module).domNode.style.display = 'inline';
        }

        if (this._field['writePermissions']) {
            var disabled = (this._field['deletePermissions']) ? false : true;
        } else {
             var disabled = true;
        }
        dijit.byId('deleteButton' + '-' + this._module).set('disabled', disabled);
        if (disabled) {
            dijit.byId('deleteButton' + '-' + this._module).domNode.style.display = 'none';
        } else {
            dijit.byId('deleteButton' + '-' + this._module).domNode.style.display = 'inline';
        }

        if (this._field['disabled']) {
            dijit.byId('submitButton' + '-' + this._module).set('disabled', true);
            dijit.byId('deleteButton' + '-' + this._module).set('disabled', true);
        } else {
            dijit.byId('submitButton' + '-' + this._module).set('disabled', false);
            dijit.byId('deleteButton' + '-' + this._module).set('disabled', false);
        }
    },

    _updateChangedClass:function(action) {
    }
});

dojo.declare("phpr.Field.HiddenField", phpr.Field, {
    _setFieldId:function() {
        this.fieldId = this._field['id'] + '-' + this._module;
    },

    _createField:function(field) {
        return new dijit.form.TextBox({
            id:    this.fieldId,
            name:  this.fieldId,
            type:  'hidden',
            value: this._getValue()
        });
    },

    _setFieldValue:function() {
        dijit.byId(this.fieldId).set('value', this._getValue());
    }
});

dojo.declare("phpr.Field.HtmltextareaField", phpr.Field, {
    _fieldWidgetId: null,

    isHtml:function() {
        var value    = this._getValue();
        var eregHtml = /([\<])([^\>]{1,})*([\>])/i;

        return value.match(eregHtml);
    },

    destroy:function() {
        if (dijit.byId('saveEditor_' + this._fieldWidgetId)) {
            dijit.byId('saveEditor_' + this._fieldWidgetId).destroy();
        }
        if (dijit.byId('editorFor_' + this._fieldWidgetId)) {
            dijit.byId('editorFor_' + this._fieldWidgetId).destroy();
        }
        if (dijit.byId('dialogFor_' + this._fieldWidgetId)) {
            dijit.byId('dialogFor_' + this._fieldWidgetId).destroy();
        }
        if (dijit.byId(this._fieldWidgetId)) {
            dijit.byId(this._fieldWidgetId).destroy();
        }

        this.inherited(arguments);
    },

    _setFieldId:function() {
        this._fieldWidgetId = this._field['id'] + '-' + this._module;
        this.fieldId        = this._fieldWidgetId + '_div';
    },

    _setButtonsId:function() {
        this.buttonsId = 'buttons_' + this._field['id'] + '-' + this._module;
    },

    _getValue:function() {
        return (this._field['value']) ? this._field['value'] : '\n\n';
    },

    _createField:function(field) {
        // Needed for the onChage function
        var fieldWidgetId = this._fieldWidgetId;

        // Contained node
        var node = new dijit.layout.ContentPane({
            id: this.fieldId
        }, document.createElement('div'));

        // Texarea for Text
        var displayTextDiv           = document.createElement('div');
        displayTextDiv.id            = 'displayText_' + fieldWidgetId;
        displayTextDiv.style.display = (this._field['disabled']) ? 'inline' : (this.isHtml()) ? 'none' : 'inline';
        var widget = new dijit.form.Textarea({
            id:       fieldWidgetId,
            name:     fieldWidgetId,
            disabled: this._field['disabled'],
            value:    this._getValue(),
            onChange: function() {
                dojo.byId('displayHtml_' + fieldWidgetId).innerHTML = this.value;
            }
        });
        displayTextDiv.appendChild(widget.domNode);

        // Display for Html
        var displayHtmlDiv              = document.createElement('div');
        displayHtmlDiv.id               = 'displayHtml_' + fieldWidgetId;
        displayHtmlDiv.innerHTML        = this._getValue();
        displayHtmlDiv.style.display    = (this._field['disabled']) ? 'none' : (this.isHtml()) ? 'inline' : 'none';
        displayHtmlDiv.style.marginLeft = '2px';

        // Create the dialog
        var dialog = new phpr.Dialog({
            id:        'dialogFor_' + fieldWidgetId,
            draggable: false,
            refocus:   false,
            style:     'width: 82%;',
            execute:   function() {
                var value = dijit.byId('editorFor_' + fieldWidgetId).get('value');
                dojo.byId('displayHtml_' + fieldWidgetId).innerHTML = value;
                dijit.byId(fieldWidgetId).set('value', value);
                dojo.style('displayText_' + fieldWidgetId, 'display', 'none');
                dojo.style('textButtons_' + fieldWidgetId, 'display', 'none');
                dojo.style('displayHtml_' + fieldWidgetId, 'display', 'inline');
                dojo.style('htmlButtons_' + fieldWidgetId, 'display', 'inline');
            }
        });

        // Add the editor and save button to the dialog
        var container = document.createElement('div');
        var editor    = new dijit.Editor({
            id:       'editorFor_' + fieldWidgetId,
            disabled: this._field['disabled'],
            style:    'width: 99%; height: 99%; border: 1px solid;',
            plugins:  ['bold', 'italic', 'underline',
                      'strikethrough', 'subscript', 'superscript', 'removeFormat', '|', 'justifyCenter',
                      'justifyFull', 'justifyLeft', 'justifyRight', 'delete', '|', 'insertOrderedList',
                      'insertUnorderedList', 'indent', 'outdent', '|', 'insertHorizontalRule', 'createLink',
                      'insertImage', '|', 'foreColor', 'hiliteColor', '|', 'fontName', 'fontSize']
        });
        var save = new dijit.form.Button({
            id:        'saveEditor_' + fieldWidgetId,
            label:     phpr.nls.get('Save'),
            iconClass: 'tick',
            type:      'submit',
            style:     'margin-left: 0px; margin-top: 10px; margin-bottom: 0px;'
        });
        container.appendChild(editor.domNode);
        container.appendChild(save.domNode);
        dialog.set('content', container);

        // Add the Text and Html nodes to the container
        node.domNode.appendChild(displayTextDiv);
        node.domNode.appendChild(displayHtmlDiv);
    },

    _setFieldValue:function() {
        var value  = this._getValue();

        dojo.byId('displayHtml_' + this._fieldWidgetId).innerHTML = value;
        dijit.byId(this._fieldWidgetId).set('value', value);

        if (this._field['disabled']) {
            dijit.byId(this._fieldWidgetId).set('disabled', true);
        } else {
            dijit.byId(this._fieldWidgetId).set('disabled', false);
        }
    },

    _createButtons:function() {
        var node = new dijit.layout.ContentPane({
            id:    this.buttonsId,
            style: 'overflow: hidden'
        }, document.createElement('div'));

        if (this._field['hint'] != '') {
            // Add Tooltip
            var container = document.createElement('div');
            container.style.whiteSpace = 'nowrap';
            var txt = document.createTextNode(this._field['hint']);
            container.appendChild(txt);

            var dialog = new dijit.TooltipDialog({
                content: container
            });

            var tooltip = new dijit.form.DropDownButton({
                showLabel: false,
                baseClass: 'smallIcon',
                iconClass: 'help',
                dropDown:  dialog
            });

            node.domNode.appendChild(tooltip.domNode);
        }

        // Needed for the onChage function
        var fieldWidgetId = this._fieldWidgetId;

        // Text buttons
        var textButtonDiv              = document.createElement('div');
        textButtonDiv.id               = 'textButtons_' + fieldWidgetId;
        textButtonDiv.style.display    = (this._field['disabled']) ? 'none' : (this.isHtml()) ? 'none' : 'inline';
        textButtonDiv.style.marginLeft = '7px';
        var toHtmlButton = new dijit.form.Button({
            showLabel: false,
            iconClass: 'toHtmlButton',
            baseClass: 'toHtmlButton',
            title:     phpr.nls.get('To HTML Mode'),
            onClick:   function() {
                dojo.style('displayText_' + fieldWidgetId, 'display', 'none');
                dojo.style('textButtons_' + fieldWidgetId, 'display', 'none');
                dojo.style('displayHtml_' + fieldWidgetId, 'display', 'inline');
                dojo.style('htmlButtons_' + fieldWidgetId, 'display', 'inline');
                dijit.byId('editorFor_' + fieldWidgetId).set('value', dijit.byId(fieldWidgetId).get('value'));
                dijit.byId('dialogFor_' + fieldWidgetId).show();
            }
        });
        textButtonDiv.appendChild(toHtmlButton.domNode);

        // Html buttons
        var htmlButtonDiv           = document.createElement('div');
        htmlButtonDiv.id            = 'htmlButtons_' + fieldWidgetId;
        htmlButtonDiv.style.display = (this._field['disabled']) ? 'none' : (this.isHtml()) ? 'inline' : 'none';
        var editHtmlButton = new dijit.form.Button({
            showLabel: false,
            iconClass: 'edit',
            baseClass: 'editButton',
            title:     phpr.nls.get('Edit'),
            onClick:   function() {
                dijit.byId('editorFor_' + fieldWidgetId).set('value', dijit.byId(fieldWidgetId).get('value'));
                dijit.byId('dialogFor_' + fieldWidgetId).show();
            }
        });
        var toTextButton = new dijit.form.Button({
            showLabel: false,
            iconClass: 'toTextButton',
            baseClass: 'toTextButton',
            title:     phpr.nls.get('To Text Mode'),
            style:     'margin-left: 10px;',
            onClick:   function() {
                dojo.style('displayText_' + fieldWidgetId, 'display', 'inline');
                dojo.style('textButtons_' + fieldWidgetId, 'display', 'inline');
                dojo.style('displayHtml_' + fieldWidgetId, 'display', 'none');
                dojo.style('htmlButtons_' + fieldWidgetId, 'display', 'none');
            }
        });
        htmlButtonDiv.appendChild(editHtmlButton.domNode);
        htmlButtonDiv.appendChild(toTextButton.domNode);

        // Add Text and Html buttons
        node.domNode.appendChild(textButtonDiv);
        node.domNode.appendChild(htmlButtonDiv);
    },

    _setButtonsValue:function() {
        var isHtml = this.isHtml();

        if (this._field['disabled']) {
            dojo.style('displayText_' + this._fieldWidgetId, 'display', 'inline');
            dojo.style('textButtons_' + this._fieldWidgetId, 'display', 'none');
            dojo.style('displayHtml_' + this._fieldWidgetId, 'display', 'none');
            dojo.style('htmlButtons_' + this._fieldWidgetId, 'display', 'none');
        } else {
            if (isHtml) {
                dojo.style('displayText_' + this._fieldWidgetId, 'display', 'none');
                dojo.style('textButtons_' + this._fieldWidgetId, 'display', 'none');
                dojo.style('displayHtml_' + this._fieldWidgetId, 'display', 'inline');
                dojo.style('htmlButtons_' + this._fieldWidgetId, 'display', 'inline');
            } else {
                dojo.style('displayText_' + this._fieldWidgetId, 'display', 'inline');
                dojo.style('textButtons_' + this._fieldWidgetId, 'display', 'inline');
                dojo.style('displayHtml_' + this._fieldWidgetId, 'display', 'none');
                dojo.style('htmlButtons_' + this._fieldWidgetId, 'display', 'none');
            }
        }
    },

    _updateChangedClass:function(action) {
        var isHtml = this.isHtml();
        if (action == 'add') {
            if (isHtml) {
                dojo.addClass(dojo.byId('displayHtml_' + this._fieldWidgetId).parentNode, 'highlightChanges');
            } else {
                dojo.addClass(dojo.byId(this._fieldWidgetId), 'highlightChanges');
            }
        } else {
            if (isHtml) {
                dojo.removeClass(dojo.byId('displayHtml_' + this._fieldWidgetId).parentNode, 'highlightChanges');
            } else {
                dojo.removeClass(dojo.byId(this._fieldWidgetId), 'highlightChanges');
            }
        }
    }
});

dojo.declare("phpr.Field.MultipleselectField", phpr.Field, {
    _setFieldId:function() {
        this.fieldId = this._field['id'] + '[]-' + this._module;
    },

    _getValue:function() {
        return this._field['value'].split(',');
    },

    _createField:function(field) {
        // The id must be an string until this path is applied:
        // http://bugs.dojotoolkit.org/ticket/10931
        for (var i in this._field['range']) {
            this._field['range'][i]['id'] = String(this._field['range'][i]['id']);
        }
        var widget = new dojox.form.CheckedMultiSelect({
            id:             this.fieldId,
            name:           this.fieldId,
            required:       this._field['required'],
            store:          new dojo.data.ItemFileWriteStore({data: {
                identifier: 'id',
			    label:      'name',
			    items:      this._field['range']
            }}),
            multiple:       true,
            invalidMessage: ''
        });
        widget.startup();

        // Set the value and the display since the widget must checked the items
        widget.set('value', this._getValue());
        if (this._field['disabled']) {
            dijit.byId(this.fieldId).set('disabled', true);
        } else {
            dijit.byId(this.fieldId).set('disabled', false);
        }
    },

    _updateChangedClass:function(action) {
        if (action == 'add') {
            dojo.addClass(dojo.byId(this.fieldId).parentNode, 'highlightChanges');
        } else {
            dojo.removeClass(dojo.byId(this.fieldId).parentNode, 'highlightChanges');
        }
    }
});

dojo.declare("phpr.Field.PercentageField", phpr.Field, {
    _getValue:function() {
        if (!this._field['value'] || isNaN(this._field['value'])) {
            this._field['value'] = 0;
        }

        return this._field['value'];
    },

    _createField:function() {
        var value = this._getValue();

        var node = document.createElement('div');

        var rulesLabelNode = document.createElement('div');
        node.appendChild(rulesLabelNode);

        var rulesTopNode = document.createElement('div');
        node.appendChild(rulesTopNode);

        var rulesBottomNode = document.createElement('div');
        node.appendChild(rulesBottomNode);

        var sliderLabels = new dijit.form.HorizontalRuleLabels({
            container:     'topDecoration',
            count:         5,
            numericMargin: 1,
            style:         'height: 1.2em; font-size: 75%; color: gray;'
        }, rulesLabelNode);

        var sliderTopRule = new dijit.form.HorizontalRule({
            container: 'topDecoration',
            count:     5,
            style:     'height: 5px;'
        }, rulesTopNode);

        var sliderBottomRule = new dijit.form.HorizontalRule({
            container: 'bottomDecoration',
            count:     11,
            style:     'height: 5px;'
        }, rulesBottomNode);

        // Slider
        var widget = new phpr.Form.HorizontalSlider({
            id:                  this.fieldId,
            name:                this.fieldId,
            maximum:             100,
            minimum:             0,
            discreteValues:      1001,
            pageIncrement:       100,
            showButtons:         false,
            intermediateChanges: true,
            required:            this._field['required'],
            disabled:            this._field['disabled'],
            value:               this._getValue(),
            style:               'height: 20px;'
        }, node);

        widget.startup();
        sliderLabels.startup();
        sliderTopRule.startup();
        sliderBottomRule.startup();
    },

    _updateChangedClass:function(action) {
        if (action == 'add') {
            dojo.addClass(dojo.byId(this.fieldId), 'highlightChanges');
        } else {
            dojo.removeClass(dojo.byId(this.fieldId), 'highlightChanges');
        }
    }
});

dojo.declare("phpr.Field.RatingField", phpr.Field, {
    _createField:function() {
        return new phpr.Form.Rating({
            id:       this.fieldId,
            name:     this.fieldId,
            disabled: this._field['disabled'],
            numStars: this._field['range'].id,
            value:    this._getValue()
        });
    },

    _setFieldValue:function() {
        dijit.byId(this.fieldId).setAttribute('value', this._getValue());

        if (this._field['disabled']) {
            dijit.byId(this.fieldId).set('disabled', true);
        } else {
            dijit.byId(this.fieldId).set('disabled', false);
        }
    },

    _updateChangedClass:function(action) {
        if (action == 'add') {
            dojo.addClass(dojo.byId(this.fieldId).parentNode, 'highlightChanges');
        } else {
            dojo.removeClass(dojo.byId(this.fieldId).parentNode, 'highlightChanges');
        }
    }
});

dojo.declare("phpr.Field.SelectField", phpr.Field, {
    _getValue:function() {
        var found = false;
        var first = null;
        for (var j in this._field['range']) {
            if (null == first) {
                first = this._field['range'][j].id;
            }
            if (this._field['range'][j].id == this._field['value']) {
                found = true;
                break;
            }
        }
        if (!found && (null !== first)) {
            this._field['value'] = first;
        }

        return this._field['value'];
    },

    _createField:function() {
        var widget = new phpr.Form.FilteringSelect({
            id:             this.fieldId,
            name:           this.fieldId,
            disabled:       this._field['disabled'],
            autoComplete:   false,
            store:          new dojo.data.ItemFileWriteStore({data: {
                identifier: 'id',
			    label:      'name',
			    items:      phpr.clone(this._field['range'])}}),
            searchAttr:     'name',
            value:          this._getValue(),
            invalidMessage: ''
        });
    },

    _setFieldValue:function() {
        // The store must be updated
        this._processDataDiff();

        // Update the value
        dijit.byId(this.fieldId).set('value', this._getValue());

        if (this._field['disabled']) {
            dijit.byId(this.fieldId).set('disabled', true);
        } else {
            dijit.byId(this.fieldId).set('disabled', false);
        }
    }
});

dojo.declare("phpr.Field.TextareaField", phpr.Field, {
    _getValue:function() {
        return (this._field['value']) ? this._field['value'] : '\n\n';
    },

    _createField:function() {
        return new dijit.form.Textarea({
            id:       this.fieldId,
            name:     this.fieldId,
            required: this._field['required'],
            disabled: this._field['disabled'],
            value:    this._getValue()
        });
    },

    _updateChangedClass:function(action) {
        if (action == 'add') {
            dojo.addClass(dojo.byId(this.fieldId), 'highlightChanges');
        } else {
            dojo.removeClass(dojo.byId(this.fieldId), 'highlightChanges');
        }
    }
});

dojo.declare("phpr.Field.TextField", phpr.Field, {
    _createField:function() {
        return new dijit.form.TextBox({
            id:             this.fieldId,
            name:           this.fieldId,
            required:       this._field['required'],
            disabled:       this._field['disabled'],
            ucfirst:        true,
            maxlength:      (this._field['length'] > 0) ? this._field['length'] : '',
            value:          this._getValue(),
            invalidMessage: ''
        });
    }
});

dojo.declare("phpr.Field.TimeField", phpr.Field, {
    _getValue:function() {
        return (this._field['value']) ? phpr.Date.isoTimeTojsDate(this._field['value']) : new Date();
    },

    _createField:function(field) {
        return new phpr.Form.TimeTextBox({
            id:          this.fieldId,
            name:        this.fieldId,
            required:    this._field['required'],
            disabled:    this._field['disabled'],
            value:       this._getValue(),
            constraints: {formatLength: 'short', timePattern: 'HH:mm'}
        });
    }
});

dojo.declare("phpr.Field.PasswordField", phpr.Field, {
    _createField:function(field) {
        return new dijit.form.TextBox({
            id:             this.fieldId,
            name:           this.fieldId,
            type:           'password',
            required:       this._field['required'],
            disabled:       this._field['disabled'],
            maxlength:      (this._field['length'] > 0) ? this._field['length'] : '',
            value:          this._getValue(),
            invalidMessage: ''
        });
    }
});

dojo.declare("phpr.Field.UploadField", phpr.Field, {
    _fieldWidgetId: null,

    destroy:function() {
        if (dojo.byId('filesIframe_' + this._fieldWidgetId)) {
            dojo.destroy(dojo.byId('filesIframe_' + this._fieldWidgetId));
        }
        if (dijit.byId(this._fieldWidgetId)) {
            dijit.byId(this._fieldWidgetId).destroy();
        }

        this.inherited(arguments);
    },

    _setFieldId:function() {
        this._fieldWidgetId = this._field['id'] + '-' + this._module;
        this.fieldId        = this._fieldWidgetId + '_div';
    },

    _createField:function() {
        var node = new dijit.layout.ContentPane({
            id: this.fieldId
        }, document.createElement('div'));

        var widget = new dijit.form.TextBox({
            id:       this._fieldWidgetId,
            name:     this._fieldWidgetId,
            type:     'hidden',
            disabled: this._field['disabled'],
            value:    this._getValue()
        });

		if (dojo.isIE) {
            var html = '<iframe id="filesIframe_' + this._fieldWidgetId + '" src="' + this._field['iFramePath'] + '"'
                + ' height="25px" width="311px" frameborder="0" scrolling="no"'
                + ' style="overflow: hidden; border: 0px;"></iframe>';
            var iframe = dojo.doc.createElement(html);
		} else {
		 	iframe     = dojo.create('iframe');
		 	iframe.id  = 'filesIframe_' + this._fieldWidgetId;
			iframe.src = this._field['iFramePath'];
            iframe.setAttribute('height', '25px');
            iframe.setAttribute('width', '311px');
            iframe.setAttribute('frameborder', 0);
            iframe.setAttribute('scrolling', 'no');
			iframe.style.overflow = 'hidden';
			iframe.style.border   = '0px';
		}

        // Insert the widgets into the div node
        node.domNode.appendChild(widget.domNode);
        node.domNode.appendChild(iframe);
    },

    _setFieldValue:function() {
        dijit.byId(this._fieldWidgetId).set('value', this._getValue());
        // Reload iframe
        var iframe = document.getElementById('filesIframe_' + this._fieldWidgetId);
        iframe.contentWindow.location.href = this._field['iFramePath'];

        if (this._field['disabled']) {
            dijit.byId(this._fieldWidgetId).set('disabled', true);
        } else {
            dijit.byId(this._fieldWidgetId).set('disabled', false);
        }

        // TODO Set the uploadField in the iframe, disabled
        //dojo.byId('filesIframe_' + this._fieldWidgetId).contentWindow.document.forms[0].uploadedFile.disabled = true;
    },

    _updateChangedClass:function(action) {
        if (action == 'add') {
            dojo.addClass(dojo.byId(this.fieldId), 'highlightChanges');
        } else {
            dojo.removeClass(dojo.byId(this.fieldId), 'highlightChanges');
        }
    }
});
