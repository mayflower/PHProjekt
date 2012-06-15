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

dojo.provide("phpr.Default.System.Form.CheckBox");
dojo.provide("phpr.Default.System.Form.HorizontalSlider");
dojo.provide("phpr.Default.System.Form.Rating");

dojo.require("dojox.form.Rating");

dojo.declare("phpr.Default.System.Form.CheckBox", dijit.form.CheckBox, {
    // Summary:
    //    Re-write the widget for return 0 on no-checked

    _getValueAttr: function() {
        // Summary:
        //    Hook so attr('value') works.
        // Description:
        //    If the CheckBox is checked, returns the value attribute.
        //    Otherwise returns 0.
        return (this.checked ? this.value : 0);
    }
});

dojo.declare("phpr.Default.System.Form.HorizontalSlider", dijit.form.HorizontalSlider, {
    _layoutHackIE7: function() {
        // Summary:
        //    Disable work around table sizing bugs on IE7 by forcing redraw
    }
});

dojo.declare("phpr.Default.System.Form.Rating", [dojox.form.Rating], {
    // Summary:
    //    Re-write the widget for fix some issues
    constructor: function(params) {
        dojo.mixin(this, params);
        var tpl = '<div class="dojoxRating dijitInline">' +
            '<input type="hidden" value="0" dojoAttachPoint="focusNode" name="${name}" /><ul>${stars}</ul>' +
            '</div>';

        var starTpl = '<li class="dojoxRatingStar dijitInline" ' +
            'dojoAttachEvent="onclick:onStarClick, onmouseover:_onMouse, onmouseout:_onMouse" value="${value}"></li>';
        var rendered = "";
        for (var i = 0; i < this.numStars; i++) {
            rendered += dojo.string.substitute(starTpl, {value: i + 1});
        }
        this.templateString = dojo.string.substitute(tpl, {stars: rendered, name: params.name});
    },

    onStarClick: function(evt) {
        if (!this.disabled) {
            this.inherited("onStarClick", arguments);
        }
    },

    setAttribute: function(key, value) {
        this.set('value', value);
        if (key == "value") {
            this._renderStars(this.value);
            this.onChange(this.value);
        }
    }
});

