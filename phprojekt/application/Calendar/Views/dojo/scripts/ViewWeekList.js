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

dojo.provide("phpr.Calendar.ViewWeekList");

dojo.declare("phpr.Calendar.ViewWeekList", phpr.Calendar.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar Week List
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    _header:              Array(7),
    _schedule:            Array(24),
    _furtherEvents:       Array(),
    _weekDays:            Array(7),
    events:               Array(),
    _htmlEventDivsAmount: null,

    beforeConstructor:function() {
        // Summary:
        //    Calls the weekDays array creation function, before constructor function
        this.setWeekDays();
    },

    afterConstructor:function() {
        // Summary:
        //    Loads the data from the database
        phpr.DataStore.addStore({url: this.url, noCache: true});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});
    },

    setUrl:function() {
        // Summary:
        //    Sets the url to get the data from
        // Description:
        //    Sets the url to get the data from
        this.url = phpr.webpath + "index.php/" + phpr.module + "/index/jsonPeriodList/dateStart/" + this._weekDays[0]
            + "/dateEnd/" + this._weekDays[6];
    },

    onLoaded:function(dataContent) {
        // Summary:
        //    This function is called when the request to the DB is received
        // Description:
        //    It parses that json info and prepares the appropriate arrays so that it can be rendered correctly the
        // template and the events.
        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Render export Button?
        this.setExportButton(meta);
        this.setSaveChangesButton(meta);

        var content = phpr.DataStore.getData({url: this.url});

        this._furtherEvents['show']   = false;
        this._furtherEvents['events'] = new Array();

        this.fillHeaderArray();
        this.fillScheduleArray();
        this.fillEventsArrays(content);

        var eventsAttr         = new Array();
        eventsAttr.borderWidth = this.EVENTS_BORDER_WIDTH;
        eventsAttr.divIdPre    = this.EVENTS_MAIN_DIV_ID;

        // All done, let's render the template
        this.render(["phpr.Calendar.template", "weekList.html"], dojo.byId('gridBox'), {
            widthTable:           this._widthTable,
            widthHourColumn:      this._widthHourColumn,
            header:               this._header,
            schedule:             this._schedule,
            events:               this.events,
            furtherEvents:        this._furtherEvents,
            furtherEventsMessage: phpr.nls.get('Further events'),
            eventsAttr:           eventsAttr
        });
        dojo.publish('Calendar.connectMouseScroll');
        dojo.publish('Calendar.connectViewResize');

        this._htmlEventDivsAmount = this.events.length;

        this.setVarsAndDivs();
        this.classesSetup(true);
    },

    exportData:function() {
        // Summary:
        //    Opens a new window in CSV mode
        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvPeriodList" + "/dateStart/"
            + this._weekDays[0] + "/dateEnd/" + this._weekDays[6]);

        return false;
    },

    setWeekDays:function() {
        // Summary:
        //    Fills the weekDays array with all the dates of the selected week in string format.
        var selectedDate = this.stringToDate();
        var dayTemp;

        for (var i = 0; i < 7; i ++) {
            dayTemp           = dojo.date.add(selectedDate, 'day', i + 1 - selectedDate.getDay());
            this._weekDays[i] = this.formatDate(dayTemp.getFullYear() + '-' + (dayTemp.getMonth() + 1) + '-'
                + dayTemp.getDate());
        }
    },

    fillScheduleArray:function() {
        // Summary:
        //    This function fills the schedule structure and background array
        // Description:
        //     Fills the array with the header and all the possible points in time for this week view: 8:00, 8:30, 9:00
        // and so on, until 19:30. Each of that rows will have as many columns as days. Also sets for every row whether
        // it is even or not.
        for (var hour = 8; hour < 20; hour++) {
            for (var half = 0; half < 2; half++) {
                var minute = half * 30;
                var row    = ((hour - 8) * 2) + half;

                this._schedule[row] = new Array(7);
                for (var day = 0; day < 7; day ++) {
                    this._schedule[row][day] = new Array();
                }

                this._schedule[row]['hour'] = this.formatTime(hour + ':' + minute);
                if (Math.floor(row / 2) == (row / 2)) {
                    // Even row
                    this._schedule[row]['even'] = true;
                } else {
                    // Odd row
                    this._schedule[row]['even'] = false;
                }
            }
        }
    },

    fillHeaderArray:function() {
        // Summary:
        //    Fills the header array with the main row of the table.
        this._header['columnsWidth'] = Math.floor((100 - this._widthHourColumn) / 7);
        var daysAbbrev               = new Array(phpr.nls.get('Mo'),
                                                 phpr.nls.get('Tu'),
                                                 phpr.nls.get('We'),
                                                 phpr.nls.get('Th'),
                                                 phpr.nls.get('Fr'),
                                                 phpr.nls.get('Sa'),
                                                 phpr.nls.get('Su'));

        this._header['days'] = new Array();
        for (var i = 0; i < 7; i ++) {
            this._header['days'][i]                 = new Array();
            this._header['days'][i]['dayAbbrev']    = daysAbbrev[i];
            this._header['days'][i]['date']         = this._weekDays[i];
        }
    },

    fillEventsArrays:function(content) {
        // Summary:
        //    Parses and analyses 'content' contents and puts every event in 'events' array, if there are any multiple
        // days event, they get splitted into each day events with a connection among them.

        this.events                 = new Array();
        furtherEventsTemp           = new Array();
        furtherEventsTemp['show']   = false;
        furtherEventsTemp['events'] = new Array();

        // For each event received from the DB
        for (var event in content) {

            var eventsInfo     = new Array();
            var id             = content[event]['id'];
            var singleDayEvent = false;

            // Process title and note
            var title = this.htmlEntities(content[event]['title']);
            var notes = this.htmlEntities(content[event]['notes']);
            notes     = notes.replace('\n', '<br />');

            // What kind of event is this one concerning multiple day events?
            if (content[event]['startDate'] == content[event]['endDate']) {
                // Single day event
                singleDayEvent = true;
            } else {
                // Multiple days event
                var eventsSplitted = this.splitMultDayEvent(content[event]['startDate'], content[event]['startTime'],
                    content[event]['endDate'], content[event]['endTime']);

                // The event has at least 1 minute inside the 8:00 to 20:00 grid?
                if (eventsSplitted['eventShownInGrid']) {
                    // Yes - It uses one or more day columns.
                    // For each day column (it can't be used 'for (var i in eventsSplitted)':
                    for (var i = 0; i < eventsSplitted.length; i ++) {
                        var eventSplitted = eventsSplitted[i];
                        if (eventSplitted['dayShownInGrid']) {
                            // Obtain more info
                            eventSplitted['multDay']    = true;
                            eventsInfo[i]               = this.processEventInfo(eventSplitted);
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
                eventsInfo[0]          = this.processEventInfo(content[event], event);
                eventsInfo[0]['shown'] = true;
            }

            // Fill the 'events' class array
            var parent = -1;
            for (var i in eventsInfo) {
                var eventInfo = eventsInfo[i];
                // Events inside the grid
                if (eventInfo['range'] == this.SHOWN_INSIDE_CHART) {
                    eventInfo['hasChanged'] = false;
                    parent                  = this.addGridEventToArray(eventInfo, id, title, notes, parent,
                        content[event]['startDate'], content[event]['startTime'], content[event]['endDate'],
                        content[event]['endTime']);

                } else if (eventInfo['range'] == this.SHOWN_OUTSIDE_CHART) {
                    // Events outside the grid: located under it as textual strings
                    furtherEventsTemp['show'] = true;
                    var nextPosition          = furtherEventsTemp['events'].length;

                    furtherEventsTemp['events'][nextPosition]          = new Array();
                    furtherEventsTemp['events'][nextPosition]['id']    = id;
                    furtherEventsTemp['events'][nextPosition]['time']  = eventInfo['timeDescrip'];
                    furtherEventsTemp['events'][nextPosition]['title'] = title;
                }
            }
        }

        this.updateSimultEventWidths();

        // Clean the repeated 'further events'. Copy the rest to the global variable
        if (furtherEventsTemp['show']) {
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
                    this._furtherEvents['events'][nextEvent]          = new Array();
                    this._furtherEvents['events'][nextEvent]['id']    = furtherEventsTemp['events'][event]['id'];
                    this._furtherEvents['events'][nextEvent]['time']  = furtherEventsTemp['events'][event]['time'];
                    this._furtherEvents['events'][nextEvent]['title'] = furtherEventsTemp['events'][event]['title'];
                }
            }
        }
    },

    getDayOrder:function(date) {
        // Summary:
        //    Receives a date like '2009-10-26' and returns the column position number
        for (var i = 0; i < 7; i++) {
            if (this._weekDays[i] == date) {
                return i;
            }
        }
    },

    splitMultDayEvent:function(startDateString, startTimeString, endDateString, endTimeString) {
        // Summary:
        //    Splits a multiple days event into as many events as days it lasts and sets to each one dates and times.
        // If a day is out of present week, it is not returned.
        // It also checkes whether the event has at least 1 minute to be shown inside the grid. If not, then it has to
        // be shown under the grid in 'Further events' section

        var startDate    = dojo.date.stamp.fromISOString(startDateString);
        var endDate      = dojo.date.stamp.fromISOString(endDateString);
        var amountEvents = dojo.date.difference(startDate, endDate) + 1;
        var events       = new Array();
        var monday       = dojo.date.stamp.fromISOString(this._weekDays[0]);
        var sunday       = dojo.date.stamp.fromISOString(this._weekDays[6]);

        // Whether the event has to show at least 1 minute inside the grid, if not, it will be shown under the grid in
        // 'Further events' section.
        events['eventShownInGrid'] = false;

        // For each resulting day
        for (var i = 0; i < amountEvents; i ++) {
            var oneDay = dojo.date.add(startDate, 'day', i);
            // If the first day starts after (or equal to) 20:00 then don't show it
            if (i == 0) {
                if (this.getMinutesDiff(startTimeString, '20:00') <= 0) {
                    continue;
                }
            }
            // If last day starts after (or equal) to 8:00 then don't show it
            if (i == amountEvents - 1) {
                if (this.getMinutesDiff(endTimeString, '8:00') >= 0) {
                    continue;
                }
            }

            var nextPos                  = events.length;
            events[nextPos]              = new Array();
            var tmp                      = dojo.date.stamp.toISOString(oneDay);
            events[nextPos]['startDate'] = tmp.substr(0, 10);

            // Is this day inside the selected week?
            if ((dojo.date.compare(oneDay, monday) >= 0) && (dojo.date.compare(oneDay, sunday) <= 0)) {
                // Yes
                events[nextPos]['shown'] = true;
            } else {
                // No
                events[nextPos]['shown'] = false;
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

    updateMultDaysEvent:function(index) {
        // Summary:
        //    Updates the date and time of all the divs corresponding to a specific multiple days event except for the
        // moved div, which has just been dragged. Current values will get added the difference between the original
        // position of the moved div and the dropped position. Also it will be prepared saving data.
        //   Since new divs can appear after the dragging, or existing divs may dissappear, the whole series of divs for
        // this event will be calculated again. Old ones will be deleted from 'events' array and new ones will be added.

        // 1 - Obtain days and minutes difference of the dragged event compared with the original position, prepare
        // other variables
        var parentDiv     = this.events[index]['multDayParent'];
        var dateOrig      = this.events[index]['multDayDateOrig'];
        var startTimeOrig = this.events[index]['multDayStartTimeOrig'];
        dateOrig          = dojo.date.stamp.fromISOString(dateOrig);
        var dateNow       = this.events[index]['date'];
        dateNow           = dojo.date.stamp.fromISOString(dateNow);
        var diffDays      = dojo.date.difference(dateOrig, dateNow);
        var diffMinutes   = this.getMinutesDiff(startTimeOrig, this.events[index]['startTime']);
        var firstEvent    = null;
        var lastEvent     = null;
        var movedEvent    = this.events[index];

        // 2 - Pick whole event coordinates
        var parent    = this.events[index]['multDayParent'];
        var startDate = this.events[parent]['multDayData']['startDate'];
        var startTime = this.events[parent]['multDayData']['startTime'];
        var endDate   = this.events[parent]['multDayData']['endDate'];
        var endTime   = this.events[parent]['multDayData']['endTime'];
        if (diffMinutes > 0) {
            // If div has been dragged vertically then round the time in halves
            startTime = this.roundTimeByHourHalves(startTime, this.ROUND_TIME_HALVES_PREVIOUS);
            endTime   = this.roundTimeByHourHalves(endTime, this.ROUND_TIME_HALVES_NEXT);
        }

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

        date1              = dojo.date.add(date1, "day", diffDays);
        date1              = dojo.date.add(date1, "minute", diffMinutes);
        date1              = dojo.date.stamp.toISOString(date1);
        date2              = dojo.date.add(date2, "day", diffDays);
        date2              = dojo.date.add(date2, "minute", diffMinutes);
        date2              = dojo.date.stamp.toISOString(date2);
        var wholeStartDate = date1.substr(0, 10);
        var wholeStartTime = date1.substr(11, 5);
        var wholeEndDate   = date2.substr(0, 10);
        var wholeEndTime   = date2.substr(11, 5);

        // 4 - Delete all divs of dragged event from main array
        this.events = dojo.filter(this.events,
            function(elem) {
                return elem['multDayParent'] != parentDiv;
            }
        )

        // 5 - Generate new this.events elements for this event (one per day shown in the grid)
        var eventsSplitted = this.splitMultDayEvent(wholeStartDate, wholeStartTime, wholeEndDate, wholeEndTime);
        var eventsInfo     = new Array();
        var parent         = -1;
        // For each day column (it can't be used 'for (var i in eventsSplitted)':
        for (var i = 0; i < eventsSplitted.length; i ++) {
            var eventSplitted = eventsSplitted[i];
            if (eventSplitted['dayShownInGrid']) {
                // Obtain more info
                eventSplitted['multDay'] = true;
                eventInfo                = this.processEventInfo(eventSplitted);
                eventInfo['multDayPos']  = eventSplitted['multDayPos'];
                eventInfo['shown']       = eventSplitted['shown'];
                if (eventSplitted['multDayPos'] == this.DATETIME_MULTIDAY_END) {
                    eventInfo['hasResizeHandler'] = true;
                } else {
                    eventInfo['hasResizeHandler'] = false;
                }
                eventInfo['hasChanged'] = true;
            }
            parent = this.addGridEventToArray(eventInfo, movedEvent['id'], movedEvent['title'], movedEvent['notes'],
                parent, wholeStartDate, wholeStartTime, wholeEndDate, wholeEndTime);
        }
    },

    toggleMultDaysDivs:function(index, visible) {
        // Summary:
        //    Makes it visible or invisible all the divs of a multiple days event but the one being dragged.

        var id = this.events[index]['id'];
        for (var i in this.events) {
            if (i != index && id == this.events[i]['id']) {
                // This is another div of received event!
                if (!visible) {
                    var mode = 'hidden';
                } else {
                    var mode = 'visible';
                }
                dojo.style(dojo.byId(this.EVENTS_MAIN_DIV_ID + i), {
                    visibility: mode
                });
            }
        }
    },

    getDayOrder:function(dateString) {
        // Summary:
        //    Receives a date string and returns the column grid order from 0 to 6
        var result = -1;
        for (var i in this._weekDays) {
            if (this._weekDays[i] == dateString) {
                result = i;
                break;
            }
        }
        return result;
    },

    getMinutesDiff:function(time1, time2) {
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

    addGridEventToArray: function(eventInfo, id, title, notes, parent, wholeStartDate, wholeStartTime, wholeEndDate,
            wholeEndTime) {
        // Summary:
        //    Adds an event to 'events' class array. Returns parent index.

        var nextEvent = this.events.length;
        if (this.events[0] == undefined) {
            nextEvent = 0;
        }
        var newEventDiv            = new Array();
        newEventDiv['shown']       = eventInfo['shown'];
        newEventDiv['order']       = nextEvent; // For Django template
        newEventDiv['id']          = id;
        newEventDiv['title']       = title;
        newEventDiv['timeDescrip'] = eventInfo['timeDescrip'];
        newEventDiv['notes']       = notes;
        newEventDiv['class']       = '';
        newEventDiv['date']        = eventInfo['date']
        newEventDiv['startTime']   = eventInfo['startTime'];
        newEventDiv['endTime']     = eventInfo['endTime'];
        newEventDiv['dayOrder']    = this.getDayOrder(eventInfo['date']);
        newEventDiv['hasChanged']  = eventInfo['hasChanged'];
        // To check whether the event is pending to be saved - The last position where it was dropped, so if
        // user drags it and leaves it in the same position, it doesn't need to be saved.
        newEventDiv['posEventDB'] = eventInfo['date'] + '-' + eventInfo['startTime']
             + '-' + eventInfo['endTime'];

        // Multiple day event? Set position among rest of divs of same event, also set if this div has to
        // allow Y resizing.
        newEventDiv['multDay'] = eventInfo['multDay'];
        if (eventInfo['multDay']) {
            if (parent == -1) {
                var parent = nextEvent;
                newEventDiv['multDayData']              = new Array();
                newEventDiv['multDayData']['startDate'] = wholeStartDate;
                newEventDiv['multDayData']['startTime'] = wholeStartTime;
                newEventDiv['multDayData']['endDate']   = wholeEndDate;
                newEventDiv['multDayData']['endTime']   = wholeEndTime;
            }
            newEventDiv['multDayParent']    = parent;
            newEventDiv['multDayPos']       = eventInfo['multDayPos'];
            newEventDiv['hasResizeHandler'] = eventInfo['hasResizeHandler'];
        } else {
            newEventDiv['hasResizeHandler'] = true;
        }
        // Whether this multiple days event is being dragged
        newEventDiv['multDayDragging'] = false;

        // Will be filled later:
        newEventDiv['currentLeft'] = null;
        newEventDiv['currentTop']  = null;

        // Put event div contents into class internal array
        this.events[nextEvent] = newEventDiv;

        return parent;
    },

    // Debugging function
    dump:function (arr,level) {
        var dumped_text = "";
        if(!level) level = 0;

        //The padding given at the beginning of the line.
        var level_padding = "";
        for(var j=0;j<level+1;j++) level_padding += "    ";

        if(typeof(arr) == 'object') { //Array/Hashes/Objects
            for(var item in arr) {
                var value = arr[item];

                if(typeof(value) == 'object') { //If it is an array,
                    dumped_text += level_padding + "'" + item + "' ...\n";
                    dumped_text += this.dump(value,level+1);
                } else {
                    dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
                }
            }
        } else { //Stings/Chars/Numbers etc.
            dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
        }
        return dumped_text;
    }
});
