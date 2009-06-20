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

dojo.provide("phpr.Calendar.ViewDayListSelf");

dojo.declare("phpr.Calendar.ViewDayListSelf", phpr.Calendar.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar Day List for the logged user (self)
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table

    // Variables that will be passed to the template for django to render it
    _schedule:       Array(23),
    _furtherEvents : Array(),
    _maxSimultEvents: 1,

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
        //    Sets the url for get the data from
        this.url = phpr.webpath + "index.php/" + phpr.module + "/index/jsonDayListSelf/date/" + this._date;
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

        var content = phpr.DataStore.getData({url: this.url});

        // Fill the structure and data of the main arrays
        this.fillScheduleArrayStructurePart1();
        this.determineColumns(content);
        this.fillScheduleArrayStructurePart2(content);
        this.fillScheduleArrayEmptyCells();

        // All done, let's render the template
        this.render(["phpr.Calendar.template", "dayListSelf.html"], dojo.byId('gridBox'), {
            widthTable:           this._widthTable,
            widthHourColumn:      this._widthHourColumn,
            date:                 this.formatDate(this._date),
            schedule:             this._schedule,
            furtherEvents:        this._furtherEvents,
            furtherEventsMessage: phpr.nls.get('Further events')
        });
        dojo.publish('Calendar.connectMouseScroll');
    },

    exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        // Description:
        //    Open a new window in CSV mode
        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvDayListSelf/date/" + this._date);

        return false;
    },

    fillScheduleArrayStructurePart1:function() {
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

                this._schedule[row]         = new Array();
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

    determineColumns:function(content) {
        // Summary:
        //    This function designs the simultaneous events, settting how many columns will be shown.

        // Determine how many columns to show
        for (var i in this._schedule) {
            var currentEventNow = -1;
            for (var event in content) {
                var eventInfo = this.getEventInfo(content[event]['startDate'], content[event]['startTime'],
                                                  content[event]['endDate'], content[event]['endTime'],
                                                  this._date, this._schedule[i]['hour']);
                if (eventInfo['type'] == this.EVENT_TIME_START || eventInfo['type'] == this.EVENT_TIME_INSIDE) {
                    currentEventNow++;
                }
            }
            if (currentEventNow >= this._maxSimultEvents) {
                this._maxSimultEvents = currentEventNow + 1;
            }
        }
        var widthColumns = Math.floor((100 - this._widthHourColumn) / this._maxSimultEvents);

        // Create the columns arrays
        for (var row in this._schedule) {
            this._schedule[row]['columns'] = new Array();
            for (column=0; column < this._maxSimultEvents; column++) {
                this._schedule[row]['columns'][column]              = new Array();
                this._schedule[row]['columns'][column]['occupied']  = false;
                this._schedule[row]['columns'][column]['typeEvent'] = this.EVENT_NONE;
                this._schedule[row]['columns'][column]['width']     = widthColumns;
            }
        }
    },

    fillScheduleArrayStructurePart2:function(content) {
        // Summary:
        //    Continues creating the schedule array structure, supporting simultaneous events. Puts every event
        // somewhere in the arrays.

        this._furtherEvents['show']   = false;
        this._furtherEvents['events'] = new Array();

        for (var event in content) {
            var eventInfo = this.getEventInfo(content[event]['startDate'], content[event]['startTime'],
                                              content[event]['endDate'], content[event]['endTime'],
                                              this._date);
            if (eventInfo['range'] == this.SHOWN_INSIDE_CHART) {
                var eventBegins = eventInfo['halfBeginning'];

                // Find which column to use
                var useColumn = -1;
                for (column = 0; column < this._maxSimultEvents; column++) {
                    var useColumn = column;
                    for (var row = eventBegins; row < (eventBegins + eventInfo['halvesDuration']); row++) {
                        if (this._schedule[row]['columns'][column]['occupied']) {
                            useColumn = -1;
                            break;
                        }
                    }
                    if (useColumn != -1) {
                        break;
                    }
                }

                var notes = this.htmlEntities(content[event]['notes']);
                notes     = notes.replace('\n', '<br />');

                this._schedule[eventBegins]['columns'][useColumn]['occupied']       = true;
                this._schedule[eventBegins]['columns'][useColumn]['typeEvent']      = this.EVENT_BEGIN;
                this._schedule[eventBegins]['columns'][useColumn]['halvesDuration'] = eventInfo['halvesDuration'];
                this._schedule[eventBegins]['columns'][useColumn]['id']             = content[event]['id'];
                this._schedule[eventBegins]['columns'][useColumn]['title']          = this.htmlEntities(content[event]['title']);
                this._schedule[eventBegins]['columns'][useColumn]['time']           = eventInfo['time'];
                this._schedule[eventBegins]['columns'][useColumn]['notes']          = notes;

                //For every next row that this event occupies
                var rowThisEventFinishes = eventBegins + eventInfo['halvesDuration'] -1;
                for (var row = eventBegins + 1; row <= rowThisEventFinishes; row++) {
                    this._schedule[row]['columns'][useColumn]['occupied']  = true;
                    this._schedule[row]['columns'][useColumn]['typeEvent'] = this.EVENT_CONTINUES;
                }

            } else if (eventInfo['range'] == this.SHOWN_OUTSIDE_CHART) {
                this._furtherEvents['show'] = true;
                var nextPosition            = this._furtherEvents['events'].length;
                this._furtherEvents['events'][nextPosition]          = new Array();
                this._furtherEvents['events'][nextPosition]['id']    = content[event]['id'];
                this._furtherEvents['events'][nextPosition]['time']  = eventInfo['time'];
                this._furtherEvents['events'][nextPosition]['title'] = content[event]['title'];
            }
        }
    },

    fillScheduleArrayEmptyCells:function() {
        // Summary:
        //    Fills inside the schedule array the classes of the empty cells

        // For every row
        for (var row = 0; row < this._schedule.length; row++) {
            // For every column
            for (var column = 0; column < this._maxSimultEvents; column++) {
                // Is it an empty cell?
                if (!this._schedule[row]['columns'][column]['occupied']) {
                    // Is it the only column?
                    if (this._maxSimultEvents == 1) {
                        this._schedule[row]['columns'][column]['class'] = 'emptyCellSingle';
                        // Is it the bottom row?
                        if (row == this._schedule.length - 1) {
                            this._schedule[row]['columns'][column]['class'] += 'Bottom';
                        }
                    } else {
                        if (column == 0) {
                            this._schedule[row]['columns'][column]['class'] = 'emptyCellLeft';
                        } else if (column == (this._maxSimultEvents - 1)) {
                            this._schedule[row]['columns'][column]['class'] = 'emptyCellRight';
                        } else {
                            this._schedule[row]['columns'][column]['class'] = '';
                        }
                        if (row == this._schedule.length - 1) {
                            this._schedule[row]['columns'][column]['class'] += ' emptyCellBottom';
                        }
                    }
                }
            }
        }
    }
});
