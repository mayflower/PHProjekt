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

dojo.provide("phpr.Calendar.DayListSelf");

dojo.declare("phpr.Calendar.DayListSelf", phpr.Component, {
    // Summary:
    //    Class for displaying a Calendar Day List for the logged user (self)
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    main:             null,
    id:               0,
    url:              null,
    _tagUrl:          null,
    _date:            null,
    _widthTable:      0,
    _widthHourColumn: 8,
    // Constants used by the function getEventInfo:
    EVENT_TIME_START:    0,
    EVENT_TIME_INSIDE:   1,
    EVENT_TIME_OUTSIDE:  2,
    EVENT_INSIDE_CHART:  0,
    EVENT_OUTSIDE_CHART: 1,

    constructor:function(/*Object*/ main, /*Int*/ id, /*String*/ date) {
        // Summary:
        //    Render the schedule table
        // Description:
        //    This function receives the list data from the server and renders the corresponding table
        this.main  = main;
        this.id    = id;
        this.url   = null;
        this._date = date;

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
        //    Sets the url for get the data from
        var dateString = this._date.getFullYear() + '-' + (this._date.getMonth() + 1) + '-' + this._date.getDate();
        this.url       = phpr.webpath + "index.php/" + phpr.module + "/index/jsonDayListSelf/date/" + dateString;
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

    getEventInfo:function(/*string*/ start, /*string*/ end, /*string*/ askedTime) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        // Description:
        //    Returns useful data about an event, used to create the schedule table. E.g.: whether it is inside or
        // outside the 8:00 to 20:00 range,  in what row of the shown table should it start and end.
        // If the 'askedTime' parameter is set, then which of three possibilities happens: 1) That time matchs the
        // start time  2) Is just inside the period  3) It is outside that period.
        var result            = new Array();
        var scheduleStartDate = new Date();
        var scheduleEndDate   = new Date();

        scheduleStartDate.setHours(8);
        scheduleStartDate.setMinutes(0);
        scheduleStartDate.setSeconds(0);
        scheduleEndDate.setHours(20);
        scheduleEndDate.setMinutes(0);
        scheduleEndDate.setSeconds(0);

        // Convert strings into Date formats
        var temp         = start.split(':');
        var startHour    = parseInt(temp[0], 10);
        var startMinutes = parseInt(temp[1], 10);
        var temp         = end.split(':');
        var endHour      = parseInt(temp[0], 10);
        var endMinutes   = parseInt(temp[1], 10);

        // Round downwards the start time to the nearest quarter of hour
        if ((startMinutes/15) != Math.floor(startMinutes/15)) {
            startMinutes = Math.floor(startMinutes/15) * 15;
        }

        // Round upwards the end time to the nearest quarter of hour
        if ((endMinutes/15) != Math.ceil(endMinutes/15)) {
            endMinutes = Math.ceil(endMinutes/15) * 15;
            if (endMinutes == 60) {
                endHour    = endHour + 1;
                endMinutes = 0;
            }
        }

        var startDate = new Date();
        var endDate   = new Date();

        startDate.setHours(startHour);
        startDate.setMinutes(startMinutes);
        startDate.setSeconds(0);
        endDate.setHours(endHour);
        endDate.setMinutes(endMinutes);
        endDate.setSeconds(0);

        // Is the event completely out of range (before or after 8:00 to 20:00) ?
        if (((scheduleStartDate >= startDate) && (scheduleStartDate >= endDate))
            || ((scheduleEndDate <= startDate) && (scheduleEndDate <= endDate))) {
            result['range'] = this.EVENT_OUTSIDE_CHART;
            result['type']  = this.EVENT_TIME_OUTSIDE;
            return result;
        } else {
            result['range'] = this.EVENT_INSIDE_CHART;
        }

        // If start time happens before 8:00, the schedule must show it from the 8:00 row (but the text will show
        // the real info)
        if (startDate < scheduleStartDate) {
            startDate = scheduleStartDate;
        }

        // If end time is after 20:00, the schedule must show it until the 19:45 row inclusive (but the text will
        // show the real info)
        if (endDate > scheduleEndDate) {
            endDate = scheduleEndDate;
        }

        var quarterBeginning       = startDate.getTime() - scheduleStartDate.getTime();
        var duration               = endDate.getTime() - startDate.getTime();
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
            var askedTimeDate = new Date();
            askedTimeDate.setHours(askedTimeHour);
            askedTimeDate.setMinutes(askedTimeMinutes);
            askedTimeDate.setSeconds(0);

            // Perform the comparison
            if (startDate.getTime() == askedTimeDate.getTime()) {
                result['type'] = this.EVENT_TIME_START;
            } else if ((startDate.getTime() < askedTimeDate.getTime()) && (askedTimeDate.getTime() < endDate.getTime())) {
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

    onLoaded:function(dataContent) {
        // Summary:
        //    This function is called when the request to the DB is received
        // Description:
        //    It parses that json info and prepares an apropriate array so that the template can render
        // appropriately the TABLE html element.
        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Render export Button
        this.setExportButton(meta);

        // Constants used to define a calendar event time in comparison to a specific moment
        var EVENT_NONE      = 0;
        var EVENT_BEGIN     = 1;
        var EVENT_CONTINUES = 2;

        var content         = phpr.DataStore.getData({url: this.url});
        var maxSimultEvents = 0;

        // Variables that will be passed to the template for django to render it
        var timeSquare          = new Array(47);
        var furtherEvents       = new Array();
        furtherEvents['show']   = false;
        furtherEvents['events'] = new Array();

        // Fill the main array with all the possible points in time for this day view
        // 8:00, 8:15, 8:30 and so on, until 19:45
        var i = -1;
        for (j = 8; j < 20; j++) {
            for (var k = 0; k < 4; k++) {
                l = k * 15;
                i++;
                timeSquare[i]         = new Array();
                timeSquare[i]['hour'] = this.formatTime(j + ':' + l);
            }
        }

        // Determine how many columns to show
        var maxSimultEvents = 1;
        for (var i in timeSquare) {
            var currentEventNow = -1;
            for (var event in content) {
                var eventInfo = this.getEventInfo(content[event]['startTime'],
                                                  content[event]['endTime'],
                                                  timeSquare[i]['hour']);
                if (eventInfo['type'] == this.EVENT_TIME_START || eventInfo['type'] == this.EVENT_TIME_INSIDE) {
                    currentEventNow++;
                }
            }
            if (currentEventNow >= maxSimultEvents) {
                maxSimultEvents = currentEventNow + 1;
            }
        }
        var widthColumns = Math.floor((100 - this._widthHourColumn) / maxSimultEvents);

        // Create the columns arrays
        for (var row in timeSquare) {
            timeSquare[row]['columns'] = new Array();
            for (column=0; column < maxSimultEvents; column++) {
                timeSquare[row]['columns'][column]                = new Array();
                timeSquare[row]['columns'][column]['occupied']    = false;
                timeSquare[row]['columns'][column]['typeEvent']   = EVENT_NONE;
                timeSquare[row]['columns'][column]['widthColumn'] = widthColumns;
            }
        }

        // For every event, put it somewhere in the arrays
        for (var event in content) {
            var eventInfo = this.getEventInfo(content[event]['startTime'], content[event]['endTime']);

            if (eventInfo['range'] == this.EVENT_INSIDE_CHART) {
                var eventBegins = eventInfo['quarterBeginning'];

                // Find which column to use
                var useColumn = -1;
                for (column = 0; column < maxSimultEvents; column++) {
                    var useColumn = column;
                    for (var row = eventBegins; row < (eventBegins + eventInfo['quartersDuration']); row++) {
                        if (timeSquare[row]['columns'][column]['occupied']) {
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

                timeSquare[eventBegins]['columns'][useColumn]['occupied']         = true;
                timeSquare[eventBegins]['columns'][useColumn]['typeEvent']        = EVENT_BEGIN;
                timeSquare[eventBegins]['columns'][useColumn]['quartersDuration'] = eventInfo['quartersDuration'];
                timeSquare[eventBegins]['columns'][useColumn]['id']               = content[event]['id'];
                timeSquare[eventBegins]['columns'][useColumn]['title']            = this.htmlEntities(content[event]['title']);
                timeSquare[eventBegins]['columns'][useColumn]['startTime']        = this.formatTime(content[event]['startTime']);
                timeSquare[eventBegins]['columns'][useColumn]['endTime']          = this.formatTime(content[event]['endTime']);
                timeSquare[eventBegins]['columns'][useColumn]['notes']            = notes;

                //For every next row that this event occupies
                var rowThisEventFinishes = eventBegins + eventInfo['quartersDuration'] -1;
                for (var row = eventBegins + 1; row <= rowThisEventFinishes; row++) {
                    timeSquare[row]['columns'][useColumn]['occupied']  = true;
                    timeSquare[row]['columns'][useColumn]['typeEvent'] = EVENT_CONTINUES;
                }

            } else if (eventInfo['range'] == this.EVENT_OUTSIDE_CHART) {
                furtherEvents['show'] = true;
                var nextPosition      = furtherEvents['events'].length;
                furtherEvents['events'][nextPosition]              = new Array();
                furtherEvents['events'][nextPosition]['id']        = content[event]['id'];
                furtherEvents['events'][nextPosition]['startTime'] = this.formatTime(content[event]['startTime']);
                furtherEvents['events'][nextPosition]['endTime']   = this.formatTime(content[event]['endTime']);
                furtherEvents['events'][nextPosition]['title']     = content[event]['title'];
            }
        }

        // All done, let's render the template
        this.render(["phpr.Calendar.template", "dayListSelf.html"], dojo.byId('gridBox'), {
            widthTable          : this._widthTable,
            widthHourColumn     : this._widthHourColumn,
            timeSquare          : timeSquare,
            furtherEvents       : furtherEvents,
            furtherEventsMessage: phpr.nls.get('Further events')
        });
    },

    exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        // Description:
        //    Open a new window in CSV mode
        var dateString = this._date.getFullYear() + '-' + (this._date.getMonth() + 1) + '-' + this._date.getDate();
        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvDayListSelf/date/" + dateString);

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
    }
});
