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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Module.Designer");

contentModuleDesignerSource = [];

dojo.require("dojo.dnd.Source");

dojo.declare("phpr.Module.Designer", dojo.dnd.AutoSource, {
    // Summary:
    //    Extend the dojo Source
    // Description:
    //    Extend the dojo Source
    tabId: "",
    onDrop: function(source, nodes, copy) {
        if (this != source) {
            this.onDropExternal(source, nodes, copy);
            var m = dojo.dnd.manager();
            phpr.makeModuleDesignerSource();
            if (this.node.id == m.target.node.id) {
                if (source.node.id == 'moduleDesignerSource') {
                    // Rewrite the sources for add the moved item

                    // Open the edit form
                    var t = this._normalizedCreator(nodes[0]);
                    var node = dojo.query('.editFields', t.node)[0];
                    dojo.style(node, 'display', 'inline');
                    phpr.editModuleDesignerField(t.node.id);
                } else {
                    phpr.moduleDesignerHideForm();
                }
            }
        } else {
            this.onDropInternal(nodes, copy);
        }
    },

    markupFactory: function(params, node) {
        params._skipStartup = true;
        var el = new phpr.Module.Designer(node, params);
        moduleDesignerElements[params.tabId] = el;
        return el;
    }
});

phpr.makeModuleDesignerSource = function() {
    // Summary:
    //    Draw the source fields
    // Description:
    //    Draw the source fields
    //    Cache the html result
    var element = dojo.byId('moduleDesignerSource');
    var html    = '<div style="text-align: center; padding: 5px 0 2px;">' +
        phpr.nls.get('Repository of available field types') +
        '<button dojoType="dijit.form.DropDownButton" showLabel="false" baseClass="smallIcon" iconClass="help" ' +
        'tabindex="-1">' +
        '<div dojoType="dijit.TooltipDialog">' +
        '<div style="white-space: nowrap;">' +
        phpr.nls.get('1. Drag a field into the right pane.') + '<br />' +
        phpr.nls.get('2. Edit the parameters of the field in the lower left pane.') +
        '</div>' +
        '</div>' +
        '</button>' +
        '</div>';
    var types = new Array('text', 'date', 'time', 'datetime', 'selectValues', 'checkbox',
                          'percentage', 'rating', 'textarea', 'upload');

    for (var i in types) {
        var id = dojo.dnd.getUniqueId();
        if (!contentModuleDesignerSource[types[i]]) {
            contentModuleDesignerSource[types[i]] = phpr.makeModuleDesignerField(types[i], 'source');
        }
        if (types[i] == 'upload') {
            contentModuleDesignerSource[types[i]] = phpr.makeModuleDesignerField(types[i], 'source');
        }
        html += '<div id="' + id + '" class="dojoDndItem" style="cursor: move;">';
        html += contentModuleDesignerSource[types[i]];
        html += '</div>';
    }

    element.innerHTML = html;
    dojo.parser.parse(element.id);
    moduleDesignerElements.moduleDesignerSource.sync();
};

phpr.makeModuleDesignerTarget = function(jsonData, tabs) {
    // Summary:
    //    Draw the target fields
    // Description:
    //    Draw the target fields in the correct tab
    if (jsonData) {
        var data = dojo.fromJson(jsonData);
        for (var j in tabs) {
            var tab = moduleDesignerElements['moduleDesignerTarget' + tabs[j].nameId];
            var element = dojo.byId('moduleDesignerTarget' + tabs[j].nameId);
            var html    = '<div style="text-align: center; padding-bottom: 2px;">' +
                phpr.nls.get('Active fields in the module') +
                '<button dojoType="dijit.form.DropDownButton" showLabel="false" baseClass="smallIcon" ' +
                'tabindex="-1" iconClass="help">' +
                '<div dojoType="dijit.TooltipDialog">' +
                '<div style="white-space: nowrap;">' +
                phpr.nls.get('Drop in this panel all the fields that you want to have in this tab.') + '<br />' +
                phpr.nls.get('For sort the fields, just drag and drop it in the correct position.') +
                '</div>' +
                '</div>' +
                '</button>' +
                '</div>';

            for (var i in data) {
                if (data[i].formTab == tabs[j].id) {
                    var id = dojo.dnd.getUniqueId();
                    html += '<div id="' + id + '" class="dojoDndItem" style="cursor: move;">';
                    html += phpr.makeModuleDesignerField(data[i].formType, 'target', data[i]);
                    html += '</div>';
                }
            }

            element.innerHTML = html;
            dojo.parser.parse(element.id);
            tab.sync();
        }
    }
};

