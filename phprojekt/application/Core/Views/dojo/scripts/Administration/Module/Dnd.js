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
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

contentModuleDesignerSource = new Array();

dojo.declare("phpr.Module.Designer", dojo.dnd.AutoSource, {
    // Summary:
    //    Extend the dojo Source
    // Description:
    //    Extend the dojo Source
    onDrop:function(source, nodes, copy) {
        if (this != source) {
            this.onDropExternal(source, nodes, copy);
            var m = dojo.dnd.manager();
            if (this.node.id == m.target.node.id) {
                if (source.node.id == 'moduleDesignerSource') {
                    // Rewrite the sources for add the moved item
                    phpr.makeModuleDesignerSource();

                    // Open the edit form
                    var t = this._normalizedCreator(nodes[0]);
                    phpr.editModuleDesignerField(t.node.id);
                }
            }
        } else {
            this.onDropInternal(nodes, copy);
        }
    },

    markupFactory:function(params, node) {
        params._skipStartup = true;
        return new phpr.Module.Designer(node, params);
    }
});

phpr.makeModuleDesignerSource = function() {
    // Summary:
    //    Draw the source fields
    // Description:
    //    Draw the source fields
    //    Cache the html result
    var element = dojo.byId('moduleDesignerSource');
    var html    = '';
    var types   = new Array('text', 'date', 'time', 'selectValues', 'checkbox',
                            'percentage', 'textarea', 'upload')

    for (i in types) {
        var id = dojo.dnd.getUniqueId();
        if (!contentModuleDesignerSource[types[i]]) {
            contentModuleDesignerSource[types[i]] = phpr.makeModuleDesignerField(types[i]);
        }
        if (types[i] == 'upload') {
            contentModuleDesignerSource[types[i]] = phpr.makeModuleDesignerField(types[i]);
        }
        html += '<div id="' + id + '" class="dojoDndItem" style="cursor: move;">';
        html += contentModuleDesignerSource[types[i]];
        html += '</div>';
    }

    element.innerHTML = html;
    dojo.parser.parse(element.id);
    moduleDesignerSource.sync();
};

phpr.makeModuleDesignerTarget = function(jsonData, tabs) {
    // Summary:
    //    Draw the target fields
    // Description:
    //    Draw the target fields in the correct tab
    if (jsonData) {
        var data = dojo.fromJson(jsonData);
        for (var j in tabs) {
            var tab     = eval("moduleDesignerTarget" + tabs[j]['nameId']);
            var element = dojo.byId('moduleDesignerTarget' + tabs[j]['nameId']);
            var html    = '';

            for (var i in data) {
                if (data[i]['formTab'] == tabs[j]['id']) {
                    var id = dojo.dnd.getUniqueId();
                    html += '<div id="' + id + '" class="dojoDndItem" style="cursor: move;">';
                    html += phpr.makeModuleDesignerField(data[i]['formType'], data[i]);
                    html += '</div>';
                }
            }

            element.innerHTML = html;
            dojo.parser.parse(element.id);
            tab.sync();
        }
    }
}

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
            var tab = eval(tabId);
            // make sure it is not the anchor
            if (tab.anchor == node){
                tab.anchor = null;
            }
            // remove it from the master map
            tab.delItem(name);
            // remove the node itself
            dojo._destroyElement(node);
        }
    }

    phpr.switchOkButton('save');
};

