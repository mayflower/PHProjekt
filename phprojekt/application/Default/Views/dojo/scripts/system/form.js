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

dojo.provide("phpr.Form.CheckBox");
dojo.provide("phpr.Form.DateTextBox");
dojo.provide("phpr.Form.FilteringSelect");
dojo.provide("phpr.Form.HorizontalSlider");
dojo.provide("phpr.Form.Rating");
dojo.provide("phpr.Form.TimeTextBox");

dojo.declare("phpr.Form.CheckBox", dijit.form.CheckBox, {
    // Summary:
    //    Re-write the widget for return 0 on no-checked

    _getValueAttr: function(){
        // Summary:
        //    Hook so attr('value') works.
        // Description:
        //    If the CheckBox is checked, returns the value attribute.
        //    Otherwise returns 0.
        return (this.checked ? this.value : 0);
    }
});

dojo.declare("phpr.Form.DateTextBox", dijit.form.DateTextBox, {
    _blankValue: '', // used by filter() when the textbox is blank

    parse:function(value, constraints) {
        // Summary:
        //    Parses as string as a Date, according to constraints
        // Date
        return this.dateLocaleModule.parse(value, constraints) || (this._isEmpty(value) ? '' : undefined);
    },

    serialize:function(d, options) {
        // Summary:
        //     This function overwrites the dijit.form.DateTextBox display
        // Description:
        //     Make sure that the date is not only displayed localized, but also
        //     the value which is returned is set to this date format
        return dojo.date.locale.format(d, {selector:'date', datePattern:'yyyy-MM-dd'}).toLowerCase();
    },

    _onBlur: function() {
        // Fix until http://bugs.dojotoolkit.org/changeset/22842 is applied
        // Keep the dropdown node for destroy it
        if (this._picker) {
            var node = this._picker.domNode.parentNode;
        }
        this.inherited(arguments);

        if (node) {
            // Destroy the popup and all the items
            dojo.destroy(node);
        }
    }
});