phpr.deleteModuleDesignerField = function(nodeId) {
    // Summary:
    //    Delete a field
    // Description:
    //    Delete only the target fields.
    //    Hide the edit form
    var node  = dojo.byId(nodeId);
    var tabId = node.parentNode.id;

    // Delete only the target items
    if (tabId != 'moduleDesignerSource') {
        if (node) {
            var tab = moduleDesignerElements[tabId];
            // make sure it is not the anchor
            if (tab.anchor == node) {
                tab.anchor = null;
            }
            // remove it from the master map
            tab.delItem(name);
            // remove the node itself
            dojo._destroyElement(node);
        }
    }

    phpr.moduleDesignerHideForm();
};

var moduleDesignerTemplates = null;
var moduleDesignerFormGarbageCollector = null;

phpr.editModuleDesignerField = function(nodeId) {
    // Summary:
    //    Make the edit form
    // Description:
    //    Make the edit form and display it
    dojo.style(dojo.byId('moduleDesignerEditor'), "display", "none");
    var selectType   = '';
    var tableType    = '';
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

    var insertNodesAndWidgetsIntoNode = function(target, nodeList) {
        for (var i in nodeList) {
            var node = nodeList[i];
            if (!node.domNode) {
                dojo.parser.parse(node);
            }
            node = node.domNode || node;
            target.appendChild(node);
        }
    };

    var getTemplates = function() {
        if (moduleDesignerTemplates !== null && dojo.isFunction(moduleDesignerTemplates.destroy)) {
            moduleDesignerTemplates.destroy();
        }

        moduleDesignerTemplates = new phpr.Default.Field();

        return moduleDesignerTemplates;
    };

    var getCollector = function() {
        if (moduleDesignerFormGarbageCollector === null) {
            moduleDesignerFormGarbageCollector = new phpr.Default.System.GarbageCollector();
        }

        return moduleDesignerFormGarbageCollector;
    };

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

    phpr.destroyWidget('moduleDesignerSubmitButtonTable');
    phpr.destroyWidget('moduleDesignerSubmitButtonForm');
    phpr.destroyWidget('moduleDesignerSubmitButtonList');
    phpr.destroyWidget('moduleDesignerSubmitButtonGeneral');

    var fieldsTable = [];
    var fieldsForm = [];
    var fieldsList = [];
    var fieldsGeneral = [];
    var template = getTemplates();
    var collector = getCollector();
    collector.collect();

    // Table
    fieldsTable.push(template.textFieldRender(phpr.nls.get('Field name'), 'tableField', tableField, 50, true, false));

    var tableTypeRange = [];
    switch (formType) {
        case 'text':
        case 'selectValues':
            tableTypeRange.push({'id': 'varchar', 'name': 'VARCHAR'});
            tableTypeRange.push({'id': 'int', 'name': 'INT'});
            fieldsTable.push(template.selectRender(tableTypeRange, phpr.nls.get('Field type'), 'tableType',
                tableType, true, false));
            fieldsTable.push(template.textFieldRender(phpr.nls.get('Field length'), 'tableLength',
                tableLength, 3, true, false));
            break;
        case 'checkbox':
            tableTypeRange.push({'id': 'int', 'name': 'INT'});
            fieldsTable.push(template.selectRender(tableTypeRange, phpr.nls.get('Field type'), 'tableType',
                tableType, true, false));
            fieldsTable.push(template.textFieldRender(phpr.nls.get('Field length'), 'tableLength', 1, 1,
                true, false));
            break;
        case 'date':
            tableTypeRange.push({'id': 'date', 'name': 'DATE'});
            fieldsTable.push(template.selectRender(tableTypeRange, phpr.nls.get('Field type'), 'tableType',
                'date', true, false));
            break;
        case 'time':
            tableTypeRange.push({'id': 'time', 'name': 'TIME'});
            fieldsTable.push(template.selectRender(tableTypeRange, phpr.nls.get('Field type'), 'tableType',
                'time', true, false));
            break;
        case 'datetime':
            tableTypeRange.push({'id': 'datetime', 'name': 'DATETIME'});
            fieldsTable.push(template.selectRender(tableTypeRange, phpr.nls.get('Field type'), 'tableType',
                'datetime', true, false));
            break;
        case 'percentage':
            tableTypeRange.push({'id': 'varchar', 'name': 'VARCHAR'});
            fieldsTable.push(template.selectRender(tableTypeRange, phpr.nls.get('Field type'), 'tableType',
                'varchar', true, false));
            break;
        case 'rating':
            tableTypeRange.push({'id': 'int', 'name': 'INT'});
            fieldsTable.push(template.selectRender(tableTypeRange, phpr.nls.get('Field type'), 'tableType',
                'int', true, false));
            break;
        case 'textarea':
            tableTypeRange.push({'id': 'text', 'name': 'TEXT'});
            fieldsTable.push(template.selectRender(tableTypeRange, phpr.nls.get('Field type'), 'tableType',
                'text', true, false));
            break;
        case 'upload':
            tableTypeRange.push({'id': 'text', 'name': 'TEXT'});
            fieldsTable.push(template.selectRender(tableTypeRange, phpr.nls.get('Field type'), 'tableType',
                'text', true, false));
            break;
    }

    if (formType === 'selectValues') {
        var selectTypeRange = [];
        selectTypeRange.push({'id': 'project', 'name': phpr.nls.get('Projects')});
        selectTypeRange.push({'id': 'user', 'name': phpr.nls.get('Users')});
        selectTypeRange.push({'id': 'contact', 'name': phpr.nls.get('Contacts')});
        selectTypeRange.push({'id': 'custom', 'name': phpr.nls.get('Custom Values')});
        fieldsTable.push(template.selectRender(selectTypeRange, phpr.nls.get('Select Type'), 'selectType',
                    selectType, true, false));
    }

    fieldsTable.push(
        template.hiddenFieldRender('', 'id', id)
    );

    fieldsTable.push(
        dojo.create('tr', {
            innerHTML: '<td class="label">' +
                        '<label for="moduleDesignerSubmitButtonTable">&nbsp;</label>' +
                        '</td><td>' +
                        '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonTable" baseClass="positive"' +
                        ' type="button" iconClass="tick"></button>' +
                        '&nbsp;' +
                        '<button dojoType="dijit.form.Button" id="moduleDesignerCancelButtonTable" baseClass="positive"' +
                        ' type="button" iconClass="cancel"></button>' +
                        '</td>'
        })
    );

    // Form
    fieldsForm.push(template.textFieldRender(phpr.nls.get('Label'), 'formLabel', formLabel, 255, true, false));

    switch (formType) {
        case 'selectValues':
            if (!formRange) {
                formRange = 'id1 # value1 | id2 # value2';
            }
            fieldsForm.push(template.textAreaRender(phpr.nls.get('Values'), 'formRange', formRange, true, false,
                phpr.nls.get('Each option have the key, and the value to display, separated by #.') + '<br />' +
                phpr.nls.get('Separate the diferent options with \'|\'.') + '<br />' +
                phpr.nls.get('For Modules queries, use Module#keyField#displayField.') + '<br />' +
                phpr.nls.get('The API will get all the keyField of the module and will use the displayField for ' +
                    'show it.')));
            break;
        case 'rating':
            if (!formRange) {
                formRange = '10';
            }
            fieldsForm.push(template.textAreaRender(phpr.nls.get('Values'), 'formRange', formRange, true, false,
                phpr.nls.get('Number of stars')));
            break;
        default:
            if (formRange) {
                fieldsForm.push(template.hiddenFieldRender('', 'formRange', formRange, true, false));
            }
    }
    fieldsForm.push(template.textFieldRender(phpr.nls.get('Default Value'), 'defaultValue', defaultValue, 0, false,
        false));

    fieldsForm.push(
        dojo.create('tr', {
            innerHTML: '<td class="label">' +
                        '<label for="moduleDesignerSubmitButtonForm">&nbsp;</label>' +
                        '</td><td>' +
                        '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonForm" baseClass="positive"' +
                        ' type="button" iconClass="tick"></button>' +
                        '&nbsp;' +
                        '<button dojoType="dijit.form.Button" id="moduleDesignerCancelButtonForm" baseClass="positive"' +
                        ' type="button" iconClass="cancel"></button>' +
                        '</td>'
        })
    );

    // List
    fieldsList.push(template.textFieldRender(phpr.nls.get('List Position'), 'listPosition', listPosition, 4, true, false,
        phpr.nls.get('Defines the position of the field in the grid. Starts with 1 in the left. ' +
            '0 for do not show it.')));

    fieldsList.push(
        dojo.create('tr', {
            innerHTML: '<td class="label">' +
                        '<label for="moduleDesignerSubmitButtonList">&nbsp;</label>' +
                        '</td><td>' +
                        '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonList" baseClass="positive"' +
                        ' type="button" iconClass="tick"></button>' +
                        '&nbsp;' +
                        '<button dojoType="dijit.form.Button" id="moduleDesignerCancelButtonList" baseClass="positive"' +
                        ' type="button" iconClass="cancel"></button>' +
                        '</td>'
        })
    );

    // General
    var statusRange = [];
    statusRange.push({'id': '0', 'name': phpr.nls.get('Inactive')});
    statusRange.push({'id': '1', 'name': phpr.nls.get('Active')});
    fieldsGeneral.push(template.selectRender(statusRange, phpr.nls.get('Status'), 'status', status, true, false));

    var isRequiredRange = [];
    isRequiredRange.push({'id': '0', 'name': phpr.nls.get('No')});
    isRequiredRange.push({'id': '1', 'name': phpr.nls.get('Yes')});
    fieldsGeneral.push(template.selectRender(isRequiredRange, phpr.nls.get('Required Field'), 'isRequired', isRequired,
        true, false));

    fieldsGeneral.push(
        dojo.create('tr', {
            innerHTML: '<td class="label">' +
                        '<label for="moduleDesignerSubmitButtonGeneral">&nbsp;</label>' +
                        '</td><td>' +
                        '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonGeneral"' +
                        ' baseClass="positive" type="button" iconClass="tick"></button>' +
                        '&nbsp;' +
                        '<button dojoType="dijit.form.Button" id="moduleDesignerCancelButtonGeneral" baseClass="positive"' +
                        ' type="button" iconClass="cancel"></button>' +
                        '</td>'
        })
    );

    var formId = 'formTable' + '_' + nodeId;
    var widget = new phpr.Default.System.TemplateWrapper({
        templateName: "phpr.Default.template.form.tabs.html",
        templateData: {
            formId: formId
        }
    });

    insertNodesAndWidgetsIntoNode(widget.formtable, fieldsTable);
    dijit.byId('moduleDesignerEditorTable').set('content', widget);
    collector.addNode(widget);

    var formId = 'formForm' + '_' + nodeId;
    var widget = new phpr.Default.System.TemplateWrapper({
        templateName: "phpr.Default.template.form.tabs.html",
        templateData: {
            formId: formId
        }
    });

    insertNodesAndWidgetsIntoNode(widget.formtable, fieldsForm);
    dijit.byId('moduleDesignerEditorForm').set('content', widget);
    collector.addNode(widget);

    var formId = 'formList' + '_' + nodeId;
    var widget = new phpr.Default.System.TemplateWrapper({
        templateName: "phpr.Default.template.form.tabs.html",
        templateData: {
            formId: formId
        }
    });

    insertNodesAndWidgetsIntoNode(widget.formtable, fieldsList);
    dijit.byId('moduleDesignerEditorList').set('content', widget);
    collector.addNode(widget);

    var formId = 'formGeneral' + '_' + nodeId;
    var widget = new phpr.Default.System.TemplateWrapper({
        templateName: "phpr.Default.template.form.tabs.html",
        templateData: {
            formId: formId
        }
    });

    insertNodesAndWidgetsIntoNode(widget.formtable, fieldsGeneral);
    dijit.byId('moduleDesignerEditorGeneral').set('content', widget);
    collector.addNode(widget);

    // Change the Range
    dojo.connect(dijit.byId("selectType"), "onChange", function() {
        switch (dijit.byId("selectType").get('value')) {
            case 'custom':
            /* falls through */
            default:
                dijit.byId("formRange").set('value', 'id1 # value1 | id2 # value2');
                dijit.byId("tableType").set('value', 'int');
                dijit.byId("tableLength").set('value', 11);
                break;
            case 'project':
                dijit.byId("formRange").set('value', 'Project # id # title');
                dijit.byId("tableType").set('value', 'int');
                dijit.byId("tableLength").set('value', 11);
                break;
            case 'user':
                dijit.byId("formRange").set('value', 'User # id # lastname');
                dijit.byId("tableType").set('value', 'int');
                dijit.byId("tableLength").set('value', 11);
                break;
            case 'contact':
                dijit.byId("formRange").set('value', 'Contact # id # name');
                dijit.byId("tableType").set('value', 'int');
                dijit.byId("tableLength").set('value', 11);
                break;
        }
    });

    // Save
    collector.addEvent(
        dojo.connect(dijit.byId('moduleDesignerSubmitButtonTable'), "onClick", function() {
            phpr.saveModuleDesignerField(nodeId, formType);
        }));
    collector.addEvent(
        dojo.connect(dijit.byId('moduleDesignerSubmitButtonForm'), "onClick", function() {
            phpr.saveModuleDesignerField(nodeId, formType);
        }));
    collector.addEvent(
        dojo.connect(dijit.byId('moduleDesignerSubmitButtonList'), "onClick", function() {
            phpr.saveModuleDesignerField(nodeId, formType);
        }));
    collector.addEvent(
        dojo.connect(dijit.byId('moduleDesignerSubmitButtonGeneral'), "onClick", function() {
            phpr.saveModuleDesignerField(nodeId, formType);
        }));

    // Cancel
    collector.addEvent(
        dojo.connect(dijit.byId('moduleDesignerCancelButtonTable'), "onClick", function() {
            phpr.moduleDesignerHideForm();
        }));
    collector.addEvent(
        dojo.connect(dijit.byId('moduleDesignerCancelButtonForm'), "onClick", function() {
            phpr.moduleDesignerHideForm();
        }));
    collector.addEvent(
        dojo.connect(dijit.byId('moduleDesignerCancelButtonList'), "onClick", function() {
            phpr.moduleDesignerHideForm();
        }));
    collector.addEvent(
        dojo.connect(dijit.byId('moduleDesignerCancelButtonGeneral'), "onClick", function() {
            phpr.moduleDesignerHideForm();
        }));

    phpr.moduleDesignerShowForm();
};