phpr.editModuleDesignerField = function(nodeId) {
    // Summary:
    //    Make the edit form
    // Description:
    //    Make the edit form and display it
    dojo.style(dojo.byId('moduleDesignerEditor'), "display", "none");
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

    phpr.destroyWidget('moduleDesignerSubmitButtonTable');
    phpr.destroyWidget('moduleDesignerSubmitButtonForm');
    phpr.destroyWidget('moduleDesignerSubmitButtonList');
    phpr.destroyWidget('moduleDesignerSubmitButtonGeneral');

    var fieldsTable   = '';
    var fieldsForm    = '';
    var fieldsList    = '';
    var fieldsGeneral = '';
    var template      = new phpr.Default.Field();
    var render        = new phpr.Component();

    // Table
    fieldsTable += template.textFieldRender(phpr.nls.get('Field Name'), 'tableField', tableField, 50, true, false);
    var tableTypeRange = new Array();
    switch (formType) {
        case 'text':
        case 'selectValues':
            tableTypeRange.push({'id': 'varchar', 'name': 'VARCHAR'});
            tableTypeRange.push({'id': 'int', 'name': 'INT'});
            fieldsTable += template.selectRender(tableTypeRange, phpr.nls.get('Field Type'), 'tableType', tableType,
                true, false);
            fieldsTable += template.textFieldRender(phpr.nls.get('Field Lenght'), 'tableLength', tableLength,
                3, true, false);
            break;
        case 'checkbox':
            tableTypeRange.push({'id': 'int', 'name': 'INT'});
            fieldsTable += template.selectRender(tableTypeRange, phpr.nls.get('Field Type'), 'tableType', tableType,
                true, false);
            fieldsTable += template.textFieldRender(phpr.nls.get('Field Lenght'), 'tableLength', 1, 1, true, false);
            break;
        case 'date':
            tableTypeRange.push({'id': 'date', 'name': 'DATE'});
            fieldsTable += template.selectRender(tableTypeRange, phpr.nls.get('Field Type'), 'tableType', 'date',
                true, false);
            break;
        case 'time':
            tableTypeRange.push({'id': 'time', 'name': 'TIME'});
            fieldsTable += template.selectRender(tableTypeRange, phpr.nls.get('Field Type'), 'tableType', 'time',
                true, false);
            break;
        case 'percentage':
            tableTypeRange.push({'id': 'varchar', 'name': 'VARCHAR'});
            fieldsTable += template.selectRender(tableTypeRange, phpr.nls.get('Field Type'), 'tableType', 'varchar',
                true, false);
            break;
        case 'textarea':
            tableTypeRange.push({'id': 'text', 'name': 'TEXT'});
            fieldsTable += template.selectRender(tableTypeRange, phpr.nls.get('Field Type'), 'tableType', 'text',
                true, false);
            break;
        case 'upload':
            tableTypeRange.push({'id': 'text', 'name': 'TEXT'});
            fieldsTable += template.selectRender(tableTypeRange, phpr.nls.get('Field Type'), 'tableType', 'text',
                true, false);
            break;
    }

    switch (formType) {
        case 'selectValues':
            var selectTypeRange = new Array();
            selectTypeRange.push({'id': 'project', 'name': phpr.nls.get('Projects')});
            selectTypeRange.push({'id': 'user', 'name': phpr.nls.get('Users')});
            selectTypeRange.push({'id': 'contact', 'name': phpr.nls.get('Contacts')});
            selectTypeRange.push({'id': 'custom', 'name': phpr.nls.get('Custom Values')});
            fieldsTable += template.selectRender(selectTypeRange, phpr.nls.get('Select Type'), 'selectType',
                selectType, true, false);
            break;
    }

    fieldsTable += '<input type="hidden" name="id" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + id + '" />';
    fieldsTable += '<tr><td class="label">';
    fieldsTable += '<label for="moduleDesignerSubmitButtonTable">&nbsp;</label>';
    fieldsTable += '</td><td>';
    fieldsTable += '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonTable" baseClass="positive"';
    fieldsTable += ' type="button" iconClass="tick"></button>';
    fieldsTable += '</td></tr>';

    // Form
    fieldsForm += template.textFieldRender(phpr.nls.get('Label'), 'formLabel', formLabel, 255, true, false);

    switch (formType) {
        case 'selectValues':
            if (!formRange) {
                formRange = 'id1 # value1 | id2 # value2';
            }
            fieldsForm += template.textAreaRender(phpr.nls.get('Range'), 'formRange', formRange, true, false);
            break;
    }
    fieldsForm += template.textFieldRender(phpr.nls.get('Default Value'), 'defaultValue', defaultValue, 0, true, false);

    fieldsForm += '<tr><td class="label">';
    fieldsForm += '<label for="moduleDesignerSubmitButtonForm">&nbsp;</label>';
    fieldsForm += '</td><td>';
    fieldsForm += '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonForm" baseClass="positive"';
    fieldsForm += ' type="button" iconClass="tick"></button>';
    fieldsForm += '</td></tr>';

    // List
    fieldsList += template.textFieldRender(phpr.nls.get('List Position'), 'listPosition', listPosition, 4, true, false);

    fieldsList += '<tr><td class="label">';
    fieldsList += '<label for="moduleDesignerSubmitButtonList">&nbsp;</label>';
    fieldsList += '</td><td>';
    fieldsList += '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonList" baseClass="positive"';
    fieldsList += ' type="button" iconClass="tick"></button>';
    fieldsList += '</td></tr>';

    // General
    var statusRange = new Array();
    statusRange.push({'id': '0', 'name': phpr.nls.get('Inactive')});
    statusRange.push({'id': '1', 'name': phpr.nls.get('Active')});
    fieldsGeneral += template.selectRender(statusRange, phpr.nls.get('Status'), 'status', status, true, false);

    var isRequiredRange = new Array();
    isRequiredRange.push({'id': '0', 'name': phpr.nls.get('No')});
    isRequiredRange.push({'id': '1', 'name': phpr.nls.get('Yes')});
    fieldsGeneral += template.selectRender(isRequiredRange, phpr.nls.get('Required Field'), 'isRequired', isRequired,
        true, false);

    fieldsGeneral += '<tr><td class="label">';
    fieldsGeneral += '<label for="moduleDesignerSubmitButtonGeneral">&nbsp;</label>';
    fieldsGeneral += '</td><td>';
    fieldsGeneral += '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonGeneral"';
    fieldsGeneral += ' baseClass="positive" type="button" iconClass="tick"></button>';
    fieldsGeneral += '</td></tr>';

    var formId = 'formTable' + '_' + nodeId;
    var html   = render.render(["phpr.Default.template", "tabs.html"], null, {
        innerTabs: fieldsTable,
        formId:    formId
    });
    dijit.byId('moduleDesignerEditorTable').attr('content', html);

    var formId = 'formForm' + '_' + nodeId;
    var html   = render.render(["phpr.Default.template", "tabs.html"], null, {
        innerTabs: fieldsForm,
        formId:    formId
    });
    dijit.byId('moduleDesignerEditorForm').attr('content', html);

    var formId = 'formList' + '_' + nodeId;
    var html   = render.render(["phpr.Default.template", "tabs.html"], null, {
        innerTabs: fieldsList,
        formId:    formId
    });
    dijit.byId('moduleDesignerEditorList').attr('content', html);

    var formId = 'formGeneral' + '_' + nodeId;
    var html   = render.render(["phpr.Default.template", "tabs.html"], null, {
        innerTabs: fieldsGeneral,
        formId:    formId
    });
    dijit.byId('moduleDesignerEditorGeneral').attr('content', html);

    // Change the Range
    dojo.connect(dijit.byId("selectType"), "onChange", function(){
        switch (dijit.byId("selectType").attr('value')) {
            case 'custom':
            default:
                dijit.byId("formRange").attr('value', 'id1 # value1 | id2 # value2');
                dijit.byId("tableType").attr('value', 'int');
                dijit.byId("tableLength").attr('value', 11);
                break;
            case 'project':
                dijit.byId("formRange").attr('value', 'Project # id # title');
                dijit.byId("tableType").attr('value', 'int');
                dijit.byId("tableLength").attr('value', 11);
                break;
            case 'user':
                dijit.byId("formRange").attr('value', 'User # id # lastname');
                dijit.byId("tableType").attr('value', 'int');
                dijit.byId("tableLength").attr('value', 11);
                break;
            case 'contact':
                dijit.byId("formRange").attr('value', 'Contact # id # name');
                dijit.byId("tableType").attr('value', 'int');
                dijit.byId("tableLength").attr('value', 11);
                break;
        }
    });

    dojo.connect(dijit.byId('moduleDesignerSubmitButtonTable'), "onClick", function() {
        phpr.saveModuleDesignerField(nodeId, formType);
    });
    dojo.connect(dijit.byId('moduleDesignerSubmitButtonForm'), "onClick", function() {
        phpr.saveModuleDesignerField(nodeId, formType);
    });
    dojo.connect(dijit.byId('moduleDesignerSubmitButtonList'), "onClick", function() {
        phpr.saveModuleDesignerField(nodeId, formType);
    });
    dojo.connect(dijit.byId('moduleDesignerSubmitButtonGeneral'), "onClick", function() {
        phpr.saveModuleDesignerField(nodeId, formType);
    });

    phpr.switchOkButton('editor');
};

