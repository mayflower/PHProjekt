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
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */

dojo.provide("phpr.Calendar.ViewMonthList");

dojo.declare("phpr.Calendar.ViewMonthList", phpr.Calendar.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar Month List.
    // Description:
    //    Displaying the list information received from the Server in a HTML table.
    COLOR_OUT_OF_MONTH: '#DEDFDE',
    COLOR_TODAY:        '#DEEBF7',
    COLOR_WEEKDAY:      '#FFFFFF',
    COLOR_WEEKEND:      '#EFEFEF',

    updateData:function(id, startDate, endDate, newItem) {
        // Summary:
        //    Delete all the cache and divs for a new render.
        for (var i in this._internalCacheDates) {
            for (var j in this._internalCacheDates[i]) {
                for (var k in this._internalCacheDates[i][j]) {
                    for (var row = 0; row < 6; row++) {
                        var id = 'content_' + row + '_ ' + this._internalCacheDates[i][j][k].date + '-Calendar';
                        if (dojo.byId(id)) {
                            dojo.destroy(dojo.byId(id));
                        }
                    }
                }
            }
        }
        this._internalCacheDates = [];

        var url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonPeriodList/dateStart/';
        phpr.DataStore.deleteDataPartialString({url: url});
    },

    /************* Private functions *************/

    _constructor:function() {
        // Summary:
        //    Define the current view name.
        this._view = 'month';
    },

    _initStructure:function() {
        // Summary:
        //    Fills the schedule array with the basic structure and data of every day of the calendar month table.
        //    It includes not only the days of this month but the necessary days of the previous and next month in
        //    order to fill 4 or 6 week rows, from Monday to Sunday.
        if (!this._internalCacheDates[this._cacheIndex]) {
            var today = new Date();
            today     = phpr.Date.getIsoDate(today);

            // First dimension is each row shown, the amount of rows depends on each month:
            this._schedule  = [];
            var dateTemp    = phpr.Date.isoDateTojsDate(this._date);
            var daysInMonth = dojo.date.getDaysInMonth(dateTemp);
            dateTemp.setDate(1);
            var firstDayDiff = dateTemp.getDay() - 1;
            if (firstDayDiff == -1) {
                firstDayDiff = 6;
            }
            var firstDayShown = dojo.date.add(dateTemp, 'day', - firstDayDiff);
            dateTemp.setDate(daysInMonth);
            var lastDayShown = dojo.date.add(dateTemp, 'day', (7 - dateTemp.getDay()));
            var totalRows    = (dojo.date.difference(firstDayShown, lastDayShown, 'day') + 1) / 7;

            // For every row
            for (var i = 0; i < totalRows; i ++) {
                this._schedule[i] = new Array(7);
                // For every day of the week
                for (var j = 0; j < 7; j ++) {
                    this._schedule[i][j]         = new Array();
                    dateTemp                     = dojo.date.add(firstDayShown, 'day', (i * 7) + j);
                    this._schedule[i][j]['day']  = dateTemp.getDate();
                    this._schedule[i][j]['date'] = phpr.Date.getIsoDate(dateTemp);
                    if (this._schedule[i][j]['date'] == today) {
                        this._schedule[i][j]['color'] = this.COLOR_TODAY;
                    } else if (((i == 0) && (this._schedule[i][j]['day'] > 22))
                        || ((i > 3) && (this._schedule[i][j]['day'] < 7))) {
                        this._schedule[i][j]['color'] = this.COLOR_OUT_OF_MONTH;
                    } else if (j < 5) {
                        this._schedule[i][j]['color'] = this.COLOR_WEEKDAY;
                    } else {
                        this._schedule[i][j]['color'] = this.COLOR_WEEKEND;
                    }
                }
            }
        }
    },

    _setUrl:function() {
        // Summary:
        //    Sets the url to get the data from.
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonPeriodList/dateStart/'
            + this._schedule[0][0]['date'] + '/dateEnd/' + this._schedule[this._schedule.length - 1][6]['date'];
    },

    _getScheduleBarContent:function() {
        // Summary:
        //    Returns the string for show in the bar.
        var date        = phpr.Date.isoDateTojsDate(this._date);
        var months      = dojo.date.locale.getNames('months', 'wide');
        var description = months[date.getMonth()];

        return description.slice(0, 1).toUpperCase() + description.slice(1) + ', ' + date.getFullYear();
    },

    _fillHeaderArray:function() {
        // Summary:
        //    Fills the header array with the main row of the table.
        if (!this._header['days']) {
            this._header['columnsWidth'] = Math.floor((100 - this._widthHourColumn) / 7);
            this._header['days']         = [];
            this._header['days'][0]      = phpr.nls.get('Monday');
            this._header['days'][1]      = phpr.nls.get('Tuesday');
            this._header['days'][2]      = phpr.nls.get('Wednesday');
            this._header['days'][3]      = phpr.nls.get('Thursday');
            this._header['days'][4]      = phpr.nls.get('Friday');
            this._header['days'][5]      = phpr.nls.get('Saturday');
            this._header['days'][6]      = phpr.nls.get('Sunday');
        }
    },

    _fillDataArray:function(content) {
        // Summary:
        //    Puts every event in the corresponding array position.
        if (!this._internalCacheDates[this._cacheIndex]) {
            for (var i in content) {
                // Split datetime in date and time
                var dateTime            = phpr.Date.isoDatetimeTojsDate(content[i]['startDatetime']);
                content[i]['startDate'] = phpr.Date.getIsoDate(dateTime);
                content[i]['startTime'] = phpr.Date.getIsoTime(dateTime);
                dateTime                = phpr.Date.isoDatetimeTojsDate(content[i]['endDatetime']);
                content[i]['endDate']   = phpr.Date.getIsoDate(dateTime);
                content[i]['endTime']   = phpr.Date.getIsoTime(dateTime);

                for (var row in this._schedule) {
                    for (weekDay in this._schedule[row]) {
                        var event = this._getEventInfo(content[i], this._schedule[row][weekDay]['date']);
                        if (event['range'] == this.SHOWN_INSIDE_CHART) {
                            if (typeof(this._schedule[row][weekDay]['events']) == 'undefined') {
                                this._schedule[row][weekDay]['events'] = [];
                            }
                            var nextEvent    = this._schedule[row][weekDay]['events'].length;
                            var contentTitle = content[i]['title'];
                            this._schedule[row][weekDay]['events'][nextEvent]          = [];
                            this._schedule[row][weekDay]['events'][nextEvent]['id']    = content[i]['id'];
                            this._schedule[row][weekDay]['events'][nextEvent]['title'] = this._htmlEntities(contentTitle);
                            this._schedule[row][weekDay]['events'][nextEvent]['time']  = event['time'];
                        }
                    }
                }
            }

            this._internalCacheDates[this._cacheIndex] = this._schedule;
        } else {
            this._schedule = this._internalCacheDates[this._cacheIndex];
        }
    },

    _getEventInfo:function(event, momentAskedString) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        var result          = [];          // The variable that will be returned
        var eventStartTime  = new Date();  // Date and time the event starts
        var eventEndTime    = new Date();  // Date and time the event ends
        var momentAskedDate = new Date();  // momentAsked (with or without time)
        var eventStartDay   = new Date();  // Just the year/month/day of the event start
        var eventEndDay     = new Date();  // Just the year/month/day of the event end

        // Convert strings variables into date ones
        eventStartDay    = phpr.Date.isoDateTojsDate(event['startDate']);
        eventEndDay      = phpr.Date.isoDateTojsDate(event['endDate']);
        momentAskedDate  = phpr.Date.isoDateTojsDate(momentAskedString);

        // Has the event to be shown for the day received (momentAskedDate)?
        if ((dojo.date.compare(eventStartDay, momentAskedDate) <= 0)
            && (dojo.date.compare(eventEndDay, momentAskedDate) >= 0)) {
            // Yes
            result['range'] = this.SHOWN_INSIDE_CHART;
            eventStartTime  = phpr.Date.isoTimeTojsDate(event['startTime']);
            eventEndTime    = phpr.Date.isoTimeTojsDate(event['endTime']);

            // Time description
            if ((dojo.date.compare(eventStartDay, momentAskedDate) < 0)
                && (dojo.date.compare(eventEndDay, momentAskedDate) > 0)) {
                result['time'] = this._eventDateTimeDescrip(this.DATETIME_MULTIDAY_MIDDLE);
            } else if (dojo.date.compare(eventEndDay, momentAskedDate) > 0) {
                result['time'] = this._eventDateTimeDescrip(this.DATETIME_MULTIDAY_START, event['startTime']);
            } else if (dojo.date.compare(eventStartDay, momentAskedDate) < 0) {
                result['time'] = this._eventDateTimeDescrip(this.DATETIME_MULTIDAY_END, null, event['endTime']);
            } else {
                result['time'] = this._eventDateTimeDescrip(this.DATETIME_SHORT, event['startTime'], event['endTime']);
            }
        } else {
            // No
            result['range'] = this.SHOWN_NOT;
        }

        return result;
    },

    _renderStructure:function() {
        // Summary:
        //    Create and show the main table for the month view.
        if (dijit.byId(this._view + 'Box-Calendar').getChildren().length == 0) {
            var div       = document.createElement('div');
            div.className = 'calendarSchedule';

            var table         = document.createElement('table');
            table.id          = this._view + 'table-Calendar';
            table.style.width = this._widthTable + '%';

            // Headers
            var tr        = table.insertRow(table.rows.length);
            var cellIndex = 0;
            for (var i in this._header['days']) {
                var td = tr.insertCell(cellIndex);
                cellIndex++;
                td.style.width = this._header['columnsWidth'] + '%';
                td.className   = 'weekDayHeaders';
                td.innerHTML   = this._header['days'][i];
            }

            div.appendChild(table);
            dijit.byId(this._view + 'Box-Calendar').set('content', div);

            // Create basic structure
            for (var i = 0; i < 6; i++) {
                this._createRow(i);
            }

            // Line
            var tr = table.insertRow(table.rows.length);
            var td = tr.insertCell(0);
            td.setAttribute('colspan', 7);
        } else {
            var table = dojo.byId(this._view + 'table-Calendar');
        }

        var indexRow = 0;

        if (this._schedule.length == 6) {
            // Month with 6 rows
            dojo.byId('trMonth_5-Calendar').style.display = (dojo.isIE) ? 'block' : 'table-row';
        } else {
            // Month with 5 rows => Hide the 6th
            dojo.byId('trMonth_5-Calendar').style.display = 'none';
        }

        // Hide all the other nodes
        dojo.query('.dateNodes', table).forEach(function(ele) {
            ele.style.display = 'none';
        });

        for (var i in this._schedule) {
            var indexCol = 0;
            for (var j in this._schedule[i]) {
                // Get the td
                var td = dojo.byId('tdMonth_' + indexRow + '_' + indexCol + '-Calendar');

                // Set the color
                td.style.backgroundColor = this._schedule[i][j].color;

                // Set the date
                td.children[0].innerHTML = this._schedule[i][j].day + '&nbsp;';
                td.children[0].setAttribute('internalDate', this._schedule[i][j].date);

                // Set the content with events if there is any
                if (this._schedule[i][j].events) {
                    var contentId = 'content_' + indexRow + '_ ' + this._schedule[i][j].date + '-Calendar';
                    if (!dojo.byId(contentId)) {
                        var node       = document.createElement('div');
                        node.id        = contentId;
                        node.className = 'dateNodes';
                        td.appendChild(node);

                        for (var k in this._schedule[i][j].events) {
                            var txt = document.createTextNode(this._schedule[i][j].events[k].time + ' ');
                            node.appendChild(txt);

                            var number = document.createElement('a');
                            number.setAttribute('href', 'javascript:void(0)');
                            dojo.connect(number, 'onclick', dojo.hitch(this, '_setUrlHash',
                                this._schedule[i][j].events[k].id));
                            number.innerHTML = this._schedule[i][j].events[k].title;
                            node.appendChild(number);

                            node.appendChild(document.createElement('br'));
                        }
                    } else {
                        dojo.byId(contentId).style.display = 'inline';
                    }
                }
                indexCol++;
            }
            indexRow++;
        }
    },

    _createRow:function(indexRow) {
        // Summary:
        //    Create a month row.
        var table     = dojo.byId(this.getDivId('table'));
        var tr        = table.insertRow(table.rows.length);
        tr.id         = 'trMonth_' + indexRow + '-Calendar';
        var cellIndex = 0;
        for (var indexCol = 0; indexCol < 7; indexCol++) {
            var td       = tr.insertCell(cellIndex);
            td.className = 'monthEvents';
            td.id        = 'tdMonth_' + indexRow + '_' + indexCol + '-Calendar';
            cellIndex++;

            var number = document.createElement('a');
            number.setAttribute('href', 'javascript:void(0)');
            dojo.connect(number, 'onclick',
                dojo.hitch(this, '_openDayView', indexRow + '_' + indexCol));
            td.appendChild(number);

            var button = new dijit.form.Button({
                id:        'addMonth_' + indexRow + '_' + indexCol + '-Calendar',
                showLabel: false,
                iconClass: 'add',
                baseClass: 'addButton',
                onClick:   dojo.hitch(this, '_openForm', indexRow + '_' + indexCol)
            });
            td.appendChild(button.domNode);

            td.appendChild(document.createElement('br'));
        }
    },

    _resizeStructure:function() {
        // Summary:
        //    Empty function since this view do not need the function.
    },

    _renderEvents:function() {
        // Summary:
        //    Empty function since this view do not need the function.
    },

    _openDayView:function(index) {
        // Summary:
        //    Get a date and open a day view with these value.
        // Get the td
        var td = dojo.byId('tdMonth_' + index + '-Calendar');
        // Get the day
        var date = td.children[0].getAttribute('internalDate');

        dojo.publish('Calendar.openDayView', [date]);
    },

    _openForm:function(index) {
        // Summary:
        //    Get a date and open a form with these value.
        // Get the td
        var td = dojo.byId('tdMonth_' + index + '-Calendar');
        // Get the date
        var day = parseInt(td.children[0].innerHTML);

        // Set the new date
        var date = phpr.Date.isoDateTojsDate(this._date);
        date.setDate(day);
        var date = phpr.Date.getIsoDate(date);

        // Call the form
        dojo.publish('Calendar.openForm', [null, 'Calendar', date, null]);
    },

    _setUrlHash:function(id) {
        // Summary:
        //    Proxy function for open the form.
        dojo.publish('Calendar.setUrlHash', ['Calendar', id]);
    },

    _exportData:function() {
        // Summary:
        //    Opens a new window in CSV mode
        var dateTemp = phpr.Date.isoDateTojsDate(this._date);
        dateTemp.setDate(1);
        var firstDayMonth = phpr.Date.getIsoDate(dateTemp);
        var daysInMonth   = dojo.date.getDaysInMonth(dateTemp);
        dateTemp.setDate(daysInMonth);
        var lastDayMonth = phpr.Date.getIsoDate(dateTemp);

        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvPeriodList/nodeId/1/dateStart/'
            + firstDayMonth + '/dateEnd/' + lastDayMonth + '/csrfToken/' + phpr.csrfToken);

        return false;
    }
});
