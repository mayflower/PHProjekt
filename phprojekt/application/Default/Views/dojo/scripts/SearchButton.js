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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @author     Reno Reckling <reno.reckling@mayflower.de>
 */

dojo.provide("phpr.Default.SearchButton");

dojo.require("dijit.form.Button");

dojo.declare(
    "phpr.Default.SearchButton",
    [dijit.form.Button, dijit._Container, dijit._HasDropDown],
    {
    templateString: __phpr_templateCache["phpr.Default.template.form.searchButton.html"],
    widgetsInTemplate: true,
    keyPressTimer: null,
    constructor: function() {
        if (!this.dropDown) {
            this.dropDown = new dijit.Menu({}, dojo.create('div'));
        }
    },
    postCreate: function() {
        this.connect(
            this.dropDown,
            "onKeyDown",
            dojo.hitch(this, "_onMenuKeyDown")
        );
        this.connect(
            this.searchField,
            "onKeyDown",
            dojo.hitch(this, "_onKeyDown", this.searchField)
        );
        this.connect(
            this.searchField,
            "onKeyPress",
            dojo.hitch(this, "_onKeyPress", this.searchField)
        );
    },
    _onMenuKeyDown: function(evt) {
        var children = this.dropDown.getChildren();
        if (children.length > 0 &&
                ((evt.keyCode == dojo.keys.UP_ARROW &&
                this.dropDown.focusedChild === children[children.length - 1]) ||
                (evt.keyCode == dojo.keys.DOWN_ARROW &&
                this.dropDown.focusedChild === children[0]))) {
            dojo.stopEvent(evt);
            this.searchField.focus();
        }
    },
    _onKeyDown: function(field, evt) {
        if (evt.keyCode == dojo.keys.ENTER) {
            this._clearTimer();
            this.onSubmit(field.get('value'));
        } else if (this.dropDown.isShowingNow &&
                (evt.keyCode == dojo.keys.BACKSPACE ||
                evt.keyCode == dojo.keys.DELETE)) {
            this._setTimer();
        } else if (evt.keyCode == dojo.keys.DOWN_ARROW && this.dropDown.isShowingNow) {
            this.dropDown.focus();
        } else if (evt.keyCode == dojo.keys.DOWN_ARROW && !this.dropDown.isShowingNow) {
            this._onChange();
        } else if (evt.keyCode == dojo.keys.UP_ARROW && this.dropDown.isShowingNow) {
            this.dropDown.focusLastChild();
        }
    },
    _onKeyPress: function(field, evt) {
        if (evt.charCode !== undefined && evt.charCode !== 0) {
            this._setTimer();
        }
        evt.stopPropagation();
    },
    _onChange: function() {
        this._clearTimer();
        this.onChange(this.searchField.get('value'));
    },
    _setTimer: function() {
        if (this.keyPressTimer) {
            this._clearTimer();
        }

        this.keyPressTimer = setTimeout(
            dojo.hitch(this, "_onChange"),
            700
        );
    },
    _clearTimer: function() {
        clearTimeout(this.keyPressTimer);
    },
    _addSuggestGroup: function(group) {
        var groupHeader = new dijit.MenuItem({
            label: group.name,
            disabled: true
        });

        this.dropDown.addChild(groupHeader);
        dojo.addClass(groupHeader.domNode, "groupHeader");
        dojo.removeClass(groupHeader.domNode, "dijitMenuItemDisabled");

        var l = group.items.length;

        for (var i = 0; i < l; i++) {
            var item = group.items[i];
            this.dropDown.addChild(this._createSuggestItem(item));
        }
    },
    _createSuggestItem: function(item) {
        var html = phpr.fillTemplate("phpr.Default.template.form.searchSuggestItem.html", {
            firstDisplay: item.firstDisplay,
            secondDisplay: item.secondDisplay
        });

        return new dijit.MenuItem({
            label: html,
            onClick: function() {
                dojo.publish(item.moduleName + ".loadResult", [item.id, item.moduleName, item.projectId]);
            }
        });
    },
    onChange: function() {},
    onSubmit: function() { },
    setSuggest: function(suggestGroups) {
        this.searchField.focus();
        this.dropDown.destroyDescendants(); 
        suggestGroups = suggestGroups || [];
        var l = suggestGroups.length;

        for (var i = 0; i < l; i++) {
            this._addSuggestGroup(suggestGroups[i]);
        }
    },
    showSuggest: function() {
        this.openDropDown();
    },
    hideSuggest: function() {
        this.closeDropDown();
    }
});
