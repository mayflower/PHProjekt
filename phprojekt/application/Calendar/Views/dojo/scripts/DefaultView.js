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
    _tagUrl:             null,
    _date:               null,
    _widthTable:         0,
    _widthHourColumn:    8,

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
    DATETIME_LONG:            0,
    DATETIME_SHORT:           1,
    DATETIME_MULTIDAY_START:  2,
    DATETIME_MULTIDAY_MIDDLE: 3,
    DATETIME_MULTIDAY_END:    4,

    constructor:function(/*Object*/ main, /*Int*/ id, /*Date*/ date, /*Array*/ users) {
        // Summary:
        //    Render the schedule table
        // Description:
        //    This function receives the list data from the server and renders the corresponding table
        this.main  = main;
        this.id    = id;
        this.url   = null;
        this._date = date;

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

    getEventInfo:function(/*string*/ eventStartDate_String, /*string*/ eventStartTime_String,
                          /*string*/ eventEndDate_String, /*string*/ eventEndTime_String,
                          /*string*/ momentAskedDate, /*string*/ momentAskedTime) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        // Description:
        //    Returns useful data about an event, used to create the schedule table. E.g.: whether it is inside or
        // outside the 8:00 to 20:00 range, in what row (and maybe day) of the shown table should it start and end.
        // If the 'momentAskedTime' optional parameter is set, then one of three possibilities happens and is informed:
        // 1) The event start time matchs that start time
        // or 2) The moment asked is inside the event period but doesn't match the event start time
        // or 3) The moment asked is outside the event time
        // Note:
        //    Because of this function having lots of time and date variables, I added the suffix '_Date' to the ones of
        // Date type format, for making all these no so difficult to understand. Also, to just a few of the String
        // variables, there was added the '_String' suffix, with the same purpose.

        var result             = new Array();  // The variable that will be returned

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
        if (momentAskedTime != null) {
            var temp               = momentAskedTime.split(':');
            var momentAskedHour    = parseInt(temp[0], 10);
            var momentAskedMinutes = parseInt(temp[1], 10);
        }

        // Round downwards the event start time to the nearest half of hour
        if ((eventStartMinutes/30) != Math.floor(eventStartMinutes/30)) {
            eventStartMinutes = Math.floor(eventStartMinutes/30) * 30;
        }

        // Round upwards the event end time to the nearest half of hour
        if ((eventEndMinutes/30) != Math.ceil(eventEndMinutes/30)) {
            eventEndMinutes = Math.ceil(eventEndMinutes/30) * 30;
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
        if (momentAskedTime != null) {
            momentAsked_Date.setHours(momentAskedHour, momentAskedMinutes, 0, 0);
        } else {
            momentAsked_Date.setHours(0, 0, 0, 0);
        }

        if (momentAskedTime != null) {
            // Compare the event start date and time with the momentAsked
            if (dojo.date.compare(eventStart_Date, momentAsked_Date) == 0) {
                result['type'] = this.EVENT_TIME_START;
            } else if ((dojo.date.compare(eventStart_Date, momentAsked_Date) < 0)
                && (dojo.date.compare(eventEnd_Date, momentAsked_Date) >= 0)) {
                result['type'] = this.EVENT_TIME_INSIDE
            } else {
                result['type'] = this.EVENT_TIME_OUTSIDE;
            }
        } else {
            // Determine if the event has to be shown for the day received (momentAskedDate). If so, also define:
            // 1) Whether it has to be inside or outside the chart.
            // 2) If it is inside the chart, in which row it has to begin, and how many rows it lasts.
            if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) <= 0)
                && (dojo.date.compare(eventEndDay_Date, momentAsked_Date) >= 0)) {
                // Shown
                var startsBeforeScheduleEnds = false;
                var endsAfterScheduleBegins  = false;
                if (dojo.date.compare(eventStart_Date, scheduleEnd_Date) < 0) {
                    startsBeforeScheduleEnds = true;
                }
                if (dojo.date.compare(eventEnd_Date, scheduleStart_Date) >= 0) {
                    endsAfterScheduleBegins = true
                }

                if (startsBeforeScheduleEnds && endsAfterScheduleBegins) {
                    result['range'] = this.SHOWN_INSIDE_CHART;
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
                    if (this.main.dayListSelf != null || this.main.dayListSelect != null) {
                        if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0)
                            || (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0)) {
                            result['time'] = this.eventDateTimeDescrip(this.DATETIME_LONG,
                                             eventStartTime_String, eventEndTime_String,
                                             eventStartDate_String, eventEndDate_String);
                        } else {
                            result['time'] = this.eventDateTimeDescrip(this.DATETIME_SHORT, eventStartTime_String,
                                                                       eventEndTime_String);
                        }
                    } else if (this.main.weekList != null || this.main.monthList != null) {
                        if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0)
                            && (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0)) {
                            result['time'] = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_MIDDLE);
                        } else if (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0) {
                            result['time'] = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_START,
                                                                       eventStartTime_String);
                        } else if (dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0) {
                            result['time'] = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_END, null,
                                                                       eventEndTime_String);
                        } else {
                            result['time'] = this.eventDateTimeDescrip(this.DATETIME_SHORT, eventStartTime_String,
                                                                       eventEndTime_String);
                        }
                    }

                    var halfBeginning        = eventStart_Date.getTime() - scheduleStart_Date.getTime();
                    var duration             = eventEnd_Date.getTime() - eventStart_Date.getTime();
                    result['halfBeginning']  = Math.floor(halfBeginning / (1000 * 60 * 30));
                    result['halvesDuration'] = Math.floor(duration / (1000 * 60 * 30));

                } else {
                    result['range']         = this.SHOWN_OUTSIDE_CHART;

                    // Date-time description
                    if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0)
                        || (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0)) {
                        result['time'] = this.eventDateTimeDescrip(this.DATETIME_LONG, eventStartTime_String,
                                                                   eventEndTime_String, eventStartDate_String,
                                                                   eventEndDate_String);
                    } else if (this.main.weekList != null) {
                        result['time'] = this.eventDateTimeDescrip(this.DATETIME_LONG, eventStartTime_String,
                                                                   eventEndTime_String, eventStartDate_String,
                                                                   eventEndDate_String);
                    } else {
                        result['time'] = this.eventDateTimeDescrip(this.DATETIME_SHORT, eventStartTime_String,
                                                                   eventEndTime_String);
                    }
                }
            } else {
                result['range'] = this.SHOWN_NOT;
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
        //    Creates the appropriate datetime event description according the mode requested
        var description;
        switch (mode) {
            case this.DATETIME_LONG:
            default:
                description = startDate + ' ' + this.formatTime(startTime) + ' - ' + endDate + ' '
                    + this.formatTime(endTime) + '<br />';
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
        var temp  = this._date.split('-');
        var year  = parseInt(temp[0], 10);
        var month = parseInt(temp[1], 10);
        var day   = parseInt(temp[2], 10);
        var date  = new Date(year, month - 1, day);
        return date;
    }
});
