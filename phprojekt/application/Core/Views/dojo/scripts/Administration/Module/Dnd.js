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

dojo.provide('phpr.Module.DesignerSource');
dojo.provide('phpr.ModuleDesigner');

dojo.declare("phpr.Module.DesignerSource", dojo.dnd.AutoSource, {
    // Summary:
    //    Extend the dojo Source for custom actions in the onDrop.
    onDrop:function(source, nodes, copy) {
        // Summary:
        //    Extend the onDrop function.
        // Description:
        //    When an item is droped in the target widget:
        //    Show the edit/delete buttons.
        //    Add the moved item into the source widget again.
        //    Open the form for edit the new field.
        if (this != source) {
            this.onDropExternal(source, nodes, copy);
            var m = dojo.dnd.manager();
            if (this.node.id == m.target.node.id) {
                if (source.node.id == 'source-ModuleDesigner') {
                    var table = m.target.anchor.childNodes[0].childNodes[1].childNodes[0];

                    // Display the buttons
                    table.childNodes[2].style.display = 'inline';

                    // Set as new item
                    dojo.addClass(m.target.anchor, 'newItem');

                    // Get the type, and re-draw the field in the source widget
                    var type = table.childNodes[1].childNodes[0].value;
                    phpr.ModuleDesigner.addField(source, type, 'source');

                    // Open the edit form
                    phpr.ModuleDesigner.editField(m.target.anchor.id);
                }
            }
        } else {
            this.onDropInternal(nodes, copy);
        }
    },

    markupFactory:function(params, node) {
        // Summary:
        //    Needed by dojo.
        params._skipStartup = true;
        return new phpr.Module.DesignerSource(node, params);
    }
});

