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

dojo.provide("phpr.Calendar.DayListSelect");

dojo.declare("phpr.Calendar.DayListSelect", phpr.Component, {
    // Summary:
    //    Class for displaying a Calendar Day List for a specific selection of users
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    main:               null,
    id:                 0,
    _headerDataUrl:     null,
    url:                null,
    _tagUrl:            null,
    _date:              null,
    _widthTable:        0,
    _widthHourColumn:   8,
    _header:            Array(),
    _schedule:          Array(48),
    _users:             Array(),
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

    constructor:function(/*Object*/ main, /*Int*/ id, /*String*/ date, /*Array*/ users) {
        // Summary:
        //    Render the schedule table
        // Description:
        //    This function receives the list data from the server and renders the corresponding table
        this.main   = main;
        this.id     = id;
        this.url    = null;
        this._date  = date;
        this._users = users;
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

        // Request header data
        var users           = this._users.join(",");
        this._headerDataUrl = phpr.webpath + "index.php/" + phpr.module + "/index/jsonGetSpecificUsers/users/" + users;
        phpr.DataStore.addStore({url: this._headerDataUrl, noCache: true});
        phpr.DataStore.requestData({url: this._headerDataUrl, processData: dojo.hitch(this, "onLoadedHeader")});
    },

    setUrl:function() {
        // Summary:
        //    Sets the url to get the data from
        // Description:
        //    Sets the url for get the data from
        var dateString = this._date.getFullYear() + '-' + (this._date.getMonth() + 1) + '-' + this._date.getDate();
        var users = this._users.join(",");
        this.url = phpr.webpath + "index.php/" + phpr.module + "/index/jsonDayListSelect"
                   + "/date/" + dateString + "/users/" + users;
    },

    showTags:function() {
        // Summary:
        //    Draw the tags
        // Description:
        //    Draw the tags
        this._tagUrl = phpr.webpath + 'index.php/Default/Tag/jsonGetTags'; // Get the module tags
        phpr.DataStore.addStore({url: this._tagUrl});
        phpr.DataStore.requestData({url: this._tagUrl, processData: dojo.hitch(this, function() {
                this.publish("drawTagsBox", [phpr.DataStore.getData({url: this._tagUrl})]);
            })
        });
    },

    onLoadedHeader:function() {
        // Summary:
        //    After the table header has been loaded, this function is called to load the events.
        phpr.DataStore.addStore({url: this.url, noCache: true});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoadedEvents")});
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

    getEventInfo:function(/*string*/ eventStartTime_String, /*string*/ eventEndTime_String, /*string*/ askedTime) {
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

    onLoadedEvents:function(dataContent) {
        // Summary:
        //    After the events data has been loaded, this function is called to render the table..
        // Description:
        //    It parses that json info and prepares an apropriate array so that the template can render
        // the schedule TABLE html element.
        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Render export Button?
        this.setExportButton(meta);

        var content = phpr.DataStore.getData({url: this.url});
        this.fillHeaderArray();

        // Fill the structure of the main array
        this.fillScheduleArrayStructure_part1();
        this.determineColumnsPerUser(content);
        this.fillScheduleArrayStructure_part2();

        // Fill it with the data of the events
        this.fillScheduleArrayData(content);

        // All done, let's render the template
        this.render(["phpr.Calendar.template", "dayListSelect.html"], dojo.byId('gridBox'), {
            widthTable          : this._widthTable,
            widthHourColumn     : this._widthHourColumn,
            header              : this._header,
            schedule            : this._schedule
        });
    },

    exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        // Description:
        //    Open a new window in CSV mode
        var dateString = this._date.getFullYear() + '-' + (this._date.getMonth() + 1) + '-' + this._date.getDate();
        var users = this._users.join(",");
        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvDayListSelect"
                   + "/date/" + dateString + "/users/" + users);
        return false;
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this Day List table
        // Description:
        //    Delete the cache for this Day List table
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

    fillScheduleArrayStructure_part1:function() {
        // Summary:
        //    This function fills the week days arrays with the rows for every quarter of hour.
        // Description:
        //    Fills the array with the header and all the possible points in time for this day view: 8:00, 8:15, 8:30
        // and so on, until 19:45. Each of that rows will have as many columns as days plus simultaneous events exist

        for (var i = 0; i < 48; i ++) {
            this._schedule[i] = new Array(this._users.length);
            for (var j = 0; j < this._users.length; j ++) {
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

        var headerData               = phpr.DataStore.getData({url: this._headerDataUrl});
        this._header                 = new Array(); //It is needed to explicitly empty the array
        this._header['columnsWidth'] = -1;
        this._header['users']        = new Array();

        for (var user in headerData) {
            userId    = parseInt(headerData[user]['id']);
            lastname  = headerData[user]['lastname'];
            firstname = headerData[user]['firstname'];

            this._header['users'][user]                 = new Array();
            this._header['users'][user]['id']           = userId;
            this._header['users'][user]['text']         = lastname + ' ' + firstname.substr(0,1) + '.';
            this._header['users'][user]['columnsTotal'] = 1;
        }
    },

    determineColumnsPerUser:function(content) {
        // Summary:
        //    This function designs the simultaneous events, settting how many columns will be shown for each user.

        var currentEventsNow = new Array();
        for (var row = 0; row < 48; row ++) {
            currentEventsNow[row] = new Array();
            for (var user = 0; user < this._users.length; user ++) {
                currentEventsNow[row][user] = 0;
            }
            for (var event in content) {
                var userId = parseInt(content[event]['participantId']);
                var eventInfo = this.getEventInfo(content[event]['startTime'],
                                                  content[event]['endTime'],
                                                  this._schedule[row]['hour']);
                if (eventInfo['type'] == this.EVENT_TIME_START
                 || eventInfo['type'] == this.EVENT_TIME_INSIDE) {
                    currentEventsNow[row][this.getUserColumnPosition(userId)] ++;
                }
            }

            for (user = 0; user < this._users.length; user ++) {
                if (currentEventsNow[row][user] > this._header['users'][user]['columnsTotal']) {
                    this._header['users'][user]['columnsTotal'] = currentEventsNow[row][user];
                }
            }
        }
        this._header['columnsWidth'] = Math.floor((100 - this._widthHourColumn) / this._header['users'].length);
    },
    
    fillScheduleArrayStructure_part2:function() {
        // Summary:
        //    Continues creating the schedule array structure, supporting simultaneous events.

        for (var row = 0; row < 48; row ++) {
            for (var user = 0; user < this._header['users'].length; user ++) {
                this._schedule[row][user]['columns'] = new Array();
                var widthColumn = Math.floor(this._header['columnsWidth']
                                  / this._header['users'][user]['columnsTotal']);
                var totalColumns = this._header['users'][user]['columnsTotal'];
                for (var column = 0; column < totalColumns; column ++) {
                    this._schedule[row][user]['columns'][column]              = new Array();
                    this._schedule[row][user]['columns'][column]['occupied']  = false;
                    this._schedule[row][user]['columns'][column]['typeEvent'] = this.EVENT_NONE;
                    this._schedule[row][user]['columns'][column]['width']     = widthColumn;
                    if (totalColumns == 1) {
                        this._schedule[row][user]['columns'][column]['class'] = 'emptyCellSingle';
                    } else if (totalColumns > 1) {
                        if (column == 0) {
                            this._schedule[row][user]['columns'][column]['class'] = 'emptyCellLeft';
                        } else if (column == (totalColumns - 1)) {
                            this._schedule[row][user]['columns'][column]['class'] = 'emptyCellRight';
                        } else {
                            this._schedule[row][user]['columns'][column]['class'] = '';
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
        //    Receives the response from the DB and puts all the events of the selected users in the appropriate
        // position inside the schedule array.

        for (var event in content) {
            var eventInfo = this.getEventInfo(content[event]['startTime'],
                                              content[event]['endTime']);

            if (eventInfo['range'] == this.EVENT_INSIDE_CHART) {
                var rowEventBegins   = eventInfo['quarterBeginning'];
                var rowEventFinishes = rowEventBegins + eventInfo['quartersDuration'];
                var userId = parseInt(content[event]['participantId']);
                var user = this.getUserColumnPosition(userId);

                // Find which column to use
                var useColumn = -1;
                var columnsTotalDay = this._header['users'][user]['columnsTotal'];

                for (column = 0; column < columnsTotalDay; column++) {
                    var useColumn = column;
                    for (var row = rowEventBegins; row < rowEventFinishes; row ++) {
                        if (this._schedule[row][user]['columns'][column]['occupied']) {
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

                this._schedule[rowEventBegins][user]['columns'][useColumn]['occupied']         = true;
                this._schedule[rowEventBegins][user]['columns'][useColumn]['typeEvent']        = this.EVENT_BEGIN;
                this._schedule[rowEventBegins][user]['columns'][useColumn]['quartersDuration'] = eventInfo['quartersDuration'];
                this._schedule[rowEventBegins][user]['columns'][useColumn]['id']               = content[event]['id'];
                this._schedule[rowEventBegins][user]['columns'][useColumn]['title']            = this.htmlEntities(content[event]['title']);
                this._schedule[rowEventBegins][user]['columns'][useColumn]['startTime']        = this.formatTime(content[event]['startTime']);
                this._schedule[rowEventBegins][user]['columns'][useColumn]['endTime']          = this.formatTime(content[event]['endTime']);
                this._schedule[rowEventBegins][user]['columns'][useColumn]['notes']            = notes;
                this._schedule[rowEventBegins][user]['columns'][useColumn]['class']            = '';

                //For every next row that this event occupies
                for (var row = rowEventBegins + 1; row < rowEventFinishes; row++) {
                    this._schedule[row][user]['columns'][useColumn]['occupied']  = true;
                    this._schedule[row][user]['columns'][useColumn]['typeEvent'] = this.EVENT_CONTINUES;
                    this._schedule[row][user]['columns'][useColumn]['class']     = '';
                }

            } else if (eventInfo['range'] == this.EVENT_OUTSIDE_CHART) {
                // For the events out of schedule (not from 8:00 to 20:00).
                // Nothing programmed by the moment
            }
        }
    },

    getUserColumnPosition:function(userId) {
        // Summary:
        //    Receives the id of a user and returns the number for the column it occupies in the header array
        for (var i = 0; i < this._header['users'].length; i ++) {
            if (this._header['users'][i]['id'] == userId) {
                return i;
            }
        }
    }
});
