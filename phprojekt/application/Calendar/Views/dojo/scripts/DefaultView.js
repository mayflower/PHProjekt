/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2009 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Calendar.DefaultView");

dojo.declare("phpr.Calendar.DefaultView", phpr.Component, {
    // Summary:
    //    Parent class for displaying a Calendar Day Based List. Day based list: it means, not grid but Day List,
    // Week List, etc. This should be inherited by each respective JS view.
    // Description:
    //    This Class provides the basic variables and functions for the class that takes care of displaying the list
    // information we receive from our Server in a HTML table.

    main:                 null,
    id:                   0,
    url:                  null,
    updateUrl:            null,
    _tagUrl:              null,
    _date:                null,
    _widthTable:          0,
    _widthHourColumn:     7,
    _cellTimeWidth:       null,
    cellDayWidth:         null,
    _cellDayHeight:       null,
    cellTimeHeight:       null,
    _gridBoxWidthPrev:    null,
    _calenSchedWidthPrev: null,
    _saveChanges:         null,
    eventHasBeenDragged:  null,
    stepH:                null,
    stepY:                null,
    posHMax:              null,
    posYMaxComplement:    null,
    eventClickDisabled:   false,

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
    EVENTS_MAIN_DIV_ID: 'containerPlainDiv',

    ROUND_TIME_HALVES_PREVIOUS: 0,
    ROUND_TIME_HALVES_NEXT:     1,

    constructor:function(/*String*/updateUrl, /*Int*/ id, /*Date*/ date, /*Array*/ users, /*Object*/ main) {
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

        if (users != null) {
            // Just for the Day group view
            this._users = users;
        }

        this.setUrl();

        if (dojo.isIE) {
            // This is to avoid a pair of scrollbars that eventually appears (not when first loading)
            this._widthTable = 97;
        } else {
            this._widthTable = 100;
        }
        this._widthHourColumn = 7;

        // Draw the tags
        this.showTags();

        this.afterConstructor();
    },

    beforeConstructor:function() {
        // Summary:
        //    Function called almost at the top of 'constructor' function.
        // Description:
        //    If there is something that must be done before executing the most of sentences of 'constructor' function,
        // inherit this function and put it inside it.
    },

    showTags:function() {
        // Summary:
        //    Draws the tags
        // Description:
        //    Draws the tags
        this._tagUrl = phpr.webpath + 'index.php/Default/Tag/jsonGetTags'; // Get the module tags
        phpr.DataStore.addStore({url: this._tagUrl});
        phpr.DataStore.requestData({url: this._tagUrl, processData: dojo.hitch(this, function() {
                this.publish("drawTagsBox", [phpr.DataStore.getData({url: this._tagUrl})]);
            })
        });
    },

    setExportButton:function(meta) {
        // Summary:
        //    Sets the export button
        // Description:
        //    If there is any row, render export Button
        if (meta.length > 0 && this._exportButton === null) {
            var params = {
                label:     phpr.nls.get('Export all items to a CSV file'),
                showLabel: false,
                baseClass: "positive",
                iconClass: "export",
                disabled:  false
            };
            this._exportButton = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(this._exportButton.domNode);
            dojo.connect(this._exportButton, "onClick", dojo.hitch(this, "exportData"));
        }
    },

    setSaveChangesButton:function(meta) {
        // Summary:
        //    Sets the Save changes button
        // Description:
        //    If there is any row, render Save changes button
        if (meta.length > 0 && this._saveChanges === null) {
            var params = {
                label:     phpr.nls.get('Save changes made to the grid through in-place editing'),
                showLabel: false,
                baseClass: "positive",
                iconClass: "disk",
                disabled:  true
            };
            this._saveChanges = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(this._saveChanges.domNode);
            dojo.connect(this._saveChanges, "onClick", dojo.hitch(this, "saveChanges"));
        }
    },

    processEventInfo:function(eventInfo,i) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        // Description:
        //    Returns useful data about an event, used to create the schedule table.
        // 1) Whether it is inside or outside the 8:00 to 20:00 range.
        // 2) Time description for the event.
        // 3) Formatted start and end times.
        // 4) Date and whether it is a multi-day event

        var result = new Array();  // The variable that will be returned

        // Times have to be rounded according the vertical time schedule divisions
        startTimeRounded = this.roundTimeByHourHalves(eventInfo['startTime'], this.ROUND_TIME_HALVES_PREVIOUS);
        endTimeRounded   = this.roundTimeByHourHalves(eventInfo['endTime'], this.ROUND_TIME_HALVES_NEXT);

        temp                  = startTimeRounded.split(':');
        var eventStartHour    = parseInt(temp[0], 10);
        var eventStartMinutes = parseInt(temp[1], 10);
        temp                  = endTimeRounded.split(':');
        var eventEndHour      = parseInt(temp[0], 10);
        var eventEndMinutes   = parseInt(temp[1], 10);
        result['startTime']   = this.formatTime(eventInfo['startTime']);
        result['endTime']     = this.formatTime(eventInfo['endTime']);

        // Is at least one minute of the event inside the schedule?
        if (eventStartHour < 20 && ((eventEndHour > 7) && !(eventEndHour == 8 && eventEndMinutes == 0))) {
            // Yes - Show the event inside the schedule
            result['range']     = this.SHOWN_INSIDE_CHART;

            // Date-time description
            // What view are we in?
            if (this.main.weekList != null) {
                // Week view
                // Is it a multiple days event?
                if (!eventInfo['multDay']) {
                    // No
                    result['timeDescrip'] = this.eventDateTimeDescrip(this.DATETIME_SHORT, result['startTime'],
                        result['endTime']);
                } else {
                    // Yes
                    result['timeDescrip'] = this.eventDateTimeDescrip(eventInfo['multDayPos'], result['startTime'],
                        result['endTime']);
                }
            }
        } else {
            // No - Shown out of the schedule
            result['range'] = this.SHOWN_OUTSIDE_CHART;
            // Date-time description
            // How many days does the event last?
            if (eventInfo['startDate'] == eventInfo['endDate']) {
                // One day
                result['timeDescrip'] = this.eventDateTimeDescrip(this.DATETIME_LONG_ONE_DAY, result['startTime'],
                    result['endTime'], eventInfo['startDate']);
            } else {
                // More than one day
                result['timeDescrip'] = this.eventDateTimeDescrip(this.DATETIME_LONG_MANY_DAYS, result['startTime'],
                    result['endTime'], eventInfo['startDate'], eventInfo['endDate']);
            }
        }

        result['date']    = eventInfo['startDate'];
        result['multDay'] = eventInfo['multDay'];

        return result;
    },

    formatTime:function(time) {
        // Summary:
        //    Formats a time string. E.g. receives '9:40:00' and returns '09:40', or receives '8:5' and returns '08:05'
        var temp    = time.split(':');
        var hour    = temp[0];
        var minutes = temp[1];
        var result  = dojo.number.format(hour, {pattern: '00'}) + ':' + dojo.number.format(minutes, {pattern: '00'});

        return result;
    },

    formatDate:function(date) {
        // Summary:
        //    Formats a date string. E.g. receives '2009-5-4' and returns '2009-05-04'
        var temp   = date.split('-');
        var year   = temp[0];
        var month  = temp[1];
        var day    = temp[2];
        var result = year + '-' + dojo.number.format(month, {pattern: '00'}) + '-'
            + dojo.number.format(day, {pattern: '00'});

        return result;
    },

    updateData:function() {
        // Summary:
        //    Deletes the cache for this List table
        phpr.DataStore.deleteData({url: this.url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    },

    htmlEntities:function(str) {
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

    eventDateTimeDescrip:function(mode, startTime, endTime, startDate, endDate) {
        // Summary:
        //    Creates the appropriate datetime event description according the requested mode
        var description;
        switch (mode) {
            case this.DATETIME_LONG_MANY_DAYS:
                description = startDate + ' &nbsp;' + this.formatTime(startTime) + ' &nbsp;- &nbsp;' + endDate
                    + ' &nbsp;' + this.formatTime(endTime);
                break;
            case this.DATETIME_LONG_ONE_DAY:
            default:
                description = startDate + ' &nbsp;' + this.formatTime(startTime) + ' - ' + this.formatTime(endTime);
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
        }

        return description;
    },

    stringToDate:function() {
        // Summary:
        //    Receives a string date '2009-10-26' and returns a Date type variable
        var temp  = this._date.split('-');
        var year  = parseInt(temp[0], 10);
        var month = parseInt(temp[1], 10);
        var day   = parseInt(temp[2], 10);
        var date  = new Date(year, month - 1, day);
        return date;
    },

    updateSizeValuesPart1:function() {
        // Summary
        //    Updates internal class variables with current sizes of schedule

        // This is done before everything because moving 'eventsArea' div changes width of 'scheduleBackground' grid
        // sometimes depending of browser size, at least in FF 3.5.
        var eventsAreaDiv = dojo.byId('eventsArea');
        dojo.style(eventsAreaDiv, {
            top: '0px'
        });

        var scheduleBkg     = dojo.byId('scheduleBackground').getElementsByTagName('td');
        this._cellTimeWidth = scheduleBkg[0].offsetWidth;
        this.cellDayWidth   = scheduleBkg[1].offsetWidth;
        this._cellDayHeight = scheduleBkg[0].offsetHeight;
        this.cellTimeHeight = scheduleBkg[8].offsetHeight;
    },

    updateSizeValuesPart2:function() {
        // Summary
        //    Updates internal class variables with current sizes of schedule
        this.stepH             = (dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth) / 7;
        this.stepH             = dojo.number.round(this.stepH, 1);
        this.stepY             = this.cellTimeHeight;
        this.posHMax           = parseInt(dojo.byId('eventsArea').style.width) - this.stepH;
        this.posYMaxComplement = parseInt(dojo.byId('eventsArea').style.height) - this.stepY;
    },

    timeToDivPosition:function(moment, isEvent, type) {
        // Summary
        //    Receives a time string and returns a number for the corresponding vertical position in pixels.
        // Parameters:
        //    moment: string, e.g.: '14:40'
        //    isEvent: whether the number returned will be used to position an event (not the background)
        //    type: used when isEvent = true, whether we are receiving the start or the end time of the event.

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
            position += this._cellDayHeight;
        }
        position = parseInt(position);

        return position;
    },

    dayToDivPosition:function(day, isEvent) {
        // Summary
        //    Receives a week day number from 0 to 6 and returns a number for the corresponding horizontal position in
        // pixels.
        // Parameters:
        //    isEvent: whether the number returned will be used to position an event (not the background)

        var widthDays = dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth;
        var position  = day * widthDays / 7;
        if (!isEvent) {
            position += this._cellTimeWidth;
        }
        position = parseInt(position);

        return position;
    },

    divPositionToTime:function(verticalPos) {
        // Summary
        //    Receives a schedule position in pixels and returns a time string
        var row     = Math.floor(verticalPos / this.cellTimeHeight);
        var hour    = 8 + Math.floor(row / 2);
        var minutes = (row % 2) * 30;
        var timeStr = hour + ':' + minutes;

        return timeStr;
    },

    divPositionToDay:function(horizontalPos) {
        // Summary
        //    Receives a number for the corresponding horizontal position in pixels on the schedule and returns a week
        // day number from 0 to 6.
        var widthDays    = dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth;
        var cellDayWidth = widthDays / 7;
        var day          = Math.floor((horizontalPos + (cellDayWidth / 2)) / cellDayWidth);

        return day;
    },

    setEventsAreaDivValues:function() {
        // Summary:
        //    Sets / updates the position and size of 'eventsArea' div according to panel and background sizes.
        // eventsArea is the div where the events will be floating inside.

        var xPos   = this.dayToDivPosition(0, false);
        var yPos   = this.timeToDivPosition('8:00', false);
        var width  = dojo.byId('scheduleBackground').offsetWidth - this._cellTimeWidth;
        var height = (this.cellTimeHeight * 24) + this._cellDayHeight;

        var eventsAreaDiv = dojo.byId('eventsArea');
        dojo.style(eventsAreaDiv, {
            left:       '0px',
            top:        '0px',
            marginLeft: xPos + 'px',
            marginTop:  yPos + 'px',
            width:      width + 'px',
            height:     height + 'px'
        });
    },

    setEventDivsValues:function() {
        // Summary:
        //    Sets / updates the position and size and textual contents of each event according to last updated values
        // in this.events and background sizes.

        for (var i in this.events) {
            var left   = this.dayToDivPosition(this.events[i]['dayOrder'], true);
            var top    = this.timeToDivPosition(this.events[i]['startTime'], true, this.TYPE_EVENT_START);
            var width  = this.cellDayWidth - (2 * this.EVENTS_BORDER_WIDTH);
            var bottom = this.timeToDivPosition(this.events[i]['endTime'], true, this.TYPE_EVENT_END);
            var height = bottom - top - (2 * this.EVENTS_BORDER_WIDTH);

            // Is this event part of two or more simulteaneous ones?
            if (this.events[i]['simultWidth']) {
                // Yes - Reduce its width
                width = (width / this.events[i]['simultAmount']) - this.EVENTS_BORDER_WIDTH;
                width = dojo.number.round(width);

                // Maybe change its left position
                left += dojo.number.round(this.cellDayWidth / this.events[i]['simultAmount']
                    * (this.events[i]['simultOrder'] - 1));
            }

            var eventDiv1 = dojo.byId(this.EVENTS_MAIN_DIV_ID + i);
            var eventDiv2 = dojo.byId('plainDiv' + i);

            this.events[i]['currentLeft']   = left;
            this.events[i]['currentTop']    = top;
            this.events[i]['currentBottom'] = bottom;

            if (this.events[i]['shown']) {
                var visibility = 'visible';
            } else {
                var visibility = 'hidden';
            }

            dojo.style(eventDiv1, {
                left:       left + 'px',
                top:        top + 'px',
                visibility: visibility
            });
            dojo.style(eventDiv2, {
                width:  width + 'px',
                height: height + 'px'
            });

            // Update textual visible contents of event
            var textualContents = this.events[i]['timeDescrip'] + ' ' + this.events[i]['title'] + '<br>' +
                this.events[i]['notes'];
            eventDiv2.innerHTML = textualContents;
        }

        if (this.main.weekList != null) {
            // Any remaining unused html divs?
            var lastIndex = parseInt(i);
            if ((lastIndex + 1) < this._htmlEventDivsAmount) {
                // Yes, hide them
                for (indexToHide = lastIndex + 1; indexToHide < this._htmlEventDivsAmount; indexToHide ++) {
                    var eventDiv1 = dojo.byId(this.EVENTS_MAIN_DIV_ID + indexToHide);
                    dojo.style(eventDiv1, {
                        visibility: 'hidden'
                    });
                }
            }
        }
    },

    classesSetup:function(startup) {
        // Summary:
        //    Creates dragging class on startup. Provides the dragging and resize classes with a reference object
        // variable to this class. Establishes a minimum height for the events, it is the height of one cell.
        // Activates or inactivates Y resize for each div.

        for (var i in this.events) {
            if (this.events[i]['shown']) {
                if (startup) {
                    var eventDiv  = new phpr.Calendar.Moveable(this.EVENTS_MAIN_DIV_ID + i, null, this);
                }

                var resizeDiv = dijit.byId('eventResize' + i)
                if (startup) {
                    resizeDiv.parentClass = this;
                    // Minimum size:
                    var minWidth      = this.cellDayWidth - (2 * this.EVENTS_BORDER_WIDTH);
                    var minHeight     = this.cellTimeHeight - (2 * this.EVENTS_BORDER_WIDTH);
                    resizeDiv.minSize = { w: minWidth, h: minHeight};
                }

                if (this.events[i]['hasResizeHandler']) {
                    resizeDiv.active = true;
                } else {
                    resizeDiv.active = false;
                }
            }
        }
    },

    eventMoved:function(node, dropped, resized) {
        // Summary:
        //    Called when an event is moved: both dragged or Y-resized, both in the dragging of the event or the border
        // itself and when mouse is released. Its purpose is to eventually update an internal array, the event
        // description, change shapes of events according to 'simultaneous events' criteria and activate Save button.
        // Parameters:
        //   node: the div node of the moved event
        //   dropped: whether the mouse button was released, so the dragged actioni has been finished
        //   resized: whether the event has just been resized (not moved)

        // 1 - Put div in the front of stack
        this.putDivInTheFront(node);

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
            this.toggleMultDaysDivs(movedEventIndex, false);
            movedEvent['multDayDragging']      = true;
            movedEvent['multDayDateOrig']      = movedEvent['date'];
            movedEvent['multDayStartTimeOrig'] = movedEvent['startTime'];
        }

        if (this.main.weekList != null && dragged && dropped) {
            var dayOrderCurrent = movedEvent['dayOrder'];
        }

        // 4 - Time and day changes
        if (dragged) {
            // If event was moved (not resized), then attend the start time change
            // Start Time did change?
            if (posTopNew != posTopCurrent) {
                var startTime             = this.divPositionToTime(posTopNew);
                startTime                 = this.formatTime(startTime);
                movedEvent['currentTop']  = posTopNew;
                movedEvent['startTime']   = startTime;
            }
            // Day did change?
            if (posLeftNew != posLeftCurrent) {
                var dayOrder              = this.divPositionToDay(posLeftNew);
                movedEvent['currentLeft'] = posLeftNew;
                movedEvent['dayOrder']    = dayOrder;
                movedEvent['date']        = this._weekDays[dayOrder];
            }
        }
        // End Time did change?
        if (posBottomNew != posBottomCurrent) {
            var endTime                              = this.divPositionToTime(posBottomNew);
            endTime                                  = this.formatTime(endTime);
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
        if (dropped) {
            var posEventCurrent = movedEvent['date'] + '-' + movedEvent['startTime']
                + '-' + movedEvent['endTime'];

            // The event was dropped in a different location than the one saved in the DB?
            if (posEventCurrent != movedEvent['posEventDB']) {
                // Yes
                this.events[movedEventIndex]['hasChanged'] = true;
                this.enableSaveButton();
            } else {
                this.events[movedEventIndex]['hasChanged'] = false;
            }

            if (this.main.weekList != null) {
                // The dropped event was being dragged (not resized) and it was a multiple days event?
                if (dragged && movedEvent['multDay']) {
                    // Yes - Update the position and sizes of the rest of divs of this event
                    this.updateMultDaysEvent(movedEventIndex);
                }
            }
        }

        // 7 - Update event textual contents
        // Is it a multiple days event?
        if (!movedEvent['multDay']) {
            // No
            var timeDescrip = this.eventDateTimeDescrip(this.DATETIME_SHORT, startTime, endTime);
        } else {
            // Yes
            if (!dropped) {
                if (resized) {
                    var timeDescrip = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_END, startTime, endTime);
                } else {
                    var timeDescrip = this.eventDateTimeDescrip(movedEvent['multDayPos'], movedEvent['startTime'],
                        movedEvent['endTime']);
                }
                this.events[movedEventIndex]['timeDescrip'] = timeDescrip;
            }
        }

        // 8 - Make changes on screen
        if (!dropped) {
            // Update description of moved or resized event
            var eventDescrip = timeDescrip + ' ' + movedEvent['title'] + '<br>' + movedEvent['notes'];
            dojo.byId('plainDiv' + movedEventIndex).innerHTML = eventDescrip;
        } else {
            // Update concurrent events internal values just in case, and update divs on screen
            this.updateSimultEventWidths();
            this.setEventDivsValues();
            this.classesSetup();
        }
    },

    nodeIdToEventOrder:function(nodeId) {
        // Summary:
        //    Receives the id of a node of an event (the main div) and returns a number corresponding to the
        // corresponding index in the events array.

        var pos   = this.EVENTS_MAIN_DIV_ID.length;
        var event = nodeId.substr(pos, nodeId.length);
        event     = parseInt(event);

        return event;
    },

    saveChanges:function() {
        // Summary:
        //    Save the changes in the server, if any
        // Description:
        //    Get all the new modified values and send them to the server

        var content  = new Array();
        var doSaving = false;

        if (this.main.weekList != null) {
            // For week view, to store processed ids and not repeat processings in multiple days events
            var processedIds = new Array;
        }

        for (var i in this.events) {
            if (this.events[i]['hasChanged']) {
                doSaving = true;
                var id   = this.events[i]['id'];

                // Is it week view and a multiple days event?
                if (!(this.main.weekList != null && this.events[i]['multDay'])) {
                    // No
                    content['data[' + id + '][startDate]'] = this.events[i]['date'];
                    content['data[' + id + '][endDate]']   = this.events[i]['date'];
                    content['data[' + id + '][startTime]'] = this.events[i]['startTime'];
                    content['data[' + id + '][endTime]']   = this.events[i]['endTime'];
                } else {
                    // Yes
                    // Was this event id already processed?
                    if (dojo.indexOf(processedIds, id) == -1) {
                        //No
                        // Obtain the data of the whole event, not just of this div
                        var parent      = this.events[i]['multDayParent'];
                        var multDayData = this.events[parent]['multDayData'];
                        content['data[' + id + '][startDate]'] = multDayData['startDate'];
                        content['data[' + id + '][endDate]']   = multDayData['endDate'];
                        content['data[' + id + '][startTime]'] = multDayData['startTime'];
                        content['data[' + id + '][endTime]']   = multDayData['endTime'];
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
                content:   content,
                onSuccess: dojo.hitch(this, function(response) {
                    new phpr.handleResponse('serverFeedback', response);
                    if (response.type == 'success') {
                        this._newRowValues = {};
                        this._oldRowValues = {};
                        this.publish("updateCacheData");
                        this.publish("reload");
                    }
                })
            });
       }
    },

    enableSaveButton:function() {
        // Summary:
        //    Enables Save button if it was disabled

        if (this._saveChanges.disabled == true) {
            dojox.fx.highlight({
                node:     this._saveChanges.id,
                color:    '#ffff99',
                duration: 1600
            }).play();
        }

        this._saveChanges.disabled = false;
        var saveButton             = dojo.byId(this._saveChanges.id);
        saveButton.disabled        = false;
    },

    isSharingSpace:function(currentEvent) {
        //    Returns info about a specific event concerning its possible 'simultaneous' condition: whether it has to be
        // shown as a simultaneous event and related data.
        // Description:
        //    This function receives an index of this.events and returns whether that event shares visual space with
        // another event, how many events share space with it and the horizontal position that this event will have
        // among the rest

        var result = new Array();

        // The event shares space with another one?
        result['sharing'] = false;

        // How much events share the same width?
        result['amountEvents'] = 1;

        // What's the order of received event among all sharing space ones?
        result['order'] = 1;

        // Split the event duration into halves
        var halves = this.splitPeriodIntoHalves(this.events[currentEvent]['startTime'],
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
                if (otherEvent != currentEvent) {
                    // ...that happens in the same day...
                    if (this.events[currentEvent]['dayOrder'] == this.events[otherEvent]['dayOrder']) {
                        // ...check whether it shares time with current half of hour of the received event.
                        // Note: if for example an event finishes at 13:15 and the next one starts at 13:20, then, both
                        // events share visually the half of hour that goes from 13:00 to 13:30.

                        // Is this half sharing time with other event?
                        var superimposed = this.eventDivsSuperimposed(halfStart, halfEnd,
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

    eventDivsSuperimposed:function(event1start, event1end, event2start, event2end) {
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

    roundTimeByHourHalves:function(timeString, direction) {
        // Summary:
        //     If start minutes are not 0 or 30, round time to previous/next 30 minutes segment start.
        // E.g.: 13:15 -> 13:00 or 13:30, according to 'direction' value.

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

    isFirstTimeEarlier:function(time1, time2) {
        // Summary:
        // Returns whether the first time is earlier than the second one
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

    updateSimultEventWidths:function() {
        // Summary:
        //    Checks every event and updates its 'simultaneous' type properties

        for (var i in this.events) {
            var simultEvents = this.isSharingSpace(i);
            if (simultEvents['sharing']) {
                this.events[i]['simultWidth']  = true;
                this.events[i]['simultAmount'] = simultEvents['amountEvents'];
                this.events[i]['simultOrder']  = simultEvents['order'];
            } else {
                this.events[i]['simultWidth'] = false;
            }
        }
    },

    splitPeriodIntoHalves:function(startTime, endTime) {
        // Summary:
        //    Receives a period of time and returns an array dividing it into halves of hour with the start and end time
        // for each half.

        // Array to be returned
        var halves = new Array();

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
            halves[half]          = new Array();
            halves[half]['start'] = currentStart;
            halves[half]['end']   = currentEnd;
        }

        return halves;
    },

    setVarsAndDivs:function() {
        // Summary:
        //    The first time this function is called (on class load) sets / updates class variables of div sizes, steps,
        // limits, etc. and updates sizes of the eventsArea and events on screen.
        // The following times does the same things only if the gridBox width has changed.

        var gridBox           = dojo.byId('gridBox');
        var gridBoxWidth      = dojo.style(gridBox, "width");
        var calendarSchedule  = dojo.byId('calendarSchedule');
        var calenSchedWidth   = dojo.style(calendarSchedule,"width");

        if (gridBoxWidth != this._gridBoxWidthPrev || calenSchedWidth != this._calenSchedWidthPrev) {
            var doUpdateAndResize = true;
            var minCalenSchedSize = 600;

            // Don't allow very small sizes because floating events positioning would start to be imprecise
            if (gridBoxWidth < minCalenSchedSize) {
                if (calenSchedWidth < minCalenSchedSize) {
                    dojo.style(calendarSchedule, {
                        width: minCalenSchedSize + 'px'
                    });
                } else {
                    // The width of the calendar schedule hasn't changed
                    doUpdateAndResize = false;
                }
            } else if (calenSchedWidth == minCalenSchedSize) {
                dojo.style(calendarSchedule, {
                    width: ''
                });
            }

            this._gridBoxWidthPrev    = gridBoxWidth;
            this._calenSchedWidthPrev = dojo.style(calendarSchedule,"width");

            if (doUpdateAndResize) {
                this.updateSizeValuesPart1();
                this.setEventsAreaDivValues();
                this.setEventDivsValues();
                this.updateSizeValuesPart2();
            }
        }
    },

    putDivInTheFront:function(node) {
        // Summary:
        //    Prepares the div to be shown in the front of any other event div this one could be dragged over.

        var EVENT_BEHIND = 1;
        var EVENT_FRONT  = 2;

        if (dojo.style(node, 'zIndex') != EVENT_FRONT) {
            var movedEvent = this.nodeIdToEventOrder(node.id);
            for (var i in this.events) {
                if (i != movedEvent) {
                    var eventDiv = dojo.byId(this.EVENTS_MAIN_DIV_ID + i);
                    dojo.style(eventDiv, {
                        zIndex: EVENT_BEHIND
                    });
                } else {
                    dojo.style(node, {
                        zIndex: EVENT_FRONT
                    });
                }
            }
        }
    }
});

dojo.provide("phpr.Calendar.Moveable");
dojo.declare("phpr.Calendar.Moveable", dojo.dnd.Moveable, {

    constructor: function(node, params, parentClass) {
        this.parentClass = parentClass;
    },

    markupFactory: function(params, node){
        return this;
    },

    onMove: function(mover, leftTop) {
        // Summary:
        //    Original function is empty. This one is in charge of making the 'stepped' allike draging. Then calls
        // eventMoved function of Calendar view class, if some movement was actually done.

        var movedEventIndex = this.parentClass.nodeIdToEventOrder(this.node.id);
        var movedEvent      = this.parentClass.events[movedEventIndex];
        var stepH           = this.parentClass.stepH;
        var stepY           = this.parentClass.stepY;
        var posHmax         = this.parentClass.posHMax;
        var posYmax         = this.parentClass.posYMaxComplement - this.node.offsetHeight;

        // Store original event position before this dragging attempt
        var originalLeft   = this.parentClass.dayToDivPosition(movedEvent['dayOrder'], true);
        var originalTop    = this.parentClass.timeToDivPosition(movedEvent['startTime'], true);

        // Calculate new left position
        if (movedEvent['simultWidth']) {
            //  If event is concurrent and it is not the first one from left to right, attach its left side to column
            // border
            leftTop.l -= stepH / movedEvent['simultAmount'] * (movedEvent['simultOrder'] - 1);
            leftTop.l  = parseInt(leftTop.l);
        }
        var rest = leftTop.l % stepH;
        if (rest < stepH / 2) {
            var left = leftTop.l - rest;
        } else {
            var left = leftTop.l + stepH - rest;
        }
        if (left < 0) {
            left = 0;
        } else if (left > posHmax) {
            left = posHmax;
        }
        leftTop.l = parseInt(left);

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
                var eventDivSecond     = dojo.byId('plainDiv' + movedEventIndex);
                var eventWidthComplete = this.parentClass.cellDayWidth - (2 * this.parentClass.EVENTS_BORDER_WIDTH);
                var eventWidthCurrent  = dojo.style(eventDivSecond, 'width');

                if (eventWidthComplete != eventWidthCurrent) {
                    dojo.style(eventDivSecond, {
                        width: eventWidthComplete + 'px'
                    });
                }
            }

            var s  = mover.node.style;
            s.left = leftTop.l + "px";
            s.top  = leftTop.t + "px";
            this.onMoved(mover, leftTop);

            // Following values will be checked by onMoveStop function of this class
            this.parentClass.eventHasBeenDragged = true;
            this.parentClass.eventClickDisabled  = true;

            // Update descriptive content of the event
            this.parentClass.eventMoved(this.node, false);
        }
    },

    onMoveStop: function(mover) {
        // summary: called after every move operation

        // Original code:
        dojo.publish("/dnd/move/stop", [mover]);
        dojo.removeClass(dojo.body(), "dojoMove");
        dojo.removeClass(this.node, "dojoMoveItem");

        // Following code has been added for this view, it calls eventMoved view class function or opens the form with
        // the clicked event.

        if (this.parentClass.eventHasBeenDragged) {
            // The event has been dragged, update descriptive content of the event and internal array
            this.parentClass.eventMoved(this.node, true);
            // Allow the event to be just clicked to open it in the form, but wait a while first...
            this.parentClass.eventHasBeenDragged = false;
            setTimeout('dojo.publish("Calendar.enableEventDivClick")', 500);
        } else {
            if (!this.parentClass.eventClickDisabled) {
                // It was just a click - Open event in the form
                var movedEvent = this.parentClass.nodeIdToEventOrder(this.node.id);
                var eventId    = this.parentClass.events[movedEvent]['id'];
                dojo.publish('Calendar.showFormFromList', [eventId]);
            }
        }
    }
});

dojo.provide("phpr.Calendar.ResizeHandle");
dojo.declare("phpr.Calendar.ResizeHandle", dojox.layout.ResizeHandle, {

    _changeSizing: function(/*Event*/ e){
        // summary: apply sizing information based on information in (e) to attached node
        var tmp = this._getNewCoords(e);
        if(tmp === false){ return; }

        if (!this.active) {
            return;
        }

        // Stepped dragging added for this view
        var currentHeight  = dojo.style(this.targetDomNode, "height");
        var step           = this.parentClass.cellTimeHeight;
        var sizerDivHeight = this.domNode.offsetHeight;
        var proposedHeight = tmp['h'];
        var steppedHeight  = sizerDivHeight + proposedHeight - (proposedHeight % step)
            + ((5 - this.parentClass.EVENTS_BORDER_WIDTH) * 2) - 7;

        // Maximum height - Set for the event end time not to be after 20:00
        var maxY      = parseInt(dojo.byId('eventsArea').offsetHeight);
        var eventTopY = parseInt(this.targetDomNode.parentNode.style.top);
        var proposedY = eventTopY + proposedHeight + step + sizerDivHeight;

        // The event bottom border will be moved?
        if (proposedY <= maxY && steppedHeight != currentHeight) {
            tmp['h'] = steppedHeight;

            if(this.targetWidget && dojo.isFunction(this.targetWidget.resize)){
                this.targetWidget.resize(tmp);
            }else{
                if(this.animateSizing){
                    var anim = dojo.fx[this.animateMethod]([
                        dojo.animateProperty({
                            node: this.targetDomNode,
                            properties: {
                                width: { start: this.startSize.w, end: tmp.w, unit:'px' }
                            },
                            duration: this.animateDuration
                        }),
                        dojo.animateProperty({
                            node: this.targetDomNode,
                            properties: {
                                height: { start: this.startSize.h, end: tmp.h, unit:'px' }
                            },
                            duration: this.animateDuration
                        })
                    ]);
                    anim.play();
                }else{
                    dojo.style(this.targetDomNode,{
                        height: tmp.h + "px"
                    });
                }
            }
            if(this.intermediateChanges){
                this.onResize(e);
            }

            this.parentClass.eventMoved(this.targetDomNode.parentNode, false, true);
        }
    },

    onResize: function(e){
        // Summary:
        //    Original function is empty. This one calls eventMoved calendar view class function.
        // Stub fired when sizing is done. Fired once
        //  after resize, or often when `intermediateChanges` is
        //  set to true.
        if (!this.active) {
            return;
        }

        this.parentClass.eventMoved(this.targetDomNode.parentNode, true, true);
    }
});
