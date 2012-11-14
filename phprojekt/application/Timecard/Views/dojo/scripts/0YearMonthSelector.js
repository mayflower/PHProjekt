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
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 */
dojo.provide("phpr.Timecard.YearMonthSelector");

dojo.declare("phpr.Timecard.YearMonthSelector", dijit.form.DropDownButton, {
    name: "yearMonthSelector",
    dropDown: null,

    constructor: function() {
        this.dropDown = new dijit.Menu({style: "display: none;"});
    },

    postCreate: function() {
        this.inherited(arguments);

        this.subscribe(
            "timecard/yearMonthChanged",
            dojo.hitch(this, function(year, month) {
                this.set("label", this.getYearMonthLabel(year, month));
            }
        ));

        phpr.get({
            url: "index.php/Timecard/index/yearsAndMonthsWithEntries"
        }).then(dojo.hitch(this, function(response) {
            var entries = response.values;
            entries = dojo.map(entries, function(entry) {
                return {year: entry.year, month: entry.month - 1};
            });
            entries = this.addLastMonths(entries);

            var menu = new dijit.Menu({style: "display: none;"});
            dojo.forEach(entries, dojo.hitch(this, function(entry) {
                menu.addChild(new dijit.MenuItem({
                    label: this.getYearMonthLabel(entry.year, entry.month),
                    onClick: function() {
                        dojo.publish("timecard/yearMonthChanged", [entry.year, entry.month]);
                    }
                }));
            }));

            var today = new Date();
            this.set("label", this.getYearMonthLabel(today.getFullYear(), today.getMonth()));
            this.set("dropDown", menu);

            var today = new Date();
            dojo.publish("timecard/yearMonthChanged", [today.getFullYear(), today.getMonth()]);
        }));
    },

    addLastMonths: function(entries) {
        for (var i = 0; i <= 4; i++) {
            var d = dojo.date.add(new Date(), "month", -i);
            if (!entries[i] || entries[i].month != d.getMonth() || entries[i].year != d.getFullYear()) {
                entries.splice(i, 0, {month: d.getMonth(), year: d.getFullYear()});
            }
        }

        return entries;
    },

    getYearMonthLabel: function(year, month) {
        return year + " " + dojo.date.locale.getNames("months", "wide")[month];
    }
});
