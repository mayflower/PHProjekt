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
 * @category  PHProjekt
 * @package   Template
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 * @link      http://www.phprojekt.com
 * @since     File available since Release 6.0
 * @version   Release: 6.1.0
 * @author    Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Main");

dojo.require("dijit.ColorPalette");

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {
    constructor: function() {
        this.module = "Timecard";
        this.loadFunctions(this.module);

        this.formWidget = phpr.Timecard.Form;
    },

    renderTemplate: function() {
        phpr.viewManager.setView(
            phpr.Default.System.DefaultView,
            phpr.Timecard.ViewContentMixin
        );
    },

    setWidgets: function() {
        this.grid = new phpr.Timecard.GridWidget({
            store: new dojo.store.JsonRest({target: 'index.php/Timecard/Timecard/'})
        });
        phpr.viewManager.getView().gridBox.set('content', this.grid);
        this.addExportButton();
    },

    addExportButton: function() {
        var params = {
            label:     phpr.nls.get('Export to CSV'),
            showLabel: true,
            baseClass: "positive",
            iconClass: "export",
            disabled:  false
        };
        this._exportButton = new dijit.form.Button(params);

        this.garbageCollector.addNode(this._exportButton);

        phpr.viewManager.getView().buttonRow.domNode.appendChild(this._exportButton.domNode);

        this._exportButton.subscribe(
            "timecard/yearMonthChanged",
            dojo.hitch(this, function(year, month) {
                if (this._exportButtonFunction) {
                    dojo.disconnect(this._exportButtonFunction);
                }
                this._exportButtonFunction = dojo.connect(
                    this._exportButton,
                    "onClick",
                    dojo.hitch(this, "exportData", year, month)
                );
            })
        );
    },

    exportData: function(year, month) {
        var start = new Date(year, month, 1),
            end = new Date(year, month + 1, 1);

        var params = {
            csrfToken: phpr.csrfToken,
            format: 'csv',
            filter: dojo.toJson({
                startDatetime: {
                    "!ge": start.toString(),
                    "!lt": end.toString()
                }
            })
        };
        window.open('index.php/Timecard/Timecard/?' + dojo.objectToQuery(params), '_blank');
    }
});
