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

dojo.provide("phpr.Default.CalendarDayBasedList");

dojo.declare("phpr.Default.CalendarDayBasedList", phpr.Component, {
    // Summary:
    //    Parent class for displaying a Calendar Day Based List. Day based list: it means, not grid but Day List,
    // Week List, etc. This should be inherited by each respective JS view.
    // Description:
    //    This Class provides the basic variables and functions for the class that takes care of displaying the list
    // information we receive from our Server in a HTML table.

    main:                null,
    id:                  0,
    url:                 null,
    _tagUrl:             null,
    _date:               null,
     _widthTable:        0,
    _widthHourColumn:    8,
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

    getEventInfo:function(/*string*/ eventStartTime_String, /*string*/ eventEndTime_String, /*string*/ askedTime,
                          /*string*/ eventStartDate_String) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        // Description:
        //    Returns useful data about an event, used to create the schedule table. E.g.: whether it is inside or
        // outside the 8:00 to 20:00 range,  in what row (and maybe day) of the shown table should it start and end.
        // If the 'askedTime' parameter is set, then which of three possibilities happens: 1) That time matchs the
        // start time  2) Is just inside the period  3) It is outside that period.

        // IMPORTANT NOTE: because of this function having lots of time and date variables, I added the suffix
        // '_Date' to the ones of Date type format, for making all these no so difficult to understand.
        // Also, to just a few of the String variables, there was added the '_String' suffix, with the same purpose.

        var result                 = new Array(); // The variable that will be returned
        var scheduleStartTime_Date = new Date();
        var scheduleEndTime_Date   = new Date();

        if (eventStartDate_String != null) {
            // Just for the Week view: set the week day (0 to 6)
            for (var i = 0; i < 7; i ++) {
                if (this._weekDays[i] == eventStartDate_String) {
                    result['weekDay'] = i;
                }
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

    updateData:function() {
        // Summary:
        //    Deletes the cache for this List table
        // Description:
        //    Deletes the cache for this List table
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