phpr.saveModuleDesignerField = function(nodeId, formType) {
    // Summary:
    //    Mix the form data and make a new field with the data
    // Description:
    //    Mix the form data and make a new field with the data
    var params = new Array();
    params = dojo.mixin(params, dijit.byId('formTable' + '_' + nodeId).attr('value'));
    params = dojo.mixin(params, dijit.byId('formForm' + '_' + nodeId).attr('value'));
    params = dojo.mixin(params, dijit.byId('formList' + '_' + nodeId).attr('value'));
    params = dojo.mixin(params, dijit.byId('formGeneral' + '_' + nodeId).attr('value'));

    phpr.switchOkButton('save');

    dojo.byId(nodeId).innerHTML = phpr.makeModuleDesignerField(formType, params);
    dojo.parser.parse(nodeId);
};

phpr.switchOkButton = function(type) {
    // Summary:
    //    Switch between the Save and the Edit Form
    // Description:
    //    Switch between the Save and the Edit Form
    dijit.byId('moduleDesignerEditor').selectChild(dijit.byId("moduleDesignerEditorTable"));
    if (type == 'editor') {
        var node = dijit.byId('moduleDesignerEditor');
        dojo.fadeIn({
            node:        node.domNode,
            duration:    300,
            beforeBegin: function() {
                node.selectChild(dijit.byId("moduleDesignerEditorTable"));
                dojo.style(node.domNode, "opacity", 0);
                dojo.style(node.domNode, "display", "block");
                dojo.style(dojo.byId('moduleDesignerSaveButton'), "display", "none");
            }
        }).play();
    } else {
        dojo.style(dojo.byId('moduleDesignerEditor'), "display", "none");
        dojo.style(dojo.byId('moduleDesignerSaveButton'), "display", "inline");
    }
};

