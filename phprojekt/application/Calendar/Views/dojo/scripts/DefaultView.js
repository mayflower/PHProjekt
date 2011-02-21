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

dojo.provide("phpr.Calendar.DefaultView");
dojo.provide("phpr.Calendar.Moveable");
dojo.provide("phpr.Calendar.ResizeHandle");

dojo.declare("phpr.Calendar.DefaultView", phpr.Render, {
    // Summary:
    //    Parent class for displaying a Calendar Day Based List.
    //    Day based list: it means, not grid but Day List, Week List, etc.
    //    This should be inherited by each respective JS view.
    cellColumnWidth:    null,
    cellTimeHeight:     null,
    posHMax:            null,
    posYMaxComplement:  null,
    stepH:              null,
    stepY:              null,
    events:             [],
    eventClickDisabled: false,

    DATETIME_LONG_MANY_DAYS:    1,
    DATETIME_LONG_ONE_DAY:      0,
    DATETIME_MULTIDAY_END:      5,
    DATETIME_MULTIDAY_MIDDLE:   4,
    DATETIME_MULTIDAY_START:    3,
    DATETIME_SHORT:             2,
    EVENTS_BORDER_WIDTH:        3,
    ROUND_TIME_HALVES_NEXT:     1,
    ROUND_TIME_HALVES_PREVIOUS: 0,
    SCROLL_DELAY:               12,
    SCROLL_DOWN:                -1,
    SCROLL_UP:                  1,
    SHOWN_INSIDE_CHART:         0,
    SHOWN_NOT:                  2,
    SHOWN_OUTSIDE_CHART:        1,
    TYPE_EVENT_END:             1,
    TYPE_EVENT_START:           0,

    _cacheIndex:       null,
    _cellTimeWidth:    null,
    _date:             null,
    _resizeConnection: null,
    _scrollConnection: null,
    _updateUrl:        null,
    _url:              null,
    _view:             null,

    _gridLastScrollTop:   0,
    _scrollDelayed:       0,
    _scrollLastDirection: 0,
    _widthHourColumn:     7,
    _widthTable:          0,

    _furtherEvents:      [],
    _header:             [],
    _internalCacheDates: [],
    _moveables:          [],
    _schedule:           [],

    _dateWheelChanged: false,

    constructor:function() {
        // Summary:
        //    Create a new and unique instance of the view.
        if (dojo.isIE) {
            // This is to avoid a pair of scrollbars that eventually appears (not when first loading)
            this._widthTable = 97;
        } else {
            this._widthTable = 100;
        }

        this._internalCacheDates = [];
        this._moveable           = [];
        this._scrollConnection   = null;
        this._resizeConnection   = null;
        this._dateWheelChanged   = false;
        this._updateUrl          = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;

        this._constructor();
    },

    init:function(date, needResize) {
        // Summary:
        //    Render the schedule table.
        // Description:
        //    Receives the list data from the server and renders the corresponding table.
        this._date       = date;
        this._cacheIndex = this._date;
        this._initStructure();
        this._setUrl();

        var barContent = this._getScheduleBarContent();
        dijit.byId(this._view + 'ScheduleBarDate-Calendar').set('content', '&nbsp;' + barContent + '&nbsp;');

        if (needResize) {
            this._resizeLayout();
        }

        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, '_renderView')});
    },

    timeToDivPosition:function(moment, isEvent, type) {
        // Summary:
        //    Receives a time string and returns a number for the corresponding vertical position in pixels.
        // Parameters:
        //    moment:  string, e.g.: '14:40'.
        //    isEvent: whether the number returned will be used to position an event (not background 'eventsArea' div).
        //    type:    used when isEvent = true, whether we are receiving the start or the end time of the event.
        var tmp     = moment.split(':');
        var hour    = parseInt(tmp[0], 10);
        var minutes = parseInt(tmp[1], 10);

        // Early and/or late events have to start and end inside the schedule
        if (hour < 8) {
            hour    = 8;
            minutes = 0;
        } else if (hour > 19 && !(hour == 20 && minutes == 0)) {
            hour    = 20;
            minutes = 0;
        }

        var row = ((hour - 8) * 2);

        if (type == this.TYPE_EVENT_START || type == undefined) {
            row += Math.floor(minutes / 30);
        } else if (type == this.TYPE_EVENT_END) {
            row += Math.ceil(minutes / 30);
        }

        var position = row * this.cellTimeHeight;

        if (!isEvent) {
            position += this._cellHeaderHeight;
        }

        position = parseInt(position);

        return position;
    },

    columnToDivPosition:function(column, isEvent) {
        // Summary:
        //    Receives a column order number and returns a number for the corresponding horizontal position in pixels.
        // Parameters:
        //    isEvent: whether the number returned will be used to position an event
        //    (not the background 'eventsArea' div).
        var widthColumns = dojo.byId(this.getDivId('table')).offsetWidth - this._cellTimeWidth;

        if (this._view == 'week') {
            if (column > 0) {
                var position = column * widthColumns / 7;
            } else {
                var position = 0;
            }
        } else if (this._view == 'daySelf') {
            var position = 0;
        } else if (this._view == 'daySelect') {
            var position = this._customColumnToDivPosition(widthColumns, column);
        }
        if (!isEvent) {
            position += this._cellTimeWidth;
        }
        position = parseInt(position);

        return position;
    },

    getDivId:function(type, index) {
        // Summary:
        //    Return the id of a div used by the view, with the index and current date.
        switch (type) {
            case 'container':
                return this._view + '_containerPlainDivFor_' + index + '_' + this._date + '-Calendar';
                breka;
            case 'resize':
                return this._view + '_eventResizeFor_' + index + '_' + this._date + '-Calendar';
                break;
            case 'plain':
                return this._view + '_plainDivFor_' + index + '_' + this._date + '-Calendar';
                break;
            case 'area':
                return this._view + '_eventAreaFor_' + this._date + '-Calendar';
                break;
            case 'table':
                return this._view + 'table-Calendar';
                break;
        }
    },

    nodeIdToEventOrder:function(nodeId) {
        // Summary:
        //    Receives the id of a node of an event (the main div) and returns a number corresponding to the
        //    corresponding index in the events array.
        var str   = this._view + '_' + 'containerPlainDivFor_';
        var pos   = str.length;
        var event = nodeId.substr(pos);
        event     = parseInt(event);

        return event;
    },

    fixLeftPosOnMove:function(originalLeft, leftTop, stepH, posHmax) {
        // Summary:
        //    Custom function that allow each view to fix the left postion.
        return originalLeft;
    },

    eventMoved:function(node, dropped, resized) {
        // Summary:
        //    Called when an event is moved: both dragged or Y-resized,
        //    both in the mouse cursor dragging and when mouse button is released.
        //    Its purpose is to eventually update an internal array, the event description,
        //    change shapes of events according to 'simultaneous events' criteria and activate Save button.
        // Parameters:
        //    node:     the div node of the moved event.
        //    dropped: (boolean) whether the mouse button was released, so the dragged actioni has been finished.
        //    resized: (boolean) whether the event has just been resized (not moved).

        // 1 - Put div in the front of stack
        this._putDivInTheFront(node);

        // 2 - Define some variables
        var posLeftNew       = parseInt(node.style.left);
        var posTopNew        = parseInt(node.style.top);
        var posBottomNew     = posTopNew + node.offsetHeight;
        var movedEventIndex  = this.nodeIdToEventOrder(node.id);
        var movedEvent       = this.events[movedEventIndex];
        var posTopCurrent    = movedEvent['currentTop'];
        var posBottomCurrent = movedEvent['currentBottom'];
        var posLeftCurrent   = movedEvent['currentLeft'];
        var dragged          = !resized;

        // 3 - If div is being moved (not resized) and the div corresponds to a multiple days event then make
        // temporarily invisible the rest of days of this event. Also save original coordinates.
        if (movedEvent['multDay'] && dragged && !dropped && !movedEvent['multDayDragging']) {
            this._toggleMultDaysDivs(movedEventIndex, false);
            movedEvent['multDayDragging']      = true;
            movedEvent['multDayDateOrig']      = movedEvent['date'];
            movedEvent['multDayStartTimeOrig'] = movedEvent['startTime'];
        }

        // 4 - Time and day changes
        if (dragged) {
            // If event was moved (not resized), then attend the start time change
            // Start Time did change?
            if (posTopNew != posTopCurrent) {
                var startTime             = this._divPositionToTime(posTopNew);
                startTime                 = this._formatTime(startTime);
                movedEvent['currentTop']  = posTopNew;
                movedEvent['startTime']   = startTime;
            }

            // Day did change?
            if (posLeftNew != posLeftCurrent) {
                this._customValuesForEventMoved(posLeftNew, movedEvent);
            }
        }
        // End Time did change?
        if (posBottomNew != posBottomCurrent) {
            var endTime                 = this._divPositionToTime(posBottomNew);
            endTime                     = this._formatTime(endTime);
            movedEvent['currentBottom'] = posBottomNew;
            movedEvent['endTime']       = endTime;
        }
        // Fill remaining values, if any, for event description update:
        if (startTime == null) {
            var startTime = movedEvent['startTime'];
        }
        if (endTime == null) {
            var endTime = movedEvent['endTime'];
        }

        // 5 - Is it a multiple days event?
        if (movedEvent['multDay']) {
            if (resized) {
                // The event was resized, update end time inside multiple days event data of main div
                var parent                                    = movedEvent['multDayParent'];
                this.events[parent]['multDayData']['endTime'] = endTime;
            }
        }

        // 6 - The event was dropped?
        var saveData = false;
        if (dropped) {
            var posEventCurrent = movedEvent['date'] + '-' + movedEvent['startTime'] + '-' + movedEvent['endTime'];
            // The event was dropped in a different location than the one saved in the DB?
            if (posEventCurrent != movedEvent['posEventDB']) {
                // Yes
                this.events[movedEventIndex]['hasChanged'] = true;
                saveData = true;
                // Update value
                this.events[movedEventIndex]['posEventDB'] = posEventCurrent;
            } else {
                this.events[movedEventIndex]['hasChanged'] = false;
            }

            // The dropped event was being dragged (not resized) and it was a multiple days event?
            if (dragged && movedEvent['multDay']) {
                // Yes - Update the position and sizes of the rest of divs of this event
                this._updateMultDaysEvent(movedEventIndex);
            }

            if (saveData) {
                // Save date here after the multiple days event was updated for get the new values
                this._saveChanges();
            }
        }

        // 7 - Update event textual contents
        // Is it a multiple days event?
        if (!movedEvent['multDay']) {
            // No
            var timeDescrip = this._eventDateTimeDescrip(this.DATETIME_SHORT, startTime, endTime);
            this.events[movedEventIndex]['timeDescrip'] = timeDescrip;
        } else {
            // Yes
            if (!dropped) {
                if (resized) {
                    var timeDescrip = this._eventDateTimeDescrip(this.DATETIME_MULTIDAY_END, startTime, endTime);
                } else {
                    var timeDescrip = this._eventDateTimeDescrip(movedEvent['multDayPos'], movedEvent['startTime'],
                        movedEvent['endTime']);
                }
                this.events[movedEventIndex]['timeDescrip'] = timeDescrip;
            }
        }

        // 8 - Make changes on screen
        if (!dropped) {
            // Update description of moved or resized event
            var eventDescrip = timeDescrip + ' ' + movedEvent['title'] + '<br />' + movedEvent['notes'];
            dojo.byId(this.getDivId('plain', movedEventIndex)).innerHTML = eventDescrip;
        } else {
            // Update concurrent events internal values just in case, and update divs on screen
            this._updateSimultEventWidths();
            this._renderEvents();
        }
    },

    updateData:function(id, startDate, endDate, newItem) {
        // Summary:
        //    Delete the cache for the current id/date.
        // Description:
        //    For new items or changes in the grid, delete all the cache.
        if (this._internalCacheDates) {
            if (!id && !startDate && !endDate && !newItem) {
                // Come from the grid, just update all
                for (var date in this._internalCacheDates) {
                    this._deleteCacheForDate(date);
                }
            } else {
                // Come from the form
                var foundDate = null;
                // Search the last date for this id
                for (var date in this._internalCacheDates) {
                    if (this._internalCacheDates[date] && this._internalCacheDates[date]['events']) {
                        for (var i in this._internalCacheDates[date]['events']) {
                            if (this._internalCacheDates[date]['events'][i].id == id) {
                                var foundDate = date;
                                break;
                            }
                        }
                    }
                }
                // Opdate cache of the date found
                if (foundDate) {
                    this._deleteCacheForDate(foundDate);
                }

                // Update cache for the new startDate and endDate
                // and all the dates between
                var startDate    = dojo.date.stamp.fromISOString(startDate);
                var endDate      = dojo.date.stamp.fromISOString(endDate);
                var amountDates  = dojo.date.difference(startDate, endDate) + 1;
                var date         = startDate;

                // For each resulting day
                for (var i = 0; i < amountDates; i++) {
                    var oneDay = phpr.Date.getIsoDate(dojo.date.add(date, 'day', i));
                    this._deleteCacheForDate(oneDay);
                }

                if (newItem) {
                    // New item => delete all
                    for (var date in this._internalCacheDates) {
                        this._deleteCacheForDate(date);
                    }
                }
            }
        }
    },

    /************* Private functions *************/

    _constructor:function() {
        // Summary:
        //    Custom constructor.
    },

    _initStructure:function() {
        // Summary:
        //    Custom function for fills the schedule array with the basic structure and data.
    },

    _setUrl:function() {
        // Summary:
        //    Custom function for set the url to get the data from.
    },

    _getScheduleBarContent:function() {
        // Summary:
        //    Custom function for set the content of the schedule bar.
    },

    _renderView:function() {
        // Summary:
        //    Called when the request to the DB is received.
        // Description:
        //    It parses that json info and prepares an apropriate array so that the template can render
        //    appropriately the TABLE html element.
        var meta    = phpr.DataStore.getMetaData({url: this._url});
        var content = phpr.DataStore.getData({url: this._url});

        // Render export Button?
        this._setExportButton(meta);

        // Fill the headers
        this._fillHeaderArray();

        // Fill the main array with the data of the events
        this._fillDataArray(content);

        // Render the view
        this._renderStructure();

        // Show events
        this._renderEvents();

        // Connect only one time the scroll and resize events
        this._connectMouseScroll();

        // Draw the tags
        this._showTags();
    },

    _setExportButton:function(meta) {
        // Summary:
        //    If there is any row, render an export Button.
        var button = dijit.byId('exportCsvButton' + this._view + '-Calendar');
        if (!button) {
            var params = {
                id:        'exportCsvButton' + this._view + '-Calendar',
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

    _exportData:function() {
        // Summary:
        //    Opens a new window in CSV mode
    },

    _fillHeaderArray:function() {
        // Summary:
        //    Fills the header array with the main row of the table.
    },

    _fillDataArray:function(content) {
        // Summary:
        //    Parses and analyses 'content' contents and puts every event in 'events' array,
        //    if there are any multiple days event,
        //    they get splitted into each day events with a connection among them.
        if (!this._internalCacheDates[this._cacheIndex]['events']) {
            this.events                 = [];
            furtherEventsTemp           = [];
            furtherEventsTemp['show']   = false;
            furtherEventsTemp['events'] = [];

            // For each event received from the DB
            for (var event in content) {
                var eventsInfo     = [];
                var id             = content[event]['id'];
                var singleDayEvent = false;

                // Split datetime in date and time
                var dateTime                = phpr.Date.isoDatetimeTojsDate(content[event]['startDatetime']);
                content[event]['startDate'] = phpr.Date.getIsoDate(dateTime);
                content[event]['startTime'] = phpr.Date.getIsoTime(dateTime);
                dateTime                    = phpr.Date.isoDatetimeTojsDate(content[event]['endDatetime']);
                content[event]['endDate']   = phpr.Date.getIsoDate(dateTime);
                content[event]['endTime']   = phpr.Date.getIsoTime(dateTime);

                // Process title and note
                var title = this._htmlEntities(content[event]['title']);
                var notes = this._htmlEntities(content[event]['notes']);
                notes = notes.replace('\n', '<br />');

                if (this._view == 'daySelect') {
                    var column = this._getUserColumnPosition(content[event]['participantId']);
                } else {
                    var column = null;
                }

                // What kind of event is this one concerning multiple day events?
                if (content[event]['startDate'] == content[event]['endDate']) {
                    // Single day event
                    singleDayEvent = true;
                } else {
                    // Multiple days event
                    if (this._view == 'daySelf' || this._view == 'daySelect') {
                        var onlyDayString = this._date;
                    } else {
                        var onlyDayString = null;
                    }
                    var eventsSplitted = this._splitMultDayEvent(content[event]['startDate'], content[event]['startTime'],
                        content[event]['endDate'], content[event]['endTime'], onlyDayString);

                    // The event has at least 1 minute inside the 8:00 to 20:00 grid?
                    if (eventsSplitted['eventShownInGrid']) {
                        // Yes - It uses one or more day columns.
                        // For each day column (it can't be used 'for (var i in eventsSplitted)':
                        for (var i = 0; i < eventsSplitted.length; i++) {
                            var eventSplitted = eventsSplitted[i];
                            if (eventSplitted['dayShownInGrid']) {
                                // Obtain more info
                                eventSplitted['multDay']    = true;
                                eventsInfo[i]               = this._getEventInfo(eventSplitted);
                                eventsInfo[i]['multDayPos'] = eventSplitted['multDayPos'];
                                eventsInfo[i]['shown']      = eventSplitted['shown'];
                                if (eventSplitted['multDayPos'] == this.DATETIME_MULTIDAY_END) {
                                    eventsInfo[i]['hasResizeHandler'] = true;
                                } else {
                                    eventsInfo[i]['hasResizeHandler'] = false;
                                }
                            }
                        }
                    } else {
                        // No - Show it as an out-of-schedule event
                        singleDayEvent = true;
                    }
                }

                if (singleDayEvent) {
                    var eventInfo          = content[event];
                    eventInfo['multDay']   = false;
                    eventsInfo[0]          = this._getEventInfo(content[event]);
                    eventsInfo[0]['shown'] = true;
                }

                // Fill the 'events' class array
                var parent = -1;
                for (var i in eventsInfo) {
                    var eventInfo = eventsInfo[i];
                    // Events inside the grid
                    if (eventInfo['range'] == this.SHOWN_INSIDE_CHART) {
                        eventInfo['hasChanged'] = false;
                        parent = this._addGridEventToArray(eventInfo, id, title, notes, parent,
                            content[event]['startDate'], content[event]['startTime'], content[event]['endDate'],
                            content[event]['endTime'], column);
                    } else if (eventInfo['range'] == this.SHOWN_OUTSIDE_CHART) {
                        // Events outside the grid: located under it as textual strings
                        furtherEventsTemp['show'] = true;
                        var nextPosition          = furtherEventsTemp['events'].length;

                        furtherEventsTemp['events'][nextPosition]          = [];
                        furtherEventsTemp['events'][nextPosition]['id']    = id;
                        furtherEventsTemp['events'][nextPosition]['time']  = eventInfo['timeDescrip'];
                        furtherEventsTemp['events'][nextPosition]['title'] = title;
                    }
                }
            }

            this._updateSimultEventWidths();

            // Clean the repeated 'further events'. Copy the rest to the global variable
            this._furtherEvents = [];
            if (furtherEventsTemp['show']) {
                this._furtherEvents['events'] = [];
                for (var event in furtherEventsTemp['events']) {
                    var repeated = false;
                    for (var i in this._furtherEvents['events']) {
                        if (this._furtherEvents['events'][i]['id'] == furtherEventsTemp['events'][event]['id']) {
                            repeated = true;
                            break;
                        }
                    }
                    if (!repeated) {
                        this._furtherEvents['show']                       = true;
                        var nextEvent                                     = this._furtherEvents['events'].length;
                        this._furtherEvents['events'][nextEvent]          = [];
                        this._furtherEvents['events'][nextEvent]['id']    = furtherEventsTemp['events'][event]['id'];
                        this._furtherEvents['events'][nextEvent]['time']  = furtherEventsTemp['events'][event]['time'];
                        this._furtherEvents['events'][nextEvent]['title'] = furtherEventsTemp['events'][event]['title'];
                    }
                }
            }

            this._internalCacheDates[this._cacheIndex]['events']        = this.events
            this._internalCacheDates[this._cacheIndex]['furtherEvents'] = this._furtherEvents;
        } else {
            this.events         = this._internalCacheDates[this._cacheIndex]['events'];
            this._furtherEvents = this._internalCacheDates[this._cacheIndex]['furtherEvents'];
        }
    },

    _htmlEntities:function(str) {
        // Summary:
        //    Converts HTML tags and code to readable HTML entities.
        // Description:
        //    Example: receives 'This is a <note>' and returns 'This is a &#60;note&#62;'
        var output    = '';
        var character = '';

        for (var i = 0; i < str.length; i++) {
            character = str.charCodeAt(i);

            if (character == 10 ||
                character == 13 ||
                (character > 47 && character < 58) ||
                (character > 62 && character < 127)) {
                output += str.charAt(i);
            } else {
                output += "&#" + str.charCodeAt(i) + ";";
            }
        }

        return output;
    },

    _eventDateTimeDescrip:function(mode, startTime, endTime, startDate, endDate) {
        // Summary:
        //    Creates the appropriate datetime event description according the requested mode
        var description;
        switch (mode) {
            case this.DATETIME_LONG_MANY_DAYS:
                description = startDate + ' &nbsp;' + this._formatTime(startTime) + ' &nbsp;- &nbsp;' + endDate
                    + ' &nbsp;' + this._formatTime(endTime);
                break;
            case this.DATETIME_SHORT:
                description = this._formatTime(startTime) + ' - ' +  this._formatTime(endTime);
                break;
            case this.DATETIME_MULTIDAY_START:
                description = this._formatTime(startTime) + ' -->';
                break;
            case this.DATETIME_MULTIDAY_MIDDLE:
                description = '<-->';
                break;
            case this.DATETIME_MULTIDAY_END:
                description = '<-- ' + this._formatTime(endTime);
                break;
            case this.DATETIME_LONG_ONE_DAY:
            default:
                description = startDate + ' &nbsp;' + this._formatTime(startTime) + ' - ' + this._formatTime(endTime);
                break;
        }

        return description;
    },

    _formatTime:function(time) {
        // Summary:
        //    Formats a time string.
        // Description:
        //    E.g. receives '9:40:00' and returns '09:40', or receives '8:5' and returns '08:05'.
        var temp    = time.split(':');
        var hour    = temp[0];
        var minutes = temp[1];
        var result  = dojo.number.format(hour, {pattern: '00'}) + ':' + dojo.number.format(minutes, {pattern: '00'});

        return result;
    },

    _splitMultDayEvent:function(startDateString, startTimeString, endDateString, endTimeString, onlyDayString) {
        // Summary:
        //    INSIDE WEEK VIEW: Splits a multiple days event into as many events as days it lasts and sets to each one
        //    dates and times. If a day is out of present week, it is not returned.
        //    INSIDE DAY VIEW: trims the event returning just the data for the selected day.
        //    It also checkes whether the event has at least 1 minute to be shown inside the grid. If not,
        //    then it has to be shown under the grid in 'Further events' section.
        var startDate    = dojo.date.stamp.fromISOString(startDateString);
        var endDate      = dojo.date.stamp.fromISOString(endDateString);
        var amountEvents = dojo.date.difference(startDate, endDate) + 1;
        var events       = [];

        if (this._view == 'week') {
            var monday = dojo.date.stamp.fromISOString(this._weekDays[0]);
            var sunday = dojo.date.stamp.fromISOString(this._weekDays[6]);
        }

        // Whether the event has to show at least 1 minute inside the grid, if not, it will be shown under the grid in
        // 'Further events' section.
        events['eventShownInGrid'] = false;

        // For each resulting day
        for (var i = 0; i < amountEvents; i++) {
            var oneDay = dojo.date.add(startDate, 'day', i);
            // If the first day starts after (or equal to) 20:00 then don't show it
            if ((i == 0) && (this._getMinutesDiff(startTimeString, '20:00') <= 0)) {
                continue;
            }
            // If last day starts after (or equal) to 8:00 then don't show it
            if ((i == amountEvents - 1) && (this._getMinutesDiff(endTimeString, '8:00') >= 0)) {
                continue;
            }

            var oneDayString = dojo.date.stamp.toISOString(oneDay);
            oneDayString     = oneDayString.substr(0, 10);

            // DAY VIEWS: Are we on a Day view and this day is not the selected one?
            if (onlyDayString != null && onlyDayString != oneDayString) {
                // Skip this day
                continue;
            }

            var nextPos                  = events.length;
            events[nextPos]              = [];
            events[nextPos]['startDate'] = oneDayString;
            events[nextPos]['shown']     = true;

            if (this._view == 'week') {
                // Is this day inside the selected week?
                if (!((dojo.date.compare(oneDay, monday) >= 0) && (dojo.date.compare(oneDay, sunday) <= 0))) {
                    // No
                    events[nextPos]['shown'] = false;
                }
            }

            // Whether this day has a part to be shown in the day column from 8:00 to 20:00.
            events[nextPos]['dayShownInGrid'] = false;

            // Set times
            if (i == 0) {
                // First day
                events[nextPos]['startTime']  = startTimeString;
                events[nextPos]['endTime']    = '20:00';
                events[nextPos]['multDayPos'] = this.DATETIME_MULTIDAY_START;
                // This day has to be shown inside the grid?
                var tmp  = startTimeString.split(':');
                var hour = parseInt(tmp[0], 10);
                if (hour < 20) {
                    events[nextPos]['dayShownInGrid'] = true;
                    events['eventShownInGrid']        = true;
                }
            } else {
                // Between second and last day
                events[nextPos]['startTime'] = '8:00';
                if (events[nextPos]['startDate'] == endDateString) {
                    // Last day
                    events[nextPos]['endTime']    = endTimeString;
                    events[nextPos]['multDayPos'] = this.DATETIME_MULTIDAY_END;
                    // This day has to be shown inside the grid?
                    var tmp     = endTimeString.split(':');
                    var hour    = parseInt(tmp[0], 10);
                    var minutes = parseInt(tmp[1], 10);

                    if (hour > 8 || (hour == 8 && minutes != 0)) {
                        events[nextPos]['dayShownInGrid'] = true;
                        events['eventShownInGrid']        = true;
                    }
                } else {
                    // Between second and penultimate day
                    events[nextPos]['endTime']        = '20:00';
                    events[nextPos]['multDayPos']     = this.DATETIME_MULTIDAY_MIDDLE;
                    events[nextPos]['dayShownInGrid'] = true;
                    events['eventShownInGrid']        = true;
                }
            }
        }

        return events;
    },

    _getMinutesDiff:function(time1, time2) {
        // Summary:
        //    Receives 2 times in string mode and returns a number with the difference in minutes
        var tmp      = time1.split(':');
        var hour1    = parseInt(tmp[0], 10);
        var minutes1 = parseInt(tmp[1], 10);
        tmp          = time2.split(':');
        var hour2    = parseInt(tmp[0], 10);
        var minutes2 = parseInt(tmp[1], 10);
        var date1    = new Date();
        var date2    = new Date();
        date1.setHours(hour1, minutes1, 0, 0);
        date2.setHours(hour2, minutes2, 0, 0);

        var diff = dojo.date.difference(date1, date2, 'minute');

        return diff;
    },

    _getEventInfo:function(eventInfo) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        // Description:
        //    It returns:
        //    1) Whether it is inside or outside the 8:00 to 20:00 range.
        //    2) Time description for the event.
        //    3) Formatted start and end times.
        //    4) Date and whether it is a multi-day event.
        var result = new Array(); // The variable that will be returned

        // Times have to be rounded according the vertical time schedule divisions
        var startTimeRounded = this._roundTimeByHourHalves(eventInfo['startTime'], this.ROUND_TIME_HALVES_PREVIOUS);
        var endTimeRounded   = this._roundTimeByHourHalves(eventInfo['endTime'], this.ROUND_TIME_HALVES_NEXT);

        var temp              = startTimeRounded.split(':');
        var eventStartHour    = parseInt(temp[0], 10);
        var eventStartMinutes = parseInt(temp[1], 10);
        var temp              = endTimeRounded.split(':');
        var eventEndHour      = parseInt(temp[0], 10);
        var eventEndMinutes   = parseInt(temp[1], 10);
        result['startTime']   = phpr.Date.getIsoTime(eventInfo['startTime']);
        result['endTime']     = phpr.Date.getIsoTime(eventInfo['endTime']);

        // Is at least one minute of the event inside the schedule?
        if (eventStartHour < 20 && ((eventEndHour > 7) && !(eventEndHour == 8 && eventEndMinutes == 0))) {
            // Yes - Show the event inside the schedule
            result['range'] = this.SHOWN_INSIDE_CHART;

            // Date-time description
            // Is it a multiple days event?
            if (!eventInfo['multDay']) {
                // No
                result['timeDescrip'] = this._eventDateTimeDescrip(this.DATETIME_SHORT, result['startTime'],
                    result['endTime']);
            } else {
                // Yes
                result['timeDescrip'] = this._eventDateTimeDescrip(eventInfo['multDayPos'], result['startTime'],
                    result['endTime']);
            }
        } else {
            // No - Shown out of the schedule
            result['range'] = this.SHOWN_OUTSIDE_CHART;
            // Date-time description
            // How many days does the event last?
            if (eventInfo['startDate'] == eventInfo['endDate']) {
                // One day
                result['timeDescrip'] = this._eventDateTimeDescrip(this.DATETIME_LONG_ONE_DAY, result['startTime'],
                    result['endTime'], eventInfo['startDate']);
            } else {
                // More than one day
                result['timeDescrip'] = this._eventDateTimeDescrip(this.DATETIME_LONG_MANY_DAYS, result['startTime'],
                    result['endTime'], eventInfo['startDate'], eventInfo['endDate']);
            }
        }

        result['date']    = eventInfo['startDate'];
        result['multDay'] = eventInfo['multDay'];

        return result;
    },

    _roundTimeByHourHalves:function(timeString, direction) {
        // Summary:
        //     If start minutes are not 0 or 30, round time to previous/next 30 minutes segment start.
        //     E.g.: 13:15 -> 13:00 or 13:30, according to 'direction' value.
        var tmp     = timeString.split(':');
        var hour    = parseInt(tmp[0], 10);
        var minutes = parseInt(tmp[1], 10);

        if (minutes % 30 != 0) {
            if (direction == this.ROUND_TIME_HALVES_PREVIOUS) {
                minutes = Math.floor(minutes / 30) * 30;
            } else if (direction == this.ROUND_TIME_HALVES_NEXT) {
                minutes = Math.ceil(minutes / 30) * 30;
                if (minutes == 60) {
                    minutes = 0;
                    hour++;
                }
            }
        }

        return hour + ':' + minutes;
    },

    _addGridEventToArray:function(event, id, title, notes, parent, wholeStartDate, wholeStartTime, wholeEndDate,
        wholeEndTime, column) {
        // Summary:
        //    Adds an event to 'events' class array.
        //    Returns parent index which is useful just for multiple day events.
        var nextEvent              = this.events.length;
        var newEventDiv            = [];
        newEventDiv['shown']       = event['shown'];
        newEventDiv['editable']    = true;
        newEventDiv['order']       = nextEvent; // For Django template
        newEventDiv['id']          = id;
        newEventDiv['title']       = title;
        newEventDiv['timeDescrip'] = event['timeDescrip'];
        newEventDiv['notes']       = notes;
        newEventDiv['date']        = event['date']
        newEventDiv['startTime']   = event['startTime'];
        newEventDiv['endTime']     = event['endTime'];
        newEventDiv['hasChanged']  = event['hasChanged'];
        newEventDiv['starttup']    = event['starttup'];
        // To check whether the event is pending to be saved - The last position where it was dropped, so if
        // user drags it and leaves it in the same position, it doesn't need to be saved.
        newEventDiv['posEventDB'] = event['date'] + '-' + event['startTime'] + '-' + event['endTime'];

        if (this._view != 'daySelect') {
            newEventDiv['column'] = this._getColumn(event['date']);
        } else {
            newEventDiv['column'] = column;
            // In the 'Selection' mode of the views, only the logged user column events are editable
            if (this._getUserColumnPosition(phpr.currentUserId) != column) {
                newEventDiv['editable'] = false;
            }
        }

        // Multiple day event? Set position among rest of divs of same event, also set if this div has to
        // allow Y resizing.
        newEventDiv['multDay'] = event['multDay'];
        if (event['multDay']) {
            if (parent == -1) {
                var parent = nextEvent;
                newEventDiv['multDayData']              = [];
                newEventDiv['multDayData']['startDate'] = wholeStartDate;
                newEventDiv['multDayData']['startTime'] = wholeStartTime;
                newEventDiv['multDayData']['endDate']   = wholeEndDate;
                newEventDiv['multDayData']['endTime']   = wholeEndTime;
            }
            newEventDiv['multDayParent']    = parent;
            newEventDiv['multDayPos']       = event['multDayPos'];
            newEventDiv['hasResizeHandler'] = event['hasResizeHandler'];
        } else {
            newEventDiv['hasResizeHandler'] = true;
        }
        // Whether this multiple days event is being dragged
        newEventDiv['multDayDragging'] = false;

        // Will be filled later:
        newEventDiv['currentLeft'] = null;
        newEventDiv['currentTop']  = null;

        // Put event div contents into class internal array
        this.events.push(newEventDiv);

        return parent;
    },

    _getUserColumnPosition:function(user) {
        // Summary:
        //    Custom function for get the column of one user.
    },

    _getColumn:function(date) {
        // Summary:
        //    Custom function for get the column of one event.
    },

    _updateSimultEventWidths:function() {
        // Summary:
        //    Checks every event and updates its 'simultaneous' type properties
        for (var i in this.events) {
            if (this.events[i] != null) {
                // parseInt is very important here:
                i = parseInt(i);
                var simultEvents = this._isSharingSpace(i);
                if (simultEvents['sharing']) {
                    this.events[i]['simultWidth']  = true;
                    this.events[i]['simultAmount'] = simultEvents['amountEvents'];
                    this.events[i]['simultOrder']  = simultEvents['order'];
                } else {
                    this.events[i]['simultWidth']  = false;
                }
            }
        }
    },

    _isSharingSpace:function(currentEvent) {
        // Summary:
        //    Returns info about a specific event concerning its possible 'simultaneous' condition:
        //    whether it has to be shown as a simultaneous event and related data.
        // Description:
        //    This function receives an index of this.events and returns whether that event shares visual space with
        //    another event, how many events share space with it and the horizontal position that this event will have
        //    among the rest.
        var result = [];

        // The event shares space with another one?
        result['sharing'] = false;

        // How much events share the same width?
        result['amountEvents'] = 1;

        // What's the order of received event among all sharing space ones?
        result['order'] = 1;

        // Split the event duration into halves
        var halves = this._splitPeriodIntoHalves(this.events[currentEvent]['startTime'],
            this.events[currentEvent]['endTime']);

        // For each half of hour this event occupies:
        for (var half in halves) {
            var eventsAmountForRow = 1;
            var eventOrder         = 1;
            var positionsOccupied  = new Array(4);
            var halfStart          = halves[half]['start'];
            var halfEnd            = halves[half]['end'];
            var storeOrder         = false;

            // For each event...
            for (var otherEvent in this.events) {
                // ...different to the received event...
                if (this.events[otherEvent] != null && otherEvent != currentEvent) {
                    // ...that happens in the same day...
                    if (this.events[currentEvent]['column'] == this.events[otherEvent]['column']) {
                        // ...check whether it shares time with current half of hour of the received event.
                        // Note: if for example an event finishes at 13:15 and the next one starts at 13:20, then, both
                        // events share visually the half of hour that goes from 13:00 to 13:30.

                        // Is this half sharing time with other event?
                        var superimposed = this._eventDivsSuperimposed(halfStart, halfEnd,
                            this.events[otherEvent]['startTime'], this.events[otherEvent]['endTime']);
                        if (superimposed) {
                            result['sharing'] = true;
                            eventsAmountForRow++;
                            if (otherEvent < currentEvent) {
                                storeOrder               = true;
                                var i                    = this.events[otherEvent]['simultOrder'];
                                positionsOccupied[i - 1] = true;
                            }
                        }
                    }
                }
            }
            // Establish new maximum simulteaneous events for any row
            if (eventsAmountForRow > result['amountEvents']) {
                result['amountEvents'] = eventsAmountForRow;
            }

            if (storeOrder && eventsAmountForRow == result['amountEvents']) {
                // Establish the horizontal order for the event among all sharing width ones
                for (var i = 0; i < positionsOccupied.length; i++) {
                    if (positionsOccupied[i] == undefined) {
                        result['order'] = i + 1;
                        break;
                    }
                }
            }
        }

        return result;
    },

    _splitPeriodIntoHalves:function(startTime, endTime) {
        // Summary:
        //    Receives a period of time and returns an array dividing it into halves
        //    of hour with the start and end time for each half.
        // Array to be returned
        var halves = [];

        // Round start and end time into halves of hour
        startTime = this._roundTimeByHourHalves(startTime, this.ROUND_TIME_HALVES_PREVIOUS);
        endTime   = this._roundTimeByHourHalves(endTime, this.ROUND_TIME_HALVES_NEXT);

        // Obtain the length in halves of the period
        var tmp          = startTime.split(':');
        var startHour    = parseInt(tmp[0], 10);
        var startMinutes = parseInt(tmp[1], 10);
        tmp              = endTime.split(':');
        var endHour      = parseInt(tmp[0], 10);
        var endMinutes   = parseInt(tmp[1], 10);
        var diffHours    = endHour - startHour;
        var difMinutes   = endMinutes - startMinutes;
        var totalHalves  = (diffHours * 2) + (difMinutes / 30);

        // Used to calculate each half of hour
        var currentStart = startTime;
        var currentEnd   = null;
        var hour         = startHour;
        var minutes      = startMinutes;

        for (var half = 0; half < totalHalves; half++) {
            if (half > 0) {
                currentStart = currentEnd;
            }

            // Add half-hour to current time end for this half-hour period
            minutes += 30;
            if (minutes == 60) {
                minutes = 0;
                hour   += 1;
            }
            currentEnd            = hour + ':' + minutes;
            halves[half]          = [];
            halves[half]['start'] = currentStart;
            halves[half]['end']   = currentEnd;
        }

        return halves;
    },

    _eventDivsSuperimposed:function(event1Start, event1End, event2Start, event2End) {
        // Summary:
        //    Returns whether the 2 events received are superimposed visually at least on one half of hour.
        var result = false;

        // The schedule time works in hour halves, so the times have to be rounded
        event1Start = this._roundTimeByHourHalves(event1Start, this.ROUND_TIME_HALVES_PREVIOUS);
        event1End   = this._roundTimeByHourHalves(event1End, this.ROUND_TIME_HALVES_NEXT);
        event2Start = this._roundTimeByHourHalves(event2Start, this.ROUND_TIME_HALVES_PREVIOUS);
        event2End   = this._roundTimeByHourHalves(event2End, this.ROUND_TIME_HALVES_NEXT);

        // Both events share at least a half of hour in the schedule?
        if (this._isFirstTimeEarlier(event1Start, event2End) && this._isFirstTimeEarlier(event2Start, event1End)) {
            // Yes
            result = true;
        }

        return result;
    },

    _isFirstTimeEarlier:function(time1, time2) {
        // Summary:
        //    Returns whether the first time is earlier than the second one.
        var result   = false;

        var tmp      = time1.split(':');
        var hour1    = parseInt(tmp[0]);
        var minutes1 = parseInt(tmp[1]);

        tmp          = time2.split(':');
        var hour2    = parseInt(tmp[0]);
        var minutes2 = parseInt(tmp[1]);

        if (hour1 < hour2) {
            result = true;
        } else if (hour1 == hour2 && minutes1 < minutes2) {
            result = true;
        }

        return result;
    },

    _renderStructure:function() {
        // Summary:
        //    Custom function for render the strucutre of the view.
    },

    _resizeStructure:function() {
        // Summary:
        //    Resize the structure for the new values of the parent content.
        var gridBoxWidth      = parseInt(dojo.byId(this._view + 'Box-Calendar').style.width);
        var calendarSchedule  = dojo.byId(this._view + 'CalendarSchedule-Calendar');
        var calenSchedWidth   = parseInt(calendarSchedule.style.width);
        var minCalenSchedSize = 600;

        // Don't allow very small sizes because floating events positioning would start to be imprecise
        if (gridBoxWidth < minCalenSchedSize) {
            if (calenSchedWidth < minCalenSchedSize) {
                dojo.style(calendarSchedule, 'width', minCalenSchedSize + 'px');
            }
        } else if (calenSchedWidth == minCalenSchedSize) {
            dojo.style(calendarSchedule, 'width', '');
        }

        // This is done before everything because when 'eventsArea' div is moved,
        // sometimes it is automatically affected the width of 'scheduleBackground' grid,
        // depending of browser size, at least in FF 3.5.
        dojo.byId(this.getDivId('area')).style.top = '0px';

        this._setCellTimeAndColumnSize();
        this._setEventsAreaDivPosition();
        this._setStepValues();
    },

    _setCellTimeAndColumnSize:function() {
        // Summary:
        //    Custom function for set the current sizes of schedule.
    },

    _setEventsAreaDivPosition:function() {
        // Summary:
        //    Sets/updates the position and size of 'eventsArea' div according to panel and background sizes.
        //    eventsArea is the div where the events will be floating inside.
        var xPos   = this.columnToDivPosition(0, false);
        var yPos   = this.timeToDivPosition('8:00', false);
        var width  = dojo.byId(this.getDivId('table')).offsetWidth - this._cellTimeWidth;
        var height = (this.cellTimeHeight * 24) + 2;

        dojo.style(this.getDivId('area'), {
            left:       '0px',
            top:        yPos + 'px',
            marginLeft: xPos + 'px',
            width:      width + 'px',
            height:     height + 'px'
        });
    },

    _setStepValues:function() {
        // Summary:
        //    Custom function for updates internal variables with current sizes of schedule.
    },

    _renderEvents:function() {
        // Summary:
        //    Size the events and create the DND on them.
        this._sizeEvents();
        this._classesSetup();
    },

    _sizeEvents:function() {
        // Summary:
        //    Sets/updates the position, size and textual contents of each event
        //    according to last updated values in this.events and background sizes.
        for (var i in this.events) {
            var eventDiv1 = dojo.byId(this.getDivId('container', i));
            if (this.events[i] != null && this.events[i]['shown']) {
                var visibility = 'visible';

                if (this._view == 'daySelf') {
                    var column = 0;
                } else {
                    var column = this.events[i]['column'];
                }

                var left   = this.columnToDivPosition(column, true);
                var top    = this.timeToDivPosition(this.events[i]['startTime'], true, this.TYPE_EVENT_START);
                var width  = this.cellColumnWidth - (2 * this.EVENTS_BORDER_WIDTH);
                var bottom = this.timeToDivPosition(this.events[i]['endTime'], true, this.TYPE_EVENT_END);
                var height = bottom - top - (2 * this.EVENTS_BORDER_WIDTH);

                // Is this event part of two or more simulteaneous ones?
                if (this.events[i]['simultWidth']) {
                    // Yes - Reduce its width
                    width = (width / this.events[i]['simultAmount']) - this.EVENTS_BORDER_WIDTH;
                    width = dojo.number.round(width);

                    // Maybe change its left position
                    left += dojo.number.round(this.cellColumnWidth / this.events[i]['simultAmount']
                        * (this.events[i]['simultOrder'] - 1));
                }

                var eventDiv2 = dojo.byId(this.getDivId('plain', i));

                this.events[i]['currentLeft']   = left;
                this.events[i]['currentTop']    = top;
                this.events[i]['currentBottom'] = bottom;

                dojo.style(eventDiv1, {
                    left: left + 'px',
                    top:  top + 'px'
                });
                dojo.style(eventDiv2, {
                    width:  width + 'px',
                    height: height + 'px'
                });

                // Update textual visible contents of event
                var textualContents = this.events[i]['timeDescrip'] + ' ' + this.events[i]['title'] + '<br />' +
                    this.events[i]['notes'];
                eventDiv2.innerHTML = textualContents;
            } else {
                var visibility = 'hidden';
            }

            dojo.style(eventDiv1, 'visibility', visibility);
        }
    },

    _classesSetup:function() {
        // Summary:
        //    Creates dragging classes, provides the dragging and resize classes with a reference object
        //    variable to this class, establishes the cell height as the minimum height for the events.
        for (var i in this.events) {
            if (this.events[i]['editable']) {
                var containerId = this.getDivId('container', i);
                if (!this._moveables[containerId]) {
                    this._moveables[containerId] = new phpr.Calendar.Moveable(containerId, null, this);
                    var resizeDiv                = dijit.byId(this.getDivId('resize', i));
                    resizeDiv.parentClass        = this;
                    // Minimum size:
                    var minWidth      = this.cellColumnWidth - (2 * this.EVENTS_BORDER_WIDTH);
                    var minHeight     = this.cellTimeHeight - (2 * this.EVENTS_BORDER_WIDTH);
                    resizeDiv.minSize = {w: minWidth, h: minHeight};
                }

                if (this.events[i] != null && this.events[i]['shown']) {
                    var resizeDivPlain = dojo.byId(this.getDivId('resize', i));
                    if (this.events[i]['hasResizeHandler']) {
                        var displayMode = 'inline';
                    } else {
                        var displayMode = 'none';
                    }
                    dojo.style(resizeDivPlain, 'display', displayMode);
                }
            }
        }
    },

    _connectMouseScroll:function() {
        // Summary:
        //    Connect the current view onScroll for change the date on it.
        if (!this._scrollConnection) {
            var view = dojo.byId(this._view + 'Box-Calendar');
            this._scrollConnection = dojo.connect(view, (!dojo.isMozilla ? 'onmousewheel' : 'DOMMouseScroll'),
                dojo.hitch(this, '_scrollDone'));
        }
        if (this._dateWheelChanged) {
            this._highlightScheduleBarDate();
            this._dateWheelChanged                           = false;
            dojo.byId(this._view + 'Box-Calendar').scrollTop = 0;
        }
    },

    _showTags:function() {
        // Summary:
        //    Draw the tags.
        // Get the module tags
        var tagUrl = phpr.webpath + 'index.php/Default/Tag/jsonGetTags';
        phpr.DataStore.addStore({url: tagUrl});
        phpr.DataStore.requestData({url: tagUrl, processData: dojo.hitch(this, function() {
            dojo.publish(phpr.module + '.drawTagsBox', [phpr.DataStore.getData({url: tagUrl})]);
        })});
    },

    _highlightScheduleBarDate:function() {
        // Summary:
        //    Highlights the date after it has been changed using the mouse wheel
        dojo.style(dojo.byId(this._view + 'ScheduleBarDate-Calendar'), {
            color:      'white',
            background: 'black'
        });
        dojox.fx.highlight({
            node:     this._view + 'ScheduleBarDate-Calendar',
            color:    '#ffff99',
            duration: 1200
        }).play();
        setTimeout(dojo.hitch(this, '_restoreScheduleBarDate'), 1500);
    },

    _restoreScheduleBarDate:function() {
        // Summary:
        //    Restore the colors after the highlights of the date.
        dojo.style(dojo.byId(this._view + 'ScheduleBarDate-Calendar'), {
            color:      'black',
            background: 'white'
        });
    },

    _scrollDone:function(e) {
        // Summary:
        //    Called whenever the user scrolls the mouse wheel over the grid.
        //    Detects whether to interpret it as a request for changing to previous or next day/week/month grid.
        // Equalize event object
        var evt = window.event || e;
        // Check for detail first so Opera uses that instead of wheelDelta
        var scrollValue = evt.detail ? evt.detail * (-1) : evt.wheelDelta;

        var view = dojo.byId(this._view + 'Box-Calendar');

        // Scrolled UP or DOWN?
        if (scrollValue > 0) {
            // UP - Is this at least the second time user scrolls up, and the grid scrolling space has reached its top?
            if (this._scrollLastDirection == this.SCROLL_UP && this._gridLastScrollTop == view.scrollTop) {
                this._scrollDelayed++;
                // Wait for a specific amount of scroll movements, so that day/week/month change doesn't happen without
                // intention.
                if (this._scrollDelayed >= this.SCROLL_DELAY) {
                    // Delayed 'time' reached, reset variables and go previous day/week/month
                    this._scrollLastDirection = 0;
                    this._scrollDelayed       = 0;
                    this._dateWheelChanged    = true;
                    dojo.publish('Calendar.setDate', [0]);
                }
            } else {
                this._scrollLastDirection = this.SCROLL_UP;
                this._scrollDelayed       = 0;
            }
        } else {
            // DOWN - Is this at least the second time user scrolls up, and the grid scrolling space has reached its
            // bottom?
            if (this._scrollLastDirection == this.SCROLL_DOWN && this._gridLastScrollTop == view.scrollTop) {
                this._scrollDelayed++;
                // Wait for a specific amount of scroll movements, so that day/week/month change doesn't happen without
                // intention.
                if (this._scrollDelayed >= this.SCROLL_DELAY) {
                    // Delayed 'time' reached, reset variables and go next day/week/month
                    this._scrollLastDirection = 0;
                    this._scrollDelayed       = 0;
                    this._dateWheelChanged    = true;
                    dojo.publish('Calendar.setDate', [2])
                }
            } else {
                this._scrollLastDirection = this.SCROLL_DOWN;
                this._scrollDelayed       = 0;
            }
        }
        this._gridLastScrollTop = view.scrollTop;
    },

    _putDivInTheFront:function(node) {
        // Summary:
        //    Prepares the div to be shown in the front of any other event div this one could be dragged over.
        var EVENT_BEHIND = 1;
        var EVENT_FRONT  = 2;

        if (dojo.style(node, 'zIndex') != EVENT_FRONT) {
            var movedEvent = this.nodeIdToEventOrder(node.id);
            for (var i in this.events) {
                if (i != movedEvent) {
                    var eventDiv = dojo.byId(this.getDivId('container', i));
                    dojo.style(eventDiv, 'zIndex', EVENT_BEHIND);
                } else {
                    dojo.style(node, 'zIndex', EVENT_FRONT);
                }
            }
        }
    },

    _toggleMultDaysDivs:function(movedEventIndex, visible) {
        // Summary:
        //    Custom function for hide the multiple events on week.
    },

    _divPositionToTime:function(verticalPos) {
        // Summary:
        //    Receives a schedule position in pixels and returns a time string.
        var row     = Math.floor(verticalPos / this.cellTimeHeight);
        var hour    = 8 + Math.floor(row / 2);
        var minutes = (row % 2) * 30;
        var timeStr = hour + ':' + minutes;

        return timeStr;
    },

    _updateMultDaysEvent:function(index) {
        // Summary:
        //    Updates the date and time of all the divs corresponding to a specific multiple days event that has just
        //    been dragged (and dropped). Current values will get added the difference between the original position
        //    of the moved div and the dropped position. Also it will be prepared saving data.
        //    The moved div itself could get its height increased.
        //    Since new divs can appear after the dragging, or existing divs may dissappear, the whole series of divs
        //    for this event will be calculated again. Old ones will be deleted from 'events' array and new ones
        //    will be added.
        //    In case of Day views, just the moved div will be affected.
        // 1 - Obtain days and minutes difference of the dragged event compared with the original position, prepare
        // other variables
        var parentDiv     = this.events[index]['multDayParent'];
        var startTimeOrig = this.events[index]['multDayStartTimeOrig'];
        if (this._view == 'week') {
            var dateOrig = this.events[index]['multDayDateOrig'];
            dateOrig     = dojo.date.stamp.fromISOString(dateOrig);
            var dateNow  = this.events[index]['date'];
            dateNow      = dojo.date.stamp.fromISOString(dateNow);
            var diffDays = dojo.date.difference(dateOrig, dateNow);
        }
        var diffMinutes = this._getMinutesDiff(startTimeOrig, this.events[index]['startTime']);
        var firstEvent  = null;
        var lastEvent   = null;
        var movedEvent  = this.events[index];

        // 2 - Pick whole event coordinates
        var parent    = this.events[index]['multDayParent'];
        var startDate = this.events[parent]['multDayData']['startDate'];
        var startTime = this.events[parent]['multDayData']['startTime'];
        var endDate   = this.events[parent]['multDayData']['endDate'];
        var endTime   = this.events[parent]['multDayData']['endTime'];
        if (diffMinutes > 0) {
            // If div has been dragged vertically then round the time in halves
            startTime = this._roundTimeByHourHalves(startTime, this.ROUND_TIME_HALVES_PREVIOUS);
            endTime   = this._roundTimeByHourHalves(endTime, this.ROUND_TIME_HALVES_NEXT);
        }
        var column = this.events[index]['column'];

        // 3 - Calculate new whole event coordinates
        var date1   = dojo.date.stamp.fromISOString(startDate);
        var tmp     = startTime.split(':');
        var hour    = parseInt(tmp[0], 10);
        var minutes = parseInt(tmp[1], 10);
        date1.setHours(hour, minutes, 0, 0);
        var date2 = dojo.date.stamp.fromISOString(endDate);
        tmp       = endTime.split(':');
        hour      = parseInt(tmp[0], 10);
        minutes   = parseInt(tmp[1], 10);
        date2.setHours(hour, minutes, 0, 0);

        if (this._view == 'week') {
            date1 = dojo.date.add(date1, 'day', diffDays);
            date2 = dojo.date.add(date2, 'day', diffDays);
        }
        date1 = dojo.date.add(date1, 'minute', diffMinutes);
        date2 = dojo.date.add(date2, 'minute', diffMinutes);
        date1 = dojo.date.stamp.toISOString(date1);
        date2 = dojo.date.stamp.toISOString(date2);
        var wholeStartDate = date1.substr(0, 10);
        var wholeStartTime = date1.substr(11, 5);
        var wholeEndDate   = date2.substr(0, 10);
        var wholeEndTime   = date2.substr(11, 5);

        // 4 - Delete all divs of dragged event from main array
        for (var i in this.events) {
            if (this.events[i] != null && this.events[i]['multDay']) {
                if (this.events[i]['multDayParent'] == parentDiv) {
                    this.events[i] = null;
                }
            }
        }

        // 5 - Generate new this.events elements for this event (one per day shown in the grid)
        if (this._view == 'daySelf' || this._view == 'daySelect') {
            var onlyDayString = this._date;
        } else {
            var onlyDayString = null;
        }
        var eventsSplitted = this._splitMultDayEvent(wholeStartDate, wholeStartTime, wholeEndDate, wholeEndTime,
            onlyDayString);
        var eventsInfo = [];
        var parent     = -1;
        // For each day column (it can't be used 'for (var i in eventsSplitted)'):
        for (var i = 0; i < eventsSplitted.length; i++) {
            var eventSplitted = eventsSplitted[i];
            if (eventSplitted['dayShownInGrid']) {
                // Obtain more info
                eventSplitted['multDay'] = true;
                eventInfo                = this._getEventInfo(eventSplitted);
                eventInfo['multDayPos']  = eventSplitted['multDayPos'];
                eventInfo['shown']       = eventSplitted['shown'];
                if (eventSplitted['multDayPos'] == this.DATETIME_MULTIDAY_END) {
                    eventInfo['hasResizeHandler'] = true;
                } else {
                    eventInfo['hasResizeHandler'] = false;
                }
                eventInfo['hasChanged'] = true;
            }
            parent = this._addGridEventToArray(eventInfo, movedEvent['id'], movedEvent['title'], movedEvent['notes'],
                parent, wholeStartDate, wholeStartTime, wholeEndDate, wholeEndTime, column);
        }
        // Update internal cache
        this._internalCacheDates[this._cacheIndex]['events'] = this.events;
    },

    _updateMultDaysEvent:function(index) {
        // Summary:
        //    Updates the date and time of all the divs corresponding to a specific multiple days event that has just
        //    been dragged (and dropped). Current values will get added the difference between the original position
        //    of the moved div and the dropped position. Also it will be prepared saving data.
        //    The moved div itself could get its height increased.
        //    Since new divs can appear after the dragging, or existing divs may dissappear, the whole series of divs
        //    for this event will be calculated again. Old ones will be deleted from 'events' array and new ones
        //    will be added.
        //    In case of Day views, just the moved div will be affected.
        // 1 - Obtain days and minutes difference of the dragged event compared with the original position, prepare
        // other variables
        var parentDiv     = this.events[index]['multDayParent'];
        var startTimeOrig = this.events[index]['multDayStartTimeOrig'];

        if (this._view == 'week') {
            var dateOrig = this.events[index]['multDayDateOrig'];
            dateOrig     = dojo.date.stamp.fromISOString(dateOrig);
            var dateNow  = this.events[index]['date'];
            dateNow      = dojo.date.stamp.fromISOString(dateNow);
            var diffDays = dojo.date.difference(dateOrig, dateNow);
        }
        var diffMinutes = this._getMinutesDiff(startTimeOrig, this.events[index]['startTime']);
        var firstEvent  = null;
        var lastEvent   = null;
        var movedEvent  = this.events[index];

        // 2 - Pick whole event coordinates
        var parent    = this.events[index]['multDayParent'];
        var startDate = this.events[parent]['multDayData']['startDate'];
        var startTime = this.events[parent]['multDayData']['startTime'];
        var endDate   = this.events[parent]['multDayData']['endDate'];
        var endTime   = this.events[parent]['multDayData']['endTime'];
        if (diffMinutes > 0) {
            // If div has been dragged vertically then round the time in halves
            startTime = this._roundTimeByHourHalves(startTime, this.ROUND_TIME_HALVES_PREVIOUS);
            endTime   = this._roundTimeByHourHalves(endTime, this.ROUND_TIME_HALVES_NEXT);
        }
        var column = this.events[index]['column'];

        // 3 - Calculate new whole event coordinates
        var date1   = dojo.date.stamp.fromISOString(startDate);
        var tmp     = startTime.split(':');
        var hour    = parseInt(tmp[0], 10);
        var minutes = parseInt(tmp[1], 10);
        date1.setHours(hour, minutes, 0, 0);
        var date2 = dojo.date.stamp.fromISOString(endDate);
        tmp       = endTime.split(':');
        hour      = parseInt(tmp[0], 10);
        minutes   = parseInt(tmp[1], 10);
        date2.setHours(hour, minutes, 0, 0);

        if (this._view == 'week') {
            date1 = dojo.date.add(date1, 'day', diffDays);
            date2 = dojo.date.add(date2, 'day', diffDays);
        }
        date1              = dojo.date.add(date1, 'minute', diffMinutes);
        date2              = dojo.date.add(date2, 'minute', diffMinutes);
        date1              = dojo.date.stamp.toISOString(date1);
        date2              = dojo.date.stamp.toISOString(date2);
        var wholeStartDate = date1.substr(0, 10);
        var wholeStartTime = date1.substr(11, 5);
        var wholeEndDate   = date2.substr(0, 10);
        var wholeEndTime   = date2.substr(11, 5);

        // 4 - Delete all divs of dragged event from main array
        for (var i in this.events) {
            if (this.events[i] != null && this.events[i]['multDay']) {
                if (this.events[i]['multDayParent'] == parentDiv) {
                    this.events[i] = null;
                }
            }
        }
        this.events = dojo.filter(this.events, function(item) {
            return item !== null;
        });

        // 5 - Generate new this.events elements for this event (one per day shown in the grid)
        if (this._view == 'daySelf' || this._view == 'daySelect') {
            var onlyDayString = this._date;
        } else {
            var onlyDayString = null;
        }
        var eventsSplitted = this._splitMultDayEvent(wholeStartDate, wholeStartTime, wholeEndDate, wholeEndTime,
            onlyDayString);
        var eventsInfo = [];
        var parent     = -1;
        // For each day column (it can't be used 'for (var i in eventsSplitted)'):
        for (var i = 0; i < eventsSplitted.length; i++) {
            var eventSplitted = eventsSplitted[i];
            if (eventSplitted['dayShownInGrid']) {
                // Obtain more info
                eventSplitted['multDay'] = true;
                eventInfo                = this._getEventInfo(eventSplitted);
                eventInfo['multDayPos']  = eventSplitted['multDayPos'];
                eventInfo['shown']       = eventSplitted['shown'];
                eventInfo['starttup']    = true;
                if (eventSplitted['multDayPos'] == this.DATETIME_MULTIDAY_END) {
                    eventInfo['hasResizeHandler'] = true;
                } else {
                    eventInfo['hasResizeHandler'] = false;
                }
                eventInfo['hasChanged'] = true;
            }
            parent = this._addGridEventToArray(eventInfo, movedEvent['id'], movedEvent['title'], movedEvent['notes'],
                parent, wholeStartDate, wholeStartTime, wholeEndDate, wholeEndTime, column);
        }
        // Update internal cache
        this._internalCacheDates[this._cacheIndex]['events'] = this.events;
    },

    _customValuesForEventMoved:function(posLeftNew, movedEvent) {
        // Summary:
        //   Custom function for set extra values on the event.
    },

    _resizeLayout:function() {
        // Summary:
        //    Resize the parent content for fix the size and resize the structure for the new values.
        // Description:
        //    Resize the structure only if already exists.
        dijit.byId('content-Calendar').resize();

        var area = dojo.byId(this.getDivId('area'));
        if (area) {
            this._resizeStructure();
        }
    },

    _saveChanges:function() {
        // Summary:
        //    Save the change in the server.
        var content  = [];
        var doSaving = false;

        for (var i in this.events) {
            if (this.events[i] != null && this.events[i]['hasChanged']) {
                this.events[i]['hasChanged'] = false;
                doSaving = true;
                var id   = this.events[i]['id'];

                // Is it a multiple days event?
                if (!this.events[i]['multDay']) {
                    // No
                    content['data[' + id + '][startDatetime]'] = this.events[i]['date'] + ' '
                        + this.events[i]['startTime'];
                    content['data[' + id + '][endDatetime]']   = this.events[i]['date'] + ' '
                        + this.events[i]['endTime'];
                } else {
                    // Yes
                    // Obtain the data of the whole event, not just of this div
                    var parent      = this.events[i]['multDayParent'];
                    var multDayData = this.events[parent]['multDayData'];

                    content['data[' + id + '][startDatetime]'] = this.events[i]['date'] + ' '
                        + this.events[i]['startTime']
                    content['data[' + id + '][endDatetime]'] = multDayData['endDate'] + ' '
                        + multDayData['endTime'];
                }
                break;
            }
        }

        if (doSaving) {
            // Post the content of all changed events
            phpr.send({
                url:       this._updateUrl,
                content:   content,
                onSuccess: dojo.hitch(this, function(response) {
                    if (response.type != 'success') {
                        new phpr.handleResponse('serverFeedback', response);
                    } else {
                        if (!this.events[i]['multDay']) {
                            dojo.publish('Calendar.updateCacheDataFromView', [id,
                                this.events[i]['date'], this.events[i]['date'], false]);
                        } else {
                            dojo.publish('Calendar.updateCacheDataFromView', [id,
                                this.events[i]['date'], multDayData['endDate'], false]);
                        }
                    }
                })
            });
        }
    },

    _deleteCacheForDate:function(date) {
        // Summary:
        //    Delete the cache and hide all the events.
        // Description:
        //    If one was deleted, now is hidden.
        //    All the others will recover the visibility later.
        if (this._internalCacheDates[date] && this._internalCacheDates[date]['events']) {
            for (var i in this._internalCacheDates[date]['events']) {
                var id = this.getDivId('container', i);
                if (dojo.byId(id)) {
                    dojo.byId(id).style.visibility = 'hidden';
                }
            }
            this._internalCacheDates[date] = null;
        }
    }
});

