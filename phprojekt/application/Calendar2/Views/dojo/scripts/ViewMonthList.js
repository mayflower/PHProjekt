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

dojo.provide("phpr.Calendar2.ViewMonthList");

dojo.declare("phpr.Calendar2.ViewMonthList", phpr.Calendar2.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar2 Month List
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    _header:              Array(7),
    _scrollLastDirection: 0,

    COLOR_WEEKDAY:      '#FFFFFF',
    COLOR_WEEKEND:      '#EFEFEF',
    COLOR_TODAY:        '#DEEBF7',
    COLOR_OUT_OF_MONTH: '#DEDFDE',

    beforeConstructor: function() {
        // Summary:
        //    Calls the schedule array basic filling function, before constructor function
        this.fillScheduleArrayPart1();
    },

    afterConstructor: function() {
        // Summary:
        //    Loads the data from the database
        phpr.DataStore.addStore({url: this.url, noCache: true});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});
    },

    setUrl: function() {
        // Summary:
        //    Sets the url to get the data from
        this.url = 'index.php/' + phpr.module + '/index/jsonPeriodList/dateStart/' +
            this._schedule[0][0].date + '/dateEnd/' + this._schedule[this._schedule.length - 1][6].date +
            '/userId/' + this.main.getActiveUser().id;
    },

    onLoaded: function(dataContent) {
        // Summary:
        //    This function is called when the request to the DB is received
        // Description:
        //    It parses that json info and prepares an apropriate array so that the template can render
        //    appropriately the TABLE html element.
        if (this._destroyed) {
            return;
        }

        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Render export Button?
        this.setExportButton(meta);

        var content = phpr.DataStore.getData({url: this.url});
        this.fillHeaderArray();

        // Fill the main array with the data of the events
        this.fillScheduleArrayPart2(content);

        // All done, let's render the template

        phpr.viewManager.getView().gridContainer.set('content',
            phpr.fillTemplate("phpr.Calendar2.template.monthList.html", {
                widthTable: this._widthTable,
                header: this._header,
                schedule: this._schedule
            }
        ));

        dojo.publish('Calendar2.connectMouseScroll');
    },

    exportData: function() {
        // Summary:
        //    Opens a new window in CSV mode
        var dateTemp = phpr.date.isoDateTojsDate(this._date);
        dateTemp.setDate(1);
        var firstDayMonth = phpr.date.getIsoDate(dateTemp);
        var daysInMonth   = dojo.date.getDaysInMonth(dateTemp);
        dateTemp.setDate(daysInMonth);
        var lastDayMonth = phpr.date.getIsoDate(dateTemp);

        window.open('index.php/' + phpr.module + '/index/csvPeriodList/nodeId/1/dateStart/' +
            firstDayMonth + '/dateEnd/' + lastDayMonth + '/csrfToken/' + phpr.csrfToken);

        return false;
    },

    fillScheduleArrayPart1: function() {
        // Summary:
        //    Fills the schedule array with the basic structure and data of every day of the calendar month table.
        //    It includes not only the days of this month but the necessary days of the previous and next month in
        //    order to fill 4 or 6 week rows, from Monday to Sunday.
        var today = new Date();
        today     = phpr.date.getIsoDate(today);

        // First dimension is each row shown, the amount of rows depends on each month:
        this._schedule = [];

        var dateTemp    = phpr.date.isoDateTojsDate(this._date);
        var daysInMonth = dojo.date.getDaysInMonth(dateTemp);
        dateTemp.setDate(1);
        var firstDayDiff = dateTemp.getDay() - 1;
        if (firstDayDiff == -1) {
            firstDayDiff = 6;
        }
        var firstDayShown = dojo.date.add(dateTemp, 'day', - firstDayDiff);
        dateTemp.setDate(daysInMonth);
        var lastDayShown = dojo.date.add(dateTemp, 'day', (7 - dateTemp.getDay()));
        var totalRows    = (dojo.date.difference(firstDayShown, lastDayShown, 'day') + 1) / 7;

        // For every row
        for (var i = 0; i < totalRows; i ++) {
            this._schedule[i] = new Array(7);
            // For every day of the week
            for (var j = 0; j < 7; j ++) {
                this._schedule[i][j]         = [];
                dateTemp                     = dojo.date.add(firstDayShown, 'day', (i * 7) + j);
                this._schedule[i][j].day  = dateTemp.getDate();
                this._schedule[i][j].date = phpr.date.getIsoDate(dateTemp);
                if (this._schedule[i][j].date == today) {
                    this._schedule[i][j].color = this.COLOR_TODAY;
                } else if (((i === 0) && (this._schedule[i][j].day > 22)) ||
                        ((i > 3) && (this._schedule[i][j].day < 7))) {
                    this._schedule[i][j].color = this.COLOR_OUT_OF_MONTH;
                } else if (j < 5) {
                    this._schedule[i][j].color = this.COLOR_WEEKDAY;
                } else {
                    this._schedule[i][j].color = this.COLOR_WEEKEND;
                }
            }
        }
    },

    fillHeaderArray: function() {
        // Summary:
        //    Fills the header array with the main row of the table.
        this._header.columnsWidth = Math.floor((100 - this._widthHourColumn) / 7);
        this._header.days         = [];
        this._header.days[0]      = phpr.nls.get('Monday');
        this._header.days[1]      = phpr.nls.get('Tuesday');
        this._header.days[2]      = phpr.nls.get('Wednesday');
        this._header.days[3]      = phpr.nls.get('Thursday');
        this._header.days[4]      = phpr.nls.get('Friday');
        this._header.days[5]      = phpr.nls.get('Saturday');
        this._header.days[6]      = phpr.nls.get('Sunday');
    },

    fillScheduleArrayPart2: function(content) {
        // Summary:
        //    Puts every event in the corresponding array position.
        for (var event in content) {

            // Split datetime in date and time
            var dateTime = phpr.date.isoDatetimeTojsDate(content[event].start);
            content[event].startDate = phpr.date.getIsoDate(dateTime);
            content[event].startTime = phpr.date.getIsoTime(dateTime);
            dateTime = phpr.date.isoDatetimeTojsDate(content[event].end);
            content[event].endDate = phpr.date.getIsoDate(dateTime);
            content[event].endTime = phpr.date.getIsoTime(dateTime);
            var warning = '';
            var currentUserId = content[event].rights[this.main.getActiveUser().id].userId;
            if (currentUserId == content[event].ownerId) {
                // This is our event, let's add a warning if somebody has not
                // accepted (or beware, somebody rejected!) our invitation.
                for (var p in content[event].confirmationStatuses) {
                    var status = content[event].confirmationStatuses[p];
                    if (1 == status) { // Pending
                        warning = '<img src="css/themes/phprojekt/images/help.gif"' + ' title="' +
                            phpr.nls.get('Some participants have not accepted yet.') + '"/>';
                    } else if (3 == status) { //Rejected
                        warning = '<img src="css/themes/phprojekt/images/warning.png"' +
                            ' title="' + phpr.nls.get('Some participants have rejected your invitation.') + '"/>';
                        // Break to prevent warning from being overwritten if
                        // someone after this participant is pending.
                        break;
                    }
                }
            } else {
                // We're just invited. Let's remind the user if we didn't
                // respond yet.
                if (content[event].confirmationStatus == 1) {
                    warning = '<img src="css/themes/phprojekt/images/help.gif"' + ' title="' +
                        phpr.nls.get('You did not respond to this invitation yet.') + '"/>';
                }
            }

            for (var row in this._schedule) {
                for (var weekDay in this._schedule[row]) {
                    var eventInfo = this.getEventInfo(content[event].startDate, content[event].startTime,
                        content[event].endDate, content[event].endTime, this._schedule[row][weekDay].date);
                    if (eventInfo.range == this.SHOWN_INSIDE_CHART) {
                        if (typeof(this._schedule[row][weekDay].events) == 'undefined') {
                            this._schedule[row][weekDay].events = [];
                        }
                        var nextEvent    = this._schedule[row][weekDay].events.length;
                        var contentTitle = content[event].summary;
                        this._schedule[row][weekDay].events[nextEvent]            = [];
                        this._schedule[row][weekDay].events[nextEvent].id      = content[event].id;
                        this._schedule[row][weekDay].events[nextEvent].summary = this.htmlEntities(contentTitle);
                        this._schedule[row][weekDay].events[nextEvent].time    = eventInfo.time;
                        this._schedule[row][weekDay].events[nextEvent].start   = content[event].start;
                        this._schedule[row][weekDay].events[nextEvent].occurrence = content[event].occurrence;
                        this._schedule[row][weekDay].events[nextEvent].warning = warning;
                    }
                }
            }
        }

        var sortFunc = function(a, b) {
            a = phpr.date.isoDatetimeTojsDate(a.start);
            b = phpr.date.isoDatetimeTojsDate(b.start);
            return a - b;
        };

        for (var row in this._schedule) {
            for (var day in this._schedule[row]) {
                if (this._schedule[row][day].events !== undefined) {
                    this._schedule[row][day].events.sort(sortFunc);
                }
            }
        }
    },

    getEventInfo: function(/*string*/ eventStartDate_String, /*string*/ eventStartTime_String,
                          /*string*/ eventEndDate_String, /*string*/ eventEndTime_String,
                          /*string*/ momentAskedDate) {
        // Summary:
        //    Returns useful data about an event, used to create the schedule table.
        var result             = []; // The variable that will be returned
        var eventStart_Date    = new Date();  // Date and time the event starts
        var eventEnd_Date      = new Date();  // Date and time the event ends
        var momentAsked_Date   = new Date();  // momentAsked (with or without time)
        var eventStartDay_Date = new Date();  // Just the year/month/day of the event start
        var eventEndDay_Date   = new Date();  // Just the year/month/day of the event end

        // Convert strings variables into date ones
        temp                 = eventStartDate_String.split('-');
        var eventStartYear   = parseInt(temp[0], 10);
        var eventStartMonth  = parseInt(temp[1], 10);
        var eventStartDay    = parseInt(temp[2], 10);
        temp                 = eventEndDate_String.split('-');
        var eventEndYear     = parseInt(temp[0], 10);
        var eventEndMonth    = parseInt(temp[1], 10);
        var eventEndDay      = parseInt(temp[2], 10);
        var temp             = momentAskedDate.split('-');
        var momentAskedYear  = parseInt(temp[0], 10);
        var momentAskedMonth = parseInt(temp[1], 10);
        var momentAskedDay   = parseInt(temp[2], 10);

        eventStartDay_Date.setFullYear(eventStartYear, eventStartMonth - 1, eventStartDay);
        eventStartDay_Date.setHours(0, 0, 0, 0);
        eventEndDay_Date.setFullYear(eventEndYear, eventEndMonth - 1, eventEndDay);
        eventEndDay_Date.setHours(0, 0, 0, 0);
        momentAsked_Date.setFullYear(momentAskedYear, momentAskedMonth - 1, momentAskedDay);
        momentAsked_Date.setHours(0, 0, 0, 0);

        // Has the event to be shown for the day received (momentAskedDate)?
        if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) <= 0) &&
                (dojo.date.compare(eventEndDay_Date, momentAsked_Date) >= 0)) {
            // Yes
            result.range       = this.SHOWN_INSIDE_CHART;
            temp                  = eventStartTime_String.split(':');
            var eventStartHour    = parseInt(temp[0], 10);
            var eventStartMinutes = parseInt(temp[1], 10);
            temp                  = eventEndTime_String.split(':');
            var eventEndHour      = parseInt(temp[0], 10);
            var eventEndMinutes   = parseInt(temp[1], 10);
            temp                  = momentAskedDate.split('-');
            eventStart_Date.setFullYear(eventStartYear, eventStartMonth - 1, eventStartDay);
            eventStart_Date.setHours(eventStartHour, eventStartMinutes, 0, 0);
            eventEnd_Date.setFullYear(eventEndYear, eventEndMonth - 1, eventEndDay);
            eventEnd_Date.setHours(eventEndHour, eventEndMinutes, 0, 0);

            // Time description
            if ((dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0) &&
                    (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0)) {
                result.time = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_MIDDLE);
            } else if (dojo.date.compare(eventEndDay_Date, momentAsked_Date) > 0) {
                result.time = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_START, eventStartTime_String);
            } else if (dojo.date.compare(eventStartDay_Date, momentAsked_Date) < 0) {
                result.time = this.eventDateTimeDescrip(this.DATETIME_MULTIDAY_END, null, eventEndTime_String);
            } else {
                result.time = this.eventDateTimeDescrip(this.DATETIME_SHORT, eventStartTime_String,
                                                           eventEndTime_String);
            }
        } else {
            // No
            result.range = this.SHOWN_NOT;
        }

        return result;
    }
});
