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
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Grid");

dojo.require("dijit.form.Button");

dojo.declare("phpr.Timecard.Grid", phpr.Default.System.Component, {
    main: null,
    _date: null,
    _node: null,
    _month: null,
    _monthView: null,
    _year: null,
    _exportButton: null,

    constructor: function(/*Object*/main, /*js Date object */date) {
        // Summary:
        //    Render the list of dates in the month
        // Description:
        //    Render the list of dates in the month
        this.main = main;
        this._date = date;
        this._month = date.getMonth();
        this._year = date.getFullYear();

        this.setUrl();
        this.setContainer();

        // Render export Button
        this.setExportButton();

        phpr.DataStore.addStore({url: this.url});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});
    },

    destroy: function() {
        this._monthView = null;
        this._exportButton = null;
        this._node = null;
        this.inherited(arguments);
    },

    setUrl: function() {
        // Summary:
        //    Set the url for getting the data
        // Description:
        //    Set the url for getting the data
        this.url = 'index.php/' + phpr.module + '/index/jsonMonthList/year/' + this._year +
            '/month/' + (this._month + 1);
    },

    setContainer: function() {
        // Summary:
        //    Set the node to put the grid
        // Description:
        //    Set the node to put the grid
        this._node = this.main._contentWidget.monthView;
    },

    onLoaded: function(reqData) {
        // Summary:
        //    Render the list itself
        // Description:
        //    Render the list itself

        if (this._destroyed === false) {
            var content = reqData.data;
            var total = 0;

            var totalClass = 'weekday';
            var entries = [];
            for (var i in content) {
                var weekClass = (content[i].week === 0 || content[i].week === 6) ? 'weekend' : 'weekday';
                if (content[i].sumInMinutes != '0') {
                    total += content[i].sumInMinutes;
                }
                if (content[i].openPeriod == 1) {
                    totalClass = 'open';
                }

                var entry = new phpr.Default.System.TemplateWrapper({
                    templateName: "phpr.Timecard.template.monthViewEntry.html",
                    templateData: {
                        week: phpr.date.getShortTranslateWeekDay(content[i].week),
                        weekClass: weekClass,
                        date: content[i].date,
                        sum: (content[i].sumInHours != '0') ? content[i].sumInHours : "-",
                        sumClass: (content[i].openPeriod == 1) ? 'open' : weekClass
                    }
                });

                this.garbageCollector.addNode(entry);
                this.garbageCollector.addEvent(
                    dojo.connect(
                        entry.domNode,
                        "onclick",
                        dojo.hitch(
                            this.main,
                            "changeDate",
                            phpr.date.isoDateTojsDate(content[i].date)
                        )
                    )
                );

                entries.push(entry);
            }

            this._monthView = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Timecard.template.monthView.html",
                templateData: {
                    monthTxt: phpr.date.getLongTranslateMonth(this._month) + ' ' + this._year,
                    totalTxt: phpr.nls.get('Total hours'),
                    total: phpr.date.convertMinutesToTime(total),
                    totalClass: totalClass
                }
            });

            this.garbageCollector.addNode(this._monthView);

            this._node.set('content', this._monthView);

            var l = entries.length;
            for (var i = 0; i < l; i++) {
                dojo.place(entries[i].domNode, this._monthView.monthViewFooter, "before");
            }

            this.setDate(new Date(this._year, this._month, this._date.getDate()));

            this.garbageCollector.addEvent(
                dojo.connect(
                    this._monthView.selectDateButton, "onClick", dojo.hitch(this,
                        function() {
                            var selectVal = this._monthView.selectDate.get('value');
                            if (selectVal !== null) {
                                this.main.changeDate(selectVal);
                            }
                        })));
        }
    },

    reload: function(date, forceReload) {
        // Summary:
        //    Reload the list if some value change
        // Description:
        //    Reload the list if some value change or forceReload is true
        this.setDate(date);
        var newMonth = date.getMonth();
        var newYear = date.getFullYear();
        if (forceReload || newMonth != this._month || newYear != this._year) {
            phpr.DataStore.deleteData({url: this.url});
            this._date = date;
            this._month = date.getMonth();
            this._year = date.getFullYear();
            this.setUrl();
            phpr.DataStore.addStore({url: this.url});
            phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});
        }
    },

    setExportButton: function() {
        // Summary:
        //    Set the export button
        // Description:
        //    Set the export button
        if (this._exportButton === null) {
            var params = {
                label: phpr.nls.get('Export to CSV'),
                showLabel: true,
                baseClass: "positive",
                iconClass: "export",
                disabled: false
            };
            this._exportButton = new dijit.form.Button(params);
            phpr.viewManager.getView().buttonRow.domNode.appendChild(this._exportButton.domNode);
            dojo.connect(this._exportButton, "onClick", dojo.hitch(this, "exportData"));
        }
    },

    exportData: function() {
        // summary:
        //    Open a new widnows in CSV mode
        // description:
        //    Export all the bookings of the month
        window.open('index.php/' + phpr.module + '/index/csvList/nodeId/1/year/' + this._year +
            '/month/' + (this._month + 1) + '/csrfToken/' + phpr.csrfToken);
        return false;
    },

    setDate: function(date) {
        if (this._monthView && this._monthView.selectDate) {
            this._monthView.selectDate.set('value', new Date(date.getFullYear(), date.getMonth(), date.getDate()));
        }
    }
});
