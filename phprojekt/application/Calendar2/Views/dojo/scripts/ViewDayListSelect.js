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

dojo.provide("phpr.Calendar2.ViewDayListSelect");

dojo.declare("phpr.Calendar2.ViewDayListSelect", phpr.Calendar2.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar2 Day List for a specific selection of users
    // Description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    _furtherEvents :  [],
    _maxSimultEvents: 1,
    events:           [],
    users:            [],

    afterConstructor: function() {
        // Summary:
        // Request header data
        var users           = this.users.join(",");
        this._headerDataUrl = 'index.php/' + phpr.module + '/index/jsonGetSpecificUsers/users/' + users;
        phpr.DataStore.addStore({url: this._headerDataUrl, noCache: true});
        phpr.DataStore.requestData({url: this._headerDataUrl, processData: dojo.hitch(this, "onLoadedHeader")});
    },

    onLoadedHeader: function() {
        // Summary:
        //    After the table header has been loaded, this function is called to load the events.
        phpr.DataStore.addStore({url: this.url, noCache: true});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoadedEvents")});
    },

    setUrl: function() {
        // Summary:
        //    Sets the url to get the data from
        // Description:
        //    Sets the url for get the data from
        var users = this.users.join(",");
        this.url  = 'index.php/' + phpr.module + '/index/jsonDayListSelect/date/' + this._date +
            '/users/' + users;
    },

    onLoadedEvents: function(dataContent) {
        // Summary:
        //    This function is called when the request to the DB is received
        // Description:
        //    It parses that json info and prepares an apropriate array so that the template can render
        // appropriately the TABLE html element.
        if (this._destroyed) {
            return;
        }

        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Render Export buttons?
        this.setExportButton(meta);

        var content = phpr.DataStore.getData({url: this.url});

        // Fill the structure and data of the main arrays
        this._schedule = new Array(23);
        this.fillHeaderArray();
        this.fillScheduleArray();
        this.fillEventsArrays(content);

        var eventsAttr         = [];
        eventsAttr.borderWidth = this.EVENTS_BORDER_WIDTH;
        eventsAttr.divIdPre    = this.EVENTS_MAIN_DIV_ID;

        // All done, let's render the template

        phpr.viewManager.getView().gridContainer.set('content',
            phpr.fillTemplate("phpr.Calendar2.template.dayListSelect.html", {
                widthTable:           this._widthTable,
                widthHourColumn:      this._widthHourColumn,
                date:                 this._date,
                header:               this._header,
                schedule:             this._schedule,
                events:               this.events,
                furtherEvents:        this._furtherEvents,
                furtherEventsMessage: phpr.nls.get('Further events'),
                eventsAttr:           eventsAttr
            }));

        dojo.publish('Calendar2.connectMouseScroll');
        dojo.publish('Calendar2.connectViewResize');

        this.setVarsAndDivs();
        this.classesSetup(true);
    },

    exportData: function() {
        // Summary:
        //    Open a new window in CSV mode
        var users = this.users.join(",");
        window.open('index.php/' + phpr.module + '/index/csvDayListSelect/nodeId/1/date/' + this._date +
            '/users/' + users + '/csrfToken/' + phpr.csrfToken);

        return false;
    },

    fillHeaderArray: function() {
        // Summary:
        //    Fills the header array with the main row of the table.
        var headerData               = phpr.DataStore.getData({url: this._headerDataUrl});
        this._header                 = []; // It is needed to explicitly empty the array
        this._header.columnsWidth = -1;
        this._header.users        = [];

        for (var user in headerData) {
            var userId  = parseInt(headerData[user].id, 10);
            var display = headerData[user].display;

            this._header.users[user]            = [];
            this._header.users[user].id      = userId;
            this._header.users[user].display = display;
        }
        this._header.columnsWidth = Math.floor((100 - this._widthHourColumn) / this.users.length);
    },

    getUserColumnPosition: function(userId) {
        // Summary:
        //    Receives the id of a user and returns the number for the column it occupies in the header array
        for (var i = 0; i < this._header.users.length; i ++) {
            if (this._header.users[i].id == userId) {
                return i;
            }
        }
    },

    fillEventsArrays: function(content) {
        // Summary:
        //    Parses and analyses 'content' contents and puts every event in 'events' array, if there are any multiple
        //    days event, they get splitted into each day events with a connection among them.
        // Note:
        //    This function is mostly copied from Calendar2_DefaultView. The
        //    column is not retrieved from the particpantId as in the old
        //    calendar. Instead, we iterate over the participants of each event.
        this.events                 = [];
        var furtherEventsTemp       = [];
        furtherEventsTemp.show   = false;
        furtherEventsTemp.events = [];

        // For each event received from the DB
        for (var event in content) {
            // Add the owner to the participants
            participants   = content[event].participants;

            for (var user in participants) {
                var userId = participants[user];
                var requested = false;
                for (var u in this.users) {
                    if (this.users[u] == userId) {
                        requested = true;
                    }
                }
                if (!requested) {
                    continue;
                }
                var eventsInfo     = [];
                var id             = content[event].id;
                var recurrenceId   = content[event].recurrenceId;
                var singleDayEvent = false;

                // Split datetime in date and time
                var dateTime = phpr.date.isoDatetimeTojsDate(content[event].start);
                content[event].startDate = phpr.date.getIsoDate(dateTime);
                content[event].startTime = phpr.date.getIsoTime(dateTime);
                dateTime = phpr.date.isoDatetimeTojsDate(content[event].end);
                content[event].endDate = phpr.date.getIsoDate(dateTime);
                content[event].endTime = phpr.date.getIsoTime(dateTime);

                // Process summary and note
                var summary = this.htmlEntities(content[event].summary);
                var comments = this.htmlEntities(content[event].comments);
                comments     = comments.replace('\n', '<br />');
                var column;
                if (this.main.dayListSelect !== null) {
                    column = this.getUserColumnPosition(userId);
                }

                // What kind of event is this one concerning multiple day events?
                if (content[event].startDate == content[event].endDate) {
                    // Single day event
                    singleDayEvent = true;
                } else {
                    // Multiple days event
                    var onlyDayString;
                    if (this.main.dayListSelf !== null || this.main.dayListSelect !== null) {
                        onlyDayString = this._date;
                    } else {
                        onlyDayString = null;
                    }
                    var eventsSplitted = this.splitMultDayEvent(content[event].startDate, content[event].startTime,
                        content[event].endDate, content[event].endTime, onlyDayString);

                    // The event has at least 1 minute inside the 8:00 to 20:00 grid?
                    if (eventsSplitted.eventShownInGrid) {
                        // Yes - It uses one or more day columns.
                        // For each day column (it can't be used 'for (var i in eventsSplitted)':
                        for (var i = 0; i < eventsSplitted.length; i ++) {
                            var eventSplitted = eventsSplitted[i];
                            if (eventSplitted.dayShownInGrid) {
                                // Obtain more info
                                eventSplitted.multDay    = true;
                                eventsInfo[i]               = this.processEventInfo(eventSplitted);
                                eventsInfo[i].multDayPos = eventSplitted.multDayPos;
                                eventsInfo[i].shown      = eventSplitted.shown;
                                if (eventSplitted.multDayPos == this.DATETIME_MULTIDAY_END) {
                                    eventsInfo[i].hasResizeHandler = true;
                                } else {
                                    eventsInfo[i].hasResizeHandler = false;
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
                    eventInfo.multDay   = false;
                    eventsInfo[0]          = this.processEventInfo(content[event], event);
                    eventsInfo[0].shown = true;
                }

                // Fill the 'events' class array
                var parent = -1;
                for (var i in eventsInfo) {
                    var eventInfo = eventsInfo[i];
                    // Events inside the grid
                    if (eventInfo.range == this.SHOWN_INSIDE_CHART) {
                        eventInfo.hasChanged = false;
                        parent = this.addGridEventToArray(eventInfo, id, recurrenceId, summary, comments, parent,
                                content[event].startDate, content[event].startTime, content[event].endDate,
                                content[event].endTime, column);
                    } else if (eventInfo.range == this.SHOWN_OUTSIDE_CHART) {
                        // Events outside the grid: located under it as textual strings
                        furtherEventsTemp.show = true;
                        var nextPosition          = furtherEventsTemp.events.length;

                        furtherEventsTemp.events[nextPosition]          = [];
                        furtherEventsTemp.events[nextPosition].id    = id;
                        furtherEventsTemp.events[nextPosition].time  = eventInfo.timeDescrip;
                        furtherEventsTemp.events[nextPosition].summary = summary;
                    }
                }
            }
        }

        this.updateSimultEventWidths();

        // Clean the repeated 'further events'. Copy the rest to the global variable
        this._furtherEvents = [];
        if (furtherEventsTemp.show) {
            this._furtherEvents.events = [];
            for (var event in furtherEventsTemp.events) {
                var repeated = false;
                for (var i in this._furtherEvents.events) {
                    if (this._furtherEvents.events[i].id == furtherEventsTemp.events[event].id) {
                        repeated = true;
                        break;
                    }
                }
                if (!repeated) {
                    this._furtherEvents.show                       = true;
                    var nextEvent                                     = this._furtherEvents.events.length;
                    this._furtherEvents.events[nextEvent]          = [];
                    this._furtherEvents.events[nextEvent].id    = furtherEventsTemp.events[event].id;
                    this._furtherEvents.events[nextEvent].time  = furtherEventsTemp.events[event].time;
                    this._furtherEvents.events[nextEvent].summary = furtherEventsTemp.events[event].summary;
                }
            }
        }
    }
});
