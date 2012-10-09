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
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */

dojo.provide("phpr.Calendar2.DefaultView");
dojo.provide("phpr.Calendar2.Moveable");
dojo.provide("phpr.Calendar2.ResizeHandle");

dojo.declare("phpr.Calendar2.DefaultView", phpr.Default.System.Component, {
    // Summary:
    //    Parent class for displaying a Calendar2 Day Based List. Day based list: it means, not grid but Day List,
    //    Week List, etc. This should be inherited by each respective JS view.
    // Description:
    //    This Class provides the basic variables and functions for the class that takes care of displaying the list
    //    information we receive from our Server in a HTML table.
    main:                 null,
    id:                   0,
    url:                  null,
    updateUrl:            null,
    _tagUrl:              null,
    _date:                null,
    _widthTable:          0,
    _widthHourColumn:     7,
    _schedule:            [],
    _cellTimeWidth:       null,
    cellColumnWidth:      null,
    cellTimeHeight:       null,
    _gridBoxWidthPrev:    null,
    _calenSchedWidthPrev: null,
    eventClickDisabled:   false,
    stepH:                null,
    stepY:                null,
    posHMax:              null,
    posYMaxComplement:    null,

    // General constants
    SCHEDULE_START_HOUR: 8,
    SCHEDULE_END_HOUR:   20,

    // Constants used by the function processEventInfo:
    EVENT_TIME_START:    0,
    EVENT_TIME_INSIDE:   1,
    EVENT_TIME_OUTSIDE:  2,
    SHOWN_INSIDE_CHART:  0,
    SHOWN_OUTSIDE_CHART: 1,
    SHOWN_NOT:           2,

    // Constants used to define a calendar event time in comparison to a specific moment:
    EVENT_NONE:      0,
    EVENT_BEGIN:     1,
    EVENT_CONTINUES: 2,

    // Constants used to define the date and time description mode for the event
    DATETIME_LONG_ONE_DAY:    0,
    DATETIME_LONG_MANY_DAYS:  1,
    DATETIME_SHORT:           2,
    DATETIME_MULTIDAY_START:  3,
    DATETIME_MULTIDAY_MIDDLE: 4,
    DATETIME_MULTIDAY_END:    5,

    TYPE_EVENT_START: 0,
    TYPE_EVENT_END:   1,

    EVENTS_BORDER_WIDTH: 3,
    EVENTS_MAIN_DIV_ID:  'containerPlainDiv',

    ROUND_TIME_HALVES_PREVIOUS: 0,
    ROUND_TIME_HALVES_NEXT:     1,

    constructor: function(/*String*/updateUrl, /*Int*/ id, /*Date*/ date, /*Array*/ users, /*Object*/ main) {
        // Summary:
        //    Render the schedule table
        // Description:
        //    This function receives the list data from the server and renders the corresponding table
        this.updateUrl = updateUrl;
        this.main      = main;
        this.id        = id;
        this.url       = null;
        this._date     = date;

        this.beforeConstructor();

        if (dojo.isArray(users)) {
            // Just for the Day group view
            this.users = users;
        }

        this.setUrl();

        if (dojo.isIE) {
            // This is to avoid a pair of scrollbars that eventually appears (not when first loading)
            this._widthTable = 97;
        } else {
            this._widthTable = 100;
        }
        this._widthHourColumn = 7;

        this.afterConstructor();
    },

    beforeConstructor: function() {
        // Summary:
        //    Function called almost at the top of 'constructor' function.
        // Description:
        //    If there is something that must be done before executing the most of sentences of 'constructor' function,
        //    inherit this function and put it inside it.
    },

    setExportButton: function(meta) {
        // Summary:
        //    Sets the export button
        // Description:
        //    If there is any row, render export Button
        if (meta.length > 0 && this._exportButton === undefined) {
            var params = {
                label:     phpr.nls.get('Export to CSV'),
                showLabel: true,
                baseClass: "positive",
                iconClass: "export",
                disabled:  false
            };
            this._exportButton = new dijit.form.Button(params);
            phpr.viewManager.getView().buttonRow.domNode.appendChild(this._exportButton.domNode);
            dojo.connect(this._exportButton, "onClick", dojo.hitch(this, "exportData"));
        }
    },

    processEventInfo: function(eventInfo) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        // Description:
        //    It returns:
        //    1) Whether it is inside or outside the 8:00 to 20:00 range.
        //    2) Time description for the event.
        //    3) Formatted start and end times.
        //    4) Date and whether it is a multi-day event
        var result = [];  // The variable that will be returned

        // Times have to be rounded according the vertical time schedule divisions
        var startTimeRounded = this.roundTimeByHourHalves(eventInfo.startTime, this.ROUND_TIME_HALVES_PREVIOUS);
        var endTimeRounded   = this.roundTimeByHourHalves(eventInfo.endTime, this.ROUND_TIME_HALVES_NEXT);

        var temp              = startTimeRounded.split(':');
        var eventStartHour    = parseInt(temp[0], 10);
        var eventStartMinutes = parseInt(temp[1], 10);
        var temp              = endTimeRounded.split(':');
        var eventEndHour      = parseInt(temp[0], 10);
        var eventEndMinutes   = parseInt(temp[1], 10);
        result.startTime   = phpr.date.getIsoTime(eventInfo.startTime);
        result.endTime     = phpr.date.getIsoTime(eventInfo.endTime);

        // Is at least one minute of the event inside the schedule?
        if (eventStartHour < 20 && ((eventEndHour > 7) && !(eventEndHour == 8 && eventEndMinutes === 0))) {
            // Yes - Show the event inside the schedule
            result.range = this.SHOWN_INSIDE_CHART;

            // Date-time description
            // Is it a multiple days event?
            if (!eventInfo.multDay) {
                // No
                result.timeDescrip = this.eventDateTimeDescrip(this.DATETIME_SHORT, result.startTime,
                    result.endTime);
            } else {
                // Yes
                result.timeDescrip = this.eventDateTimeDescrip(eventInfo.multDayPos, result.startTime,
                    result.endTime);
            }
        } else {
            // No - Shown out of the schedule
            result.range = this.SHOWN_OUTSIDE_CHART;
            // Date-time description
            // How many days does the event last?
            if (eventInfo.startDate == eventInfo.endDate) {
                // One day
                result.timeDescrip = this.eventDateTimeDescrip(this.DATETIME_LONG_ONE_DAY, result.startTime,
                    result.endTime, eventInfo.startDate);
            } else {
                // More than one day
                result.timeDescrip = this.eventDateTimeDescrip(this.DATETIME_LONG_MANY_DAYS, result.startTime,
                    result.endTime, eventInfo.startDate, eventInfo.endDate);
            }
        }

        result.date    = eventInfo.startDate;
        result.multDay = eventInfo.multDay;

        return result;
    },

    formatTime: function(time) {
        // Summary:
        //    Formats a time string. E.g. receives '9:40:00' and returns '09:40', or receives '8:5' and returns '08:05'
        var temp    = time.split(':');
        var hour    = temp[0];
        var minutes = temp[1];
        var result  = dojo.number.format(hour, {pattern: '00'}) + ':' + dojo.number.format(minutes, {pattern: '00'});

        return result;
    },

    updateData: function() {
        // Summary:
        //    Deletes the cache for this List table
        phpr.DataStore.deleteData({url: this.url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    },

    htmlEntities: function(str) {
        // Summary:
        //    Converts HTML tags and code to readable HTML entities
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

    eventDateTimeDescrip: function(mode, startTime, endTime, startDate, endDate) {
        // Summary:
        //    Creates the appropriate datetime event description according the requested mode
        var description;
        switch (mode) {
            case this.DATETIME_LONG_MANY_DAYS:
                description = startDate + ' &nbsp;' + this.formatTime(startTime) + ' &nbsp;- &nbsp;' + endDate +
                    ' &nbsp;' + this.formatTime(endTime);
                break;
            case this.DATETIME_SHORT:
                description = this.formatTime(startTime) + ' - ' +  this.formatTime(endTime);
                break;
            case this.DATETIME_MULTIDAY_START:
                description = this.formatTime(startTime) + ' -->';
                break;
            case this.DATETIME_MULTIDAY_MIDDLE:
                description = '<-->';
                break;
            case this.DATETIME_MULTIDAY_END:
                description = '<-- ' + this.formatTime(endTime);
                break;
            case this.DATETIME_LONG_ONE_DAY:
                /* falls through */
            default:
                description = startDate + ' &nbsp;' + this.formatTime(startTime) + ' - ' + this.formatTime(endTime);
                break;
        }

        return description;
    },

    updateSizeValuesPart1: function() {
        // Summary
        //    Updates internal class variables with current sizes of schedule

        // This is done before everything because when 'eventsArea' div is moved, sometimes it is automatically affected
        // the width of 'scheduleBackground' grid, depending of browser size, at least in FF 3.5.
        var eventsAreaDiv = dojo.byId('eventsArea');
        dojo.style(eventsAreaDiv, 'top', '0px');

        var scheduleBkg      = dojo.byId('scheduleBackground').getElementsByTagName('td');
        this._cellTimeWidth  = scheduleBkg[0].offsetWidth;
        this.cellColumnWidth = scheduleBkg[1].offsetWidth;
        if (this.main.weekList !== null) {
            this._cellHeaderHeight = scheduleBkg[0].offsetHeight;
            this.cellTimeHeight    = scheduleBkg[8].offsetHeight;
        } else if (this.main.dayListSelf !== null) {
            this.cellTimeHeight = scheduleBkg[0].offsetHeight;
        } else if (this.main.dayListSelect !== null) {
            this._cellHeaderHeight = scheduleBkg[0].offsetHeight;
            this.cellTimeHeight    = scheduleBkg[this.users.length + 1].offsetHeight;
        }

        // Ie8 obtains badly cell height - dojo.isIE returns 7 when using 8.0
        var isIe8 = navigator.userAgent.indexOf('MSIE 8');
        if (this.main.dayListSelf !== null && isIe8 > -1) {
            this.cellTimeHeight -= 1;
        }
    },

    updateSizeValuesPart2: function() {
        // Summary
        //    Updates internal class variables with current sizes of schedule
        if (this.main.dayListSelf !== null) {
            this.stepH = dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth;
        } else if (this.main.dayListSelect !== null) {
            this.stepH = (dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth) / this.users.length;
        } else if (this.main.weekList !== null) {
            this.stepH = (dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth) / 7;
        }
        this.stepH             = dojo.number.round(this.stepH, 1);
        this.stepY             = this.cellTimeHeight;
        this.posHMax           = parseInt(dojo.byId('eventsArea').style.width, 10) - this.stepH;
        this.posYMaxComplement = parseInt(dojo.byId('eventsArea').style.height, 10);
    },

    timeToDivPosition: function(moment, isEvent, type) {
        // Summary
        //    Receives a time string and returns a number for the corresponding vertical position in pixels.
        // Parameters:
        //    moment: string, e.g.: '14:40'
        //    isEvent: whether the number returned will be used to position an event (not background 'eventsArea' div)
        //    type: used when isEvent = true, whether we are receiving the start or the end time of the event.
        var tmp     = moment.split(':');
        var hour    = parseInt(tmp[0], 10);
        var minutes = parseInt(tmp[1], 10);

        // Early and/or late events have to start and end inside the schedule
        if (hour < 8) {
            hour    = 8;
            minutes = 0;
        } else if (hour > 19 && !(hour == 20 && minutes === 0)) {
            hour    = 20;
            minutes = 0;
        }

        var row = ((hour - 8) * 2);

        if (type == this.TYPE_EVENT_START || type === undefined) {
            row += Math.floor(minutes / 30);
        } else if (type == this.TYPE_EVENT_END) {
            row += Math.ceil(minutes / 30);
        }
        var position = row * this.cellTimeHeight;
        if (!isEvent && (this.main.dayListSelect !== null || this.main.weekList !== null)) {
            position += this._cellHeaderHeight;
        }
        position = parseInt(position, 10);

        return position;
    },

    columnToDivPosition: function(column, isEvent) {
        // Summary
        //    Receives a column order number and returns a number for the corresponding horizontal position in pixels.
        // Parameters:
        //    isEvent: whether the number returned will be used to position an event
        //    (not the background 'eventsArea' div)
        var widthColumns = dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth;
        var position;
        if (this.main.weekList !== null) {
            position = column * widthColumns / 7;
        } else if (this.main.dayListSelf !== null) {
            position = 0;
        } else if (this.main.dayListSelect !== null) {
            position = column * widthColumns / this.users.length;
        }
        if (!isEvent) {
            position += this._cellTimeWidth;
        }
        position = parseInt(position, 10);

        return position;
    },

    divPositionToTime: function(verticalPos) {
        // Summary
        //    Receives a schedule position in pixels and returns a time string
        var row     = Math.floor(verticalPos / this.cellTimeHeight);
        var hour    = 8 + Math.floor(row / 2);
        var minutes = (row % 2) * 30;
        var timeStr = hour + ':' + minutes;

        return timeStr;
    },

    divPositionToColumn: function(horizontalPos) {
        // Summary
        //    Receives a number for the corresponding horizontal position in pixels on the schedule and returns a week
        //    column order number.
        var widthDays = dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth;
        var cellColumnWidth;
        if (this.main.dayListSelect !== null) {
            cellColumnWidth = widthDays / this.users.length;
        } else if (this.main.weekList !== null) {
            cellColumnWidth = widthDays / 7;
        }
        var day = Math.floor((horizontalPos + (cellColumnWidth / 2)) / cellColumnWidth);

        return day;
    },

    setEventsAreaDivValues: function() {
        // Summary:
        //    Sets / updates the position and size of 'eventsArea' div according to panel and background sizes.
        //    eventsArea is the div where the events will be floating inside.
        var xPos   = this.columnToDivPosition(0, false);
        var yPos   = this.timeToDivPosition('8:00', false);
        var width  = dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth;
        var height = this.cellTimeHeight * 24;

        var eventsAreaDiv = dojo.byId('eventsArea');
        dojo.style(eventsAreaDiv, {
            left:       '0px',
            top:        yPos + 'px',
            marginLeft: xPos + 'px',
            width:      width + 'px',
            height:     height + 'px'
        });
    },

    setEventDivsValues: function() {
        // Summary:
        //    Sets / updates the position and size and textual contents of each event according to last updated values
        //    in this.events and background sizes.
        for (var i in this.events) {
            var eventDiv1 = dojo.byId(this.EVENTS_MAIN_DIV_ID + i);
            var visibility;
            if (this.events[i] !== null && this.events[i].shown) {
                visibility = 'visible';
                var column;
                if (this.main.dayListSelf !== null) {
                    column = 0;
                } else if (this.main.dayListSelect !== null || this.main.weekList !== null) {
                    column = this.events[i].column;
                }
                var left       = this.columnToDivPosition(column, true);
                var top        = this.timeToDivPosition(this.events[i].startTime, true, this.TYPE_EVENT_START);
                var width      = this.cellColumnWidth - (2 * this.EVENTS_BORDER_WIDTH);
                var bottom     = this.timeToDivPosition(this.events[i].endTime, true, this.TYPE_EVENT_END);
                var height     = bottom - top - (2 * this.EVENTS_BORDER_WIDTH);

                // Is this event part of two or more simulteaneous ones?
                if (this.events[i].simultWidth) {
                    // Yes - Reduce its width
                    width = (width / this.events[i].simultAmount) - this.EVENTS_BORDER_WIDTH;
                    width = dojo.number.round(width);

                    // Maybe change its left position
                    left += dojo.number.round(this.cellColumnWidth / this.events[i].simultAmount *
                            (this.events[i].simultOrder - 1));
                }

                var eventDiv2 = dojo.byId('plainDiv' + i);

                this.events[i].currentLeft   = left;
                this.events[i].currentTop    = top;
                this.events[i].currentBottom = bottom;

                dojo.style(eventDiv1, {
                    left:       left + 'px',
                    top:        top + 'px'
                });
                dojo.style(eventDiv2, {
                    width:  width + 'px',
                    height: height + 'px'
                });

                // Update textual visible contents of event
                var textualContents = this.events[i].timeDescrip + ' ' + this.events[i].summary + '<br />' +
                    this.events[i].comments;
                eventDiv2.innerHTML = textualContents;
            } else {
                visibility = 'hidden';
            }

            dojo.style(eventDiv1, 'visibility', visibility);
        }

        if (this.main.weekList !== null) {
            // Any remaining unused html divs?
            var lastIndex = parseInt(i, 10);
            if ((lastIndex + 1) < this._htmlEventDivsAmount) {
                // Yes, hide them
                for (indexToHide = lastIndex + 1; indexToHide < this._htmlEventDivsAmount; indexToHide ++) {
                    var eventDiv1 = dojo.byId(this.EVENTS_MAIN_DIV_ID + indexToHide);
                    dojo.style(eventDiv1, 'visibility', 'hidden');
                }
            }
        }
    },

    classesSetup: function(startup) {
        // Summary:
        //    On startup: creates dragging classes, provides the dragging and resize classes with a reference object
        //    variable to this class, establishes the cell height as the minimum height for the events.
        //    On startup and everytime it is called: activates or inactivates Y resize for each div.
        for (var i in this.events) {
            if (this.events[i].editable) {
                if (startup) {
                    new phpr.Calendar2.Moveable(this.EVENTS_MAIN_DIV_ID + i, null, this);
                    var resizeDiv         = dijit.byId('eventResize' + i);
                    resizeDiv.parentClass = this;
                    // Minimum size:
                    var minWidth      = this.cellColumnWidth - (2 * this.EVENTS_BORDER_WIDTH);
                    var minHeight     = this.cellTimeHeight - (2 * this.EVENTS_BORDER_WIDTH);
                    resizeDiv.minSize = {w: minWidth, h: minHeight};
                }

                if (this.events[i] !== undefined && this.events[i].shown) {
                    var resizeDivPlain = dojo.byId('eventResize' + i);
                    var displayMode;
                    if (this.events[i].hasResizeHandler) {
                        displayMode = 'inline';
                    } else {
                        displayMode = 'none';
                    }
                    dojo.style(resizeDivPlain, 'display', displayMode);
                }
            }
        }
    },

    eventMoved: function(node, dropped, resized) {
        // Summary:
        //    Called when an event is moved: both dragged or Y-resized, both in the mouse cursor dragging and when mouse
        //    button is released. Its purpose is to eventually update an internal array, the event description, change
        //    shapes of events according to 'simultaneous events' criteria and activate Save button.
        // Parameters:
        //    node: the div node of the moved event
        //    dropped: (boolean) whether the mouse button was released, so the dragged actioni has been finished
        //    resized: (boolean) whether the event has just been resized (not moved)

        // 1 - Put div in the front of stack
        this.putDivInTheFront(node);

        // 2 - Define some variables
        var posLeftNew       = parseInt(node.style.left, 10);
        var posTopNew        = parseInt(node.style.top, 10);
        var posBottomNew     = posTopNew + node.offsetHeight;
        var movedEventIndex  = this.nodeIdToEventOrder(node.id);
        var movedEvent       = this.events[movedEventIndex];
        var posTopCurrent    = movedEvent.currentTop;
        var posBottomCurrent = movedEvent.currentBottom;
        var posLeftCurrent   = movedEvent.currentLeft;
        var dragged          = !resized;
        var startTime = null;
        var endTime = null;

        // 3 - If div is being moved (not resized) and the div corresponds to a multiple days event then make
        // temporarily invisible the rest of days of this event. Also save original coordinates.
        if (movedEvent.multDay && dragged && !dropped && !movedEvent.multDayDragging) {
            if (this.main.weekList !== null) {
                this.toggleMultDaysDivs(movedEventIndex, false);
            }
            movedEvent.multDayDragging      = true;
            movedEvent.multDayDateOrig      = movedEvent.date;
            movedEvent.multDayStartTimeOrig = movedEvent.startTime;
        }

        if (this.main.weekList !== null && dragged && dropped) {
            var columnCurrent = movedEvent.column;
        }

        // 4 - Time and day changes
        if (dragged) {
            // If event was moved (not resized), then attend the start time change
            // Start Time did change?
            if (posTopNew != posTopCurrent) {
                startTime             = this.divPositionToTime(posTopNew);
                startTime                 = this.formatTime(startTime);
                movedEvent.currentTop  = posTopNew;
                movedEvent.startTime   = startTime;
            }
            // Day did change?
            if (this.main.weekList !== null && posLeftNew != posLeftCurrent) {
                var column                = this.divPositionToColumn(posLeftNew);
                movedEvent.currentLeft = posLeftNew;
                movedEvent.column      = column;
                movedEvent.date        = this._weekDays[column];
            }
        }
        // End Time did change?
        if (posBottomNew != posBottomCurrent) {
            endTime                 = this.divPositionToTime(posBottomNew);
            endTime                     = this.formatTime(endTime);
            movedEvent.currentBottom = posBottomNew;
            movedEvent.endTime       = endTime;
        }
        // Fill remaining values, if any, for event description update:
        if (startTime === null) {
            startTime = movedEvent.startTime;
        }
        if (endTime === null) {
            endTime = movedEvent.endTime;
        }

        // 5 - Is it a multiple days event?
        if (movedEvent.multDay) {
            if (resized) {
                // The event was resized, update end time inside multiple days event data of main div
                var parent                                    = movedEvent.multDayParent;
                this.events[parent].multDayData.endTime = endTime;
            }
        }

        // 6 - The event was dropped?
        if (dropped) {
            var posEventCurrent = movedEvent.date + '-' + movedEvent.startTime + '-' + movedEvent.endTime;

            // The event was dropped in a different location than the one saved in the DB?
            if (posEventCurrent != movedEvent.posEventDB) {
                // Yes
                this.events[movedEventIndex].hasChanged = true;
                this.saveChanges();
            } else {
                this.events[movedEventIndex].hasChanged = false;
            }

            // The dropped event was being dragged (not resized) and it was a multiple days event?
            if (dragged && movedEvent.multDay) {
                // Yes - Update the position and sizes of the rest of divs of this event
                this.updateMultDaysEvent(movedEventIndex);
            }

            // update events array
            this.events[movedEventIndex].occurrence = movedEvent.date + ' ' + movedEvent.startTime;
        }

        // 7 - Update event textual contents
        // Is it a multiple days event?
        var timeDescrip;
        if (!movedEvent.multDay) {
            // No
            var timeDescrip = this.eventDateTimeDescrip(this.DATETIME_SHORT, startTime, endTime);
            this.events[movedEventIndex].timeDescrip = timeDescrip;
        } else {
            // Yes
            if (!dropped) {
                if (resized) {
                    timeDescrip = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_END, startTime, endTime);
                } else {
                    timeDescrip = this.eventDateTimeDescrip(movedEvent.multDayPos, movedEvent.startTime,
                        movedEvent.endTime);
                }
                this.events[movedEventIndex].timeDescrip = timeDescrip;
            }
        }

        // 8 - Make changes on screen
        if (!dropped) {
            // Update description of moved or resized event
            var eventDescrip = timeDescrip + ' ' + movedEvent.summary + '<br />' + movedEvent.comments;
            dojo.byId('plainDiv' + movedEventIndex).innerHTML = eventDescrip;
        } else {
            // Update concurrent events internal values just in case, and update divs on screen
            this.updateSimultEventWidths();
            this.setEventDivsValues();
            this.classesSetup();
        }
    },

    nodeIdToEventOrder: function(nodeId) {
        // Summary:
        //    Receives the id of a node of an event (the main div) and returns a number corresponding to the
        //    corresponding index in the events array.
        var pos   = this.EVENTS_MAIN_DIV_ID.length;
        var event = nodeId.substr(pos, nodeId.length);
        event     = parseInt(event, 10);

        return event;
    },

    saveChanges: function() {
        // Summary:
        //    Save the changes in the server, if any
        // Description:
        //    Get all the new modified values and send them to the server
        var content  = [];
        var doSaving = false;

        // For week view, to store processed ids and not repeat processings in multiple days events
        var processedIds = [];

        for (var i in this.events) {
            if (this.events[i] !== undefined && this.events[i].hasChanged) {
                doSaving = true;
                var id         = this.events[i].id;
                var occurrence = this.events[i].occurrence;

                // Is it a multiple days event?
                if (!this.events[i].multDay) {
                    // No
                    content['data[' + id + '][' + occurrence + '][start]'] = this.events[i].date + ' ' +
                        this.events[i].startTime;
                    content['data[' + id + '][' + occurrence + '][end]']   = this.events[i].date + ' ' +
                        this.events[i].endTime;
                } else {
                    // Yes
                    // Was this event id already processed?
                    if (dojo.indexOf(processedIds, id) == -1) {
                        //No
                        // Obtain the data of the whole event, not just of this div
                        var parent      = this.events[i].multDayParent;
                        var multDayData = this.events[parent].multDayData;

                        content['data[' + id + '][start]'] = multDayData.startDate + ' ' + multDayData.startTime;
                        content['data[' + id + '][end]'] = multDayData.endDate + ' ' + multDayData.endTime;
                        // Add this event id to the list of processed ones:
                        processedIds.push(id);
                    }
                }
            }
        }

        if (doSaving) {
            // Post the content of all changed events
            phpr.send({
                url:       this.updateUrl,
                content:   content
            }).then(dojo.hitch(this, function(response) {
                if (response) {
                    if (response.type != 'success') {
                        new phpr.handleResponse('serverFeedback', response);
                    } else {
                        this._newRowValues = {};
                        this._oldRowValues = {};
                        this.publish("updateCacheData");
                        this.updateOccurrences(response.changedOccurrences);
                    }
                }
            }));
        }
    },

    updateOccurrences: function(changedOccurrences) {
        for (var id in changedOccurrences) {
            for (var i in this.events) {
                if (this.events[i].id == id) {
                    this.events[i].occurrence = changedOccurrences[id];
                }
            }
        }
    },

    isSharingSpace: function(currentEvent) {
        // Summary:
        //    Returns info about a specific event concerning its possible 'simultaneous' condition:
        //    whether it has to be shown as a simultaneous event and related data.
        // Description:
        //    This function receives an index of this.events and returns whether that event shares visual space with
        //    another event, how many events share space with it and the horizontal position that this event will have
        //    among the rest.
        var result = [];

        // The event shares space with another one?
        result.sharing = false;

        // How much events share the same width?
        result.amountEvents = 1;

        // What's the order of received event among all sharing space ones?
        result.order = 1;

        // Split the event duration into halves
        var halves = this.splitPeriodIntoHalves(this.events[currentEvent].startTime,
            this.events[currentEvent].endTime);

        // For each half of hour this event occupies:
        for (var half in halves) {
            var eventsAmountForRow = 1;
            var eventOrder         = 1;
            var positionsOccupied  = new Array(4);
            var halfStart          = halves[half].start;
            var halfEnd            = halves[half].end;
            var storeOrder         = false;

            // For each event...
            for (var otherEvent in this.events) {
                // ...different to the received event...
                if (this.events[otherEvent] !== undefined && otherEvent != currentEvent) {
                    // ...that happens in the same day...
                    if (this.events[currentEvent].column == this.events[otherEvent].column) {
                        // ...check whether it shares time with current half of hour of the received event.
                        // Note: if for example an event finishes at 13:15 and the next one starts at 13:20, then, both
                        // events share visually the half of hour that goes from 13:00 to 13:30.

                        // Is this half sharing time with other event?
                        var superimposed = this.eventDivsSuperimposed(halfStart, halfEnd,
                            this.events[otherEvent].startTime, this.events[otherEvent].endTime);
                        if (superimposed) {
                            result.sharing = true;
                            eventsAmountForRow++;
                            if (otherEvent < currentEvent) {
                                storeOrder               = true;
                                var i                    = this.events[otherEvent].simultOrder;
                                positionsOccupied[i - 1] = true;
                            }
                        }
                    }
                }
            }
            // Establish new maximum simulteaneous events for any row
            if (eventsAmountForRow > result.amountEvents) {
                result.amountEvents = eventsAmountForRow;
            }

            if (storeOrder && eventsAmountForRow == result.amountEvents) {
                // Establish the horizontal order for the event among all sharing width ones
                for (var i = 0; i < positionsOccupied.length; i++) {
                    if (positionsOccupied[i] === undefined) {
                        result.order = i + 1;
                        break;
                    }
                }
            }
        }

        return result;
    },

    eventDivsSuperimposed: function(event1start, event1end, event2start, event2end) {
        // Summary:
        //    Returns whether the 2 events received are superimposed visually at least on one half of hour
        var result = false;

        // The schedule time works in hour halves, so the times have to be rounded
        event1start = this.roundTimeByHourHalves(event1start, this.ROUND_TIME_HALVES_PREVIOUS);
        event1end   = this.roundTimeByHourHalves(event1end, this.ROUND_TIME_HALVES_NEXT);
        event2start = this.roundTimeByHourHalves(event2start, this.ROUND_TIME_HALVES_PREVIOUS);
        event2end   = this.roundTimeByHourHalves(event2end, this.ROUND_TIME_HALVES_NEXT);

        // Both events share at least a half of hour in the schedule?
        if (this.isFirstTimeEarlier(event1start, event2end) && this.isFirstTimeEarlier(event2start, event1end)) {
            // Yes
            result = true;
        }

        return result;
    },

    roundTimeByHourHalves: function(timeString, direction) {
        // Summary:
        //     If start minutes are not 0 or 30, round time to previous/next 30 minutes segment start.
        //     E.g.: 13:15 -> 13:00 or 13:30, according to 'direction' value.
        var tmp     = timeString.split(':');
        var hour    = parseInt(tmp[0], 10);
        var minutes = parseInt(tmp[1], 10);

        if (minutes % 30 !== 0) {
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

    isFirstTimeEarlier: function(time1, time2) {
        // Summary:
        //    Returns whether the first time is earlier than the second one
        var result   = false;

        var tmp      = time1.split(':');
        var hour1    = parseInt(tmp[0], 10);
        var minutes1 = parseInt(tmp[1], 10);

        tmp          = time2.split(':');
        var hour2    = parseInt(tmp[0], 10);
        var minutes2 = parseInt(tmp[1], 10);

        if (hour1 < hour2) {
            result = true;
        } else if (hour1 == hour2 && minutes1 < minutes2) {
            result = true;
        }

        return result;
    },

    updateSimultEventWidths: function() {
        // Summary:
        //    Checks every event and updates its 'simultaneous' type properties
        for (var i in this.events) {
            if (this.events[i] !== undefined) {
                // parseInt is very important here:
                i = parseInt(i, 10);
                var simultEvents = this.isSharingSpace(i);
                if (simultEvents.sharing) {
                    this.events[i].simultWidth  = true;
                    this.events[i].simultAmount = simultEvents.amountEvents;
                    this.events[i].simultOrder  = simultEvents.order;
                } else {
                    this.events[i].simultWidth  = false;
                }
            }
        }
    },

    splitPeriodIntoHalves: function(startTime, endTime) {
        // Summary:
        //    Receives a period of time and returns an array dividing it into halves
        //    of hour with the start and end time for each half.

        // Array to be returned
        var halves = [];

        // Round start and end time into halves of hour
        startTime = this.roundTimeByHourHalves(startTime, this.ROUND_TIME_HALVES_PREVIOUS);
        endTime   = this.roundTimeByHourHalves(endTime, this.ROUND_TIME_HALVES_NEXT);

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
        var currentEnd;
        var hour    = startHour;
        var minutes = startMinutes;

        for (half = 0; half < totalHalves; half++) {
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
            halves[half].start = currentStart;
            halves[half].end   = currentEnd;
        }

        return halves;
    },

    setVarsAndDivs: function() {
        var gridBox          = phpr.viewManager.getView().gridContainer;
        var gridBoxWidth     = dojo.style(gridBox.domNode, "width");
        var calendarSchedule = dojo.byId('calendarSchedule');
        var calenSchedWidth  = dojo.style(calendarSchedule, "width");

        var minCalenSchedSize = 600;

        // Don't allow very small sizes because floating events positioning would start to be imprecise
        if (gridBoxWidth < minCalenSchedSize) {
            if (calenSchedWidth < minCalenSchedSize) {
                dojo.style(calendarSchedule, 'width', minCalenSchedSize + 'px');
            }
        } else if (calenSchedWidth == minCalenSchedSize) {
            dojo.style(calendarSchedule, 'width', '');
        }

        this.updateSizeValuesPart1();
        this.setEventsAreaDivValues();
        this.setEventDivsValues();
        this.updateSizeValuesPart2();
    },

    putDivInTheFront: function(node) {
        // Summary:
        //    Prepares the div to be shown in the front of any other event div this one could be dragged over.
        var EVENT_BEHIND = 1;
        var EVENT_FRONT  = 2;

        if (dojo.style(node, 'zIndex') != EVENT_FRONT) {
            var movedEvent = this.nodeIdToEventOrder(node.id);
            for (var i in this.events) {
                if (i != movedEvent) {
                    var eventDiv = dojo.byId(this.EVENTS_MAIN_DIV_ID + i);
                    dojo.style(eventDiv, 'zIndex', EVENT_BEHIND);
                } else {
                    dojo.style(node, 'zIndex', EVENT_FRONT);
                }
            }
        }
    },

    getEventInfo: function(/*string*/ eventStartDate_String, /*string*/ eventStartTime_String,
            /*string*/ eventEndDate_String, /*string*/ eventEndTime_String,
            /*string*/ momentAskedDate, /*string*/ momentAskedTime) {
        // Summary:
        //    IMPORTANT: This function is similar to 'processEventInfo' but works for Day Group view with fixed events.
        //    Returns useful data about an event, used to create the schedule table.
        // Description:
        //    Returns useful data about an event, used to create the schedule table. E.g.: whether it is inside or
        //    outside the 8:00 to 20:00 range, in what row (and maybe day) of the shown table should it start and end.
        //    If the 'momentAskedTime' optional parameter is set, then one of three possibilities happens
        //    and is informed:
        //    1) The event start time matchs that start time
        //    or 2) The moment asked is inside the event period but doesn't match the event start time
        //    or 3) The moment asked is outside the event time
        //    Note:
        //      Because of this function having lots of time and date variables, I added the suffix '_Date' to
        //      the ones of Date type format, for making all these no so difficult to understand. Also,
        //      to just a few of the String variables, there was added the '_String' suffix, with the same purpose.
        var result             = []; // The variable that will be returned
        var scheduleStart_Date = new Date();  // For the momentAskedDate (current Day), what time the schedule starts
        var scheduleEnd_Date   = new Date();  // For the momentAskedDate (current Day), what time the schedule ends
        var eventStart_Date    = new Date();  // Date and time the event starts
        var eventEnd_Date      = new Date();  // Date and time the event ends
        var momentAsked_Date   = new Date();  // momentAsked (with or without time)
        var eventStartDay_Date = new Date();  // Just the year/month/day of the event start
        var eventEndDay_Date   = new Date();  // Just the year/month/day of the event end

        var temp             = momentAskedDate.split('-');
        var momentAskedYear  = parseInt(temp[0], 10);
        var momentAskedMonth = parseInt(temp[1], 10);
        var momentAskedDay   = parseInt(temp[2], 10);
        scheduleStart_Date.setFullYear(momentAskedYear, momentAskedMonth - 1, momentAskedDay);
        scheduleStart_Date.setHours(this.SCHEDULE_START_HOUR, 0, 0, 0);
        scheduleEnd_Date.setFullYear(momentAskedYear, momentAskedMonth - 1, momentAskedDay);
        scheduleEnd_Date.setHours(this.SCHEDULE_END_HOUR, 0, 0, 0);

        // Convert event start and end Strings into Date formats
        temp                  = eventStartDate_String.split('-');
        var eventStartYear    = parseInt(temp[0], 10);
        var eventStartMonth   = parseInt(temp[1], 10);
        var eventStartDay     = parseInt(temp[2], 10);
        temp                  = eventStartTime_String.split(':');
        var eventStartHour    = parseInt(temp[0], 10);
        var eventStartMinutes = parseInt(temp[1], 10);
        temp                  = eventEndDate_String.split('-');
        var eventEndYear      = parseInt(temp[0], 10);
        var eventEndMonth     = parseInt(temp[1], 10);
        var eventEndDay       = parseInt(temp[2], 10);
        temp                  = eventEndTime_String.split(':');
        var eventEndHour      = parseInt(temp[0], 10);
        var eventEndMinutes   = parseInt(temp[1], 10);
        temp                  = momentAskedDate.split('-');
        var momentAskedHour;
        var momentAskedMinutes;
        if (momentAskedTime !== null) {
            var temp               = momentAskedTime.split(':');
            var momentAskedHour    = parseInt(temp[0], 10);
            var momentAskedMinutes = parseInt(temp[1], 10);
        }

        // Round downwards the event start time to the nearest half of hour
        if ((eventStartMinutes / 30) != Math.floor(eventStartMinutes / 30)) {
            eventStartMinutes = Math.floor(eventStartMinutes / 30) * 30;
        }
        // Round upwards the event end time to the nearest half of hour
        if ((eventEndMinutes / 30) != Math.ceil(eventEndMinutes / 30)) {
            eventEndMinutes = Math.ceil(eventEndMinutes / 30) * 30;
            if (eventEndMinutes == 60) {
                eventEndHour ++;
                eventEndMinutes = 0;
            }
        }

        eventStart_Date.setFullYear(eventStartYear, eventStartMonth - 1, eventStartDay);
        eventStart_Date.setHours(eventStartHour, eventStartMinutes, 0, 0);
        eventEnd_Date.setFullYear(eventEndYear, eventEndMonth - 1, eventEndDay);
        eventEnd_Date.setHours(eventEndHour, eventEndMinutes, 0, 0);
        eventStartDay_Date.setFullYear(eventStartYear, eventStartMonth - 1, eventStartDay);
        eventStartDay_Date.setHours(0, 0, 0, 0);
        eventEndDay_Date.setFullYear(eventEndYear, eventEndMonth - 1, eventEndDay);
        eventEndDay_Date.setHours(0, 0, 0, 0);
        momentAsked_Date.setFullYear(momentAskedYear, momentAskedMonth - 1, momentAskedDay);
        if (momentAskedTime !== null) {
            momentAsked_Date.setHours(momentAskedHour, momentAskedMinutes, 0, 0);
        } else {
            momentAsked_Date.setHours(0, 0, 0, 0);
        }
        if (momentAskedTime !== null) {
            // Compare the event start date and time with the momentAsked
            if (dojo.date.compare(eventStart_Date, momentAsked_Date) === 0) {
                result.type = this.EVENT_TIME_START;
            } else if ((dojo.date.compare(eventStart_Date, momentAsked_Date) < 0) &&
                    (dojo.date.compare(eventEnd_Date, momentAsked_Date) >= 0)) {
                result.type = this.EVENT_TIME_INSIDE;
            } else {
                result.type = this.EVENT_TIME_OUTSIDE;
            }
        } else {
            // Determine if the event has to be shown for the day received (momentAskedDate). If so, also define:
            // 1) Whether it has to be inside or outside the chart.
            // 2) If it is inside the chart, in which row it has to begin, and how many rows it lasts.
            if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) <= 0) &&
                    (dojo.date.compare(eventEndDay_Date, momentAsked_Date) >= 0)) {
                // Shown
                var startsBeforeScheduleEnds = false;
                var endsAfterScheduleBegins  = false;
                if (dojo.date.compare(eventStart_Date, scheduleEnd_Date) < 0) {
                    startsBeforeScheduleEnds = true;
                }
                if (dojo.date.compare(eventEnd_Date, scheduleStart_Date) >= 0) {
                    endsAfterScheduleBegins = true;
                }
                if (startsBeforeScheduleEnds && endsAfterScheduleBegins) {
                    result.range = this.SHOWN_INSIDE_CHART;
                    // If event start happens before the asked day at 8:00, the schedule must show it from the 8:00 row
                    // (but the text will show the real info)
                    if (dojo.date.compare(eventStart_Date, scheduleStart_Date) < 0) {
                        eventStart_Date = scheduleStart_Date;
                    }
                    // If event end happens after the asked day at 20:00, the schedule must show it until the 19:30 row
                    // inclusive (but the text will show the real info)
                    if (dojo.date.compare(eventEnd_Date, scheduleEnd_Date) > 0) {
                        eventEnd_Date = scheduleEnd_Date;
                    }

                    // Date-time description
                    if (this.main.dayListSelf !== null || this.main.dayListSelect !== null) {
                        if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0) ||
                                (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0)) {
                            result.time = this.eventDateTimeDescrip(this.DATETIME_LONG_MANY_DAYS,
                                eventStartTime_String, eventEndTime_String, eventStartDate_String, eventEndDate_String);
                        } else {
                            result.time = this.eventDateTimeDescrip(this.DATETIME_SHORT, eventStartTime_String,
                                eventEndTime_String);
                        }
                    } else if (this.main.weekList !== null || this.main.monthList !== null) {
                        if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0) &&
                                (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0)) {
                            result.time = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_MIDDLE);
                        } else if (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0) {
                            result.time = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_START,
                                eventStartTime_String);
                        } else if (dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0) {
                            result.time = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_END, null,
                                eventEndTime_String);
                        } else {
                            result.time = this.eventDateTimeDescrip(this.DATETIME_SHORT, eventStartTime_String,
                                eventEndTime_String);
                        }
                    }
                    var halfBeginning        = eventStart_Date.getTime() - scheduleStart_Date.getTime();
                    var duration             = eventEnd_Date.getTime() - eventStart_Date.getTime();
                    result.halfBeginning  = Math.floor(halfBeginning / (1000 * 60 * 30));
                    result.halvesDuration = Math.floor(duration / (1000 * 60 * 30));

                } else {
                    result.range = this.SHOWN_OUTSIDE_CHART;

                    // Date-time description
                    if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0) ||
                            (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0)) {
                        result.time = this.eventDateTimeDescrip(this.DATETIME_LONG_MANY_DAYS, eventStartTime_String,
                            eventEndTime_String, eventStartDate_String, eventEndDate_String);
                    } else if (this.main.weekList !== null) {
                        result.time = this.eventDateTimeDescrip(this.DATETIME_LONG_MANY_DAYS, eventStartTime_String,
                            eventEndTime_String, eventStartDate_String, eventEndDate_String);
                    } else {
                        result.time = this.eventDateTimeDescrip(this.DATETIME_SHORT, eventStartTime_String,
                            eventEndTime_String);
                    }
                }
            } else {
                result.range = this.SHOWN_NOT;
            }
        }

        return result;
    },

    splitMultDayEvent: function(startDateString, startTimeString, endDateString, endTimeString, onlyDayString) {
        // Summary:
        //    INSIDE WEEK VIEW: Splits a multiple days event into as many events as days it lasts and sets to each one
        //    dates and times. If a day is out of present week, it is not returned.
        //    INSIDE DAY VIEW: trims the event returning just the data for the selected day.
        //    It also checkes whether the event has at least 1 minute to be shown inside the grid. If not,
        //    then it has to be shown under the grid in 'Further events' section
        var startDate    = dojo.date.stamp.fromISOString(startDateString);
        var endDate      = dojo.date.stamp.fromISOString(endDateString);
        var amountEvents = dojo.date.difference(startDate, endDate) + 1;
        var events       = [];
        var monday;
        var sunday;
        if (this.main.weekList !== null) {
            monday = dojo.date.stamp.fromISOString(this._weekDays[0]);
            sunday = dojo.date.stamp.fromISOString(this._weekDays[6]);
        }

        // Whether the event has to show at least 1 minute inside the grid, if not, it will be shown under the grid in
        // 'Further events' section.
        events.eventShownInGrid = false;

        // For each resulting day
        for (var i = 0; i < amountEvents; i ++) {
            var oneDay = dojo.date.add(startDate, 'day', i);
            // If the first day starts after (or equal to) 20:00 then don't show it
            if ((i === 0) && (this.getMinutesDiff(startTimeString, '20:00') <= 0)) {
                continue;
            }
            // If last day starts after (or equal) to 8:00 then don't show it
            if ((i == amountEvents - 1) && (this.getMinutesDiff(endTimeString, '8:00') >= 0)) {
                continue;
            }

            var oneDayString = dojo.date.stamp.toISOString(oneDay);
            oneDayString     = oneDayString.substr(0, 10);

            // DAY VIEWS: Are we on a Day view and this day is not the selected one?
            if (onlyDayString !== null && onlyDayString != oneDayString) {
                // Skip this day
                continue;
            }

            var nextPos                  = events.length;
            events[nextPos]              = [];
            events[nextPos].startDate = oneDayString;
            events[nextPos].shown     = true;

            if (this.main.weekList !== null) {
                // Is this day inside the selected week?
                if (!((dojo.date.compare(oneDay, monday) >= 0) && (dojo.date.compare(oneDay, sunday) <= 0))) {
                    // No
                    events[nextPos].shown = false;
                }
            }

            // Whether this day has a part to be shown in the day column from 8:00 to 20:00.
            events[nextPos].dayShownInGrid = false;

            // Set times
            if (i === 0) {
                // First day
                events[nextPos].startTime  = startTimeString;
                events[nextPos].endTime    = '20:00';
                events[nextPos].multDayPos = this.DATETIME_MULTIDAY_START;
                // This day has to be shown inside the grid?
                var tmp  = startTimeString.split(':');
                var hour = parseInt(tmp[0], 10);
                if (hour < 20) {
                    events[nextPos].dayShownInGrid = true;
                    events.eventShownInGrid        = true;
                }
            } else {
                // Between second and last day
                events[nextPos].startTime = '8:00';
                if (events[nextPos].startDate == endDateString) {
                    // Last day
                    events[nextPos].endTime    = endTimeString;
                    events[nextPos].multDayPos = this.DATETIME_MULTIDAY_END;
                    // This day has to be shown inside the grid?
                    var tmp     = endTimeString.split(':');
                    var hour    = parseInt(tmp[0], 10);
                    var minutes = parseInt(tmp[1], 10);

                    if (hour > 8 || (hour == 8 && minutes !== 0)) {
                        events[nextPos].dayShownInGrid = true;
                        events.eventShownInGrid        = true;
                    }
                } else {
                    // Between second and penultimate day
                    events[nextPos].endTime        = '20:00';
                    events[nextPos].multDayPos     = this.DATETIME_MULTIDAY_MIDDLE;
                    events[nextPos].dayShownInGrid = true;
                    events.eventShownInGrid        = true;
                }
            }
        }

        return events;
    },

    getMinutesDiff: function(time1, time2) {
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

    fillEventsArrays: function(content) {
        // Summary:
        //    Parses and analyses 'content' contents and puts every event in 'events' array, if there are any multiple
        //    days event, they get splitted into each day events with a connection among them.
        this.events                 = [];
        furtherEventsTemp           = [];
        furtherEventsTemp.show   = false;
        furtherEventsTemp.events = [];

        // For each event received from the DB
        for (var event in content) {
            var eventsInfo     = [];
            var id             = content[event].id;
            var occurrence     = content[event].occurrence;
            var singleDayEvent = false;

            // Split datetime in date and time
            var dateTime = phpr.date.isoDatetimeTojsDate(content[event].start);
            content[event].startDate = phpr.date.getIsoDate(dateTime);
            content[event].startTime = phpr.date.getIsoTime(dateTime);
            dateTime = phpr.date.isoDatetimeTojsDate(content[event].end);
            content[event].endDate = phpr.date.getIsoDate(dateTime);
            content[event].endTime = phpr.date.getIsoTime(dateTime);

            // Process summary and note
            var summary = this.htmlEntities(content[event].summary);
            var comments = this.htmlEntities(content[event].comments);
            comments     = comments.replace('\n', '<br />');
            var column;
            if (this.main.dayListSelect !== null) {
                column = this.getUserColumnPosition(content[event].participantId);
            }

            // What kind of event is this one concerning multiple day events?
            if (content[event].startDate == content[event].endDate) {
                // Single day event
                singleDayEvent = true;
            } else {
                // Multiple days event
                var onlyDayString;
                if (this.main.dayListSelf !== null || this.main.dayListSelect !== null) {
                    onlyDayString = this._date;
                } else {
                    onlyDayString = null;
                }
                var eventsSplitted = this.splitMultDayEvent(content[event].startDate, content[event].startTime,
                    content[event].endDate, content[event].endTime, onlyDayString);

                // The event has at least 1 minute inside the 8:00 to 20:00 grid?
                if (eventsSplitted.eventShownInGrid) {
                    // Yes - It uses one or more day columns.
                    // For each day column (it can't be used 'for (var i in eventsSplitted)':
                    for (var i = 0; i < eventsSplitted.length; i ++) {
                        var eventSplitted = eventsSplitted[i];
                        if (eventSplitted.dayShownInGrid) {
                            // Obtain more info
                            eventSplitted.multDay    = true;
                            eventsInfo[i]               = this.processEventInfo(eventSplitted);
                            eventsInfo[i].multDayPos = eventSplitted.multDayPos;
                            eventsInfo[i].shown      = eventSplitted.shown;
                            if (eventSplitted.multDayPos == this.DATETIME_MULTIDAY_END) {
                                eventsInfo[i].hasResizeHandler = true;
                            } else {
                                eventsInfo[i].hasResizeHandler = false;
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
                eventInfo.multDay   = false;
                eventsInfo[0]          = this.processEventInfo(content[event], event);
                eventsInfo[0].shown = true;
            }

            // Fill the 'events' class array
            var parent = -1;
            for (var i in eventsInfo) {
                var eventInfo = eventsInfo[i];
                // Events inside the grid
                if (eventInfo.range === this.SHOWN_INSIDE_CHART) {
                    eventInfo.hasChanged = false;
                    parent                  = this.addGridEventToArray(eventInfo, id, occurrence, summary, comments, parent,
                        content[event].startDate, content[event].startTime, content[event].endDate,
                        content[event].endTime, column);
                } else if (eventInfo.range === this.SHOWN_OUTSIDE_CHART) {
                    // Events outside the grid: located under it as textual strings
                    furtherEventsTemp.show = true;
                    var nextPosition          = furtherEventsTemp.events.length;

                    furtherEventsTemp.events[nextPosition]          = [];
                    furtherEventsTemp.events[nextPosition].id    = id;
                    furtherEventsTemp.events[nextPosition].time  = eventInfo.timeDescrip;
                    furtherEventsTemp.events[nextPosition].summary = summary;
                    furtherEventsTemp.events[nextPosition].occurrence = occurrence;
                }
            }
        }

        this.updateSimultEventWidths();

        // Clean the repeated 'further events'. Copy the rest to the global variable
        this._furtherEvents = [];
        if (furtherEventsTemp.show) {
            this._furtherEvents.events = [];
            for (var event in furtherEventsTemp.events) {
                var repeated = false;
                for (var i in this._furtherEvents.events) {
                    if (this._furtherEvents.events[i].id == furtherEventsTemp.events[event].id) {
                        repeated = true;
                        break;
                    }
                }
                if (!repeated) {
                    this._furtherEvents.show                       = true;
                    var nextEvent                                     = this._furtherEvents.events.length;
                    this._furtherEvents.events[nextEvent]          = [];
                    this._furtherEvents.events[nextEvent].id    = furtherEventsTemp.events[event].id;
                    this._furtherEvents.events[nextEvent].time  = furtherEventsTemp.events[event].time;
                    this._furtherEvents.events[nextEvent].summary = furtherEventsTemp.events[event].summary;
                    this._furtherEvents.events[nextEvent].occurrence = furtherEventsTemp.events[event].occurrence;
                }
            }
        }
    },

    addGridEventToArray: function(eventInfo, id, occurrence, summary, comments, parent, wholeStartDate,
             wholeStartTime, wholeEndDate, wholeEndTime, column) {
        // Summary:
        //    Adds an event to 'events' class array. Returns parent index which is useful just for multiple day events.
        var nextEvent = this.events.length;
        var newEventDiv         = [];
        newEventDiv.shown       = eventInfo.shown;
        newEventDiv.editable    = true;
        newEventDiv.order       = nextEvent; // For Django template
        newEventDiv.id          = id;
        newEventDiv.occurrence  = occurrence;
        newEventDiv.summary     = summary;
        newEventDiv.timeDescrip = eventInfo.timeDescrip;
        newEventDiv.comments    = comments;
        newEventDiv.date        = eventInfo.date;
        newEventDiv.startTime   = eventInfo.startTime;
        newEventDiv.endTime     = eventInfo.endTime;
        newEventDiv.hasChanged  = eventInfo.hasChanged;
        // To check whether the event is pending to be saved - The last position where it was dropped, so if
        // user drags it and leaves it in the same position, it doesn't need to be saved.
        newEventDiv.posEventDB = eventInfo.date + '-' + eventInfo.startTime + '-' + eventInfo.endTime;

        if (this.main.dayListSelf !== null || this.main.weekList !== null) {
            newEventDiv.column = this.getColumn(eventInfo.date);
        } else if (this.main.dayListSelect !== null) {
            newEventDiv.column = column;
            // In the 'Selection' mode of the views, only the logged user column events are editable
            if (this.getUserColumnPosition(phpr.currentUserId) != column) {
                newEventDiv.editable = false;
            }
        }

        // Multiple day event? Set position among rest of divs of same event, also set if this div has to
        // allow Y resizing.
        newEventDiv.multDay = eventInfo.multDay;
        if (eventInfo.multDay) {
            if (parent == -1) {
                var parent = nextEvent;
                newEventDiv.multDayData              = [];
                newEventDiv.multDayData.startDate = wholeStartDate;
                newEventDiv.multDayData.startTime = wholeStartTime;
                newEventDiv.multDayData.endDate   = wholeEndDate;
                newEventDiv.multDayData.endTime   = wholeEndTime;
            }
            newEventDiv.multDayParent    = parent;
            newEventDiv.multDayPos       = eventInfo.multDayPos;
            newEventDiv.hasResizeHandler = eventInfo.hasResizeHandler;
        } else {
            newEventDiv.hasResizeHandler = true;
        }
        // Whether this multiple days event is being dragged
        newEventDiv.multDayDragging = false;

        // Will be filled later:
        newEventDiv.currentLeft = null;
        newEventDiv.currentTop  = null;

        // Put event div contents into class internal array
        this.events.push(newEventDiv);

        return parent;
    },

    getColumn: function(date) {
        // Summary:
        //    Receives a date like '2009-10-26' and returns the column position number
        var result;

        if (this.main.weekList !== null) {
            for (var i = 0; i < 7; i++) {
                if (this._weekDays[i] == date) {
                    result = i;
                    break;
                }
            }
        } else if (this.main.dayListSelf !== null) {
            result = 0;
        }

        return result;
    },

    fillScheduleArray: function() {
        // Summary:
        //    This function fills the schedule structure and background array
        // Description:
        //     Fills the array with all the possible days and hour:minutes for this day or week view: 8:00, 8:30, 9:00
        //     and so on, until 19:30. Each of that rows will have as many columns as days. Also sets for every row
        //     whether it is even or not.
        for (var hour = 8; hour < 20; hour++) {
            for (var half = 0; half < 2; half++) {
                var minute = (half === 0) ? '00' : '30';
                var row    = ((hour - 8) * 2) + half;

                var totalColumns;
                if (this.main.weekList) {
                    totalColumns = 7;
                } else if (this.main.dayListSelf) {
                    totalColumns = 1;
                } else if (this.main.dayListSelect) {
                    totalColumns = this.users.length;
                }
                this._schedule[row] = new Array(totalColumns);

                for (var column = 0; column < totalColumns; column ++) {
                    this._schedule[row][column] = [];
                }

                hour = ("" + hour).length === 1 ? "0" + hour : hour;
                this._schedule[row].hour = phpr.date.getIsoTime(hour + ':' + minute);

                var tmp = (row / 2);
                if (Math.floor(tmp) == tmp) {
                    // Even row
                    this._schedule[row].even = true;
                } else {
                    // Odd row
                    this._schedule[row].even = false;
                }
            }
        }
    },

    updateMultDaysEvent: function(index) {
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
        var parentDiv     = this.events[index].multDayParent;
        var startTimeOrig = this.events[index].multDayStartTimeOrig;
        var diffDays;
        if (this.main.weekList !== null) {
            var dateOrig = this.events[index].multDayDateOrig;
            dateOrig     = dojo.date.stamp.fromISOString(dateOrig);
            var dateNow  = this.events[index].date;
            dateNow      = dojo.date.stamp.fromISOString(dateNow);
            diffDays = dojo.date.difference(dateOrig, dateNow);
        }
        var diffMinutes = this.getMinutesDiff(startTimeOrig, this.events[index].startTime);
        var firstEvent  = null;
        var lastEvent   = null;
        var movedEvent  = this.events[index];

        // 2 - Pick whole event coordinates
        var parent    = this.events[index].multDayParent;
        var startDate = this.events[parent].multDayData.startDate;
        var startTime = this.events[parent].multDayData.startTime;
        var endDate   = this.events[parent].multDayData.endDate;
        var endTime   = this.events[parent].multDayData.endTime;
        if (diffMinutes > 0) {
            // If div has been dragged vertically then round the time in halves
            startTime = this.roundTimeByHourHalves(startTime, this.ROUND_TIME_HALVES_PREVIOUS);
            endTime   = this.roundTimeByHourHalves(endTime, this.ROUND_TIME_HALVES_NEXT);
        }
        var column = this.events[index].column;

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

        if (this.main.weekList !== null) {
            date1 = dojo.date.add(date1, "day", diffDays);
            date2 = dojo.date.add(date2, "day", diffDays);
        }
        date1              = dojo.date.add(date1, "minute", diffMinutes);
        date2              = dojo.date.add(date2, "minute", diffMinutes);
        date1              = dojo.date.stamp.toISOString(date1);
        date2              = dojo.date.stamp.toISOString(date2);
        var wholeStartDate = date1.substr(0, 10);
        var wholeStartTime = date1.substr(11, 5);
        var wholeEndDate   = date2.substr(0, 10);
        var wholeEndTime   = date2.substr(11, 5);

        // 4 - Delete all divs of dragged event from main array
        for (var i in this.events) {
            if (this.events[i] !== null && this.events[i].multDay) {
                if (this.events[i].multDayParent == parentDiv) {
                    this.events[i] = null;
                }
            }
        }

        // 5 - Generate new this.events elements for this event (one per day shown in the grid)
        var onlyDayString;
        if (this.main.dayListSelf !== null || this.main.dayListSelect !== null) {
            onlyDayString = this._date;
        } else {
            onlyDayString = null;
        }
        var eventsSplitted = this.splitMultDayEvent(wholeStartDate, wholeStartTime, wholeEndDate, wholeEndTime,
            onlyDayString);
        var eventsInfo = [];
        var parent     = -1;
        // For each day column (it can't be used 'for (var i in eventsSplitted)'):
        for (var i = 0; i < eventsSplitted.length; i ++) {
            var eventSplitted = eventsSplitted[i];
            if (eventSplitted.dayShownInGrid) {
                // Obtain more info
                eventSplitted.multDay = true;
                eventInfo                = this.processEventInfo(eventSplitted);
                eventInfo.multDayPos  = eventSplitted.multDayPos;
                eventInfo.shown       = eventSplitted.shown;
                if (eventSplitted.multDayPos == this.DATETIME_MULTIDAY_END) {
                    eventInfo.hasResizeHandler = true;
                } else {
                    eventInfo.hasResizeHandler = false;
                }
                eventInfo.hasChanged = true;
            }
            parent = this.addGridEventToArray(eventInfo, movedEvent.id, movedEvent.occurrence,
                    movedEvent.summary, movedEvent.comments, parent, wholeStartDate, wholeStartTime, wholeEndDate,
                    wholeEndTime, column);
        }
    }
});

dojo.declare("phpr.Calendar2.Moveable", dojo.dnd.Moveable, {
    eventDivMoved: false,

    constructor: function(node, params, parentClass) {
        this.parentClass = parentClass;
    },

    markupFactory: function(params, node) {
        return this;
    },

    onMove: function(mover, leftTop) {
        // Summary:
        //    Original function is empty. This one is in charge of making the 'stepped' allike draging.
        //    Then calls eventMoved function of Calendar2 view class, if some movement was actually done.
        var movedEventIndex = this.parentClass.nodeIdToEventOrder(this.node.id);
        var movedEvent      = this.parentClass.events[movedEventIndex];
        var stepH           = this.parentClass.stepH;
        var stepY           = this.parentClass.stepY;
        var posHmax         = this.parentClass.posHMax;
        var posYmax         = this.parentClass.posYMaxComplement - this.node.offsetHeight;

        // Store original event position before this dragging attempt
        var originalLeft = this.parentClass.columnToDivPosition(movedEvent.column, true);
        var originalTop  = this.parentClass.timeToDivPosition(movedEvent.startTime, true);

        // Following value will be checked by onMoveStop function of this class
        this.parentClass.eventClickDisabled = true;

        // Calculate new left position
        if (movedEvent.simultWidth) {
            // If event is concurrent and it is not the first one from left to right,
            // attach its left side to column border
            leftTop.l -= stepH / movedEvent.simultAmount * (movedEvent.simultOrder - 1);
            leftTop.l  = parseInt(leftTop.l, 10);
        }

        if (this.parentClass.main.weekList !== null) {
            var rest = leftTop.l % stepH;
            var left;
            if (rest < stepH / 2) {
                left = leftTop.l - rest;
            } else {
                left = leftTop.l + stepH - rest;
            }
            if (left < 0) {
                left = 0;
            } else if (left > posHmax) {
                left = posHmax;
            }
            leftTop.l = parseInt(left, 10);
        } else {
            leftTop.l = originalLeft;
        }

        // Calculate new top position
        var top = leftTop.t - (leftTop.t % stepY);
        if (top < 0) {
            top = 0;
        } else if (top > posYmax) {
            top = stepY * parseInt(posYmax / stepY, 10);
        }
        leftTop.t = parseInt(top, 10);

        // According to new calculated left and top values, the div will be moved?
        if (originalLeft != leftTop.l || originalTop != leftTop.t) {
            // Yes
            // If the event is a concurrent one, return it to 100% column width
            if (movedEvent.simultWidth) {
                var eventDivSecond     = dojo.byId('plainDiv' + movedEventIndex);
                var eventWidthComplete = this.parentClass.cellColumnWidth - (2 * this.parentClass.EVENTS_BORDER_WIDTH);
                var eventWidthCurrent  = dojo.style(eventDivSecond, 'width');

                if (eventWidthComplete != eventWidthCurrent) {
                    dojo.style(eventDivSecond, 'width', eventWidthComplete + 'px');
                }
            }

            var s  = mover.node.style;
            s.left = leftTop.l + "px";
            s.top  = leftTop.t + "px";
            this.onMoved(mover, leftTop);

            // Following value will be checked by onMoveStop function of this class
            this.eventDivMoved = true;

            // Update descriptive content of the event
            this.parentClass.eventMoved(this.node, false);
        }
    },

    onMoveStop: function(mover) {
        // Summary: called after every move operation

        // Original code:
        dojo.publish("/dnd/move/stop", [mover]);
        dojo.removeClass(dojo.body(), "dojoMove");
        dojo.removeClass(this.node, "dojoMoveItem");

        // Following code has been added for this view, it calls eventMoved view class function or opens the form with
        // the clicked event.
        if (this.eventDivMoved) {
            // The event has been dragged, update descriptive content of the event and internal array
            this.parentClass.eventMoved(this.node, true);
            // Allow the event to be just clicked to open it in the form, but wait a while first...
            this.eventDivMoved = false;
            setTimeout('dojo.publish("Calendar2.enableEventDivClick")', 500);
        } else {
            if (!this.parentClass.eventClickDisabled) {
                // It was just a click - Open event in the form
                var movedEvent = this.parentClass.nodeIdToEventOrder(this.node.id);
                var eventId    = this.parentClass.events[movedEvent].id;
                var occurrence = this.parentClass.events[movedEvent].occurrence;
                dojo.publish('Calendar2.openForm', [eventId, null, null, null, occurrence]);
            }
        }
        this.parentClass.eventClickDisabled = false;
    }
});

dojo.declare("phpr.Calendar2.ResizeHandle", dojox.layout.ResizeHandle, {
    _changeSizing: function(/*Event*/ e) {
        // Summary: apply sizing information based on information in (e) to attached node
        var tmp = this._getNewCoords(e);
        if (tmp === false) {
            return;
        }

        // Stepped dragging added for this views
        var currentHeight  = dojo.style(this.targetDomNode, "height");
        var step           = this.parentClass.cellTimeHeight;
        var sizerDivHeight = this.domNode.offsetHeight;
        var proposedHeight = tmp.h;
        var steppedHeight  = sizerDivHeight + proposedHeight - (proposedHeight % step) +
            ((5 - this.parentClass.EVENTS_BORDER_WIDTH) * 2) - 7;
        // Depending on the view and browser the steppedHeight value may be different:
        if (this.parentClass.main.dayListSelf !== null) {
            steppedHeight += 3;
            if (dojo.isIE) {
                steppedHeight += 2;
            }
        }

        // Maximum height - Set for the event end time not to be after 20:00
        var maxY      = parseInt(dojo.byId('eventsArea').offsetHeight, 10) + step;
        var eventTopY = parseInt(this.targetDomNode.parentNode.style.top, 10);
        var proposedY = eventTopY + proposedHeight + step + sizerDivHeight;

        // The event bottom border will be moved?
        if (proposedY <= maxY && steppedHeight != currentHeight) {
            tmp.h = steppedHeight;

            if (this.targetWidget && dojo.isFunction(this.targetWidget.resize)) {
                this.targetWidget.resize(tmp);
            } else {
                if (this.animateSizing) {
                    var anim = dojo.fx[this.animateMethod]([
                        dojo.animateProperty({
                            node:       this.targetDomNode,
                            properties: {width: {start: this.startSize.w, end: tmp.w, unit: 'px'}},
                            duration:   this.animateDuration
                        }),
                        dojo.animateProperty({
                            node:       this.targetDomNode,
                            properties: {height: {start: this.startSize.h, end: tmp.h, unit: 'px'}},
                            duration:   this.animateDuration
                        })
                    ]);
                    anim.play();
                } else {
                    dojo.style(this.targetDomNode, 'height', tmp.h + "px");
                }
            }
            if (this.intermediateChanges) {
                this.onResize(e);
            }

            this.parentClass.eventMoved(this.targetDomNode.parentNode, false, true);
        }
    },

    onResize: function(e) {
        // Summary:
        //    Original function is empty. This one calls eventMoved calendar view class function.
        //    Stub fired when sizing is done. Fired once
        //    after resize, or often when `intermediateChanges` is set to true.
        this.parentClass.eventMoved(this.targetDomNode.parentNode, true, true);
    }
});
