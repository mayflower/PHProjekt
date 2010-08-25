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
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Grid");

dojo.declare("phpr.Timecard.Grid", phpr.Component, {
    main:          null,
    _date:         null,
    _node:         null,
    _month:        null,
    _year:         null,
    _exportButton: null,

    constructor:function(/*Object*/main, /*js Date object */date) {
        // Summary:
        //    Render the list of dates in the month
        // Description:
        //    Render the list of dates in the month
        this.main   = main;
        this._date  = date;
        this._month = date.getMonth();
        this._year  = date.getFullYear();

        this.setUrl();
        this.setNode();

        // Render export Button
        this.setExportButton();

        phpr.DataStore.addStore({url: this.url});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});
    },

    setUrl:function() {
        // Summary:
        //    Set the url for getting the data
        // Description:
        //    Set the url for getting the data
        this.url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonMonthList/year/' + this._year + '/month/'
            + (this._month + 1);
    },

    setNode:function() {
        // Summary:
        //    Set the node to put the grid
        // Description:
        //    Set the node to put the grid
        this._node = dijit.byId("monthView");
    },

    onLoaded:function() {
        // Summary:
        //    Render the list itself
        // Description:
        //    Render the list itself
        var content = phpr.DataStore.getData({url: this.url});
        var total   = 0;

        var dates      = new Array();
        var totalClass = 'weekday';
        for (var i in content) {
            var weekClass = (content[i]['week'] == 0 || content[i]['week'] == 6) ? 'weekend' : 'weekday';
            dates.push({
                week:      phpr.Date.getShortTranslateWeekDay(content[i]['week']),
                weekClass: weekClass,
                date:      content[i]['date'],
                sum:       (content[i]['sumInHours'] != '0') ? content[i]['sumInHours'] : "-",
                sumClass:  (content[i]['openPeriod'] == 1) ? 'open' : weekClass
            });
            if (content[i]['sumInMinutes'] != '0') {
                total += content[i]['sumInMinutes'];
            }
            if (content[i]['openPeriod'] == 1) {
                totalClass = 'open';
            }
        }

        this.render(["phpr.Timecard.template", "monthView.html"], this._node.domNode, {
            monthTxt:   phpr.Date.getLongTranslateMonth(this._month) + ' ' + this._year,
            totalTxt:   phpr.nls.get('Total hours'),
            total:      phpr.Date.convertMinutesToTime(total),
            totalClass: totalClass,
            dates:      dates
        });
        dijit.byId("selectDate").set('value', new Date(this._year, this._month, this._date.getDate()));
    },

    reload:function(date, forceReload) {
        // Summary:
        //    Reload the list if some value change
        // Description:
        //    Reload the list if some value change or forceReload is true
        var newMonth = date.getMonth();
        var newYear  = date.getFullYear();
        if (forceReload || newMonth != this._month || newYear != this._year) {
            phpr.DataStore.deleteData({url: this.url});
            this._date  = date,
            this._month = date.getMonth();
            this._year  = date.getFullYear();
            this.setUrl();
            phpr.DataStore.addStore({url: this.url});
            phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});
        }
    },

    setExportButton:function() {
        // Summary:
        //    Set the export button
        // Description:
        //    Set the export button
        if (this._exportButton === null) {
            var params = {
                label:     phpr.nls.get('Export to CSV'),
                showLabel: true,
                baseClass: "positive",
                iconClass: "export",
                disabled:  false
            };
            this._exportButton = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(this._exportButton.domNode);
            dojo.connect(this._exportButton, "onClick", dojo.hitch(this, "exportData"));
        }
    },

    exportData:function() {
        // summary:
        //    Open a new widnows in CSV mode
        // description:
        //    Export all the bookings of the month
        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvList/nodeId/1/year/'
            + this._year + '/month/' + (this._month + 1) + '/csrfToken/' + phpr.csrfToken);
        return false;
    }
});
