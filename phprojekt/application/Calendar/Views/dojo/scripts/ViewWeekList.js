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
    _header:        Array(7),
    _schedule:      Array(24),
    _furtherEvents: Array(),
    _weekDays:      Array(7),
    events:         Array(),

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

        this.setVarsAndDivs();
        this.classesSetup();
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
                var eventsSplitted = this.splitMultDayEvent(content[event]);

                // The event has at least 1 minute inside the 8:00 to 20:00 grid?
                if (eventsSplitted['eventShownInGrid']) {
                    // Yes - It uses one or more day columns. Split it into each day events and keep connection among
                    // them
                    // For each day column (it can't be used 'for (var i in eventsSplitted)':
                    for (var i = 0; i < eventsSplitted.length; i ++) {
                        var eventInfo = eventsSplitted[i];
                        if (eventInfo['dayShownInGrid']) {
                            // Obtain more info
                            eventInfo['multDay'] = true;
                            eventsInfo[i]        = this.processEventInfo(eventInfo);
                            if (eventInfo['multDayPos'] == this.DATETIME_MULTIDAY_END) {
                                eventsInfo[i]['hasResizeHandler'] = true;
                            } else {
                                eventsInfo[i]['hasResizeHandler'] = false;
                            }
                        }
                    }
                } else {
                    // No - Show it as a 1 day out-of-schedule event
                    singleDayEvent = true;
                }
            }

            if (singleDayEvent) {
                var eventInfo        = content[event];
                eventInfo['multDay'] = false;
                eventsInfo[0]        = this.processEventInfo(content[event], event);
            }

            // Fill the 'events' class array
            for (var i in eventsInfo) {
                var eventInfo = eventsInfo[i];

                // Events inside the grid
                if (eventInfo['range'] == this.SHOWN_INSIDE_CHART) {
                    var nextEvent = this.events.length;
                    if (this.events[0] == undefined) {
                        nextEvent = 0;
                    }

                    this.events[nextEvent]                = new Array();
                    this.events[nextEvent]['order']       = nextEvent; // For Django template
                    this.events[nextEvent]['id']          = id;
                    this.events[nextEvent]['title']       = title;
                    this.events[nextEvent]['timeDescrip'] = eventInfo['timeDescrip'];
                    this.events[nextEvent]['notes']       = notes;
                    this.events[nextEvent]['class']       = '';
                    this.events[nextEvent]['date']        = eventInfo['date']
                    this.events[nextEvent]['startTime']   = eventInfo['startTime'];
                    this.events[nextEvent]['endTime']     = eventInfo['endTime'];
                    this.events[nextEvent]['dayOrder']    = this.getDayOrder(eventInfo['date']);
                    this.events[nextEvent]['hasChanged']  = false;
                    // To check whether the event is pending to be saved - The last position where it was dropped, so if
                    // user drags it and leaves it in the same position, it doesn't need to be saved.
                    this.events[nextEvent]['posEventDB'] = eventInfo['date'] + '-' + eventInfo['startTime']
                         + '-' + eventInfo['endTime'];

                    // Multiple day event? Set position among rest of divs of same event, also set if this div has to
                    // allow Y resizing.
                    this.events[nextEvent]['multDay'] = eventInfo['multDay'];
                    if (eventInfo['multDay']) {
                        this.events[nextEvent]['multDayPos']       = eventInfo['multDayPos'];
                        this.events[nextEvent]['hasResizeHandler'] = eventInfo['hasResizeHandler'];
                    } else {
                        this.events[nextEvent]['hasResizeHandler'] = true;
                    }

                    // Will be filled later:
                    this.events[nextEvent]['currentLeft'] = null;
                    this.events[nextEvent]['currentTop']  = null;

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

    splitMultDayEvent:function(eventInfo) {
        // Summary:
        //    Splits a multiple days event into as many events as days it lasts and sets to each one dates and times.
        // If a day is out of present week, it is not returned.
        // It also checkes whether the event has at least 1 minute to be shown inside the grid. If not, then it has to
        // be shown under the grid in 'Further events' section

        var startDate    = dojo.date.stamp.fromISOString(eventInfo['startDate']);
        var endDate      = dojo.date.stamp.fromISOString(eventInfo['endDate']);
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

            // Is this day inside the week?
            if ((dojo.date.compare(oneDay, monday) >= 0) && (dojo.date.compare(oneDay, sunday) <= 0)) {
                // Yes
                var nextPos                  = events.length;
                events[nextPos]              = new Array();
                var tmp                      = dojo.date.stamp.toISOString(oneDay);
                events[nextPos]['startDate'] = tmp.substr(0, 10);

                // Whether this day has a part to be shown in the day column from 8:00 to 20:00.
                events[nextPos]['dayShownInGrid'] = false;

                // Set times
                if (i == 0) {
                    // First day
                    events[nextPos]['startTime']  = eventInfo['startTime'];
                    events[nextPos]['endTime']    = '20:00';
                    events[nextPos]['multDayPos'] = this.DATETIME_MULTIDAY_START;
                    // This day has to be shown inside the grid?
                    var tmp  = eventInfo['startTime'].split(':');
                    var hour = parseInt(tmp[0], 10);
                    if (hour < 20) {
                        events[nextPos]['dayShownInGrid'] = true;
                        events['eventShownInGrid']        = true;
                    }
                } else {
                    // Between second and last day
                    events[nextPos]['startTime'] = '8:00';
                    if (events[nextPos]['startDate'] == eventInfo['endDate']) {
                        // Last day
                        events[nextPos]['endTime']    = eventInfo['endTime'];
                        events[nextPos]['multDayPos'] = this.DATETIME_MULTIDAY_END;
                        // This day has to be shown inside the grid?
                        var tmp     = eventInfo['endTime'].split(':');
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
        }

        return events;
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
