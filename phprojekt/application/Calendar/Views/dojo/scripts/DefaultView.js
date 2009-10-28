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

    main:                null,
    id:                  0,
    url:                 null,
    updateUrl:           null,
    _tagUrl:             null,
    _date:               null,
    _widthTable:         0,
    _widthHourColumn:    8,
    _cellTimeWidth:      null,
    _cellDayWidth:       null,
    _cellDayHeight:      null,
    _cellTimeHeight:     null,
    _lastGridBoxWidth:   null,
    _saveChanges:        null,

    // General constants
    SCHEDULE_START_HOUR: 8,
    SCHEDULE_END_HOUR:   20,

    // Constants used by the function getEventInfo:
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

    EVENTS_BORDER_WIDTH: 5,
    EVENTS_MAIN_DIV_ID: 'containerPlainDiv',

    ROUND_TIME_HALVES_PREVIOUS: 0,
    ROUND_TIME_HALVES_NEXT:     1,

    constructor:function(/*String*/updateUrl, /*Int*/ id, /*Date*/ date, /*Array*/ users, /*Object*/ main) {
        // Summary:
        //    Render the schedule table
        // Description:
        //    This function receives the list data from the server and renders the corresponding table

        dojo.subscribe("Calendar.eventMoved", this, "eventMoved");
        dojo.subscribe("Calendar.saveChanges", this, "saveChanges");

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
        //    If there is any row, renders export Button
        if (meta.length > 0) {
            var params = {
                baseClass: "positive",
                iconClass: "export",
                alt:       "Export",
                disabled:  false
            };
            var exportButton = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(exportButton.domNode);
            dojo.connect(exportButton, "onClick", dojo.hitch(this, "exportData"));
        }
    },

    setSaveChangesButton:function(meta) {
        // Summary:
        //    Set the Save changes button
        // Description:
        //    If there is any event for the selected period, render Save changes button
        if (meta.length > 0) {
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

    getEventInfo:function(/*string*/ eventStartDate, /*string*/ eventStartTime, /*string*/ eventEndDate,
                          /*string*/ eventEndTime) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        // Description:
        //    Returns useful data about an event, used to create the schedule table.
        // 1) Whether it is inside or outside the 8:00 to 20:00 range.
        // 2) Time description for the event.

        var result = new Array();  // The variable that will be returned

        // Times have to be rounded according the vertical time schedule divisions
        eventStartTime = this.roundTimeByHourHalves(eventStartTime, this.ROUND_TIME_HALVES_PREVIOUS);
        eventEndTime =   this.roundTimeByHourHalves(eventEndTime, this.ROUND_TIME_HALVES_NEXT);

        temp                  = eventStartTime.split(':');
        var eventStartHour    = parseInt(temp[0], 10);
        var eventStartMinutes = parseInt(temp[1], 10);
        temp                  = eventEndTime.split(':');
        var eventEndHour      = parseInt(temp[0], 10);
        var eventEndMinutes   = parseInt(temp[1], 10);

        // Is at least one minute of the event inside the schedule?
        if (eventStartHour < 20 && ((eventEndHour > 7) && !(eventEndHour == 8 && eventEndMinutes == 0))) {
            // Yes - Show the event inside the schedule
            result['range']     = this.SHOWN_INSIDE_CHART;
            result['startTime'] = eventStartTime;
            result['endTime']   = eventEndTime;
            result['dayOrder']  = this.getDayOrder(eventStartDate);

            // Date-time description
            result['timeDescrip'] = this.eventDateTimeDescrip(this.DATETIME_SHORT, eventStartTime,
                                    eventEndTime);
        } else {

            // No - Shown out of the schedule
            result['range']         = this.SHOWN_OUTSIDE_CHART;
            // Date-time description
            if (this.main.weekList != null) {
                if (eventStartDate == eventEndDate) {
                    result['time'] = this.eventDateTimeDescrip(this.DATETIME_LONG_ONE_DAY, eventStartTime,
                            eventEndTime, eventStartDate, eventEndDate);
                } else {
                    result['time'] = this.eventDateTimeDescrip(this.DATETIME_LONG_MANY_DAYS, eventStartTime,
                        eventEndTime, eventStartDate, eventEndDate);
                }
            } else {
                result['time'] = this.eventDateTimeDescrip(this.DATETIME_SHORT, eventStartTime,
                                                           eventEndTime);
            }
        }

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
                description = startDate + ' ' + this.formatTime(startTime) + ' - ' + endDate + ' '
                    + this.formatTime(endTime);
                break;
            case this.DATETIME_LONG_ONE_DAY:
            default:
                description = startDate + ' ' + this.formatTime(startTime) + ' - ' + this.formatTime(endTime);
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

    updateSizeValues:function() {
        // Summary
        //    Updates internal class variables with current sizes of schedule
        var scheduleBkg      = dojo.byId('scheduleBackground').getElementsByTagName('td');
        this._cellTimeWidth  = scheduleBkg[0].offsetWidth;
        this._cellDayWidth   = scheduleBkg[1].offsetWidth;
        this._cellDayHeight  = scheduleBkg[0].offsetHeight;
        this._cellTimeHeight = scheduleBkg[8].offsetHeight;
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
        var position = row * this._cellTimeHeight;
        if (!isEvent) {
            position += this._cellDayHeight;
        }

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

        return position;
    },

    divPositionToTime:function(verticalPos) {
        // Summary
        //    Receives a schedule position in pixels and returns a time string
        var row     = Math.floor(verticalPos / this._cellTimeHeight);
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
        var height = (this._cellTimeHeight * 24) + this._cellDayHeight;

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
        //    Sets / updates the position and size of each event according to panel and background sizes.

        for (var i in this._events) {
            var left   = this.dayToDivPosition(this._events[i]['dayOrder'], true);
            var top    = this.timeToDivPosition(this._events[i]['startTime'], true, this.TYPE_EVENT_START);
            var width  = this._cellDayWidth - (2 * this.EVENTS_BORDER_WIDTH);
            var bottom = this.timeToDivPosition(this._events[i]['endTime'], true, this.TYPE_EVENT_END);
            var height = bottom - top - (2 * this.EVENTS_BORDER_WIDTH);

            // Is this event part of two or more simulteaneous ones?
            if (this._events[i]['simultWidth']) {
                // Yes
                // Reduce its width
                width = (width / this._events[i]['simultAmount']) - this.EVENTS_BORDER_WIDTH;

                // Maybe change its left position
                left += Math.floor(this._cellDayWidth / this._events[i]['simultAmount']
                    * (this._events[i]['simultOrder'] - 1));
            }

            var eventDiv1 = dojo.byId(this.EVENTS_MAIN_DIV_ID + i);
            var eventDiv2 = dojo.byId('plainDiv' + i);

            this._events[i]['currentLeft']   = left;
            this._events[i]['currentTop']    = top;
            this._events[i]['currentBottom'] = bottom;

            dojo.style(eventDiv1, {
                left: left + 'px',
                top:  top + 'px'
            });
            dojo.style(eventDiv2, {
                width:  width + 'px',
                height: height + 'px'
            });
        }
    },

    connectMoveableClass:function() {
        // Summary:
        //    Something needed for the view to work fine! Strange issue
        for (var i in this._events) {
            var eventDiv         = new phpr.Calendar.Moveable(this.EVENTS_MAIN_DIV_ID + i);
            eventDiv.parentClass = this;
        }
    },

    setEventMinimumSizes:function() {
        // Summary:
        //    Establishes a minimum height for the events, it is the height of one cell.

        for (var i in this._events) {
            var divEventResize = dijit.byId('eventResize' + i);
            var minWidth       = this._cellDayWidth - (2 * this.EVENTS_BORDER_WIDTH);
            var minHeight      = this._cellTimeHeight - (2 * this.EVENTS_BORDER_WIDTH);

            divEventResize.minSize = { w: minWidth, h: minHeight};
        }
    },

    eventMoved:function(node, dropped) {
        // Summary:
        //    Called when an event is moved: both dragged or Y-resized, both in the dragging of the event or the border
        // itself and when mouse is released. Its purpose is to eventually update an internal array, the event
        // description, change shapes of events according to 'simultaneous events' criteria and activate Save button.

        var posLeftNew   = parseInt(node.style.left);
        var posTopNew    = parseInt(node.style.top);
        var posBottomNew = posTopNew + node.offsetHeight;

        var movedEvent = this.nodeIdToEventOrder(node.id);

        var posLeftCurrent   = this._events[movedEvent]['currentLeft'];
        var posTopCurrent    = this._events[movedEvent]['currentTop'];
        var posBottomCurrent = this._events[movedEvent]['currentBottom'];
        var maxBottomAllowed = parseInt(dojo.byId('eventsArea').style.height) - this._cellDayHeight / 2;
        var minTopAllowed    = 0;

        // Is the mouse pointer dragging the event inside schedule boundaries?
        if (posTopNew >= minTopAllowed && posBottomNew <= maxBottomAllowed) {
            // Yes
            // Day did change?
            if (posLeftCurrent != posLeftNew) {
                // Yes - Store new data in the events array
                var dayOrder = this.divPositionToDay(posLeftNew);
                if (dropped) {
                    this._events[movedEvent]['currentLeft'] = posLeftNew;
                    this._events[movedEvent]['hasChanged']  = true;
                    this._events[movedEvent]['dayOrder']    = dayOrder;
                    this._events[movedEvent]['startDate']   = this._weekDays[dayOrder];
                    this._events[movedEvent]['endDate']     = this._weekDays[dayOrder];
                }
            }

            // Times did change?
            var endTimeNew     = this.divPositionToTime(posBottomNew);
            endTimeNew         = this.formatTime(endTimeNew);
            var endTimeCurrent = this._events[movedEvent]['endTime'];
            endTimeNew         = this.formatTime(endTimeNew);
            var startTimeNew   = this.divPositionToTime(posTopNew);
            startTimeNew       = this.formatTime(startTimeNew);

            if (posTopCurrent != posTopNew || posBottomCurrent != posBottomNew || endTimeCurrent != endTimeNew) {
                // Yes - Store new data in the events array
                if (dropped) {
                    this._events[movedEvent]['currentTop']    = posTopNew;
                    this._events[movedEvent]['currentBottom'] = posBottomNew;
                    this._events[movedEvent]['hasChanged']    = true;
                    this._events[movedEvent]['startTime']     = startTimeNew;
                    this._events[movedEvent]['endTime']       = endTimeNew;
                }
            }

            if (dropped) {
                this.enableSaveButton();
            }

            // Update event textual contents
            var timeDescrip  = this.eventDateTimeDescrip(this.DATETIME_SHORT, startTimeNew, endTimeNew);
            var eventDescrip = timeDescrip + ' ' + this._events[movedEvent]['title'] + '<br>'
                + this._events[movedEvent]['notes'];

            dojo.byId('plainDiv' + movedEvent).innerHTML = eventDescrip;
        }

        // Check the items just in case there has been a change concerning simultaneous events
        if (dropped) {
            this.updateSimultEventWidths();
            this.setEventDivsValues();
        }
    },

    nodeIdToEventOrder:function(nodeId) {
        // Summary:
        //    Receives the id of a node of an event (the main div) and returns a number corresponding to the
        // corresponding index in the _events array.

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

        for (var i in this._events) {
            if (this._events[i]['hasChanged']) {
                doSaving = true;
                var id   = this._events[i]['id'];
                content['data[' + id + '][startDate]'] = this._events[i]['startDate'];
                content['data[' + id + '][endDate]']   = this._events[i]['endDate'];
                content['data[' + id + '][startTime]'] = this._events[i]['startTime'];
                content['data[' + id + '][endTime]']   = this._events[i]['endTime'];
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
        // mlp1: if you are in week view and go to list view and then come back to this view, this function doesn't work
        // because there is something wrong with the save button and some functions are called simultaneously as many
        // times as times you go out and come back to week view.
        var debugging = false;
        if (debugging) {
            console.log('ENTRA AL EVENT MOVED')
            var saveButton             = dojo.byId(this._saveChanges.id);
            console.log(saveButton)
        }

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
        //    This function receives an index of this._events and returns whether that event shares visual space with
        // another event, how many events share space with it and the horizontal position that this event will have
        // among the rest

        var result = new Array();

        // The event shares space with another one?
        result['sharing'] = false;

        // How much events share the same width?
        result['amountEvents'] = 1;
        var currentMaxEvents;

        // What's the order of received event among all sharing space ones?
        result['order'] = 1;
        var currentMaxOrder;

        // Split the event duration into halves
        var halves = this.splitPeriodIntoHalves(this._events[currentEvent]['startTime'],
            this._events[currentEvent]['endTime']);

        // For each half of hour this event occupies:
        for (var half in halves) {
            currentMaxEvents     = 1;
            currentMaxOrder      = 1;
            var halfStart        = halves[half]['start'];
            var halfEnd          = halves[half]['end'];

            // For each event...
            for (var otherEvent in this._events) {
                // ...different to the received event...
                if (otherEvent != currentEvent) {
                    // ...that happens in the same day...
                    if (this._events[currentEvent]['dayOrder'] == this._events[otherEvent]['dayOrder']) {
                        // ...check whether it shares time with current half of hour of the received event.
                        // Note: if for example an event finishes at 13:15 and the next one starts at 13:20, then, both
                        // events share visually the half of hour that goes from 13:00 to 13:30.

                        // Is this half sharing time with other event?
                        var superimposed = this.eventDivsSuperimposed(halfStart, halfEnd,
                            this._events[otherEvent]['startTime'], this._events[otherEvent]['endTime']);
                        if (superimposed) {
                            result['sharing'] = true;
                            currentMaxEvents++;
                            if (otherEvent < currentEvent) {
                                currentMaxOrder++;
                            }
                        }
                    }
                }
                // Establish new maximum simulteaneous events for any row
                if (currentMaxEvents > result['amountEvents']) {
                    result['amountEvents'] = currentMaxEvents;
                }
                // Establish the horizontal order for the event among all sharing width ones
                if (currentMaxOrder > result['order']) {
                    result['order'] = currentMaxOrder;
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

        for (var i in this._events) {
            var simultEvents = this.isSharingSpace(i);
            if (simultEvents['sharing']) {
                this._events[i]['simultWidth']  = true;
                this._events[i]['simultAmount'] = simultEvents['amountEvents'];
                this._events[i]['simultOrder']  = simultEvents['order'];
            } else {
                this._events[i]['simultWidth'] = false;
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
    }
});

dojo.provide("phpr.Calendar.Moveable");
dojo.declare("phpr.Calendar.Moveable", dojo.dnd.Moveable, {

    eventHasBeenDragged: null,

    onMoving: function(mover, leftTop) {
        // Summary:
        //    Original function is empty. This one is in charge of making the 'stepped' allike draging. Then calls
        // eventMoved function of Calendar view class.

        // Following value will be checked by onMoveStop function of this class
        this.eventHasBeenDragged = true;

        var cellTimeWidth = this.parentClass._cellTimeWidth;
        var widthDays     = dojo.byId('scheduleBackground').offsetWidth - cellTimeWidth;
        var stepH         = widthDays / 7;
        var stepY         = dojo.byId('scheduleBackground').getElementsByTagName('td')[8].offsetHeight;
        //var stepY         = this.parentClass._cellDayHeight;

        // Define maximum left and top positions
        var HMax        = parseInt(dojo.byId('eventsArea').style.width) - stepH;
        var eventHeight = this.node.offsetHeight;
        var YMax        = parseInt(dojo.byId('eventsArea').style.height) - eventHeight - stepY;

        // Set left position
        var rest = leftTop.l % stepH;
        if (rest < stepH / 2) {
            var left = leftTop.l - rest;
        } else {
            var left = leftTop.l + stepH - rest;
        }

        if (left < 0) {
            left = 0;
        } else if (left > HMax) {
            left = HMax;
        }

        // Simultaneous events
        var movedEvent = this.parentClass.nodeIdToEventOrder(this.node.id);
        movedEvent     = parseInt(movedEvent);
        eventInfo      = this.parentClass._events[movedEvent];
        // Is this event part of simultaneous ones and it is not in the first position?
        if (eventInfo['simultWidth'] && eventInfo['simultOrder'] > 1) {
            left += ((eventInfo['simultOrder'] - 1) / eventInfo['simultAmount'] * stepH) - 1;
        }

        leftTop.l = left;

        // Set top position
        var top = leftTop.t - (leftTop.t % stepY);
        if (top < 0) {
            top = 0;
        } else if (top > YMax) {
            top = stepY * parseInt(YMax / stepY);
        }
        leftTop.t = top;

        // Update descriptive content of the event
        dojo.publish("Calendar.eventMoved", [this.node, false]);
    },

    onMoveStop: function(mover) {
        // Original code:
        // summary: called after every move operation
        dojo.publish("/dnd/move/stop", [mover]);
        dojo.removeClass(dojo.body(), "dojoMove");
        dojo.removeClass(this.node, "dojoMoveItem");

        // Following code has been added for this view, it calls eventMoved view class function or opens the form with
        // the clicked event.

        if (this.eventHasBeenDragged) {
            // The event has been dragged, update descriptive content of the event and internal array
            dojo.publish("Calendar.eventMoved", [this.node, true]);
            this.eventHasBeenDragged = false;
        } else {
            // It was just a click - Open event in the form
            var movedEvent = this.parentClass.nodeIdToEventOrder(this.node.id);
            var eventId    = this.parentClass._events[movedEvent]['id'];
            dojo.publish('Calendar.showFormFromList', [eventId]);
        }
    }
});

dojo.provide("phpr.Calendar.ResizeHandle");
dojo.declare("phpr.Calendar.ResizeHandle", dojox.layout.ResizeHandle, {
    _changeSizing: function(/*Event*/ e){
        // summary: apply sizing information based on information in (e) to attached node
        var tmp = this._getNewCoords(e);
        if(tmp === false){ return; }

        // Stepped dragging added for this view
        var step           = dojo.byId('scheduleBackground').getElementsByTagName('td')[8].offsetHeight;
        var sizerDivHeight = this.domNode.offsetHeight;
        var proposedHeight = tmp['h'];
        var sizerToBottom  = Math.floor(sizerDivHeight / 2);
        var steppedHeight  = sizerToBottom + proposedHeight - (proposedHeight % step);

        // Maximum height - Set for the event end time not to be after 20:00
        var maxY      = parseInt(dojo.byId('eventsArea').offsetHeight);
        var eventTopY = parseInt(this.targetDomNode.parentNode.style.top);
        var proposedY = eventTopY + proposedHeight + step + sizerDivHeight;
        if (proposedY <= maxY) {
            tmp['h'] = steppedHeight;
        } else {
            return;
        }

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

        dojo.publish("Calendar.eventMoved", [this.targetDomNode.parentNode, false]);
    },

    onResize: function(e){
        // Summary:
        //    Original function is empty. This one calls eventMoved calendar view class function.
        // Stub fired when sizing is done. Fired once
        //  after resize, or often when `intermediateChanges` is
        //  set to true.
        dojo.publish("Calendar.eventMoved", [this.targetDomNode.parentNode, true]);
    }
});
