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

dojo.provide("phpr.Calendar.ViewDayListSelect");

dojo.declare("phpr.Calendar.ViewDayListSelect", phpr.Calendar.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar Day List for a specific selection of users
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    _headerDataUrl: null,
    _header:        Array(),
    _schedule:      Array(24),
    _users:         Array(),

    afterConstructor:function() {
        // Summary:
        //    Starts the data loading process, requesting it from the database

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
        this.url  = phpr.webpath + "index.php/" + phpr.module + "/index/jsonDayListSelect/date/" + dateString
            + "/users/" + users;
    },

    onLoadedHeader:function() {
        // Summary:
        //    After the table header has been loaded, this function is called to load the events.
        phpr.DataStore.addStore({url: this.url, noCache: true});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoadedEvents")});
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
            widthTable:      this._widthTable,
            widthHourColumn: this._widthHourColumn,
            header:          this._header,
            schedule:        this._schedule
        });
    },

    exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        // Description:
        //    Open a new window in CSV mode
        var dateString = this._date.getFullYear() + '-' + (this._date.getMonth() + 1) + '-' + this._date.getDate();
        var users      = this._users.join(",");
        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvDayListSelect/date/" + dateString
            + "/users/" + users);

        return false;
    },

    fillScheduleArrayStructure_part1:function() {
        // Summary:
        //    This function fills the week days arrays with the rows for every half of hour.
        // Description:
        //    Fills the array with the users and all the possible points in time for this day view: 8:00, 8:30, 9:00
        // and so on, until 19:30. Each of that rows will have as many columns as users plus simultaneous events exist.
        // Also sets for every row whether it is even or not.

        for (var hour = 8; hour < 20; hour++) {
            for (var half = 0; half < 2; half++) {
                var minute = half * 30;
                var row    = ((hour - 8) * 2) + half;

                this._schedule[row] = new Array(this._users.length);
                for (var user = 0; user < this._users.length; user ++) {
                    this._schedule[row][user] = new Array();
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
        var headerData               = phpr.DataStore.getData({url: this._headerDataUrl});
        this._header                 = new Array(); //It is needed to explicitly empty the array
        this._header['columnsWidth'] = -1;
        this._header['users']        = new Array();

        for (var user in headerData) {
            var userId    = parseInt(headerData[user]['id']);
            var lastname  = headerData[user]['lastname'];
            var firstname = headerData[user]['firstname'];

            this._header['users'][user]                 = new Array();
            this._header['users'][user]['id']           = userId;
            this._header['users'][user]['text']         = lastname + ' ' + firstname.substr(0, 1) + '.';
            this._header['users'][user]['columnsTotal'] = 1;
        }
    },

    determineColumnsPerUser:function(content) {
        // Summary:
        //    This function designs the simultaneous events, settting how many columns will be shown for each user.
        var currentEventsNow = new Array();
        for (var row = 0; row < 24; row ++) {
            currentEventsNow[row] = new Array();
            for (var user = 0; user < this._users.length; user ++) {
                currentEventsNow[row][user] = 0;
            }
            for (var event in content) {
                var userId = parseInt(content[event]['participantId']);
                var eventInfo = this.getEventInfo(content[event]['startTime'], content[event]['endTime'],
                    this._schedule[row]['hour']);
                if (eventInfo['type'] == this.EVENT_TIME_START || eventInfo['type'] == this.EVENT_TIME_INSIDE) {
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
        for (var row = 0; row < 24; row ++) {
            for (var user = 0; user < this._header['users'].length; user ++) {
                this._schedule[row][user]['columns'] = new Array();
                var widthColumn  = Math.floor(this._header['columnsWidth'] /
                    this._header['users'][user]['columnsTotal']);
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
            var eventInfo = this.getEventInfo(content[event]['startTime'], content[event]['endTime']);

            if (eventInfo['range'] == this.EVENT_INSIDE_CHART) {
                var rowEventBegins   = eventInfo['halfBeginning'];
                var rowEventFinishes = rowEventBegins + eventInfo['halvesDuration'];

                var userId = parseInt(content[event]['participantId']);
                var user   = this.getUserColumnPosition(userId);

                // Find which column to use
                var useColumn       = -1;
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

                this._schedule[rowEventBegins][user]['columns'][useColumn]['occupied']       = true;
                this._schedule[rowEventBegins][user]['columns'][useColumn]['typeEvent']      = this.EVENT_BEGIN;
                this._schedule[rowEventBegins][user]['columns'][useColumn]['halvesDuration'] = eventInfo['halvesDuration'];
                this._schedule[rowEventBegins][user]['columns'][useColumn]['id']             = content[event]['id'];
                this._schedule[rowEventBegins][user]['columns'][useColumn]['title']          = this.htmlEntities(content[event]['title']);
                this._schedule[rowEventBegins][user]['columns'][useColumn]['startTime']      = this.formatTime(content[event]['startTime']);
                this._schedule[rowEventBegins][user]['columns'][useColumn]['endTime']        = this.formatTime(content[event]['endTime']);
                this._schedule[rowEventBegins][user]['columns'][useColumn]['notes']          = notes;
                this._schedule[rowEventBegins][user]['columns'][useColumn]['class']          = '';

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