dojo.declare("phpr.Calendar.Moveable", dojo.dnd.Moveable, {
    // Summary:
    //    Custom Moveable class to use the variables of the view for move the events.
    _eventDivMoved: false,
    _parentClass:   null,

    constructor:function(node, params, parentClass) {
        // Summary:
        //    Add the parent class (current view).
        this._parentClass = parentClass;
    },

    markupFactory:function(params, node){
        // Summary:
        //    Needed by dojo.
        return this;
    },

    onMove:function(mover, leftTop) {
        // Summary:
        //    Original function is empty. This one is in charge of making the 'stepped' allike draging.
        //    Then calls eventMoved function of Calendar view class, if some movement was actually done.
        var movedEventIndex = this._parentClass.nodeIdToEventOrder(this.node.id);
        var movedEvent      = this._parentClass.events[movedEventIndex];
        var stepH           = this._parentClass.stepH;
        var stepY           = this._parentClass.stepY;
        var posHmax         = this._parentClass.posHMax;
        var posYmax         = this._parentClass.posYMaxComplement - this.node.offsetHeight;

        // Store original event position before this dragging attempt
        var originalLeft = this._parentClass.columnToDivPosition(movedEvent['column'], true);
        var originalTop  = this._parentClass.timeToDivPosition(movedEvent['startTime'], true);

        // Following value will be checked by onMoveStop function of this class
        this._parentClass.eventClickDisabled = true;

        // Calculate new left position
        if (movedEvent['simultWidth']) {
            // If event is concurrent and it is not the first one from left to right,
            // attach its left side to column border
            leftTop.l -= stepH / movedEvent['simultAmount'] * (movedEvent['simultOrder'] - 1);
            leftTop.l  = parseInt(leftTop.l);
        }

        // Let each class to fix the left value
        leftTop.l = this._parentClass.fixLeftPosOnMove(originalLeft, leftTop.l, stepH, posHmax)

        // Calculate new top position
        var top = leftTop.t - (leftTop.t % stepY);
        if (top < 0) {
            top = 0;
        } else if (top > posYmax) {
            top = stepY * parseInt(posYmax / stepY);
        }
        leftTop.t = parseInt(top);

        // According to new calculated left and top values, the div will be moved?
        if (originalLeft != leftTop.l || originalTop != leftTop.t) {
            // Yes
            // If the event is a concurrent one, return it to 100% column width
            if (movedEvent['simultWidth']) {
                var eventDivSecond     = dojo.byId(this._parentClass.getDivId('plain', movedEventIndex));
                var eventWidthComplete = this._parentClass.cellColumnWidth - (2 * this._parentClass.EVENTS_BORDER_WIDTH);
                var eventWidthCurrent  = dojo.style(eventDivSecond, 'width');

                if (eventWidthComplete != eventWidthCurrent) {
                    dojo.style(eventDivSecond, 'width', eventWidthComplete + 'px');
                }
            }

            var s  = mover.node.style;
            s.left = leftTop.l + 'px';
            s.top  = leftTop.t + 'px';
            this.onMoved(mover, leftTop);

            // Following value will be checked by onMoveStop function of this class
            this._eventDivMoved = true;

            // Update descriptive content of the event
            this._parentClass.eventMoved(this.node, false);
        }
    },

    onMoveStop:function(mover) {
        // Summary:
        //   Called after every move operation.
        // Original code:
        dojo.publish("/dnd/move/stop", [mover]);
        dojo.removeClass(dojo.body(), 'dojoMove');
        dojo.removeClass(this.node, 'dojoMoveItem');

        // Following code has been added for this view, it calls eventMoved view class function or opens the form with
        // the clicked event.
        if (this._eventDivMoved) {
            // The event has been dragged, update descriptive content of the event and internal array
            this._parentClass.eventMoved(this.node, true);
            // Allow the event to be just clicked to open it in the form, but wait a while first...
            this._eventDivMoved = false;
            setTimeout(dojo.hitch(this, '_enableClick'), 500);
        } else {
            if (!this._parentClass.eventClickDisabled) {
                // It was just a click - Open event in the form
                var movedEvent = this._parentClass.nodeIdToEventOrder(this.node.id);
                var eventId    = this._parentClass.events[movedEvent]['id'];
                dojo.publish('Calendar.setUrlHash', [phpr.module, eventId]);
            }
        }
        this._parentClass.eventClickDisabled = false;
    },

    _enableClick:function() {
        // Summary:
        //   Restore the value after the click was finished.
        this._parentClass.eventClickDisabled = false;
    }
});