dojo.provide("phpr.Default.System.Form.MultiFilteringSelect");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.form.Button");
dojo.declare("phpr.Default.System.Form.MultiFilteringSelect", dijit.form.FilteringSelect, {
    selection: null,
    selectionContainer: null,
    _createdOwnSelectionContainer: false,
    value: null,
    _items: null,
    constructor: function() {
        this.selection = {};
        this._items = [];
    },
    destroy: function() {
        this._destroyButtons();

        if (this._createdOwnSelectionContainer) {
            dojo.destroy(this.selectionContainer);
        }

        this.inherited(arguments);
    },
    postMixInProperties: function() {
        if (!this.store) {
            var srcNodeRef = this.srcNodeRef;

            // if user didn't specify store, then assume there are option tags
            this.store = new phpr.Default.System._ComboBoxDataStore(srcNodeRef);
            this.selection = this.store.getSelectedItems();

            // if there is no value set and there is an option list, set
            // the value to the first value to be consistent with native
            // Select

            // Firefox and Safari set value
            // IE6 and Opera set selectedIndex, which is automatically set
            // by the selected attribute of an option tag
            // IE6 does not set value, Opera sets value = selectedIndex
            if (!("value" in this.params)) {
                var item = (this.item = this.store.fetchSelectedItem());
                if (item) {
                    var valueField = this._getValueField();
                    this.value = this.store.getValue(item, valueField);
                }
            }
        }

        this.inherited(arguments);
    },
    postCreate: function() {
        this.inherited(arguments);

        this.selectionContainer = dojo.byId(this.selectionContainer);

        if (!this.selectionContainer) {
            this.selectionContainer = dojo.create('ul', null, this.domNode, 'after');
            dojo.addClass(this.selectionContainer, "multipleFilteringSelectContainer");
            this._createdOwnSelectionContainer = true;
        }

        this.selectionContainer = this.selectionContainer.domNode || this.selectionContainer;

        this._renderSelection();
    },
    isValid: function() {
        return true;
    },
    onChange: function(value) {
        this.inherited(arguments);
        this._addToSelection(this.item);
    },
    _onKey: function(evt) {
        this.inherited(arguments);
        if (evt.charOrCode == dojo.keys.ENTER) {
            if (this.item) {
                this._addToSelection(this.item);
            }
        }
    },
    _addToSelection: function(item) {
        if (!item) {
            return;
        }

        var value = this.store.getValue(item, this._getValueField());
        var displayedValue = this.displayedValue;

        if (value !== "" && value !== null && !this.selection.hasOwnProperty(value)) {
            this.selection[value] = displayedValue;
            this._renderSelection();
            this.value = null;
            this.reset();
        }
    },
    _removeFromSelection: function(value) {
        if (this.selection.hasOwnProperty(value)) {
            delete this.selection[value];
            this._renderSelection();
        }
    },
    _openResultList: function(results, dataObject) {
        var newResults = [];
        dojo.forEach(results, function(item, i) {
            if (!this.selection.hasOwnProperty(dataObject.store.getValue(item, this._getValueField()))) {
                newResults.push(item);
            }
        }, this);

        var newargs = arguments;
        newargs[0] = newResults;
        this.inherited(newargs);
    },
    _renderSelection: function() {
        if (this.selectionContainer) {
            this._destroyButtons();
            dojo.empty(this.selectionContainer);
            var self = this;
            for (var i in this.selection) {
                var item = new phpr.Default.System.TemplateWrapper({
                    templateName: "phpr.Default.template.form.multipleFilteringSelectItem.html",
                    templateData: {
                        text: this.selection[i]
                    }
                });

                this.connect(item.checkBox, 'onChange', dojo.hitch(this, "_removeFromSelection", i));

                this.selectionContainer.appendChild(item.domNode);
                this._items.push(item);
            }
        }
    },
    _destroyButtons: function() {
        for (var i = 0; i < this._items.lengh; i++) {
            this._items[i].destroyRecursive();
        }
        this._items = [];
    },
    _getValueAttr: function() {
        var ret = [];
        for (var i in this.selection) {
            if (this.selection.hasOwnProperty(i)) {
                ret.push(i);
            }
        }
        return ret;
    },
    _setValueAttr: function(values) {
        if (!values || !dojo.isArray(values) || values.length === 0) {
            return;
        }
        this.selection = {};
        for (var i in values) {
            this.selection[values[i]] = null;
        }
        this.store.fetch({ onComplete: dojo.hitch(this, this._integrateDisplayValues) });
    },
    _integrateDisplayValues: function(storeitems) {
        var valueField = this._getValueField();
        for (var i in this.selection) {
            if (this.selection.hasOwnProperty(i) && this.selection[i] === null) {
                for (var e in storeitems) {
                    if (this.store.getValue(storeitems[e], valueField) == i) {
                        this.selection[i] = this.store.getLabel(storeitems[e]);
                    }
                }
            }
        }
        this._renderSelection();
    },
    _setMaxOptions: function() {}
});

dojo.declare("phpr.Default.System._ComboBoxDataStore", dijit.form._ComboBoxDataStore, {
    _selectedItems: null,
    constructor: function( /*DomNode*/ root) {
        this._selectedItems = {};
        var self = this;
        dojo.query("> option", this.root).forEach(function(option) {
            if (option.selected && option.value) {
                var text = (option.innerText || option.textContent || '');
                self._selectedItems[option.value] = text;
            }
        });
    },
    getSelectedItems: function() {
        return this._selectedItems;
    },
    fetchSelectedItem: function() {
        return null;
    }
});
