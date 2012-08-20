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

dojo.provide("phpr.Calendar2.ViewDayListSelf");

dojo.declare("phpr.Calendar2.ViewDayListSelf", phpr.Calendar2.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar2 Day List for the logged user (self)
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table

    // Variables that will be passed to the template for django to render it
    _furtherEvents :  [],
    _maxSimultEvents: 1,
    events:           [],

    afterConstructor: function() {
        // Summary:
        //    Loads the data from the database
        phpr.DataStore.addStore({url: this.url, noCache: true});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});
    },

    setUrl: function() {
        // Summary:
        //    Sets the url to get the data from
        this.url = 'index.php/' + phpr.module + '/index/jsonDayListSelf/date/' + this._date +
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

        // Render Export buttons?
        this.setExportButton(meta);

        var content = phpr.DataStore.getData({url: this.url});

        // Fill the structure and data of the main arrays
        this._schedule = new Array(23);
        this.fillScheduleArray();
        this.fillEventsArrays(content);

        var eventsAttr         = [];
        eventsAttr.borderWidth = this.EVENTS_BORDER_WIDTH;
        eventsAttr.divIdPre    = this.EVENTS_MAIN_DIV_ID;

        // All done, let's render the template

        phpr.viewManager.getView().gridContainer.set('content',
            phpr.fillTemplate("phpr.Calendar2.template.dayListSelf.html", {
                widthTable:           this._widthTable,
                widthHourColumn:      this._widthHourColumn,
                date:                 this._date,
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
        window.open('index.php/' + phpr.module + '/index/csvDayListSelf/nodeId/1/date/' + this._date +
            '/csrfToken/' + phpr.csrfToken);

        return false;
    }
});