dojo.declare("phpr.Form.FilteringSelect", dijit.form.FilteringSelect, {
    // Summary:
    //    Extend the dojo FilteringSelect for fix some bugs.
    // Description:
    //    The dojo select do not allow two or more labels with the same name,
    //    for select users that is a problem (users with the same name),
    //    See: http://trac.dojotoolkit.org/ticket/7279
    //    Also change the query options and highlight for work with trees in select.

    // Highlight any occurrence
    highlightMatch: 'all',

    // `${0}*` means "starts with", `*${0}*` means "contains", `${0}` means "is"
    queryExpr: "*${0}*",

    // Internal var for fix the bug of items with the same display
    _lastSelectedId: null,

    _onBlur:function() {
        // Summary:
		//    Called magically when focus has shifted away from this widget and it's drop down
        this.inherited(arguments);

        // Fix until http://bugs.dojotoolkit.org/changeset/22842 is applied
        // Keep the dropdown node for destroy it
        if (this._popupWidget) {
            var node = this._popupWidget.domNode.parentNode;

            // Destroy the popup and all the items
            this._popupWidget.destroy();
            this._popupWidget = null;

            dojo.destroy(node);
        }
    },

    _onKeyPress:function(/*Event*/ evt) {
        // Create the popup again if was destroyed
		if (!this._popupWidget) {
            var popupId = this.id + '_popup';
            this._popupWidget = new dijit.form._ComboBoxMenu({
                onChange: dojo.hitch(this, this._selectOption),
                id:       popupId,
                dir:      this.dir
            });
            dijit.removeWaiState(this.focusNode, 'activedescendant');
            dijit.setWaiState(this.textbox, 'owns', popupId); // associate popup with textbox
		}

		this.inherited(arguments);
	},

    _doSelect:function(/*Event*/ tgt) {
        // Summary:
        //    Overrides ComboBox._doSelect(), the method called when an item in the menu is selected.
        // Description:
        //    FilteringSelect overrides this to set both the visible and
        //    hidden value from the information stored in the menu.
        //    Also mark the last selected item.
        this._setValueFromItem(tgt.item, true);
        this._lastSelectedId = this.get('value');
    },

    _setDisplayedValueAttr:function(/*String*/ label, /*Boolean?*/ priorityChange) {
        // Summary:
        //    Overrides dijit.form.FilteringSelect._setDisplayedValueAttr().
        // Description:
        //    Change the query for search the id if an item is select,
        //    or by the name is not (normal case)

        // When this is called during initialization it'll ping the datastore
        // for reverse lookup, and when that completes (after an XHR request)
        // will call setValueAttr()... but that shouldn't trigger an onChange()
        // event, even when it happens after creation has finished
        if(!this._created){
            priorityChange = false;
        }

        if(this.store) {
            var query = dojo.clone(this.query); // #6196: populate query with user-specifics
            // Escape meta characters of dojo.data.util.filter.patternToRegExp().
            if (this._lastSelectedId != null) {
                this._lastQuery = query['value'] = this._lastSelectedId;
            } else {
                this._lastQuery = query[this.searchAttr] = label.replace(/([\\\*\?])/g, "\\$1");
            }
            this._lastSelectedId = null;

            // If the label is not valid, the callback will never set it,
            // so the last valid value will get the warning textbox set the
            // textbox value now so that the impending warning will make
            // sense to the user
            this.textbox.value = label;
            this._lastDisplayedValue = label;
            var _this = this;
            var fetch = {
                query:        query,
                queryOptions: {
                    ignoreCase: this.ignoreCase,
                    deep:       true
                },
                onComplete: function(result, dataObject) {
                    dojo.hitch(_this, '_callbackSetLabel')(result, dataObject, priorityChange);
                },
                onError: function(errText) {
                    dojo.hitch(_this, '_setValue')('', label, false);
                }
            };
            dojo.mixin(fetch, this.fetchProperties);
            this.store.fetch(fetch);
        }
    },

    doHighlight: function(/*String*/label, /*String*/find) {
        // Summary:
        //    Highlights the string entered by the user in the menu.
        //    Change the function for Highlights all the occurences

        // Add greedy when this.highlightMatch=="all"
        var modifiers    = 'i' + (this.highlightMatch == 'all' ? 'g' : '');
        var escapedLabel = this._escapeHtml(label);
        find             = dojo.regexp.escapeString(find); // escape regexp special chars
        var ret = escapedLabel.replace(new RegExp("(^|\\s|\\w)("+ find +")", modifiers),
            '$1<span class="dijitComboBoxHighlightMatch">$2</span>');
        return ret; // Returns String, (almost) valid HTML (entities encoded)
    }
});
dojo.extend(dijit.form._ComboBoxMenu, {
    clearResultList: function(){
        // Summary:
        //		Clears the entries in the drop down list, but of course keeps the previous and next buttons.
        while(this.domNode.childNodes.length > 2) {
            dojo.destroy(this.domNode.childNodes[this.domNode.childNodes.length-2]);
        }
    }
});

dojo.declare("phpr.Form.HorizontalSlider", dijit.form.HorizontalSlider, {
    _layoutHackIE7: function() {
        // Summary:
        //    Disable work around table sizing bugs on IE7 by forcing redraw
    }
});

dojo.declare("phpr.Form.Rating", dojox.form.Rating, {
    // Summary:
    //    Re-write the widget for fix some issues
    constructor:function(params) {
        dojo.mixin(this, params);
        var tpl = '<div class="dojoxRating dijitInline">'
            + '<input type="hidden" value="0" dojoAttachPoint="focusNode" name="${name}" /><ul>${stars}</ul>'
            + '</div>';

        var starTpl = '<li class="dojoxRatingStar dijitInline" '
          + 'dojoAttachEvent="onclick:onStarClick,onmouseover:_onMouse,onmouseout:_onMouse" value="${value}"></li>';
        var rendered = "";
        for(var i = 0; i < this.numStars; i++) {
            rendered += dojo.string.substitute(starTpl, {value: i + 1});
        }
        this.templateString = dojo.string.substitute(tpl, {stars: rendered, name: params.name});
    },

    onStarClick:function(evt) {
        if (!this.disabled) {
            this.inherited('onStarClick', arguments);
        }
    },

    setAttribute:function(key, value){
        this.set('value', value);
        if (key == 'value') {
            this._renderStars(this.value);
            this.onChange(this.value);
        }
    }
});

dojo.declare("phpr.Form.TimeTextBox", dijit.form.TimeTextBox, {
    _onBlur: function() {
        // Fix until http://bugs.dojotoolkit.org/changeset/22842 is applied
        // Keep the dropdown node for destroy it
        if (this._picker) {
            var node = this._picker.domNode.parentNode;
        }
        this.inherited(arguments);

        if (node) {
            // Destroy the popup and all the items
            dojo.destroy(node);
        }
    }
});
