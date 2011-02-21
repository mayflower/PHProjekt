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

dojo.provide("phpr.Calendar.ViewDayListSelect");

dojo.declare("phpr.Calendar.ViewDayListSelect", phpr.Calendar.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar Day List for a specific selection of users.
    // Description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table.
    _headerDataUrl: null,
    _userIndex:     null,
    _users:         [],

    updateData:function(id, startDate, endDate, newItem) {
        // Summary:
        //    Delete the cache for the current id/date and the url.
        this.inherited(arguments);

        var url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDayListSelect/date/';
        phpr.DataStore.deleteDataPartialString({url: url});
    },

    /************* Private functions *************/

    _constructor:function() {
        // Summary:
        //    Define the current view name.
        this._view = 'daySelect';
    },

    init:function(date, users, needResize) {
        // Summary:
        //    Render the schedule table.
        // Description:
        //    Call also the user description before call for the data.
        if (needResize) {
            this.resizeLayout();
        }
        this._users     = users;
        var users       = this._users.join(',');
        this._headerDataUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetSpecificUsers/users/' + users;
        phpr.DataStore.addStore({url: this._headerDataUrl});
        phpr.DataStore.requestData({url: this._headerDataUrl, processData: dojo.hitch(this, function() {
            this._date       = date;
            this._cacheIndex = this._date + '_' + this._users.join('_');
            this._initStructure();
            this._setUrl();

            var barContent = this._getScheduleBarContent();
            dijit.byId(this._view + 'ScheduleBarDate-Calendar').set('content', '&nbsp;' + barContent + '&nbsp;');

            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, '_renderView')});
        })});
    },

    _initStructure:function() {
        // Summary:
        //    Fills the weekDays array with all the dates of the selected week in string format.
        if (!this._internalCacheDates[this._cacheIndex]) {
            this._internalCacheDates[this._cacheIndex] = [];
        }
        if (this._schedule.length == 0) {
            for (var hour = 8; hour < 20; hour++) {
                for (var half = 0; half < 2; half++) {
                    var minute = (half == 0) ? '00' : '30';
                    var row    = ((hour - 8) * 2) + half;

                    this._schedule[row]         = [];
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
        }
    },

    _setUrl:function() {
        // Summary:
        //    Sets the url to get the data from.
        var users = this._users.join(',');
        this._url  = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDayListSelect/date/' + this._date
            + '/users/' + users;
    },

    _getScheduleBarContent:function() {
        // Summary:
        //    Returns the string for show in the bar.
        var date        = phpr.Date.isoDateTojsDate(this._date);
        var days        = dojo.date.locale.getNames('days', 'wide');
        var description = days[date.getDay()];

        return description.slice(0, 1).toUpperCase() + description.slice(1) + ', ' + this._date;
    },

    _fillHeaderArray:function() {
        // Summary:
        //    Fills the header array with the main row of the table.
        var headerData               = phpr.DataStore.getData({url: this._headerDataUrl});
        this._header                 = [];
        this._header['columnsWidth'] = -1;
        this._header['users']        = [];

        for (var user in headerData) {
            var userId  = parseInt(headerData[user]['id']);
            var display = headerData[user]['display'];

            this._header['users'][user]            = [];
            this._header['users'][user]['id']      = userId;
            this._header['users'][user]['display'] = display;
        }
        this._header['columnsWidth'] = Math.floor((100 - this._widthHourColumn) / this._users.length);
    },

    _renderStructure:function() {
        // Summary:
        //    Create and show the main table for the daySelect view.
        if (dojo.byId(this._view + 'Box-Calendar').children.length == 0) {
            var contentView       = document.createElement('div');
            contentView.id        = this._view + 'CalendarSchedule-Calendar';
            contentView.className = 'calendarSchedule';
            dijit.byId(this._view + 'Box-Calendar').set('content', contentView);
        } else {
            var contentView = dojo.byId(this._view + 'CalendarSchedule-Calendar');
        }

        // Hide all the other tables
        dojo.query('.tableArea', contentView).forEach(function(ele) {
            ele.style.display = 'none';
        });

        // Tables
        var table = dojo.byId(this._view + 'tableFor_' + this._cacheIndex + '-Calendar');
        if (!table) {
            var table         = document.createElement('table');
            table.id          = this._view + 'tableFor_' + this._cacheIndex + '-Calendar';
            table.className   = 'tableArea';
            table.style.width = this._widthTable + '%';

            contentView.appendChild(table);

            // Headers
            var tr         = table.insertRow(table.rows.length);
            var cellIndex  = 0;
            var td         = tr.insertCell(cellIndex);
            td.style.width = this._widthHourColumn + '%';
            cellIndex++;
            for (var i in this._header['users']) {
                var td = tr.insertCell(cellIndex);
                cellIndex++;
                td.style.width = this._header['columnsWidth'] + '%';
                td.className   = 'weekDayHeaders';
                td.innerHTML   = this._header['users'][i].display;
            }

            // Create basic structure
            var headerData  = phpr.DataStore.getData({url: this._headerDataUrl});
            for (var i in this._schedule) {
                cellIndex      = 0;
                var tr         = table.insertRow(table.rows.length);
                var td         = tr.insertCell(cellIndex);
                td.className   = 'hours';
                td.style.width = this._widthHourColumn + '%';
                td.innerHTML   = this._schedule[i].hour;
                cellIndex++;

                for (var user in headerData) {
                    var td       = tr.insertCell(cellIndex);
                    td.className = (this._schedule[i].even) ? 'emptyCellEven' : 'emptyCellOdd';
                    cellIndex++;
                }
            }

            // Line
            var tr = table.insertRow(table.rows.length);
            var td = tr.insertCell(0);
            td.setAttribute('colspan', headerData.length + 1);
        } else {
            table.style.display = (dojo.isIE == 7) ? 'block' : 'table';
        }

        // Hide all the other events area
        dojo.query('.eventsArea', contentView).forEach(function(ele) {
            ele.style.display = 'none';
        });

        // Create / show the events area for this date
        var eventArea = dojo.byId(this._view + '_eventAreaFor_' + this._cacheIndex + '-Calendar');
        if (!eventArea) {
            var eventArea       = document.createElement('div');
            eventArea.id        = this._view + '_eventAreaFor_' + this._cacheIndex + '-Calendar';
            eventArea.className = 'eventsArea';
            dojo.style(eventArea, {'float': 'left', position: 'absolute'});
            contentView.appendChild(eventArea);
            this._resizeStructure();
        } else {
            eventArea.style.display = 'inline';
        }

        // Create events if do not exists yet
        for (var i in this.events) {
            if (!dojo.byId(this._view + '_containerPlainDivFor_' + i + '_' + this._cacheIndex + '-Calendar')) {
                var event            = document.createElement('div');
                event.id             = this._view + '_containerPlainDivFor_' + i + '_' + this._cacheIndex + '-Calendar',
                event.className      = 'eventsDivMain';
                event.style.position = 'absolute';
                event.style.overflow = 'hidden';
                eventArea.appendChild(event);

                var plainDiv               = document.createElement('div');
                plainDiv.id                = this._view + '_plainDivFor_' + i + '_' + this._cacheIndex + '-Calendar',
                plainDiv.className         = 'eventsDivSecond';
                plainDiv.style.borderWidth = this.EVENTS_BORDER_WIDTH + 'px';
                plainDiv.style.cursor      = 'pointer';
                event.appendChild(plainDiv);

                // Add Resize only for the current user
                if (this.events[i].editable) {
                    var resize = new phpr.Calendar.ResizeHandle({
                        id:           this._view + '_eventResizeFor_' + i + '_' + this._cacheIndex + '-Calendar',
                        resizeAxis:   'y',
                        activeResize: true,
                        targetId:     this._view + '_plainDivFor_' + i + '_' + this._cacheIndex + '-Calendar',
                        style:        'bottom: 0; width: 100%; position: absolute;'
                    });
                    event.appendChild(resize.domNode);
                }
            }
        }
    },

    getDivId:function(type, index) {
        // Summary:
        //    Custom function for change the index to use the list of users also.
        switch (type) {
            case 'container':
                return this._view + '_containerPlainDivFor_' + index + '_' + this._cacheIndex + '-Calendar';
                breka;
            case 'resize':
                return this._view + '_eventResizeFor_' + index + '_' + this._cacheIndex + '-Calendar';
                break;
            case 'plain':
                return this._view + '_plainDivFor_' + index + '_' + this._cacheIndex + '-Calendar';
                break;
            case 'area':
                return this._view + '_eventAreaFor_' + this._cacheIndex + '-Calendar';
                break;
            case 'table':
                return this._view + 'tableFor_' + this._cacheIndex + '-Calendar';
                break;
        }
    },

    _setCellTimeAndColumnSize:function() {
        // Summary
        //    Updates internal class variables with current sizes of schedule.
        var scheduleBkg      = dojo.byId(this.getDivId('table')).getElementsByTagName('td');
        this._cellTimeWidth  = scheduleBkg[0].offsetWidth;
        this.cellColumnWidth = scheduleBkg[1].offsetWidth;

        this._cellHeaderHeight = scheduleBkg[0].offsetHeight;
        this.cellTimeHeight    = scheduleBkg[this._users.length + 1].offsetHeight;
    },

    _setStepValues:function() {
        // Summary
        //     Updates internal class variables with current sizes of schedule.
        this.stepH = (dojo.byId(this.getDivId('table')).offsetWidth - this._cellTimeWidth) / this._users.length;

        var eventAreaId        = this.getDivId('area');
        this.stepH             = dojo.number.round(this.stepH, 1);
        this.stepY             = this.cellTimeHeight;
        this.posHMax           = parseInt(dojo.byId(eventAreaId).style.width) - this.stepH;
        this.posYMaxComplement = parseInt(dojo.byId(eventAreaId).style.height);
    },

    _customColumnToDivPosition:function(widthColumns, column) {
        return column * widthColumns / this._users.length;
    },

    _getUserColumnPosition:function(userId) {
        // Summary:
        //    Receives the id of a user and returns the number for the column it occupies in the header array.
        for (var i = 0; i < this._header['users'].length; i ++) {
            if (this._header['users'][i]['id'] == userId) {
                return i;
            }
        }
    },

    _exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        var users = this._users.join(',');
        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvDayListSelect/nodeId/1/date/' + this._date
            + '/users/' + users + '/csrfToken/' + phpr.csrfToken);

        return false;
    }
});