dojo.declare("phpr.ModuleDesigner", null, {
    // Summary:
    //    The class manage the source and target fields.
    _moduleId:             null,
    _fieldTemplate:        null,
    _sourceWidget:         null,
    _sourceWidgetPosition: [],
    _targetWidget:         [],

    constructor:function() {
        // Summary:
        //    Set the field render on create the class.
        this._fieldTemplate = new phpr.TableForm('ModuleDesigner');
    },

    createSourceFields:function() {
        // Summary:
        //    Draw the source fields.
        var sourceNode = dojo.byId('source-ModuleDesigner');

        // Create a text node for show the help
        var textContent = document.createElement('div');
        dojo.style(textContent, {
            textAlign: 'center',
            padding:   '5px 0 2px;'
        });
        textContent.innerHTML = phpr.nls.get('Repository of available field types');

        var container = document.createElement('div');
        container.style.whiteSpace = 'nowrap';
        var txt1 = document.createTextNode(phpr.nls.get('1. Drag a field into the right pane.'));
        var nl   = document.createElement('br');
        var txt2 = document.createTextNode(phpr.nls.get('2. Edit the parameters of the field in the lower left pane.'));
        container.appendChild(txt1);
        container.appendChild(nl);
        container.appendChild(txt2);

        var dialog = new dijit.TooltipDialog({
            content: container
        });

        var tooltip = new dijit.form.DropDownButton({
            showLabel: false,
            baseClass: 'smallIcon',
            iconClass: 'help',
            tabindex:  -1,
            dropDown:  dialog
        });

        textContent.appendChild(tooltip.domNode);
        sourceNode.appendChild(textContent);

        // Create the source widget
        this._sourceWidget = new phpr.Module.DesignerSource('source-ModuleDesigner', {
            copyOnly: false,
            skipForm: true,
            accept:   ['none'] // Disable drop from target to source
        });

        // Add all the field types
        var types = ['text', 'date', 'time', 'datetime', 'selectValues', 'checkbox',
                     'percentage', 'rating', 'textarea', 'upload'];
        for (i in types) {
            this.addField(this._sourceWidget, types[i], 'source');
        }
    },

    addField:function(sourceWidget, fildType, targetType, params) {
        // Summary:
        //    Add a table with the label/field/icons into a new dnd item.
        // Description:
        //    For source field, use fixed positions.
        var pos = {
            'text':         0,
            'date':         1,
            'time':         2,
            'datetime':     3,
            'selectValues': 4,
            'checkbox':     5,
            'percentage':   6,
            'rating':       7,
            'textarea':     8,
            'upload':       9
        };

        var id      = dojo.dnd.getUniqueId();
        var table   = this._createTableField(fildType, targetType, params);
        var item    = document.createElement('div');
        var nextPos = pos[fildType] + 1;
        if (targetType == 'source' && this._sourceWidgetPosition[nextPos]) {
            // Add the item before the anchor field
            var anchor = dojo.byId(this._sourceWidgetPosition[nextPos]);
            sourceWidget.insertNodes(false, [{data: ' ', id: id}], true, anchor);
        } else {
            sourceWidget.insertNodes(false, [{data: ' ', id: id}]);
        }

        if (!dojo.isIE) {
            // Remove empty text node
            dojo.byId(id).removeChild(dojo.byId(id).childNodes[0]);
        }

        // Add the table to the dnd item.
        dojo.byId(id).appendChild(table);

        if (targetType == 'source') {
            // Save the id in the position
            this._sourceWidgetPosition[pos[fildType]] = id;
        }
    },

    createTargetFields:function(moduleId, jsonData, tabs) {
        // Summary:
        //    Draw the target fields in the correct tab.
        this._moduleId = moduleId;
        if (jsonData) {
            var data = dojo.fromJson(jsonData);
            for (var j in tabs) {
                var tabId     = 'moduleDesignerTargetTab' + tabs[j]['id'];
                var tabWidget = dijit.byId(tabId);
                if (!tabWidget) {
                    var tabWidget = new dijit.layout.ContentPane({
                        id:      tabId,
                        title:   phpr.nls.get(tabs[j]['name'])
                    });
                    dijit.byId('target-ModuleDesigner').addChild(tabWidget);
                    // Fix layout
                    // When add a tab into the dialog, the top lost their value
                    dijit.byId('target-ModuleDesigner').containerNode.style.top = '22px';

                } else {
                    tabWidget.set('title', phpr.nls.get(tabs[j]['name']));
                }

                // Hide all the other modules divs
                dojo.forEach(dojo.byId(tabId).children, function(node) {
                    node.style.display = 'none';
                });

                // Create or show a div for this module
                var targetId = 'target-ModuleDesigner-' + tabs[j]['id'] + '-' + this._moduleId;
                if (!dojo.byId(targetId)) {
                    var node = document.createElement('div');
                    node.id  = targetId;
                    dojo.style(node, {
                        backgroundColor: '#fff',
                        width:           '100%',
                        height:          '95%',
                        margin:          '0px'
                    });
                    dojo.byId(tabId).appendChild(node);
                } else {
                    dojo.byId(targetId).style.display = 'inline';
                }

                // If no have content, add the help and the fields
                if (dojo.byId(targetId).children.length == 0) {
                    // Create a text node for show the help
                    var textContent = document.createElement('div');
                    dojo.style(textContent, {
                        textAlign:     'center',
                        paddingBottom: '2px'
                    });
                    textContent.innerHTML = phpr.nls.get('Active fields in the module');

                    var container = document.createElement('div');
                    container.style.whiteSpace = 'nowrap';
                    var txt1 = document.createTextNode(phpr.nls.get('Drop in this panel all the fields that you want to'
                        + ' have in this tab.'));
                    var nl   = document.createElement('br');
                    var txt2 = document.createTextNode(phpr.nls.get('For sort the fields, just drag and drop it in the'
                        + ' correct position.'));
                    container.appendChild(txt1);
                    container.appendChild(nl);
                    container.appendChild(txt2);

                    var dialog = new dijit.TooltipDialog({
                        content: container
                    });

                    var tooltip = new dijit.form.DropDownButton({
                        showLabel: false,
                        baseClass: 'smallIcon',
                        iconClass: 'help',
                        tabindex:  -1,
                        dropDown:  dialog
                    });

                    textContent.appendChild(tooltip.domNode);
                    dojo.byId(targetId).appendChild(textContent);

                    // Create the source widget
                    this._targetWidget[targetId] = new phpr.Module.DesignerSource(targetId, {
                        skipForm:   true,
                        selfAccept: true
                    });

                    for (var i in data) {
                        if (data[i]['formTab'] == tabs[j]['id']) {
                            this.addField(this._targetWidget[targetId], data[i]['formType'], 'target', data[i]);
                        }
                    }
                }
            }
        }
    },

    editField:function(nodeId) {
        // Summary:
        //    Make the edit form.
        dojo.style(dojo.byId('editor-ModuleDesigner'), 'display', 'none');
        var selectType   = '';
        var tableType    = ''
        var tableLength  = '';
        var tableField   = '';
        var formLabel    = '';
        var formType     = '';
        var formRange    = '';
        var defaultValue = '';
        var listPosition = '';
        var status       = '';
        var isRequired   = '';
        var id           = '';
        dojo.query('.hiddenValue', dojo.byId(nodeId)).forEach(function(ele) {
            switch (ele.name) {
                case 'selectType':
                    selectType = ele.value;
                    break;
                case 'tableType':
                    tableType = ele.value;
                    break;
                case 'tableLength':
                    tableLength = ele.value;
                    break;
                case 'tableField':
                    tableField = ele.value;
                    break;
                case 'formLabel':
                    formLabel = ele.value;
                    break;
                case 'formType':
                    formType = ele.value;
                    break;
                case 'formRange':
                    formRange = ele.value;
                    break;
                case 'defaultValue':
                    defaultValue = ele.value;
                    break;
                case 'listPosition':
                    listPosition = ele.value;
                    break;
                case 'status':
                    status = ele.value;
                    break;
                case 'isRequired':
                    isRequired = ele.value;
                    break;
                case 'id':
                    id = ele.value;
                    break;
            }
        });

        // Table tab
        var tableTab = this._createTableTab(tableField, formType, tableType, tableLength,
            selectType, id, nodeId);

        // Form tab
        var formTab = this._createFormTab(formLabel, formType, formRange, defaultValue);

        // List tab
        var listTab = this._createListTab(listPosition);

        // General tab
        var generalTab = this._createGeneralTab(status, isRequired);

        var forms = {
            formTable:   {tab: 'editorTable-ModuleDesigner',   table: tableTab},
            formForm:    {tab: 'editorForm-ModuleDesigner',    table: formTab},
            formList:    {tab: 'editorList-ModuleDesigner',    table: listTab},
            formGeneral: {tab: 'editorGeneral-ModuleDesigner', table: generalTab}
        };
        for (var i in forms) {
            var formWidget = dijit.byId(i);
            if (!formWidget) {
                // New form
                var formWidget = new dijit.form.Form({
                    id:       i,
                    name:     i,
                    style:    'height: 100%',
                    onSubmit: function() {
                        return false;
                    }
                });
                formWidget.domNode.appendChild(forms[i].table);

                dijit.byId(forms[i].tab).set('content', formWidget);

                // Save
                dojo.connect(dijit.byId('submitButton-ModuleDesigner-' + i), 'onClick', function() {
                    phpr.ModuleDesigner.saveField();
                });
                // Cancel
                dojo.connect(dijit.byId('deleteButton-ModuleDesigner-' + i), 'onClick', function() {
                    phpr.ModuleDesigner.switchOkButton('save');
                });

                if (i == 'formForm') {
                    // Connect the change of the Range
                    dojo.connect(dijit.byId('selectType-ModuleDesigner'), 'onChange', function(){
                        switch (dijit.byId('selectType-ModuleDesigner').get('value')) {
                            case 'custom':
                            default:
                                dijit.byId('formRange-ModuleDesigner').set('value', 'id1 # value1 | id2 # value2');
                                dijit.byId('tableType-ModuleDesigner').set('value', 'int');
                                dijit.byId('tableLength-ModuleDesigner').set('value', 11);
                                break;
                            case 'project':
                                dijit.byId('formRange-ModuleDesigner').set('value', 'Project # id # title');
                                dijit.byId('tableType-ModuleDesigner').set('value', 'int');
                                dijit.byId('tableLength-ModuleDesigner').set('value', 11);
                                break;
                            case 'user':
                                dijit.byId('formRange-ModuleDesigner').set('value', 'User # id # lastname');
                                dijit.byId('tableType-ModuleDesigner').set('value', 'int');
                                dijit.byId('tableLength-ModuleDesigner').set('value', 11);
                                break;
                            case 'contact':
                                dijit.byId('formRange-ModuleDesigner').set('value', 'Contact # id # name');
                                dijit.byId('tableType-ModuleDesigner').set('value', 'int');
                                dijit.byId('tableLength-ModuleDesigner').set('value', 11);
                                break;
                        }
                    });
                }
            }
        }

        // Hide disable rows
        // tableLength
        var tr = dojo.byId('tableLength-ModuleDesigner').parentNode.parentNode.parentNode.parentNode;
        if (dojo.byId('tableLength-ModuleDesigner').disabled) {
            tr.style.display = 'none';
        } else {
            tr.style.display = (dojo.isIE) ? 'block' : 'table-row';
        }
        // selectType
        var tr = dojo.byId('selectType-ModuleDesigner').parentNode.parentNode.parentNode.parentNode;
        if (dojo.byId('selectType-ModuleDesigner').disabled) {
            tr.style.display = 'none';
        } else {
            tr.style.display = (dojo.isIE) ? 'block' : 'table-row';
        }
        // formRange
        var tr = dojo.byId('formRange-ModuleDesigner').parentNode.parentNode;
        if (dojo.byId('formRange-ModuleDesigner').disabled) {
            tr.style.display = 'none';
        } else {
            tr.style.display = (dojo.isIE) ? 'block' : 'table-row';
        }

        this.switchOkButton('editor');
    },

    saveField:function() {
        // Summary:
        //    Mix the form data and make a new field with the data.
        var params = [];
        params     = dojo.mixin(params, dijit.byId('formTable').get('value'));
        params     = dojo.mixin(params, dijit.byId('formForm').get('value'));
        params     = dojo.mixin(params, dijit.byId('formList').get('value'));
        params     = dojo.mixin(params, dijit.byId('formGeneral').get('value'));

        var sendData = [];
        // Add the fields without the module string
        for (var index in params) {
            var newIndex       = index.substr(0, index.length - 15);
            sendData[newIndex] = params[index];
        }

        this.switchOkButton('save');

        var tr = dojo.byId(sendData.nodeId).childNodes[0].childNodes[1].childNodes[0];

        // Update label
        tr.childNodes[0].childNodes[0].innerHTML = sendData.formLabel;

        // Update hidden values
        dojo.query('.hiddenValue', tr).forEach(function(ele) {
            ele.value = sendData[ele.name];
        });

        if (sendData.formType == 'selectValues') {
            // Update range
            var rangeData = this._getRangeAndDefaultValue(sendData.selectType, sendData.formRange);
            var range        = rangeData.range;
            var defaultValue = rangeData.defaultValue;
            var select       = dijit.byId(tr.childNodes[1].childNodes[1].childNodes[2].childNodes[0].id);
            select.store = new dojo.data.ItemFileWriteStore({data: {
                identifier: 'id',
    			label:      'name',
    			items:      range
            }});
            select.set('value', defaultValue);
        }
    },

    deleteField:function(nodeId) {
        // Summary:
        //    Delete a field.
        // Description:
        //    Delete only the target fields.
        //    Hide the edit form
        var node  = dojo.byId(nodeId);
        var tabId = node.parentNode.id;

        // Delete only the target items
        if (tabId != 'source-ModuleDesigner') {
            if (node) {
                var tab = this._targetWidget[tabId];
                // Make sure it is not the anchor
                if (tab.anchor == node){
                    tab.anchor = null;
                }
                // Remove it from the master map
                tab.delItem(name);
                // Hide and mark as deleted the node
                node.style.display = 'none';
                node.className     = 'deleted';
            }
        }

        this.switchOkButton('save');
    },

    switchOkButton:function(type) {
        // Summary:
        //    Switch between the Save and the Edit Form.
        dijit.byId('editor-ModuleDesigner').selectChild(dijit.byId('editorTable-ModuleDesigner'));
        if (type == 'editor') {
            var node = dijit.byId('editor-ModuleDesigner');
            dojo.fadeIn({
                node:        node.domNode,
                duration:    300,
                beforeBegin: function() {
                    node.selectChild(dijit.byId('editorTable-ModuleDesigner'));
                    dojo.style(node.domNode, 'opacity', 0);
                    dojo.style(node.domNode, 'display', 'block');
                    dojo.style(dojo.byId('saveButton-ModuleDesigner'), 'display', 'none');
                }
            }).play();
        } else {
            dojo.style(dojo.byId('editor-ModuleDesigner'), 'display', 'none');
            dojo.style(dojo.byId('saveButton-ModuleDesigner'), 'display', 'block');
        }
    },

    /************* Private functions *************/

    _createTableField:function(formType, target, params) {
        // Summary:
        //    Draw a field using the params and the formType.
        var html       = '';
        var formLabel  = null;
        var labelFor   = null;
        var tableField = null;
        var required   = null;
        var labelTxt   = null;
        var inputTxt   = null;
        if (!params) {
            params = new Array();
        }

        var formLabel  = '';
        var selectType = params['selectType'] || 'custom';
        var tableType  = params['tableType'] || 'varchar';
        if (tableType == 'int') {
            var tableLength = params['tableLength'] || 11;
        } else {
            var tableLength = params['tableLength'] || 255;
        }
        var tableField   = params['tableField'] || '';
        var formRange    = params['formRange'] || '';
        var defaultValue = params['defaultValue'] || '';
        var listPosition = parseInt(params['listPosition']);
        if  (isNaN(listPosition)) {
            listPosition = 0;
        }
        var status = parseInt(params['status']);
        if  (isNaN(status)) {
            status = 1;
        }
        var isRequired = params['isRequired'] || 0;
        var id         = params['id'] || 0;

        if (formType == 'selectValues') {
            var options = formRange.split('|');
            for (var i in options) {
                var values = options[i].split('#');
                if (values[0] && values[1] && !values[2]) {
                    selectType = 'custom';
                    break;
                } else if (values[0] && values[1] && values[2]) {
                    selectType = values[0].replace(/(^\s*)|(\s*$)/g, "").toLowerCase();
                    if (!phpr.inArray(selectType, new Array('project', 'user', 'contact'))) {
                        selectType = 'custom';
                    }
                    break;
                }
            }
        }

        switch (formType) {
            case 'text':
            default:
                formLabel = params['formLabel'] || 'Text';
                labelFor  = 'text';
                inputTxt  = new dijit.form.TextBox({ucfirst: true});
                break;
            case 'checkbox':
                formLabel = params['formLabel'] || 'Checkbox';
                labelFor  = 'checkbox';
                inputTxt  = new phpr.Form.CheckBox({value: 1});
                break;
            case 'date':
                formLabel = params['formLabel'] || 'Date';
                labelFor  = 'date';
                inputTxt  = new phpr.Form.DateTextBox({constraints: {datePattern: 'yyyy-MM-dd'}, promptMessage: 'dd.mm.yy'});
                break;
            case 'time':
                formLabel = params['formLabel'] || 'Time';
                labelFor  = 'time';
                inputTxt  = new phpr.Form.TimeTextBox({constraints: {formatLength: 'short', timePattern: 'HH:mm'}});
                break;
            case 'datetime':
                formLabel = params['formLabel'] || 'Datetime';
                labelFor  = 'datetime';
                inputTxt  = document.createElement('div');
                inputTxt.className = 'twoFields';
                var dateInput = new phpr.Form.DateTextBox({constraints: {datePattern: 'yyyy-MM-dd'},
                    promptMessage: 'dd.mm.yy'});
                var timeInput = new phpr.Form.TimeTextBox({constraints: {formatLength: 'short', timePattern: 'HH:mm'}});
                inputTxt.appendChild(dateInput.domNode);
                inputTxt.appendChild(timeInput.domNode);
                break;
            case 'selectValues':
                formLabel = params['formLabel'] || 'Select';
                labelFor  = 'selectValues';

                var rangeData = this._getRangeAndDefaultValue(selectType, formRange);
                var range        = rangeData.range;
                var defaultValue = rangeData.defaultValue;

                inputTxt = new phpr.Form.FilteringSelect({
                    autoComplete:   false,
                    store:          new dojo.data.ItemFileWriteStore({data: {
                        identifier: 'id',
        			    label:      'name',
        			    items:      phpr.clone(range)}}),
                    searchAttr:     'name',
                    value:          defaultValue,
                    invalidMessage: ''
                });
                break;
            case 'percentage':
                formLabel = params['formLabel'] || 'Percentage';
                labelFor  = 'percentage';
                var node  = document.createElement('div');

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
                var inputTxt = new phpr.Form.HorizontalSlider({
                    maximum:             100,
                    minimum:             0,
                    discreteValues:      1001,
                    pageIncrement:       100,
                    showButtons:         false,
                    intermediateChanges: true,
                    style:               'height: 20px;'
                }, node);

                inputTxt.startup();
                sliderLabels.startup();
                sliderTopRule.startup();
                sliderBottomRule.startup();
                break;
            case 'rating':
                var numStars = params['formRange'] || 10;
                formLabel    = params['formLabel'] || 'Rating';
                labelFor     = 'rating';
                inputTxt     = new phpr.Form.Rating({name: 'rating', numStars: numStars, value: 1});
                break;
            case 'textarea':
                formLabel = params['formLabel'] || 'Textarea';
                labelFor  = 'textarea';
                inputTxt  = new dijit.form.Textarea({name: 'textarea'});
                break;
            case 'upload':
                formLabel = params['formLabel'] || 'Upload';
                labelFor  = 'upload';
                inputTxt  = new dijit.form.TextBox({type: 'file', baseClass: 'file'});
                break;
        }

        var table = dojo.doc.createElement('table');
        table.className = 'form';
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
    	table.appendChild(colgroup);

        // Create row
        var row = table.insertRow(table.rows.length);

        // Label
        var cell       = row.insertCell(0);
        cell.className = 'label';
        var label = document.createElement('label');
        label.setAttribute('for', labelFor);
        var txt = document.createTextNode(phpr.nls.get(formLabel, dijit.byId('name-Administration-Module').value));
        label.appendChild(txt);
        cell.appendChild(label);

        // Field
        var cell   = row.insertCell(1);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'internalType', value: labelFor});
        cell.appendChild(hidden.domNode);
        if (formType == 'datetime') {
            cell.appendChild(inputTxt);
        } else {
            cell.appendChild(inputTxt.domNode);
        }
        // Hiden fields
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'selectType', baseClass: 'hiddenValue',
            value: selectType});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'tableType', baseClass: 'hiddenValue',
            value: tableType});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'tableLength', baseClass: 'hiddenValue',
            value: tableLength});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'tableField', baseClass: 'hiddenValue',
             value: tableField});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'formLabel', baseClass: 'hiddenValue',
            value: formLabel});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'formType', baseClass: 'hiddenValue',
            value: formType});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'formRange', baseClass: 'hiddenValue',
            value: formRange});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'defaultValue', baseClass: 'hiddenValue',
            value: defaultValue});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'listPosition', baseClass: 'hiddenValue',
            value: listPosition});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'status', baseClass: 'hiddenValue',
            value: status});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'isRequired', baseClass: 'hiddenValue',
            value: isRequired});
        cell.appendChild(hidden.domNode);
        var hidden = new dijit.form.TextBox({type: 'hidden', name: 'id', baseClass: 'hiddenValue',
            value: id});
        cell.appendChild(hidden.domNode);

        // Buttons
        var cell = row.insertCell(2);
        if (target == 'source') {
            var display = 'none';
        } else {
            var display = 'inline';
        }
        cell.style.display = display;

        var editButton = new dijit.form.Button({
            iconClass: 'edit',
            type:      'button',
            baseClass: 'positive smallIcon',
            style:     'margin-bottom: 5px;',
            onClick:   function() {
                phpr.ModuleDesigner.editField(this.domNode.parentNode.parentNode.parentNode.parentNode.parentNode.id);
            }
        });
        var separator = document.createTextNode('      ');
        var deleteButton = new dijit.form.Button({
            iconClass: 'cross',
            type:      'button',
            baseClass: 'positive smallIcon',
            style:     'margin-bottom: 5px;',
            onClick:   function() {
                phpr.ModuleDesigner.deleteField(this.domNode.parentNode.parentNode.parentNode.parentNode.parentNode.id);
            }
        });
        cell.appendChild(editButton.domNode);
        cell.appendChild(separator);
        cell.appendChild(deleteButton.domNode);

        return table;
    },

    _getRangeAndDefaultValue:function(selectType, formRange) {
        if (selectType == 'project') {
            var range = [
                {id: 1, name: phpr.nls.get('Example Project 1')},
                {id: 2, name: phpr.nls.get('Example Project 2')}
            ];
            var defaultValue = 1;
        } else if (selectType == 'user') {
            var range = [
                {id: 1, name: phpr.nls.get('Example User 1')},
                {id: 2, name: phpr.nls.get('Example User 2')}
            ];
            var defaultValue = 1;
        } else if (selectType == 'contact') {
            var range = [
                {id: 1, name: phpr.nls.get('Example Contact 1')},
                {id: 2, name: phpr.nls.get('Example Contact 2')}
            ];
            var defaultValue = 1;
        } else {
            if (!formRange) {
                formRange = 'id1 # value1 | id2 # value2';
            }
            var defaultValue = null;
            var range        = []
            var options      = formRange.split('|');
            if (options.length > 1) {
                for (var i in options) {
                    var values = options[i].split('#');
                    if (values[0] && values[1]) {
                        if (!defaultValue) {
                            defaultValue = values[0];
                        }
                        range.push({
                            'id':   values[0],
                            'name': phpr.nls.get(values[1], dijit.byId('name-Administration-Module').value)
                        });
                    }
                }
            } else {
                var values = options[0].split('#');
                if (values[1] && values[2]) {
                    for (var k = 1; k < 3 ; k++) {
                        if (!defaultValue) {
                            defaultValue = values[1];
                        }
                        range.push({
                            'id':   values[1],
                            'name': values[2] + k
                        });
                    }
                }
            }
        }

        return {
            range:        range,
            defaultValue: defaultValue
        }
    },

    _createTableTab:function(tableField, formType, tableType, tableLength, selectType, id, nodeId) {
        // Summary:
        //    Create the table tab in the first call, in the second just update the values.
        var tabId = 'table';
        if (!this._fieldTemplate.existsTable(tabId)) {
            this._fieldTemplate.createTable(tabId);
        }

        // tableField
        var fieldValues = {
            type:     'text',
            id:       'tableField',
            label:    phpr.nls.get('Field name'),
            disabled: false,
            required: true,
            value:    tableField,
            tab:      tabId,
            hint:     '',
            length:   50
        };
        this._fieldTemplate.addRow(fieldValues);

        // tableType
        var range = [];
        switch (formType) {
            case 'text':
            case 'selectValues':
                range.push({id: 'varchar', name: 'VARCHAR'});
                range.push({id: 'int',     name: 'INT'});
                break;
            case 'checkbox':
                range.push({id: 'int', name: 'INT'});
                tableLength = 1;
                break;
            case 'date':
                range.push({id: 'date', name: 'DATE'});
                tableType = 'date';
                break;
            case 'time':
                range.push({id: 'time', name: 'TIME'});
                tableType = 'time';
                break;
            case 'datetime':
                range.push({id: 'datetime', name: 'DATETIME'});
                tableType = 'datetime';
                break;
            case 'percentage':
                range.push({id: 'varchar', name: 'VARCHAR'});
                tableType = 'varchar';
                break;
            case 'rating':
                range.push({id: 'int', name: 'INT'});
                tableType = 'int';
                break;
            case 'textarea':
                range.push({id: 'text', name: 'TEXT'});
                tableType = 'text';
                break;
            case 'upload':
                range.push({id: 'text', name: 'TEXT'});
                tableType = 'text';
                break;
        }

        // tableType
        var fieldValues = {
            type:     'selectbox',
            id:       'tableType',
            label:    phpr.nls.get('Field type'),
            disabled: false,
            required: true,
            value:    tableType,
            range:    range,
            tab:      tabId,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // tableLength
        if (formType == 'selectValues' || formType == 'checkbox' || formType == 'text') {
            var tableLengthDisable = false;
        } else {
            var tableLengthDisable = true;
            tableLength            = null;
        }
        var fieldValues = {
            type:     'text',
            id:       'tableLength',
            label:    phpr.nls.get('Field lenght'),
            disabled: tableLengthDisable,
            required: true,
            value:    tableLength,
            tab:      tabId,
            hint:     '',
            length:   3
        };
        this._fieldTemplate.addRow(fieldValues);

        // selectType
        if (formType == 'selectValues') {
            var selectTypeDisable = false;
        } else {
            var selectTypeDisable = true;
            selectType            = '';
        }
        var range = [];
        range.push({id: '',        name: ''});
        range.push({id: 'project', name: phpr.nls.get('Projects')});
        range.push({id: 'user',    name: phpr.nls.get('Users')});
        range.push({id: 'contact', name: phpr.nls.get('Contacts')});
        range.push({id: 'custom',  name: phpr.nls.get('Custom Values')});
        var fieldValues = {
            type:     'selectbox',
            id:       'selectType',
            label:    phpr.nls.get('Select Type'),
            disabled: selectTypeDisable,
            required: true,
            value:    selectType,
            range:    range,
            tab:      tabId,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // hidden id
        var fieldValues = {
            type:     'hidden',
            id:       'id',
            disabled: false,
            required: true,
            value:    id,
            tab:      tabId,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // hidden nodeId
        var fieldValues = {
            type:     'hidden',
            id:       'nodeId',
            disabled: false,
            required: true,
            value:    nodeId,
            tab:      tabId,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // hidden formType
        var fieldValues = {
            type:     'hidden',
            id:       'formType',
            disabled: false,
            required: true,
            value:    formType,
            tab:      tabId,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        return this._addButtons(tabId, 'formTable');
    },

    _addButtons:function(tabId, formName) {
        if (!dojo.byId('buttons-ModuleDesigner-' + formName)) {
            var node = new dijit.layout.ContentPane({
                id: 'buttons-ModuleDesigner-' + formName
            }, document.createElement('div'));

            var saveButton = new dijit.form.Button({
                id:        'submitButton-ModuleDesigner-' + formName,
                label:     '',
                iconClass: 'tick',
                type:      'button',
                disabled:  false
            });
            var deleteButton = new dijit.form.Button({
                id:        'deleteButton-ModuleDesigner-' + formName,
                label:     '',
                iconClass: 'cancel',
                type:      'button',
                disabled:  false
            });

            var container = document.createElement('div');
            container.appendChild(saveButton.domNode);
            container.appendChild(document.createTextNode('    '));
            container.appendChild(deleteButton.domNode);
            node.set('content', container);

            var table = this._fieldTemplate.getTable(tabId);
            var row   = table.insertRow(table.rows.length);
            var cell  = row.insertCell(0);
            var cell  = row.insertCell(1);
            cell.appendChild(node.domNode);
            var cell  = row.insertCell(2);
        }

        return table;
    },

    _createFormTab:function(formLabel, formType, formRange, defaultValue) {
        // Summary:
        //    Create the form tab in the first call, in the second just update the values.
        var tabId = 'form';
        if (!this._fieldTemplate.existsTable(tabId)) {
            this._fieldTemplate.createTable(tabId);
        }

        // formLabel
        var fieldValues = {
            type:     'text',
            id:       'formLabel',
            label:    phpr.nls.get('Label'),
            disabled: false,
            required: true,
            value:    formLabel,
            tab:      tabId,
            hint:     '',
            length:   255
        };
        this._fieldTemplate.addRow(fieldValues);

        // formRange
        var hint = '';
        if (formType == 'selectValues' || formType == 'rating') {
            var formRangeDisable = false;
            if (formType == 'selectValues') {
                if (!formRange) {
                    formRange = 'id1 # value1 | id2 # value2';
                }
                hint = phpr.nls.get('Each option have the key, and the value to display, separated by #.')
                    + '<br />'
                    + phpr.nls.get('Separate the diferent options with \'|\'.')
                    + '<br />'
                    + phpr.nls.get('For Modules queries, use Module#keyField#displayField.')
                    + '<br />'
                    + phpr.nls.get('The API will get all the keyField of the module and will use the displayField for '
                    + 'show it.')
            } else {
                if (!formRange) {
                    formRange = '10';
                }
                hint = phpr.nls.get('Number of stars');
            }
        } else {
            var formRangeDisable = true;
            formRange            = '';
        }
        var fieldValues = {
            type:     'simpletextarea',
            id:       'formRange',
            label:    phpr.nls.get('Values'),
            disabled: formRangeDisable,
            required: true,
            value:    formRange,
            tab:      tabId,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);
        if (dojo.byId('row_formRange-ModuleDesigner')) {
            if (formRangeDisable) {
                // Hide the tooltips
                if (dojo.byId('tooltip_formRangeSelect-ModuleDesigner')) {
                    dojo.byId('tooltip_formRangeSelect-ModuleDesigner').style.display = 'none';
                }
                if (dojo.byId('tooltip_formRangeRating-ModuleDesigner')) {
                    dojo.byId('tooltip_formRangeRating-ModuleDesigner').style.display = 'none';
                }
            } else {
                // Create special tooltip
                if (formType == 'selectValues') {
                    var tooltipId = 'tooltip_formRangeSelect-ModuleDesigner';
                } else {
                    var tooltipId = 'tooltip_formRangeRating-ModuleDesigner';
                }
                if (!dijit.byId(tooltipId)) {
                    var container              = document.createElement('div');
                    container.style.whiteSpace = 'nowrap';
                    container.innerHTML        = hint;

                    var dialog = new dijit.TooltipDialog({
                        content: container
                    });

                    var widget = new dijit.form.DropDownButton({
                        id:        tooltipId,
                        showLabel: false,
                        baseClass: 'smallIcon',
                        iconClass: 'help',
                        dropDown:  dialog
                    });
                    dojo.byId('row_formRange-ModuleDesigner').childNodes[2].appendChild(widget.domNode);
                }
                if (formType == 'selectValues') {
                    var selectDisplay = 'inline';
                    var ratingDisplay = 'none';
                } else {
                    var selectDisplay = 'none';
                    var ratingDisplay = 'inline';
                }
                if (dojo.byId('tooltip_formRangeSelect-ModuleDesigner')) {
                    dojo.byId('tooltip_formRangeSelect-ModuleDesigner').style.display = selectDisplay;
                }
                if (dojo.byId('tooltip_formRangeRating-ModuleDesigner')) {
                    dojo.byId('tooltip_formRangeRating-ModuleDesigner').style.display = ratingDisplay;
                }
            }
        }

        // defaultValue
        var fieldValues = {
            type:     'text',
            id:       'defaultValue',
            label:    phpr.nls.get('Default Value'),
            disabled: false,
            required: true,
            value:    defaultValue,
            tab:      tabId,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        return this._addButtons(tabId, 'formForm');
    },

    _createListTab:function(listPosition) {
        // Summary:
        //    Create the list tab in the first call, in the second just update the values.
        var tabId = 'list';
        if (!this._fieldTemplate.existsTable(tabId)) {
            this._fieldTemplate.createTable(tabId);
        }

        // listPosition
        var fieldValues = {
            type:     'text',
            id:       'listPosition',
            label:    phpr.nls.get('List Position'),
            disabled: false,
            required: true,
            value:    listPosition,
            tab:      tabId,
            hint:     phpr.nls.get('Defines the position of the field in the grid. Starts with 1 in the left. '
                + '0 for do not show it.'),
            length:   4
        };
        this._fieldTemplate.addRow(fieldValues);

        return this._addButtons(tabId, 'formList');
    },

    _createGeneralTab:function(status, isRequired) {
        // Summary:
        //    Create the general tab in the first call, in the second just update the values.
        var tabId = 'general';
        if (!this._fieldTemplate.existsTable(tabId)) {
            this._fieldTemplate.createTable(tabId);
        }

        // status
        var range = [];
        range.push({id: '0', name: phpr.nls.get('Inactive')});
        range.push({id: '1', name: phpr.nls.get('Active')});
        var fieldValues = {
            type:     'selectbox',
            id:       'status',
            label:    phpr.nls.get('Status'),
            disabled: false,
            required: true,
            value:    status,
            range:    range,
            tab:      tabId,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // isRequired
        var range = [];
        range.push({id: '0', name: phpr.nls.get('No')});
        range.push({id: '1', name: phpr.nls.get('Yes')});
        var fieldValues = {
            type:     'selectbox',
            id:       'isRequired',
            label:    phpr.nls.get('Required Field'),
            disabled: false,
            required: true,
            value:    isRequired,
            range:    range,
            tab:      tabId,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        return this._addButtons(tabId, 'formGeneral');
    }
});