phpr.saveModuleDesignerField = function(nodeId, formType) {
    // Summary:
    //    Mix the form data and make a new field with the data
    // Description:
    //    Mix the form data and make a new field with the data
    var params = {};

    params = dojo.mixin(params, dijit.byId('formTable' + '_' + nodeId).get('value'));
    params = dojo.mixin(params, dijit.byId('formForm' + '_' + nodeId).get('value'));
    params = dojo.mixin(params, dijit.byId('formList' + '_' + nodeId).get('value'));
    params = dojo.mixin(params, dijit.byId('formGeneral' + '_' + nodeId).get('value'));

    phpr.moduleDesignerHideForm();

    dojo.byId(nodeId).innerHTML = phpr.makeModuleDesignerField(formType, 'target', params);
    dojo.parser.parse(nodeId);
};

phpr.moduleDesignerShowForm = function() {
    dojo.style(dojo.byId('moduleDesignerEditor'), "display", "block");
    dojo.style(dojo.byId('moduleDesignerSaveButton'), "display", "none");
    var node = dijit.byId('moduleDesignerEditor');
    node.selectChild(dijit.byId("moduleDesignerEditorTable"));
    dijit.byId("moduleDesignerEditorTable").resize();
};

phpr.moduleDesignerHideForm = function() {
    dojo.style(dojo.byId('moduleDesignerEditor'), "display", "none");
    dojo.style(dojo.byId('moduleDesignerSaveButton'), "display", "block");
};

