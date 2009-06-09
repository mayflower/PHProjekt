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
    _furtherEvents: Array,
    _weekDays:      Array(7),

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
            widthTable:           this._widthTable,
            widthHourColumn:      this._widthHourColumn,
            header:               this._header,
            schedule:             this._schedule,
            furtherEvents:        this._furtherEvents,
            furtherEventsMessage: phpr.nls.get('Further events')
        });
    },

    exportData:function() {
        // Summary:
        //    Opens a new window in CSV mode
        // Description:
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

    fillScheduleArrayStructure_part1:function() {
        // Summary:
        //    This function fills the week days arrays with the rows for every half of hour.
        // Description:
        //     Fills the array with the header and all the possible points in time for this week view: 8:00, 8:30, 9:00
        // and so on, until 19:30. Each of that rows will have as many columns as days plus simultaneous events exist.
        // Also sets for every row whether it is even or not.
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
            this._header['days'][i]['columnsTotal'] = 1;
            this._header['days'][i]['dayAbbrev']    = daysAbbrev[i];
            this._header['days'][i]['date']         = this._weekDays[i];
        }
    },

    determineColumnsPerDay:function(content) {
        // Summary:
        //    This function designs the simultaneous events, settting how many columns will be shown for each user.
        var currentEventsNow = new Array();
        for (var row = 0; row < 24; row ++) {
            currentEventsNow[row] = new Array();

            for (var day in this._weekDays) {
                currentEventsNow[row][day] = 0;
                for (var event in content) {
                    var eventInfo = this.getEventInfo(content[event]['startDate'], content[event]['startTime'],
                                                      content[event]['endDate'], content[event]['endTime'],
                                                      this._weekDays[day], this._schedule[row]['hour']);
                    if (eventInfo['type'] == this.EVENT_TIME_START || eventInfo['type'] == this.EVENT_TIME_INSIDE) {
                        currentEventsNow[row][day] ++;
                    }
                }
                if (currentEventsNow[row][day] > this._header['days'][day]['columnsTotal']) {
                    this._header['days'][day]['columnsTotal'] = currentEventsNow[row][day];
                }
            }
        }
    },

    fillScheduleArrayStructure_part2:function() {
        // Summary:
        //    Continues creating the schedule array structure, supporting simultaneous events.
        for (var row = 0; row < 24; row ++) {
            for (var day = 0; day < 7; day ++) {
                this._schedule[row][day]['columns'] = new Array();
                var widthColumn  = Math.floor(this._header['columnsWidth'] /
                    this._header['days'][day]['columnsTotal']);
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
                    // Last row? Show bottom line
                    if (row == this._schedule.length - 1) {
                        this._schedule[row][day]['columns'][column]['class'] += ' emptyCellBottom';
                    }
                }
            }
        }
    },

    fillScheduleArrayData:function(content) {
        // Summary:
        //    Puts every event in the corresponding array position.

        furtherEventsTemp           = new Array();
        furtherEventsTemp['show']   = false;
        furtherEventsTemp['events'] = new Array();
        // All events IDs to be shown both in schedule and 'Further events'
        eventsToBeShown             = new Array();

        for (var event in content) {
            for (var day in this._weekDays) {
                var eventInfo = this.getEventInfo(content[event]['startDate'], content[event]['startTime'],
                                                    content[event]['endDate'], content[event]['endTime'],
                                                    this._weekDays[day]);
                if (eventInfo['range'] == this.SHOWN_INSIDE_CHART) {
                    var rowEventBegins   = eventInfo['halfBeginning'];
                    var rowEventFinishes = rowEventBegins + eventInfo['halvesDuration'];

                    // Find which column to use
                    var useColumn       = -1;
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
                    this._schedule[rowEventBegins][day]['columns'][useColumn]['occupied']       = true;
                    this._schedule[rowEventBegins][day]['columns'][useColumn]['typeEvent']      = this.EVENT_BEGIN;
                    this._schedule[rowEventBegins][day]['columns'][useColumn]['halvesDuration'] = eventInfo['halvesDuration'];
                    this._schedule[rowEventBegins][day]['columns'][useColumn]['id']             = content[event]['id'];
                    this._schedule[rowEventBegins][day]['columns'][useColumn]['title']          = this.htmlEntities(content[event]['title']);
                    this._schedule[rowEventBegins][day]['columns'][useColumn]['time']           = eventInfo['time'];
                    this._schedule[rowEventBegins][day]['columns'][useColumn]['notes']          = notes;
                    this._schedule[rowEventBegins][day]['columns'][useColumn]['class']          = '';

                    //For every next row that this event occupies
                    for (var row = rowEventBegins + 1; row < rowEventFinishes; row++) {
                        this._schedule[row][day]['columns'][useColumn]['occupied']  = true;
                        this._schedule[row][day]['columns'][useColumn]['typeEvent'] = this.EVENT_CONTINUES;
                        this._schedule[row][day]['columns'][useColumn]['class']     = '';
                    }
                    eventsToBeShown[eventsToBeShown.length] = content[event]['id'];

                } else if (eventInfo['range'] == this.SHOWN_OUTSIDE_CHART) {
                    furtherEventsTemp['show'] = true;
                    var nextPosition = furtherEventsTemp['events'].length;
                    furtherEventsTemp['events'][nextPosition]          = new Array();
                    furtherEventsTemp['events'][nextPosition]['id']    = content[event]['id'];
                    furtherEventsTemp['events'][nextPosition]['time']  = eventInfo['time'];
                    furtherEventsTemp['events'][nextPosition]['title'] = content[event]['title'];
                }
            }
        }

        // Clean the repeated 'further events'. Copy the rest to the global variable
        if (furtherEventsTemp['show']) {
            for (var event in furtherEventsTemp['events']) {
                var repeated = false;
                for (var i in eventsToBeShown) {
                    if (eventsToBeShown[i] == furtherEventsTemp['events'][event]['id']) {
                        repeated = true;
                        break;
                    }
                }
                if (!repeated) {
                    this._furtherEvents['show']                       = true;
                    eventsToBeShown[eventsToBeShown.length]           = furtherEventsTemp['events'][event]['id'];
                    var nextEvent                                     = this._furtherEvents['events'].length;
                    this._furtherEvents['events'][nextEvent]          = new Array();
                    this._furtherEvents['events'][nextEvent]['id']    = furtherEventsTemp['events'][event]['id'];
                    this._furtherEvents['events'][nextEvent]['time']  = furtherEventsTemp['events'][event]['time'];
                    this._furtherEvents['events'][nextEvent]['title'] = furtherEventsTemp['events'][event]['title'];
                }
            }
        }
    }
});
