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
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */

dojo.provide("phpr.Calendar.ViewDayListSelect");

dojo.declare("phpr.Calendar.ViewDayListSelect", phpr.Calendar.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar Day List for a specific selection of users
    // Description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table
    _furtherEvents :  Array(),
    _maxSimultEvents: 1,
    events:           Array(),
    users:            Array(),

    afterConstructor:function() {
        // Summary:
        // Request header data
        var users           = this.users.join(",");
        this._headerDataUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetSpecificUsers/users/' + users;
        phpr.DataStore.addStore({url: this._headerDataUrl, noCache: true});
        phpr.DataStore.requestData({url: this._headerDataUrl, processData: dojo.hitch(this, "onLoadedHeader")});
    },

    onLoadedHeader:function() {
        // Summary:
        //    After the table header has been loaded, this function is called to load the events.
        phpr.DataStore.addStore({url: this.url, noCache: true});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoadedEvents")});
    },

    setUrl:function() {
        // Summary:
        //    Sets the url to get the data from
        // Description:
        //    Sets the url for get the data from
        var users = this.users.join(",");
        this.url  = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDayListSelect/date/' + this._date
            + '/users/' + users;
    },

    onLoadedEvents:function(dataContent) {
        // Summary:
        //    This function is called when the request to the DB is received
        // Description:
        //    It parses that json info and prepares an apropriate array so that the template can render
        // appropriately the TABLE html element.

        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Render Export buttons?
        this.setExportButton(meta);

        var content = phpr.DataStore.getData({url: this.url});

        // Fill the structure and data of the main arrays
        this._schedule = new Array(23);
        this.fillHeaderArray();
        this.fillScheduleArray();
        this.fillEventsArrays(content);

        var eventsAttr         = new Array();
        eventsAttr.borderWidth = this.EVENTS_BORDER_WIDTH;
        eventsAttr.divIdPre    = this.EVENTS_MAIN_DIV_ID;

        // All done, let's render the template
        this.render(["phpr.Calendar.template", "dayListSelect.html"], dojo.byId('gridBox'), {
            widthTable:           this._widthTable,
            widthHourColumn:      this._widthHourColumn,
            date:                 this._date,
            header:               this._header,
            schedule:             this._schedule,
            events:               this.events,
            furtherEvents:        this._furtherEvents,
            furtherEventsMessage: phpr.nls.get('Further events'),
            eventsAttr:           eventsAttr
        });

        this.main.connectMouseScroll();
        this.main.connectViewResize();

        this.setVarsAndDivs();
        this.classesSetup(true);
    },

    exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        var users = this.users.join(",");
        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvDayListSelect/nodeId/1/date/' + this._date
            + '/users/' + users + '/csrfToken/' + phpr.csrfToken);

        return false;
    },

    fillHeaderArray:function() {
        // Summary:
        //    Fills the header array with the main row of the table.
        var headerData               = phpr.DataStore.getData({url: this._headerDataUrl});
        this._header                 = new Array(); // It is needed to explicitly empty the array
        this._header['columnsWidth'] = -1;
        this._header['users']        = new Array();

        for (var user in headerData) {
            var userId  = parseInt(headerData[user]['id']);
            var display = headerData[user]['display'];

            this._header['users'][user]            = new Array();
            this._header['users'][user]['id']      = userId;
            this._header['users'][user]['display'] = display;
        }
        this._header['columnsWidth'] = Math.floor((100 - this._widthHourColumn) / this.users.length);
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
