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

dojo.provide("phpr.Calendar2.ViewWeekList");

dojo.declare("phpr.Calendar2.ViewWeekList", phpr.Calendar2.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar2 Week List
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    _header:              new Array(7),
    _furtherEvents:       [],
    _weekDays:            new Array(7),
    events:               [],
    _htmlEventDivsAmount: null,
    _cellDayHeight:       null,

    beforeConstructor: function() {
        // Summary:
        //    Calls the weekDays array creation function, before constructor function
        this.setWeekDays();
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
        this.url = 'index.php/' + phpr.module + '/index/jsonPeriodList/dateStart/' + this._weekDays[0] +
            '/dateEnd/' + this._weekDays[6] + '/userId/' + this.main.getActiveUser().id;
    },

    onLoaded: function(dataContent) {
        // Summary:
        //    This function is called when the request to the DB is received
        // Description:
        //    It parses that json info and prepares the appropriate arrays so that it can be rendered correctly the
        //    template and the events.
        if (this._destroyed) {
            return;
        }

        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Render Export buttons?
        this.setExportButton(meta);

        var content = phpr.DataStore.getData({url: this.url});

        this._schedule = new Array(24);
        this.fillHeaderArray();
        this.fillScheduleArray();
        this.fillEventsArrays(content);

        var eventsAttr         = [];
        eventsAttr.borderWidth = this.EVENTS_BORDER_WIDTH;
        eventsAttr.divIdPre    = this.EVENTS_MAIN_DIV_ID;

        // All done, let's render the template

        phpr.viewManager.getView().gridContainer.set('content',
            phpr.fillTemplate("phpr.Calendar2.template.weekList.html", {
                widthTable:           this._widthTable,
                widthHourColumn:      this._widthHourColumn,
                header:               this._header,
                schedule:             this._schedule,
                events:               this.events,
                furtherEvents:        this._furtherEvents,
                furtherEventsMessage: phpr.nls.get('Further events'),
                eventsAttr:           eventsAttr
            }
        ));

        dojo.publish('Calendar2.connectMouseScroll');
        dojo.publish('Calendar2.connectViewResize');

        this._htmlEventDivsAmount = this.events.length;

        this.setVarsAndDivs();
        this.classesSetup(true);
    },

    exportData: function() {
        // Summary:
        //    Opens a new window in CSV mode
        window.open('index.php/' + phpr.module + '/index/csvPeriodList/nodeId/1/dateStart/' +
                this._weekDays[0] + '/dateEnd/' + this._weekDays[6] + '/csrfToken/' + phpr.csrfToken);

        return false;
    },

    setWeekDays: function() {
        // Summary:
        //    Fills the weekDays array with all the dates of the selected week in string format.
        var selectedDate = phpr.date.isoDateTojsDate(this._date);
        var dayTemp;

        for (var i = 0; i < 7; i ++) {
            dayTemp           = dojo.date.add(selectedDate, 'day', i + 1 - selectedDate.getDay());
            this._weekDays[i] = phpr.date.getIsoDate(dayTemp);
        }
    },

    fillHeaderArray: function() {
        // Summary:
        //    Fills the header array with the main row of the table.
        this._header.columnsWidth = Math.floor((100 - this._widthHourColumn) / 7);
        this._header.days         = [];
        for (var i = 0; i < 7; i ++) {
            var index                            = (i + 1) < 7 ? i + 1 : 0;
            this._header.days[i]              = [];
            this._header.days[i].dayAbbrev = phpr.date.getShortTranslateWeekDay(index);
            this._header.days[i].date      = this._weekDays[i];
        }
    },

    toggleMultDaysDivs: function(index, visible) {
        // Summary:
        //    Makes it visible or invisible all the divs of a multiple days event but the one being dragged.

        var id = this.events[index].id;
        for (var i in this.events) {
            if (this.events[i] !== null && i != index && id == this.events[i].id) {
                // This is another div of received event!
                var mode;
                if (!visible) {
                    mode = 'hidden';
                } else {
                    mode = 'visible';
                }
                dojo.style(dojo.byId(this.EVENTS_MAIN_DIV_ID + i), 'visibility', mode);
            }
        }
    }
});