dojo.declare("phpr.Calendar.ResizeHandle", dojox.layout.ResizeHandle, {
    // Summary:
    //    Custom ResizeHandle class to use the variables of the view for move the events.
    _changeSizing:function(e) {
        // Summary:
        //    Apply sizing information based on information in (e) to attached node.
        var tmp = this._getNewCoords(e);
        if(tmp === false) {
            return;
        }

        // Stepped dragging added for this views
        var currentHeight  = dojo.style(this.targetDomNode, 'height');
        var step           = this.parentClass.cellTimeHeight;
        var sizerDivHeight = this.domNode.offsetHeight;
        var proposedHeight = tmp['h'];
        var steppedHeight  = sizerDivHeight + proposedHeight - (proposedHeight % step)
            + ((5 - this.parentClass.EVENTS_BORDER_WIDTH) * 2) - 7;

        // Maximum height - Set for the event end time not to be after 20:00
        var maxY      = parseInt(dojo.byId(this.parentClass.getDivId('area')).offsetHeight) + step;
        var eventTopY = parseInt(this.targetDomNode.parentNode.style.top);
        var proposedY = eventTopY + proposedHeight + step + sizerDivHeight;

        // The event bottom border will be moved?
        if (proposedY <= maxY && steppedHeight != currentHeight) {
            tmp['h'] = steppedHeight;

            if (this.targetWidget && dojo.isFunction(this.targetWidget.resize)){
                this.targetWidget.resize(tmp);
            } else {
                if (this.animateSizing){
                    var anim = dojo.fx[this.animateMethod]([
                        dojo.animateProperty({
                            node:       this.targetDomNode,
                            properties: {width: {start: this.startSize.w, end: tmp.w, unit:'px'}},
                            duration:   this.animateDuration
                        }),
                        dojo.animateProperty({
                            node:       this.targetDomNode,
                            properties: {height: {start: this.startSize.h, end: tmp.h, unit:'px'}},
                            duration:   this.animateDuration
                        })
                    ]);
                    anim.play();
                } else {
                    dojo.style(this.targetDomNode, 'height', tmp.h + 'px');
                }
            }
            if (this.intermediateChanges){
                this.onResize(e);
            }

            this.parentClass.eventMoved(this.targetDomNode.parentNode, false, true);
        }
    },

    onResize:function(e) {
        // Summary:
        //    Calls eventMoved calendar view class function.
        //    Stub fired when sizing is done.
        //    Fired once after resize, or often when `intermediateChanges` is set to true.
        this.parentClass.eventMoved(this.targetDomNode.parentNode, true, true);
    }
});
