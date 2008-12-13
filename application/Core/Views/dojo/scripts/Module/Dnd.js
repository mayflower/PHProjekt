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
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.declare("phpr.Module.Designer", dojo.dnd.AutoSource, {

    onDrop:function(source, nodes, copy) {
        if(this != source) {
            this.onDropExternal(source, nodes, copy);
            phpr.makeModuleDesignerSource();
            var m = dojo.dnd.manager();
            if (this.node.id == m.target.node.id) {
                if (source.node.id == 'moduleDesignerSource') {
                    dijit.byId('moduleDesignerEditor').selectChild(dijit.byId("moduleDesignerEditorTable"));
                    dojo.style(dojo.byId('moduleDesignerEditor'), "display", "none");
                    var t = this._normalizedCreator(nodes[0]);
                    phpr.editModuleDesignerField(t, this);
                }
            }
        } else {
            this.onDropInternal(nodes, copy);
        }
    },

    onMouseDown:function(e) {
        if(this._legalMouseDown(e) && (!this.skipForm || !dojo.dnd.isFormElement(e))){
            this.mouseDown = true;
            this.mouseButton = e.button;
            this._lastX = e.pageX;
            this._lastY = e.pageY;

            if (this.node.id != 'moduleDesignerSource') {
                if (this.current) {
                    dijit.byId('moduleDesignerEditor').selectChild(dijit.byId("moduleDesignerEditorTable"));
                    dojo.style(dojo.byId('moduleDesignerEditor'), "display", "none");
                    var t = this._normalizedCreator(this.current);
                    phpr.editModuleDesignerField(t, this);
                }
            }

            dojo.dnd.Source.superclass.onMouseDown.call(this, e);
        }
    },

    markupFactory:function(params, node) {
        params._skipStartup = true;
        return new phpr.Module.Designer(node, params);
    }
});

phpr.makeModuleDesignerSource = function() {
    var element = dojo.byId('moduleDesignerSource');
    var html    = '';
    var types   = new Array('text', 'date', 'time', 'selectValues', 'checkbox',
                            'percentage', 'textarea', 'upload')

    for (i in types) {
        var id = dojo.dnd.getUniqueId();
        html += '<div id="' + id + '" class="dojoDndItem" style="cursor: move;">';
        html += phpr.makeModuleDesignerField(types[i]);
        html += '</div>';
    }

    element.innerHTML = html;
    dojo.parser.parse(element.id);
    moduleDesignerSource.sync();
};