phpr.makeModuleDesignerField = function(formType, target, params) {
    // Summary:
    //    Draw a field using the params and the formType
    // Description:
    //    Draw a field using the params and the formType
    var html       = '';
    var formLabel  = null;
    var labelFor   = null;
    var tableField = null;
    var required   = null;
    var labelTxt   = null;
    var inputTxt   = null;
    if (!params) {
        params = [];
    }

    var formLabel  = '';
    var selectType = params.selectType || 'custom';
    var tableType  = params.tableType || 'varchar';
    var tableLength;
    if (tableType == 'int') {
        tableLength = params.tableLength || 11;
    } else {
        tableLength = params.tableLength || 255;
    }
    var tableField   = params.tableField || '';
    var formRange    = params.formRange || '';
    var defaultValue = params.defaultValue || '';
    var listPosition = parseInt(params.listPosition, 10);
    if  (isNaN(listPosition)) {
        listPosition = 0;
    }
    var status = parseInt(params.status, 10);
    if  (isNaN(status)) {
        status = 1;
    }
    var isRequired = params.isRequired || 0;
    var id         = params.id || 0;

    if (formType == 'selectValues') {
        var options = formRange.split("|");
        for (var i in options) {
            var values = options[i].split("#");
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
        /* falls through */
        default:
            formLabel = params.formLabel || 'Text';
            labelFor = 'text';
            inputTxt = '<input type="text" dojoType="dijit.form.TextBox" ucfirst="true" />';
            break;
        case 'checkbox':
            formLabel = params.formLabel || 'Checkbox';
            labelFor = 'checkbox';
            inputTxt = '<input type="checkbox" dojotype="phpr.Default.System.Form.CheckBox" value="1" />';
            break;
        case 'date':
            formLabel = params.formLabel || 'Date';
            labelFor = 'date';
            inputTxt = '<input type="text" dojoType="phpr.DateTextBox" constraints="{datePattern:\'yyyy-MM-dd\'}"' +
                ' promptMessage="dd.mm.yy" />';
            break;
        case 'time':
            formLabel = params.formLabel || 'Time';
            labelFor = 'time';
            inputTxt = '<input type="text" dojoType="dijit.form.TimeTextBox"' +
                ' constraints="{formatLength:\'short\', timePattern:\'HH:mm\'}" />';
            break;
        case 'datetime':
            formLabel = params.formLabel || 'Datetime';
            labelFor = 'datetime';
            inputTxt = '<div class="twoFields">';
            inputTxt += '<input type="text" dojoType="phpr.DateTextBox" constraints="{datePattern:\'yyyy-MM-dd\'}"' +
                ' promptMessage="dd.mm.yy" />';
            inputTxt += '<input type="text" dojoType="dijit.form.TimeTextBox"' +
                ' constraints="{formatLength:\'short\', timePattern:\'HH:mm\'}" />';
            inputTxt += '</div>';
            break;
        case 'selectValues':
            formLabel = params.formLabel || 'Select';
            labelFor  = 'select';
            inputTxt  = '<select dojoType="phpr.FilteringSelect" autocomplete="false" invalidMessage="" >';

            if (selectType == 'project') {
                inputTxt += '<option value="1">' + phpr.nls.get('Example Project 1') + '</option>';
                inputTxt += '<option value="2">' + phpr.nls.get('Example Project 2') + '</option>';
            } else if (selectType == 'user') {
                inputTxt += '<option value="1">' + phpr.nls.get('Example User 1') + '</option>';
                inputTxt += '<option value="2">' + phpr.nls.get('Example User 2') + '</option>';
            } else if (selectType == 'contact') {
                inputTxt += '<option value="1">' + phpr.nls.get('Example Contact 1') + '</option>';
                inputTxt += '<option value="2">' + phpr.nls.get('Example Contact 2') + '</option>';
            } else {
                if (!formRange) {
                    formRange = 'id1 # value1 | id2 # value2';
                }
                var formRangeOptions = [];
                var options          = formRange.split("|");
                if (options.length > 1) {
                    for (var i in options) {
                        var values = options[i].split("#");
                        if (values[0] && values[1]) {
                            formRangeOptions.push({
                                'id':   values[0],
                                'name': phpr.nls.get(values[1], dijit.byId('name').value)
                            });
                        }
                    }
                } else {
                    var values = options[0].split("#");
                    if (values[1] && values[2]) {
                        for (var k = 1; k < 3 ; k++) {
                            formRangeOptions.push({
                                'id':   values[1],
                                'name': values[2] + k
                            });
                        }
                    }
                }
                for (var i in formRangeOptions) {
                    inputTxt += '<option value="' + formRangeOptions[i].id + '">' + formRangeOptions[i].name +
                        '</option>';
                }
            }
            inputTxt += '</select>';
            break;
        case 'percentage':
            formLabel = params.formLabel || 'Percentage';
            labelFor = 'percentage';
            inputTxt = '<div dojoType="phpr.Default.System.Form.HorizontalSlider" maximum="100" minimum="0"';
            inputTxt += ' pageIncrement="100" showButtons="false" intermediateChanges="true" style="height: 20px;">';
            inputTxt += '<ol dojoType="dijit.form.HorizontalRuleLabels" container="topDecoration"';
            inputTxt += ' style="height:1.2em;font-size:75%;color:gray;" count="5" numericMargin="1"></ol>';
            inputTxt += '<div dojoType="dijit.form.HorizontalRule" container="topDecoration"';
            inputTxt += ' count=5 style="height:5px;"></div>';
            inputTxt += '<div dojoType="dijit.form.HorizontalRule" container="bottomDecoration"';
            inputTxt += ' count="11" style="height:5px;"></div>';
            inputTxt += '</div>';
            break;
        case 'rating':
            var numStars = params.formRange || 10;
            formLabel = params.formLabel || 'Rating';
            labelFor = 'rating';
            inputTxt = '<div name="rating" dojoType="phpr.Default.System.Form.Rating" numStars="' + numStars + '"></div>';
            break;
        case 'textarea':
            formLabel = params.formLabel || 'Textarea';
            labelFor = 'textarea';
            inputTxt = '<textarea dojoType="dijit.form.Textarea">\n\n</textarea>';
            break;
        case 'upload':
            var widgetId = dojo.dnd.getUniqueId();
            formLabel = params.formLabel || 'Upload';
            labelFor = 'upload';
            inputTxt = '<input type="file" class="file" />';
            break;
    }
    labelTxt = '<label for="' + labelFor + '">' + phpr.nls.get(formLabel, dijit.byId('name').value) + '</label>';

    html += '<table class="form"><col class="col1" /><col class="col2" /><col class="col3" />';
    html += '<tr><td class="label">';
    html += labelTxt;
    html += '</td><td>';
    html += inputTxt;

    html += '<input type="hidden" name="selectType" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        selectType + '" />';
    html += '<input type="hidden" name="tableType" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        tableType + '" />';
    html += '<input type="hidden" name="tableLength" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        tableLength + '" />';
    html += '<input type="hidden" name="tableField" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        tableField + '" />';

    html += '<input type="hidden" name="formLabel" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        formLabel + '" />';
    html += '<input type="hidden" name="formType" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        formType + '" />';
    html += '<input type="hidden" name="formRange" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        formRange + '" />';
    html += '<input type="hidden" name="defaultValue" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        defaultValue + '" />';

    html += '<input type="hidden" name="listPosition" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        listPosition + '" />';

    html += '<input type="hidden" name="status" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        status + '" />';
    html += '<input type="hidden" name="isRequired" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        isRequired + '" />';
    html += '<input type="hidden" name="id" class="hiddenValue" dojoType="dijit.form.TextBox" value="' +
        id + '" />';

    var display;
    if (target == 'source') {
        display = 'none';
    } else {
        display = 'inline';
    }
    html += '</td><td class="editFields" style="display: ' + display + '">';
    html += '<button dojoType="dijit.form.Button" baseClass="positive smallIcon" iconClass="edit"';
    html += ' onClick="' +
        'phpr.editModuleDesignerField(this.domNode.parentNode.parentNode.parentNode.parentNode.parentNode.id);"';
    html += ' margin-bottom: 5px;">';
    html += '</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    html += '<button dojoType="dijit.form.Button" baseClass="positive smallIcon" iconClass="cross"';
    html += ' onClick="' +
        'phpr.deleteModuleDesignerField(this.domNode.parentNode.parentNode.parentNode.parentNode.parentNode.id);"';
    html += ' margin-bottom: 5px;">';
    html += '</button>';
    html += '</td></tr></table>';

    return html;
};
