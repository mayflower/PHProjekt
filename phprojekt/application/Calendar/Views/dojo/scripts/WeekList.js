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
 * @version    $Id:$
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Calendar.WeekList");

dojo.declare("phpr.Calendar.WeekList", phpr.Component, {
    // Summary:
    //    Class for displaying a Calendar Week List
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    main:                null,
    id:                  0,
    url:                 null,
    _tagUrl:             null,
    _date:               null,
    _widthTable:         0,
    _widthHourColumn:    8,
    _header:             Array(7),
    _schedule:           Array(48),
    _furtherEvents:      Array,
    _weekDays:           Array(7),
    // Constants used by the function getEventInfo:
    EVENT_TIME_START:    0,
    EVENT_TIME_INSIDE:   1,
    EVENT_TIME_OUTSIDE:  2,
    EVENT_INSIDE_CHART:  0,
    EVENT_OUTSIDE_CHART: 1,
    // Constants used to define a calendar event time in comparison to a specific moment:
    EVENT_NONE:          0,
    EVENT_BEGIN:         1,
    EVENT_CONTINUES:     2,

    constructor:function(/*Object*/ main, /*Int*/ id, /*String*/ date) {
        // Summary:
        //    Render the schedule table
        // Description:
        //    This function receives the list data from the server and renders the corresponding table
        this.main  = main;
        this.id    = id;
        this.url   = null;
        this._date = date;

        this.setWeekDays();
        this.setUrl();

        if (dojo.isIE) {
            // This is to avoid a pair of scrollbars that eventually appears (not when first loading)
            this._widthTable = 97;
        } else {
            this._widthTable = 100;
        }
        this._widthHourColumn = 7;

        phpr.DataStore.addStore({url: this.url, noCache: true});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});

        // Draw the tags
        this.showTags();
    },

    setUrl:function() {
        // Summary:
        //    Sets the url to get the data from
        // Description:
        //    Sets the url to get the data from
        this.url = phpr.webpath + "index.php/" + phpr.module + "/index/jsonPeriodList"
            + "/dateStart/" + this._weekDays[0] + "/dateEnd/" + this._weekDays[6];
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

    getEventInfo:function(/*string*/ eventStartTime_String, /*string*/ eventEndTime_String, /*string*/ askedTime,
                          /*string*/ eventStartDate_String) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        // Description:
        //    Returns useful data about an event, used to create the schedule table. E.g.: whether it is inside or
        // outside the 8:00 to 20:00 range,  in what row and day of the shown table should it start and end.
        // If the 'askedTime' parameter is set, then which of three possibilities happens: 1) That time matchs the
        // start time  2) Is just inside the period  3) It is outside that period.

        // IMPORTANT NOTE: because of this function having lots of time and date variables, I added the suffix
        // '_Date' to the ones of Date type format, for making all these no so difficult to understand.
        // Also, to just a few of the String variables, there was added the '_String' suffix, with the same purpose.

        var result                 = new Array(); // The variable that will be returned
        var scheduleStartTime_Date = new Date();
        var scheduleEndTime_Date   = new Date();

        // Set the week day (0 to 6)
        for (var i = 0; i < 7; i ++) {
            if (this._weekDays[i] == eventStartDate_String) {
                result['weekDay'] = i;
            }
        }

        scheduleStartTime_Date.setHours(8);
        scheduleStartTime_Date.setMinutes(0);
        scheduleStartTime_Date.setSeconds(0);
        scheduleEndTime_Date.setHours(20);
        scheduleEndTime_Date.setMinutes(0);
        scheduleEndTime_Date.setSeconds(0);

        // Convert event start and end Strings into Date formats
        var temp              = eventStartTime_String.split(':');
        var eventStartHour    = parseInt(temp[0], 10);
        var eventStartMinutes = parseInt(temp[1], 10);
        var temp              = eventEndTime_String.split(':');
        var eventEndHour      = parseInt(temp[0], 10);
        var eventEndMinutes   = parseInt(temp[1], 10);

        // Round downwards the event start time to the nearest quarter of hour
        if ((eventStartMinutes/15) != Math.floor(eventStartMinutes/15)) {
            eventStartMinutes = Math.floor(eventStartMinutes/15) * 15;
        }

        // Round upwards the event end time to the nearest quarter of hour
        if ((eventEndMinutes/15) != Math.ceil(eventEndMinutes/15)) {
            eventEndMinutes = Math.ceil(eventEndMinutes/15) * 15;
            if (eventEndMinutes == 60) {
                eventEndHour ++;
                eventEndMinutes = 0;
            }
        }

        var eventStartTime_Date = new Date();
        var eventEndTime_Date   = new Date();

        eventStartTime_Date.setHours(eventStartHour);
        eventStartTime_Date.setMinutes(eventStartMinutes);
        eventStartTime_Date.setSeconds(0);
        eventEndTime_Date.setHours(eventEndHour);
        eventEndTime_Date.setMinutes(eventEndMinutes);
        eventEndTime_Date.setSeconds(0);

        // Is the event completely out of range (before or after 8:00 to 20:00) ?
        if (((scheduleStartTime_Date >= eventStartTime_Date) && (scheduleStartTime_Date >= eventEndTime_Date))
            || ((scheduleEndTime_Date <= eventStartTime_Date) && (scheduleEndTime_Date <= eventEndTime_Date))) {
            result['range'] = this.EVENT_OUTSIDE_CHART;
            result['type']  = this.EVENT_TIME_OUTSIDE;
            return result;
        } else {
            result['range'] = this.EVENT_INSIDE_CHART;
        }

        // If start time happens before 8:00, the schedule must show it from the 8:00 row (but the text will show
        // the real info)
        if (eventStartTime_Date < scheduleStartTime_Date) {
            eventStartTime_Date = scheduleStartTime_Date;
        }

        // If end time is after 20:00, the schedule must show it until the 19:45 row inclusive (but the text will
        // show the real info)
        if (eventEndTime_Date > scheduleEndTime_Date) {
            eventEndTime_Date = scheduleEndTime_Date;
        }

        var quarterBeginning       = eventStartTime_Date.getTime() - scheduleStartTime_Date.getTime();
        var duration               = eventEndTime_Date.getTime() - eventStartTime_Date.getTime();
        result['quarterBeginning'] = Math.floor(quarterBeginning / (1000*60*15));
        result['quartersDuration'] = Math.floor(duration / (1000*60*15));

        if (askedTime != null) {
            var temp             = askedTime.split(':');
            var askedTimeHour    = temp[0];
            var askedTimeMinutes = temp[1];

            // Round downwards the time to search for, to the nearest quarter of hour
            if ((askedTimeMinutes/15) != Math.floor(askedTimeMinutes/15)) {
                askedTimeMinutes = Math.floor(askedTimeMinutes/15) * 15;
            }
            var askedTime_Date = new Date();
            askedTime_Date.setHours(askedTimeHour);
            askedTime_Date.setMinutes(askedTimeMinutes);
            askedTime_Date.setSeconds(0);

            // Perform the comparison
            if (eventStartTime_Date.getTime() == askedTime_Date.getTime()) {
                result['type'] = this.EVENT_TIME_START;
            } else if ((eventStartTime_Date.getTime() < askedTime_Date.getTime())
                       && (askedTime_Date.getTime() < eventEndTime_Date.getTime())) {
                result['type'] = this.EVENT_TIME_INSIDE;
            } else {
                result['type'] = this.EVENT_TIME_OUTSIDE;
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
        var temp    = date.split('-');
        var year    = temp[0];
        var month   = temp[1];
        var day     = temp[2];
        var result  = year + '-' + dojo.number.format(month, {pattern: '00'}) + '-'
            + dojo.number.format(day, {pattern: '00'});
        return result;
    },

    onLoaded:function(dataContent) {
        // Summary:
        //    This function is called when the request to the DB is received
        // Description:
        //    It parses that json info and prepares an apropriate array so that the template can render
        // appropriately the TABLE html element.
        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Render export Button?
        this.setExportButton(meta);

        var content = phpr.DataStore.getData({url: this.url});

        this._furtherEvents['show']   = false;
        this._furtherEvents['events'] = new Array();

        this.fillHeaderArray();

        // Fill the structure of the main array
        this.fillScheduleArrayStructure_part1();
        this.determineColumnsPerDay(content);
        this.fillScheduleArrayStructure_part2();

        // Fill it with the data of the events
        this.fillScheduleArrayData(content);

        // All done, let's render the template
        this.render(["phpr.Calendar.template", "weekList.html"], dojo.byId('gridBox'), {
            widthTable          : this._widthTable,
            widthHourColumn     : this._widthHourColumn,
            header              : this._header,
            schedule            : this._schedule,
            furtherEvents       : this._furtherEvents,
            furtherEventsMessage: phpr.nls.get('Further events')
        });
    },

    exportData:function() {
        // Summary:
        //    Opens a new window in CSV mode
        // Description:
        //    Opens a new window in CSV mode
        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvPeriodList"
            + "/dateStart/" + this._weekDays[0] + "/dateEnd/" + this._weekDays[6]);
        return false;
    },

    updateData:function() {
        // Summary:
        //    Deletes the cache for this Week List table
        // Description:
        //    Deletes the cache for this Week List table
        phpr.DataStore.deleteData({url: this.url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    },

    htmlEntities:function(str) {
        // Summary:
        //    Converts HTML tags and code to readable HTML entities
        // Description:
        //    Converts HTML tags and code to readable HTML entities.
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

    setWeekDays:function() {
        // Summary:
        //    Fills the weekDays array with all the dates of the week in string format.
        var dCurrentDate = dijit.byId('selectDate').attr('value');
        var dayTemp;

        for (var i = 0; i < 7; i ++) {
            dayTemp           = dojo.date.add(dCurrentDate, 'day', i + 1 - this._date.getDay());
            this._weekDays[i] = this.formatDate(dayTemp.getFullYear() + '-' + (dayTemp.getMonth() + 1) + '-'
                + dayTemp.getDate());
        }
    },

    fillScheduleArrayStructure_part1:function() {
        // Summary:
        //    This function fills the week days arrays with the rows for every quarter of hour.
        // Description:
        // Fills the array with the header and all the possible points in time for this day view: 8:00, 8:15, 8:30
        // and so on, until 19:45. Each of that rows will have as many columns as days plus simultaneous events exist

        for (var i = 0; i < 48; i ++) {
            this._schedule[i] = new Array(7);
            for (var j = 0; j < 7; j ++) {
                this._schedule[i][j] = new Array();
            }
        }

        for (var hour = 8; hour < 20; hour++) {
            for (var quarter = 0; quarter < 4; quarter++) {
                var minute = quarter * 15;
                var row = ((hour - 8) * 4) + quarter;
                this._schedule[row]['hour'] = this.formatTime(hour + ':' + minute);
            }
        }
    },

    fillHeaderArray:function() {
        // Summary:
        //    Fills the header array with the main row of the table.

        this._header['columnsWidth'] = -1;
        var daysAbbrev = new Array(phpr.nls.get('Mo'),
                                   phpr.nls.get('Tu'),
                                   phpr.nls.get('We'),
                                   phpr.nls.get('Th'),
                                   phpr.nls.get('Fr'),
                                   phpr.nls.get('Sa'),
                                   phpr.nls.get('Su'));

        this._header['days'] = new Array();
        for (var i = 0; i < 7; i ++) {
            this._header['days'][i]                 = new Array();
            this._header['days'][i]['columnsTotal'] = 1;
            this._header['days'][i]['columnsWidth'] = -1;
            this._header['days'][i]['dayAbbrev']    = phpr.nls.get(daysAbbrev[i]);
            this._header['days'][i]['date']         = this._weekDays[i];
        }
    },

    determineColumnsPerDay:function(content) {
        // Summary:
        //    This function designs the simultaneous events, settting how many columns will be shown for each user.

        var currentEventsNow = new Array();
        for (var row = 0; row < 48; row ++) {
            currentEventsNow[row] = new Array();
            for (var day = 0; day < 7; day ++) {
                currentEventsNow[row][this._weekDays[day]] = 0; // Example: currentEventsNow[0]['2009-01-01'] = 0
            }

            for (var event in content) {
                var eventInfo = this.getEventInfo(content[event]['startTime'],
                                                  content[event]['endTime'],
                                                  this._schedule[row]['hour']);

                if (eventInfo['type'] == this.EVENT_TIME_START || eventInfo['type'] == this.EVENT_TIME_INSIDE) {
                    currentEventsNow[row][content[event]['startDate']] ++;
                }
            }

            for (var day = 0; day < 7; day ++) {
                if (currentEventsNow[row][this._weekDays[day]] > this._header['days'][day]['columnsTotal']) {
                    this._header['days'][day]['columnsTotal'] = currentEventsNow[row][this._weekDays[day]];
                }
            }
        }

        this._header['columnsWidth'] = Math.floor((100 - this._widthHourColumn) / 7);
    },

    fillScheduleArrayStructure_part2:function() {
        // Summary:
        //    Continues creating the schedule array structure, supporting simultaneous events.

        for (var row = 0; row < 48; row ++) {
            for (var day = 0; day < 7; day ++) {
                this._schedule[row][day]['columns'] = new Array();
                var widthColumn = Math.floor(this._header['columnsWidth']
                                 / this._header['days'][day]['columnsTotal']);
                var totalColumns = this._header['days'][day]['columnsTotal'];
                for (var column = 0; column < totalColumns; column ++) {
                    this._schedule[row][day]['columns'][column]              = new Array();
                    this._schedule[row][day]['columns'][column]['occupied']  = false;
                    this._schedule[row][day]['columns'][column]['typeEvent'] = this.EVENT_NONE;
                    this._schedule[row][day]['columns'][column]['width']     = widthColumn;
                    if (totalColumns == 1) {
                        this._schedule[row][day]['columns'][column]['class'] = 'emptyCellSingle';
                    } else if (totalColumns > 1) {
                        if (column == 0) {
                            this._schedule[row][day]['columns'][column]['class'] = 'emptyCellLeft';
                        } else if (column == (totalColumns - 1)) {
                            this._schedule[row][day]['columns'][column]['class'] = 'emptyCellRight';
                        } else {
                            this._schedule[row][day]['columns'][column]['class'] = '';
                        }
                    }
                }
            }
        }
    },

    fillScheduleArrayData:function(content) {
        // Summary:
        //    Puts every event in the corresponding array and position.
        // Description:
        //    Receives the array of a day of the week, and the response from the DB and puts all the events of that
        // day in the appropriate position inside the array.

        for (var event in content) {
            var eventInfo = this.getEventInfo(content[event]['startTime'],
                                              content[event]['endTime'],
                                              null,
                                              content[event]['startDate']);

            if (eventInfo['range'] == this.EVENT_INSIDE_CHART) {
                var rowEventBegins   = eventInfo['quarterBeginning'];
                var rowEventFinishes = rowEventBegins + eventInfo['quartersDuration'];
                var day = eventInfo['weekDay'];

                // Find which column to use
                var useColumn = -1;
                var columnsTotalDay = this._header['days'][day]['columnsTotal'];

                for (column = 0; column < columnsTotalDay; column++) {
                    var useColumn = column;
                    for (var row = rowEventBegins; row < rowEventFinishes; row ++) {
                        if (this._schedule[row][day]['columns'][column]['occupied']) {
                            useColumn = -1;
                            break;
                        }
                    }
                    if (useColumn != -1) {
                        break
                    }
                }

                var notes = this.htmlEntities(content[event]['notes']);
                notes     = notes.replace('\n', '<br />');

                this._schedule[rowEventBegins][day]['columns'][useColumn]['occupied']         = true;
                this._schedule[rowEventBegins][day]['columns'][useColumn]['typeEvent']        = this.EVENT_BEGIN;
                this._schedule[rowEventBegins][day]['columns'][useColumn]['quartersDuration'] = eventInfo['quartersDuration'];
                this._schedule[rowEventBegins][day]['columns'][useColumn]['id']               = content[event]['id'];
                this._schedule[rowEventBegins][day]['columns'][useColumn]['title']            = this.htmlEntities(content[event]['title']);
                this._schedule[rowEventBegins][day]['columns'][useColumn]['startTime']        = this.formatTime(content[event]['startTime']);
                this._schedule[rowEventBegins][day]['columns'][useColumn]['endTime']          = this.formatTime(content[event]['endTime']);
                this._schedule[rowEventBegins][day]['columns'][useColumn]['notes']            = notes;
                this._schedule[rowEventBegins][day]['columns'][useColumn]['class']            = '';

                //For every next row that this event occupies
                for (var row = rowEventBegins + 1; row < rowEventFinishes; row++) {
                    this._schedule[row][day]['columns'][useColumn]['occupied']  = true;
                    this._schedule[row][day]['columns'][useColumn]['typeEvent'] = this.EVENT_CONTINUES;
                    this._schedule[row][day]['columns'][useColumn]['class']     = '';
                }

            } else if (eventInfo['range'] == this.EVENT_OUTSIDE_CHART) {
                this._furtherEvents['show'] = true;
                var nextPosition            = this._furtherEvents['events'].length;
                this._furtherEvents['events'][nextPosition]              = new Array();
                this._furtherEvents['events'][nextPosition]['id']        = content[event]['id'];
                this._furtherEvents['events'][nextPosition]['startDate'] = content[event]['startDate'];
                this._furtherEvents['events'][nextPosition]['startTime'] = this.formatTime(content[event]['startTime']);
                this._furtherEvents['events'][nextPosition]['endTime']   = this.formatTime(content[event]['endTime']);
                this._furtherEvents['events'][nextPosition]['title']     = content[event]['title'];
            }
        }
    }
});
