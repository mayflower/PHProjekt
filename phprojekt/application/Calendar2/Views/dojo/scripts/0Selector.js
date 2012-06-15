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
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Selector abstraction to be independent from specific dom elements etc.
 * the filename was chosen because we need to be loaded before Main.js
 */
dojo.provide("phpr.Calendar2.Selector");
dojo.declare("phpr.Calendar2.Selector", phpr.Default.System.Component, {
    constructor: function(options) {
        if (!dojo.isObject(options)) {
            throw new Error("Selector options missing!");
        } else if (!options.titleContainer) {
            throw new Error("Selector title container missing!");
        } else if (!options.labelContainer) {
            throw new Error("Selector label container missing!");
        } else if (!options.selectorContainer) {
            throw new Error("Selector selector container missing!");
        } else if (!options.selectionContainer) {
            throw new Error("Selector selection container missing!");
        } else if (!options.errorContainer) {
            throw new Error("Selector error container missing!");
        } else if (!options.doneButtonWidget) {
            throw new Error("Selector done button container missing!");
        } else if (!options.itemList) {
            throw new Error("Selector item list missing!");
        }

        options.onComplete = options.onComplete || function() {};

        options.labels = options.labels || {};
        options.labels.title = options.labels.title || "Title";
        options.labels.label = options.labels.label || "Label";
        options.labels.done = options.labels.done || "Done";
        options.labels.noSelection = options.labels.noSelection || "No selection";

        this._selection = options.preSelection || [];
        this._options = options;
        this._renderSelector();
    },
    destroy: function() {
        this._options.titleContainer.destroyDescendants();
        this._options.labelContainer.destroyDescendants();
        this._options.selectorContainer.destroyDescendants();
        this._options.selectionContainer.destroyDescendants();
        this._options.errorContainer.destroyDescendants();

        this.inherited(arguments);
    },
    getSelection: function() {
        return this._filterSelectWidget.get('value');
    },
    _renderSelector: function() {
        this._options.titleContainer.set('content', this._options.labels.title);
        this._options.labelContainer.set('content', this._options.labels.label);
        this._options.errorContainer.set('content', this._options.labels.noSelection);
        this._options.doneButtonWidget.set('label', this._options.labels.done);

        var dataItems = [];
        for (var i in this._options.itemList) {
            var item = this._options.itemList[i];
            dataItems.push({ display: item.display, value: item.id});
        }

        var data = {
            label: "display",
            items: dataItems };
        var store = new dojo.data.ItemFileReadStore({ data: data });

        this._filterSelectWidget = new phpr.Default.System.Form.MultiFilteringSelect({
                selectionContainer: this._options.selectionContainer,
                name: "userSelect",
                store: store,
                searchAttr: "display"
            },
            dojo.create("input")
        );

        this._filterSelectWidget.set('value', this._selection);

        this.garbageCollector.addNode(this._filterSelectWidget);

        this._options.selectorContainer.set('content', this._filterSelectWidget);

        this.garbageCollector.addEvent(
            dojo.connect(
                this._options.doneButtonWidget,
                'onClick',
                dojo.hitch(this, this._selectionDone)
            )
        );
    },
    _selectionDone: function() {
        if (this._filterSelectWidget.get('value').length === 0) {
            this._showError();
            return;
        }

        this._hideError();

        this._options.onComplete();
    },
    _hideError: function() {
        this._options.errorContainer.domNode.style.visibility = 'hidden';
    },
    _showError: function() {
        this._options.errorContainer.domNode.style.visibility = 'visible';
    }
});
