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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Default.EditorContainer");

dojo.require("dijit._editor.plugins.LinkDialog");
dojo.require("dijit._editor.plugins.TextColor");
dojo.require("dijit._editor.plugins.FontChoice");
dojo.require("dijit.Editor");

dojo.declare("phpr.Default.EditorContainer", [dijit._Widget], {
    domNode: null,
    // Has the editore been loaded yet?
    loaded: false,
    // The css style of the editor
    style: "",
    // The editor widget
    editor: null,
    // The editor Dom node
    editorNode: null,
    // Editor parameters
    params: {},
    // Editor value
    value: "",
    constructor: function(params, srcNodeRef) {
        this.domNode = srcNodeRef;
        if (params.style) {
            this.style = params.style;
        }
        this.params = params;
        this.editorNode = dojo.create('div');
    },
    destroy: function() {
        if (this.editor && this.editor.destroy && !this.editor._beingDestroyed) {
            this.editor.destroy();
        }

        this.editor = null;

        if (this.editorNode) {
            dojo.destroy(this.editorNode);
        }

        this.editorNode = null;
        this.domNode = null;
        this.inherited(arguments);
    },
    postCreate: function() {
        dojo.attr(this.domNode, 'style', this.style);
    },
    show: function() {
        // Summary:
        //    Show the Editor
        // Description:
        //    Checks whether the editor has been loaded, if not, load it and
        //    show it.
        if (!this.loaded) {
            this.loadEditor();
            this.loaded = true;
        }
    },
    loadEditor: function() {
        // Summary:
        //    Instantiate the dijit editor
        if (this.editor && this.editor.destroy) {
            this.editor.destroy();
            this.editor = null;
        }
        var newpar = dojo.clone(this.params);
        if (newpar.id) {
            newpar.id = "EditorContainer_" + newpar.id;
        }
        this.editor = new dijit.Editor(newpar, this.domNode);
        this.editor.set('value', this.value);

    },
    _getValueAttr: function() {
        return this.editor.get('value');
    },
    _setValueAttr: function(arg) {
        this.value = arg;
        if (this.editor) {
            this.editor.set('value', arg);
        }
    }
});
