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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

dojo.provide('phpr.Default.EditorDialog');
dojo.declare('phpr.Default.EditorDialog', phpr.Dialog, {
    draggable: false,
    refocus: false,
    style: 'width: 82%',

    buildRendering: function() {
        this.inherited(arguments);

        this.editor = new dijit.Editor({
            style: 'width: 99%; height: 99%; border: 1px solid;',
            extraPlugins: [
                'subscript', 'superscript', 'removeFormat', 'delete', '|', 'insertHorizontalRule', 'createLink',
                'insertImage', '|', 'foreColor', 'hiliteColor', '|', 'fontName', 'fontSize'
            ]
        }, dojo.create('div', null, this.containerNode));

        this.saveButton = new dijit.form.Button({
            iconClass: 'tick',
            type: 'submit',
            style: 'margin-left: 0px; margin-top: 10px; margin-bottom: 0px;',
            label: phpr.nls.get('save')
        }, dojo.create('div', null, this.containerNode));
    },

    _getValueAttr: function() {
        if (this.editor && this.editor.get) {
            return this.editor.get('value');
        }
    },

    _setValueAttr: function(val) {
        return this.editor.set('value', val);
    }
});

