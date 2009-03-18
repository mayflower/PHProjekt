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
        var dateString = this._date.getFullYear() + '-' + (this._date.getMonth() + 1) + '-' + this._date.getDate();
        this.url       = phpr.webpath + "index.php/" + phpr.module + "/index/jsonDayListSelf/date/" + dateString;
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

        var content         = phpr.DataStore.getData({url: this.url});
        var maxSimultEvents = 0;

        // Variables that will be passed to the template for django to render it
        var timeSquare          = new Array(47);
        var furtherEvents       = new Array();
        furtherEvents['show']   = false;
        furtherEvents['events'] = new Array();

        // Fill the main array with all the possible points in time for this day view
        // 8:00, 8:15, 8:30 and so on, until 19:45, and whether it is an even row or not.
        for (var hour = 8; hour < 20; hour++) {
            for (var quarter = 0; quarter < 4; quarter++) {
                var minute = quarter * 15;
                var row    = ((hour - 8) * 4) + quarter;

                timeSquare[row]         = new Array();
                timeSquare[row]['hour'] = this.formatTime(hour + ':' + minute);
                if (Math.floor(row / 2) == (row / 2)) {
                    // Even row
                    timeSquare[row]['even'] = true;
                } else {
                    // Odd row
                    timeSquare[row]['even'] = false;
                }
            }
        }

        // Determine how many columns to show
        var maxSimultEvents = 1;
        for (var i in timeSquare) {
            var currentEventNow = -1;
            for (var event in content) {
                var eventInfo = this.getEventInfo(content[event]['startTime'], content[event]['endTime'],
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
                timeSquare[row]['columns'][column]              = new Array();
                timeSquare[row]['columns'][column]['occupied']  = false;
                timeSquare[row]['columns'][column]['typeEvent'] = this.EVENT_NONE;
                timeSquare[row]['columns'][column]['width']     = widthColumns;
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
                timeSquare[eventBegins]['columns'][useColumn]['typeEvent']        = this.EVENT_BEGIN;
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
                    timeSquare[row]['columns'][useColumn]['typeEvent'] = this.EVENT_CONTINUES;
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
            widthTable:           this._widthTable,
            widthHourColumn:      this._widthHourColumn,
            timeSquare:           timeSquare,
            furtherEvents:        furtherEvents,
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
    }
});
