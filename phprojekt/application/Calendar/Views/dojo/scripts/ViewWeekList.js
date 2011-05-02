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

dojo.provide("phpr.Calendar.ViewWeekList");

dojo.declare("phpr.Calendar.ViewWeekList", phpr.Calendar.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar Week List
    // Description:
    //    Takes care of displaying the list information we receive from our Server in a HTML table.
    _htmlEventDivsAmount: null,
    _weekDays:            [],

    fixLeftPosOnMove:function(originalLeft, leftTop, stepH, posHmax) {
        // Summary:
        //    Fix the left postion of the event.
        var rest = leftTop % stepH;
        if (rest < stepH / 2) {
            var left = leftTop - rest;
        } else {
            var left = leftTop + stepH - rest;
        }
        if (left < 0) {
            left = 0;
        } else if (left > posHmax) {
            left = posHmax;
        }
        return parseInt(left);
    },

    updateData:function(id, startDate, endDate, newItem) {
        // Summary:
        //    Delete the cache for the current id/date and the url.
        this.inherited(arguments);

        var url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonPeriodList/dateStart/';
        phpr.DataStore.deleteDataPartialString({url: url});
    },

    /************* Private functions *************/

    _constructor:function() {
        // Summary:
        //    Define the current view name.
        this._view = 'week';
    },

    _initStructure:function() {
        // Summary:
        //    Fills the weekDays array with all the dates of the selected week in string format.
        if (!this._internalCacheDates[this._cacheIndex]) {
            var selectedDate = phpr.Date.isoDateTojsDate(this._date);
            var dayTemp;

            for (var i = 0; i < 7; i ++) {
                dayTemp           = dojo.date.add(selectedDate, 'day', i + 1 - selectedDate.getDay());
                this._weekDays[i] = phpr.Date.getIsoDate(dayTemp);
            }

            this._internalCacheDates[this._cacheIndex]             = [];
            this._internalCacheDates[this._cacheIndex]['weekDays'] = this._weekDays;

            if (!this._internalCacheDates[this._cacheIndex]['schedule']) {
                for (var hour = 8; hour < 20; hour++) {
                    for (var half = 0; half < 2; half++) {
                        var minute = (half == 0) ? '00' : '30';
                        var row    = ((hour - 8) * 2) + half;

                        var totalColumns    = 7;
                        this._schedule[row] = [];

                        for (var column = 0; column < totalColumns; column ++) {
                            this._schedule[row][column] = [];
                        }

                        this._schedule[row]['hour'] = phpr.Date.getIsoTime(hour + ':' + minute);

                        var tmp = (row / 2);
                        if (Math.floor(tmp) == tmp) {
                            // Even row
                            this._schedule[row]['even'] = true;
                        } else {
                            // Odd row
                            this._schedule[row]['even'] = false;
                        }
                    }
                }
                this._internalCacheDates[this._cacheIndex]['schedule'] = this._schedule;
            }
        } else {
            this._weekDays = this._internalCacheDates[this._cacheIndex]['weekDays'];
            this._schedule = this._internalCacheDates[this._cacheIndex]['schedule'];
        }
    },

    _setUrl:function() {
        // Summary:
        //    Sets the url to get the data from.
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonPeriodList/dateStart/' + this._weekDays[0]
            + '/dateEnd/' + this._weekDays[6];
    },

    _getScheduleBarContent:function() {
        // Summary:
        //    Returns the string for show in the bar.
        var date         = phpr.Date.isoDateTojsDate(this._date);
        var firstDayYear = new Date(date.getFullYear(), 0, 1);
        var week         = Math.ceil((((date - firstDayYear) / 86400000) + firstDayYear.getDay()) / 7);

        return week + ' . ' + phpr.nls.get('Calendar week');
    },

    _fillHeaderArray:function() {
        // Summary:
        //    Fills the header array with the main row of the table.
        if (!this._internalCacheDates[this._cacheIndex]['header']) {
            this._header                 = [];
            this._header['columnsWidth'] = Math.floor((100 - this._widthHourColumn) / 7);
            this._header['days']         = [];
            for (var i = 0; i < 7; i ++) {
                var index                            = (i + 1) < 7 ? i + 1 : 0;
                this._header['days'][i]              = [];
                this._header['days'][i]['dayAbbrev'] = phpr.Date.getShortTranslateWeekDay(index);
                this._header['days'][i]['date']      = this._weekDays[i];
            }
            this._internalCacheDates[this._cacheIndex]['header'] = this._header;
        } else {
            this._header = this._internalCacheDates[this._cacheIndex]['header'];
        }
    },

    _renderView:function(dataContent) {
        // Summary:
        //    Called when the request to the DB is received.
        this.inherited(arguments);

        this._htmlEventDivsAmount = this.events.length;
    },

    _getColumn:function(date) {
        // Summary:
        //    Receives a date like '2009-10-26' and returns the column position number.
        var result;

        for (var i = 0; i < 7; i++) {
            if (this._weekDays[i] == date) {
                result = i;
                break;
            }
        }

        return result;
    },

    _renderStructure:function() {
        // Summary:
        //    Create and show the main table for the week view.
        if (dijit.byId(this._view + 'Box-Calendar').getChildren().length == 0) {
            var contentView       = document.createElement('div');
            contentView.id        = this._view + 'CalendarSchedule-Calendar';
            contentView.className = 'calendarSchedule';

            var table         = document.createElement('table');
            table.id          = this._view + 'table-Calendar';
            table.style.width = this._widthTable + '%';

            // Headers
            var tr         = table.insertRow(table.rows.length);
            var cellIndex  = 0;
            var td         = tr.insertCell(cellIndex);
            td.style.width = this._widthHourColumn + '%';
            cellIndex++;

            for (var i in this._header['days']) {
                var td = tr.insertCell(cellIndex);
                cellIndex++;
                td.id          = 'tdWeek_' + i + '-Calendar';
                td.style.width = this._header['columnsWidth'] + '%';
                td.className   = 'weekDayHeaders';

                var dateTxt = document.createElement('a');
                dateTxt.setAttribute('href', 'javascript:void(0)');
                dojo.connect(dateTxt, 'onclick',
                    dojo.hitch(this, '_openDayView', i));
                td.appendChild(dateTxt);

                var button = new dijit.form.Button({
                    id:        'addWeek_' + i + '-Calendar',
                    showLabel: false,
                    iconClass: 'add',
                    baseClass: 'addButton',
                    onClick:   dojo.hitch(this, '_openForm', i)
                });
                td.appendChild(button.domNode);
            }

            var furtherEvents = document.createElement('div');
            furtherEvents.id  = this._view + 'furtherEvents-Calendar';

            contentView.appendChild(table);
            contentView.appendChild(furtherEvents);
            dijit.byId(this._view + 'Box-Calendar').set('content', contentView);

            // Create basic structure
            for (var i in this._schedule) {
                var tr    = table.insertRow(table.rows.length);
                cellIndex = 0;
                var td    = tr.insertCell(cellIndex);
                cellIndex++;
                td.className = 'hours';
                td.innerHTML = this._schedule[i].hour;
                for (var j = 0; j < 7 ; j++) {
                    var td = tr.insertCell(cellIndex);
                    cellIndex++;
                    td.className = (this._schedule[i].even) ? 'emptyCellEven' : 'emptyCellOdd';
                }
            }

            // Line
            var tr = table.insertRow(table.rows.length);
            var td = tr.insertCell(0);
            td.setAttribute('colspan', 8);
        } else {
            var contentView = dojo.byId(this._view + 'CalendarSchedule-Calendar');
            var table       = dojo.byId('tableWeek-Calendar');
        }

        // Fill the headers with the date
        for (var i in this._header['days']) {
            var td = dojo.byId('tdWeek_' + i + '-Calendar');

            // Set the date
            td.children[0].innerHTML = this._header['days'][i].dayAbbrev + this._header['days'][i].date;
        }

        // Hide all the other events area
        dojo.query('.eventsArea', contentView).forEach(function(ele) {
            ele.style.display = 'none';
        });

        // Create / show the events area for this date
        var eventArea = dojo.byId(this._view + '_eventAreaFor_' + this._date + '-Calendar');
        if (!eventArea) {
            var eventArea       = document.createElement('div');
            eventArea.id        = this._view + '_eventAreaFor_' + this._date + '-Calendar';
            eventArea.className = 'eventsArea';
            dojo.style(eventArea, {'float': 'left', position: 'absolute'});
            contentView.appendChild(eventArea);
            this._resizeStructure();
        } else {
            eventArea.style.display = 'inline';
        }

        // Create events if do not exists yet
        for (var i in this.events) {
            if (!dojo.byId(this._view + '_containerPlainDivFor_' + i + '_' + this._date + '-Calendar')) {
                var event            = document.createElement('div');
                event.id             = this._view + '_containerPlainDivFor_' + i + '_' + this._date + '-Calendar',
                event.className      = 'eventsDivMain';
                event.style.position = 'absolute';
                event.style.overflow = 'hidden';
                eventArea.appendChild(event);

                var plainDiv               = document.createElement('div');
                plainDiv.id                = this._view + '_plainDivFor_' + i + '_' + this._date + '-Calendar',
                plainDiv.className         = 'eventsDivSecond';
                plainDiv.style.borderWidth = this.EVENTS_BORDER_WIDTH + 'px';
                plainDiv.style.cursor      = 'pointer';

                var resize = new phpr.Calendar.ResizeHandle({
                    id:           this._view + '_eventResizeFor_' + i + '_' + this._date + '-Calendar',
                    resizeAxis:   'y',
                    activeResize: true,
                    targetId:     this._view + '_plainDivFor_' + i + '_' + this._date + '-Calendar',
                    style:        'bottom: 0; width: 100%; position: absolute;'
                });
                event.appendChild(plainDiv);
                event.appendChild(resize.domNode);
            }
        }

        // Further events
        var furtherEvents = dojo.byId(this._view + 'furtherEvents-Calendar');
        if (this._furtherEvents.show) {
            furtherEvents.style.display = 'inline';

            var html = phpr.nls.get('Further events') + ':<br />';
            for (var i in this._furtherEvents['events']) {
                html += this._furtherEvents['events'][i].time + ':&nbsp;'
                    + '<a href="javascript: dojo.publish(\'Calendar.setUrlHash\', [\'Calendar\', '
                    + this._furtherEvents['events'][i].id + ']);">' + this._furtherEvents['events'][i].title + '</a>'
                    + '<br />';
            }
            html += '<br />';
            dojo.empty(furtherEvents);
            furtherEvents.innerHTML = html;
        } else {
            furtherEvents.style.display = 'none';
        }
    },

    _setCellTimeAndColumnSize:function() {
        // Summary:
        //    Updates internal class variables with current sizes of schedule.
        var scheduleBkg      = dojo.byId(this.getDivId('table')).getElementsByTagName('td');
        this._cellTimeWidth  = scheduleBkg[0].offsetWidth;
        this.cellColumnWidth = scheduleBkg[1].offsetWidth;

        this._cellHeaderHeight = scheduleBkg[0].offsetHeight;
        this.cellTimeHeight    = scheduleBkg[8].offsetHeight;
    },

    _setStepValues:function() {
        // Summary:
        //     Updates internal class variables with current sizes of schedule.
        this.stepH             = (dojo.byId(this.getDivId('table')).offsetWidth - this._cellTimeWidth) / 7;
        this.stepH             = dojo.number.round(this.stepH, 1);
        this.stepY             = this.cellTimeHeight;
        this.posHMax           = parseInt(dojo.byId(this.getDivId('area')).style.width) - this.stepH;
        this.posYMaxComplement = parseInt(dojo.byId(this.getDivId('area')).style.height);
    },

    _openDayView:function(index) {
        // Summary:
        //    Get a date and open a day view with these value.
        var date = this._weekDays[index];
        // Call the form
        dojo.publish('Calendar.openDayView', [date]);
    },

    _openForm:function(index) {
        // Summary:
        //    Get a date and open a form with these value.
        var date = this._weekDays[index];
        // Call the form
        dojo.publish('Calendar.openForm', [null, 'Calendar', date, null]);
    },

    _toggleMultDaysDivs:function(index, visible) {
        // Summary:
        //    Makes it visible or invisible all the divs of a multiple days event but the one being dragged.
        var id = this.events[index]['id'];
        for (var i in this.events) {
            if (this.events[i] != null && i != index && id == this.events[i]['id']) {
                // This is another div of received event!
                if (!visible) {
                    var mode = 'hidden';
                } else {
                    var mode = 'visible';
                }
                dojo.style(dojo.byId(this.getDivId('container', i)), 'visibility', mode);
            }
        }
    },

    _customValuesForEventMoved:function(posLeftNew, movedEvent) {
        // Summary:
        //   Set extra values on the event.
        var column                = this._divPositionToColumn(posLeftNew);
        movedEvent['currentLeft'] = posLeftNew;
        movedEvent['column']      = column;
        movedEvent['date']        = this._weekDays[column];
    },

    _divPositionToColumn:function(horizontalPos) {
        // Summary:
        //    Receives a number for the corresponding horizontal position in pixels on the schedule and returns a week
        //    column order number.
        var widthDays       = dojo.byId(this.getDivId('table')).offsetWidth - this._cellTimeWidth;
        var cellColumnWidth = widthDays / 7;
        var day             = Math.floor((horizontalPos + (cellColumnWidth / 2)) / cellColumnWidth);

        return day;
    },

    _exportData:function() {
        // Summary:
        //    Opens a new window in CSV mode
        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvPeriodList/nodeId/1/dateStart/'
            + this._weekDays[0] + '/dateEnd/' + this._weekDays[6] + '/csrfToken/' + phpr.csrfToken);

        return false;
    }
});