phpr.makeModuleDesignerTarget = function(jsonData, tabs) {
    if (jsonData) {
        var data = dojo.fromJson(jsonData);
        for (var j in tabs) {
            var tab = eval("moduleDesignerTarget" + tabs[j]['nameId']);
            var element = dojo.byId('moduleDesignerTarget' + tabs[j]['nameId']);
            var html = '';

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

phpr.editModuleDesignerField = function(object, target) {
    var nodeId       = null;
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
    dojo.query('.hiddenValue', object.node).forEach(function(ele) {
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

    nodeId            = object.node.id;
    var fieldsTable   = '';
    var fieldsForm    = '';
    var fieldsList    = '';
    var fieldsGeneral = '';
    var template      = new phpr.Default.Field();
    var render        = new phpr.Component();

    // Table
    fieldsTable += template.textFieldRender('Field Name', 'tableField', tableField, true, false);
    var tableTypeRange = new Array();
    switch (formType) {
        case 'text':
        case 'checkbox':
        case 'selectValues':
            tableTypeRange.push({'id': 'varchar', 'name': 'VARCHAR'});
            tableTypeRange.push({'id': 'int', 'name': 'INT'});
            fieldsTable += template.selectRender(tableTypeRange, 'Field Type', 'tableType', tableType, true, false);
            fieldsTable += template.textFieldRender('Table Lenght', 'tableLength', tableLength, true, false);
            break;
        case 'date':
            tableTypeRange.push({'id': 'date', 'name': 'DATE'});
            fieldsTable += template.selectRender(tableTypeRange, 'Field Type', 'tableType', 'date', true, false);
            break;
        case 'time':
            tableTypeRange.push({'id': 'time', 'name': 'TIME'});
            fieldsTable += template.selectRender(tableTypeRange, 'Field Type', 'tableType', 'time', true, false);
            break;
        case 'percentage':
            tableTypeRange.push({'id': 'varchar', 'name': 'VARCHAR'});
            fieldsTable += template.selectRender(tableTypeRange, 'Field Type', 'tableType', 'varchar', true, false);
            break;
        case 'textarea':
            tableTypeRange.push({'id': 'text', 'name': 'TEXT'});
            fieldsTable += template.selectRender(tableTypeRange, 'Field Type', 'tableType', 'text', true, false);
            break;
        case 'upload':
            tableTypeRange.push({'id': 'varchar', 'name': 'VARCHAR'});
            fieldsTable += template.selectRender(tableTypeRange, 'Field Type', 'tableType', 'varchar', true, false);
            break;
    }

    switch (formType) {
        case 'selectValues':
            var selectTypeRange = new Array();
            selectTypeRange.push({'id': 'project', 'name': 'Project List'});
            selectTypeRange.push({'id': 'user', 'name': 'User List'});
            selectTypeRange.push({'id': 'custom', 'name': 'Custom Values'});
            fieldsTable += template.selectRender(selectTypeRange, 'Select Type', 'selectType', selectType, true, false);
            break;
    }

    fieldsTable += '<input type="hidden" name="id" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + id + '" />';

    fieldsTable += '<tr><td class="label">';
    fieldsTable += '<label for="moduleDesignerSubmitButtonTable">&nbsp;</label>';
    fieldsTable += '</td><td>';
    fieldsTable += '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonTable" baseClass="positive" type="submit" iconClass="tick"></button>';
    fieldsTable += '</td></tr>';

    // Form
    fieldsForm += template.textFieldRender('Label', 'formLabel', formLabel, true, false);

    switch (formType) {
        case 'selectValues':
            if (!formRange) {
                formRange = 'id1 # value1 | id2 # value2';
            }
            fieldsForm += template.textAreaRender('Range', 'formRange', formRange, true, false);
            break;
    }
    fieldsForm += template.textFieldRender('Default Value', 'defaultValue', defaultValue, true, false);

    fieldsForm += '<tr><td class="label">';
    fieldsForm += '<label for="moduleDesignerSubmitButtonForm">&nbsp;</label>';
    fieldsForm += '</td><td>';
    fieldsForm += '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonForm" baseClass="positive" type="submit" iconClass="tick"></button>';
    fieldsForm += '</td></tr>';

    // List
    fieldsList += template.textFieldRender('List Position', 'listPosition', listPosition, true, false);

    fieldsList += '<tr><td class="label">';
    fieldsList += '<label for="moduleDesignerSubmitButtonList">&nbsp;</label>';
    fieldsList += '</td><td>';
    fieldsList += '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonList" baseClass="positive" type="submit" iconClass="tick"></button>';
    fieldsList += '</td></tr>';

    // General
    var statusRange = new Array();
    statusRange.push({'id': '0', 'name': 'Inactive'});
    statusRange.push({'id': '1', 'name': 'Active'});
    fieldsGeneral += template.selectRender(statusRange, 'Status', 'status', status, true, false);

    var isRequiredRange = new Array();
    isRequiredRange.push({'id': '0', 'name': 'No'});
    isRequiredRange.push({'id': '1', 'name': 'Yes'});
    fieldsGeneral += template.selectRender(isRequiredRange, 'Required Field', 'isRequired', isRequired, true, false);

    fieldsGeneral += '<tr><td class="label">';
    fieldsGeneral += '<label for="moduleDesignerSubmitButtonGeneral">&nbsp;</label>';
    fieldsGeneral += '</td><td>';
    fieldsGeneral += '<button dojoType="dijit.form.Button" id="moduleDesignerSubmitButtonGeneral" baseClass="positive" type="submit" iconClass="tick"></button>';
    fieldsGeneral += '</td></tr>';

    var formId = 'formTable' + '_' + nodeId;
    var html = render.render(["phpr.Default.template", "tabs.html"], null, {
        innerTabs: fieldsTable,
        formId: formId
    });
    dijit.byId('moduleDesignerEditorTable').attr('content', html);

    var formId = 'formForm' + '_' + nodeId;
    var html = render.render(["phpr.Default.template", "tabs.html"], null, {
        innerTabs: fieldsForm,
        formId: formId
    });
    dijit.byId('moduleDesignerEditorForm').attr('content', html);

    var formId = 'formList' + '_' + nodeId;
    var html = render.render(["phpr.Default.template", "tabs.html"], null, {
        innerTabs: fieldsList,
        formId: formId
    });
    dijit.byId('moduleDesignerEditorList').attr('content', html);

    var formId = 'formGeneral' + '_' + nodeId;
    var html = render.render(["phpr.Default.template", "tabs.html"], null, {
        innerTabs: fieldsGeneral,
        formId: formId
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
                dijit.byId("formRange").attr('value', 'User # id # username');
                dijit.byId("tableType").attr('value', 'int');
                dijit.byId("tableLength").attr('value', 11);
                break;
        }
    });

    dojo.connect(dijit.byId('moduleDesignerSubmitButtonTable'), "onClick", function() {
        phpr.saveModuleDesignerField(nodeId, target, formType);
    });
    dojo.connect(dijit.byId('moduleDesignerSubmitButtonForm'), "onClick", function() {
        phpr.saveModuleDesignerField(nodeId, target, formType);
    });
    dojo.connect(dijit.byId('moduleDesignerSubmitButtonList'), "onClick", function() {
        phpr.saveModuleDesignerField(nodeId, target, formType);
    });
    dojo.connect(dijit.byId('moduleDesignerSubmitButtonGeneral'), "onClick", function() {
        phpr.saveModuleDesignerField(nodeId, target, formType);
    });

    dojo.fadeIn({
        node: dojo.byId('moduleDesignerEditor'),
        duration: 300,
        beforeBegin: function(){
            var node = dojo.byId('moduleDesignerEditor');
            dijit.byId('moduleDesignerEditor').selectChild(dijit.byId("moduleDesignerEditorTable"));
            dojo.style(node, "opacity", 0);
            dojo.style(node, "display", "block");
            dojo.style(dojo.byId('moduleDesignerSaveButton'), "display", "none");
        }
    }).play();
};

phpr.saveModuleDesignerField = function(nodeId, target, formType){
    var params = new Array();
    params = dojo.mixin(params, dijit.byId('formTable' + '_' + nodeId).attr('value'));
    params = dojo.mixin(params, dijit.byId('formForm' + '_' + nodeId).attr('value'));
    params = dojo.mixin(params, dijit.byId('formList' + '_' + nodeId).attr('value'));
    params = dojo.mixin(params, dijit.byId('formGeneral' + '_' + nodeId).attr('value'));

    dijit.byId('moduleDesignerEditor').selectChild(dijit.byId("moduleDesignerEditorTable"));
    dojo.style(dojo.byId('moduleDesignerEditor'), "display", "none");
    dojo.style(dojo.byId('moduleDesignerSaveButton'), "display", "inline");

    dojo.byId(nodeId).innerHTML = phpr.makeModuleDesignerField(formType, params);
    dojo.parser.parse(nodeId);
};

phpr.makeModuleDesignerField = function(formType, params) {
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
            inputTxt = '<input type="checkbox" dojotype="dijit.form.CheckBox" value="on" />'
            break;
        case 'date':
            formLabel = params['formLabel'] || 'Date';
            labelFor = 'date';
            inputTxt = '<input type="text" dojoType="phpr.DateTextBox" constraints="{datePattern:\'yyyy-MM-dd\'}" promptMessage="dd.mm.yy" />';
            break;
        case 'time':
            formLabel = params['formLabel'] || 'Time';
            labelFor = 'time';
            inputTxt = '<input type="text" dojoType="dijit.form.TimeTextBox" constraints="{formatLength:\'short\', timePattern:\'HH:mm\'}" />';
            break;
        case 'selectValues':
            formLabel = params['formLabel'] || 'Select';
            labelFor  = 'select';
            inputTxt = '<select dojoType="dijit.form.FilteringSelect" autocomplete="true" searchAttr="name" invalidMessage="" >';

            if (selectType == 'project') {
                inputTxt += '<option value="1">Example Project 1</option>';
                inputTxt += '<option value="2">Example Project 2</option>';
            } else if (selectType == 'user') {
                inputTxt += '<option value="1">Example User 1</option>';
                inputTxt += '<option value="2">Example User 2</option>';
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
                    inputTxt += '<option value="' + formRangeOptions[i]['id'] + '">' + formRangeOptions[i]['name'] + '</option>';
                }
            }
            inputTxt += '</select>';
            break;
        case 'percentage':
            formLabel = params['formLabel'] || 'Percentaje';
            labelFor = 'percentage';
            inputTxt = '<div dojoType="dijit.form.HorizontalSlider" maximum="100" minimum="0" pageIncrement="100" showButtons="false"';
            inputTxt += ' intermediateChanges="true" style="height: 20px;">';
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
            inputTxt = '<input type="hidden" id="' + widgetId + '" dojoType="dijit.form.TextBox" />';
            inputTxt += '<iframe src="' + phpr.webpath + 'index.php/Project/index/uploadForm/field/' + widgetId + '" height="25px" width="100%" frameborder="0" style="overflow:hidden; border:0px;">></iframe>';
            break;
    }
    labelTxt = '<label for="' + labelFor + '">' + formLabel + '</label>';

    html += '<table class="form"><col class="col1" /><tr><td class="label">';
    html += labelTxt;
    html += '</td><td>';
    html += inputTxt;

    html += '<input type="hidden" name="selectType" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + selectType + '" />';
    html += '<input type="hidden" name="tableType" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + tableType + '" />';
    html += '<input type="hidden" name="tableLength" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + tableLength + '" />';
    html += '<input type="hidden" name="tableField" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + tableField + '" />';

    html += '<input type="hidden" name="formLabel" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + formLabel + '" />';
    html += '<input type="hidden" name="formType" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + formType + '" />';
    html += '<input type="hidden" name="formRange" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + formRange + '" />';
    html += '<input type="hidden" name="defaultValue" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + defaultValue + '" />';

    html += '<input type="hidden" name="listPosition" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + listPosition + '" />';

    html += '<input type="hidden" name="status" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + status + '" />';
    html += '<input type="hidden" name="isRequired" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + isRequired + '" />';
    html += '<input type="hidden" name="id" class="hiddenValue" dojoType="dijit.form.TextBox" value="' + id + '" />';

    html += '</td></tr></table>';

    return html;
};
