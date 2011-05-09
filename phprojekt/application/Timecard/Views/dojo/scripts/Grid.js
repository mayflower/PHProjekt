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
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Grid");

dojo.declare("phpr.Timecard.Grid", null, {
    _connect: [],
    _date:    null,
    _month:   null,
    _url:     null,
    _year:    null,

    init:function(date, forceReload) {
        // Summary:
        //    Init the values and render the list.
        // Description:
        //    Reload the list if some value change or forceReload is true.
        var reload = false;
        this._date = date;

        if (!this._month && this._year) {
            this._month = date.getMonth();
            this._year  = date.getFullYear();
            reload = true;
        } else {
            var newMonth = date.getMonth();
            var newYear  = date.getFullYear();
            if (forceReload || newMonth != this._month || newYear != this._year) {
                reload = true;
            }
            this._month = newMonth;
            this._year  = newYear;
        }

        // Render export Button
        this._setExportButton();

        if (reload) {
            phpr.DataStore.deleteData({url: this._url});
            this._setUrl();
            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, '_getGridData')});
        }
    },

    /************* Private functions *************/

    _setUrl:function() {
        // Summary:
        //    Set the url for getting the data.
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonMonthList/year/' + this._year + '/month/'
            + (this._month + 1);
    },

    _setExportButton:function(meta) {
        // Summary:
        //    Render an export Button.
        // Add an export button
        var button = dijit.byId('exportCsvButton-Timecard');
        if (!button) {
            var params = {
                id:        'exportCsvButton-Timecard',
                label:     phpr.nls.get('Export to CSV'),
                showLabel: true,
                baseClass: 'positive',
                iconClass: 'export',
                disabled:  false,
                onClick:   dojo.hitch(this, '_exportData')
            };
            var button = new dijit.form.Button(params);
            dojo.byId('buttonRow').appendChild(button.domNode);
        } else {
            dojo.style(button.domNode, 'display', 'inline');
        }
    },

    _getGridData:function() {
        // Summary:
        //    Render the list of days in the month.
        var content = phpr.DataStore.getData({url: this._url});
        var total   = 0;

        var dates      = [];
        var totalClass = 'weekday';
        for (var i in content) {
            var weekClass = (content[i]['week'] == 0 || content[i]['week'] == 6) ? 'weekend' : 'weekday';
            dates.push({
                week:      phpr.Date.getShortTranslateWeekDay(content[i]['week']),
                weekClass: weekClass,
                date:      content[i]['date'],
                sum:       (content[i]['sumInHours'] != '0') ? content[i]['sumInHours'] : '-',
                sumClass:  (content[i]['openPeriod'] == 1) ? 'open' : weekClass
            });
            if (content[i]['sumInMinutes'] != '0') {
                total += content[i]['sumInMinutes'];
            }
            if (content[i]['openPeriod'] == 1) {
                totalClass = 'open';
            }
        }

        var table = dojo.byId('tableMonthView-Timecard');
        if (!table) {
            // Create the table
            var html = phpr.Render.render(['phpr.Timecard.template', 'monthView.html'], null, {
                monthTxt: phpr.Date.getLongTranslateMonth(this._month) + ' ' + this._year
            });
            dijit.byId(this._getNodeId()).set('content', html);
            var table = dojo.byId('tableMonthView-Timecard');
        }

        // Update the month
        dojo.byId('monthTxt-Timecard').innerHTML = phpr.Date.getLongTranslateMonth(this._month);

        // Remove old rows
        dojo.forEach(this._connect, function(link) {
            dojo.disconnect(link);
        });
        this._connect = [];
        dojo.query('.weekend, .weekday, .open', table).forEach(function(ele) {
            dojo.destroy(ele);
        });

        // Just update the rows
        for (var i in dates) {
            var row       = table.insertRow(table.rows.length);
            row.className = dates[i].weekClass;

            var cell             = row.insertCell(0);
            cell.style.textAlign = 'right';
            cell.innerHTML       = dates[i].week;

            var cell = row.insertCell(1);
            var link = document.createElement('a');
            link.setAttribute('href', 'javascript:void(0)');
            this._connect.push(dojo.connect(link, 'onclick', function() {
                var date = phpr.Date.isoDateTojsDate(this.innerHTML);
                dijit.byId('selectDate-Timecard').set('value',
                    new Date(date.getFullYear(), date.getMonth(), date.getDate()));
                dojo.publish('Timecard.changeDate', [date]);
            }));
            link.innerHTML = dates[i].date;
            cell.appendChild(link);

            var cell       = row.insertCell(2);
            cell.className = dates[i].sumClass;
            cell.innerHTML = dates[i].sum;
        }

        var row       = table.insertRow(table.rows.length);
        row.className = totalClass;

        var cell             = row.insertCell(0);
        cell.colSpan         = 2
        cell.style.textAlign = 'right';
        cell.innerHTML       = phpr.nls.get('Total hours');

        var cell        = row.insertCell(1);
        cell.className  = totalClass
        var space       = document.createElement('div');
        space.className = 'timecardSeparator';
        cell.appendChild(space);
        var txt = document.createTextNode(phpr.Date.convertMinutesToTime(total));
        cell.appendChild(txt);

        dijit.byId('selectDate-Timecard').set('value', new Date(this._year, this._month, this._date.getDate()));
    },

    _getNodeId:function() {
        // Summary:
        //    Set the node Id where put the grid.
        return 'monthView-Timecard';
    },

    _exportData:function() {
        // Summary:
        //    Open a new window in CSV mode.
        // Description:
        //    Export all the bookings of the month
        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvList/nodeId/1/year/'
            + this._year + '/month/' + (this._month + 1) + '/csrfToken/' + phpr.csrfToken);

        return false;
    }
});