phpr.makeModuleDesignerField = function(formType, params) {
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
        params = new Array();
    }

    var formLabel    = '';
    var selectType   = params['selectType'] || 'custom';
    var tableType    = params['tableType'] || 'varchar';
    if (tableType == 'int') {
        var tableLength  = params['tableLength'] || 11;
    } else {
        var tableLength  = params['tableLength'] || 255;
    }
    var tableField   = params['tableField'] || '';
    var formRange    = params['formRange'] || '';
    var defaultValue = params['defaultValue'] || '';
    var listPosition = params['listPosition'] || 1;
    var status       = params['status'] || 1;
    var isRequired   = params['isRequired'] || 0;
    var id           = params['id'] || 0;

    if (formType == 'selectValues') {
        var options = formRange.split("|");
        for (var i in options) {
            var values = options[i].split("#");
            if (values[0] && values[1] && !values[2]) {
                selectType = 'custom';
                break;
            } else if (values[0] && values[1] && values[2]) {
                selectType = values[0].replace(/(^\s*)|(\s*$)/g, "").toLowerCase();
                break;
            }
        }
    }

    switch (formType) {
        case 'text':
        default:
            formLabel = params['formLabel'] || 'Text';
            labelFor = 'text';
            inputTxt = '<input type="text" dojoType="dijit.form.TextBox" ucfirst="true" />'
            break;
        case 'checkbox':
            formLabel = params['formLabel'] || 'Checkbox';
            labelFor = 'checkbox';
            inputTxt = '<input type="checkbox" dojotype="dijit.form.CheckBox" value="1" />'
            break;
        case 'date':
            formLabel = params['formLabel'] || 'Date';
            labelFor = 'date';
            inputTxt = '<input type="text" dojoType="phpr.DateTextBox" constraints="{datePattern:\'yyyy-MM-dd\'}"'
                + ' promptMessage="dd.mm.yy" />';
            break;
        case 'time':
            formLabel = params['formLabel'] || 'Time';
            labelFor = 'time';
            inputTxt = '<input type="text" dojoType="dijit.form.TimeTextBox"'
                + ' constraints="{formatLength:\'short\', timePattern:\'HH:mm\'}" />';
            break;
        case 'selectValues':
            formLabel = params['formLabel'] || 'Select';
            labelFor  = 'select';
            inputTxt  = '<select dojoType="phpr.FilteringSelect" autocomplete="false" invalidMessage="" >';

            if (selectType == 'project') {
                inputTxt += '<option value="1">Example Project 1</option>';
                inputTxt += '<option value="2">Example Project 2</option>';
            } else if (selectType == 'user') {
                inputTxt += '<option value="1">Example User 1</option>';
                inputTxt += '<option value="2">Example User 2</option>';
            } else if (selectType == 'contact') {
                inputTxt += '<option value="1">Example Contact 1</option>';
                inputTxt += '<option value="2">Example Contact 2</option>';
            } else {
                if (!formRange) {
                    formRange = 'id1 # value1 | id2 # value2';
                }
                var formRangeOptions = new Array();
                var options = formRange.split("|");
                for (var i in options) {
                    var values = options[i].split("#");
                        if (values[0] && values[1]) {
                            formRangeOptions.push({
                                'id':   values[0],
                                'name': values[1]
                        });
                    }
                }
                for (i in formRangeOptions) {
                    inputTxt += '<option value="' + formRangeOptions[i]['id'] + '">' + formRangeOptions[i]['name']
                        + '</option>';
                }
            }
            inputTxt += '</select>';
            break;
        case 'percentage':
            formLabel = params['formLabel'] || 'Percentage';
            labelFor = 'percentage';
            inputTxt = '<div dojoType="dijit.form.HorizontalSlider" maximum="100" minimum="0"';
            inputTxt += ' pageIncrement="100" showButtons="false" intermediateChanges="true" style="height: 20px;">';
            inputTxt += '<ol dojoType="dijit.form.HorizontalRuleLabels" container="topDecoration"';
            inputTxt += ' style="height:1.2em;font-size:75%;color:gray;" count="5" numericMargin="1"></ol>';
            inputTxt += '<div dojoType="dijit.form.HorizontalRule" container="topDecoration"';
            inputTxt += ' count=5 style="height:5px;"></div>';
            inputTxt += '<div dojoType="dijit.form.HorizontalRule" container="bottomDecoration"';
            inputTxt += ' count="11" style="height:5px;"></div>';
            inputTxt += '</div>';
            break;
        case 'textarea':
            formLabel = params['formLabel'] || 'Textarea';
            labelFor = 'textarea';
            inputTxt = '<textarea dojoType="dijit.form.Textarea">\n\n</textarea>';
            break;
        case 'upload':
            var widgetId = dojo.dnd.getUniqueId();
            formLabel = params['formLabel'] || 'Upload';
            labelFor = 'upload';
            inputTxt = '<input type="hidden" dojoType="dijit.form.TextBox" id="' + widgetId + '" />';
            inputTxt += '<iframe id="filesIframe_' + widgetId + '" src="' + phpr.webpath
                + 'index.php/Default/File/fileForm/moduleName/Project/field/' + widgetId
                + '" height="25px" width="100%" frameborder="0" style="overflow: hidden; border: 0px;">></iframe>';
            break;
    }
    labelTxt = '<label for="' + labelFor + '">' + formLabel + '</label>';

    html += '<table class="form"><col class="col1" /><col class="col2" /><col class="col3" />';
    html += '<tr><td class="label">';
    html += labelTxt;
    html += '</td><td>';
    html += inputTxt;

    html += '<input type="hidden" name="selectType" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + selectType + '" />';
    html += '<input type="hidden" name="tableType" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + tableType + '" />';
    html += '<input type="hidden" name="tableLength" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + tableLength + '" />';
    html += '<input type="hidden" name="tableField" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + tableField + '" />';

    html += '<input type="hidden" name="formLabel" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + formLabel + '" />';
    html += '<input type="hidden" name="formType" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + formType + '" />';
    html += '<input type="hidden" name="formRange" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + formRange + '" />';
    html += '<input type="hidden" name="defaultValue" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + defaultValue + '" />';

    html += '<input type="hidden" name="listPosition" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + listPosition + '" />';

    html += '<input type="hidden" name="status" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + status + '" />';
    html += '<input type="hidden" name="isRequired" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + isRequired + '" />';
    html += '<input type="hidden" name="id" class="hiddenValue" dojoType="dijit.form.TextBox" value="'
        + id + '" />';

    html += '</td><td>';
    html += '<button dojoType="dijit.form.Button" baseClass="positive" iconClass="tick"';
    html += ' onClick="'
        + 'phpr.editModuleDesignerField(this.domNode.parentNode.parentNode.parentNode.parentNode.parentNode.id);"';
    html += ' margin-bottom: 5px;">';
    html += phpr.nls.get('Edit');
    html += '</button>&nbsp;&nbsp;'
    html += '<button dojoType="dijit.form.Button" baseClass="positive" iconClass="cross"';
    html += ' onClick="'
        + 'phpr.deleteModuleDesignerField(this.domNode.parentNode.parentNode.parentNode.parentNode.parentNode.id);"';
    html += ' margin-bottom: 5px;">';
    html += phpr.nls.get('Delete');
    html += '</button>'
    html += '</td></tr></table>';

    return html;
};
