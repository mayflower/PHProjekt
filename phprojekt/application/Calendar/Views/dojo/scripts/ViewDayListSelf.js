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
    _furtherEvents :  Array(),
    _maxSimultEvents: 1,
    events:           Array(),

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

        // Render Export and Save buttons?
        this.setExportButton(meta);
        this.setSaveChangesButton(meta);

        var content = phpr.DataStore.getData({url: this.url});

        // Fill the structure and data of the main arrays
        this._schedule = new Array(23);
        this.fillScheduleArray();
        this.fillEventsArrays(content);

        var eventsAttr         = new Array();
        eventsAttr.borderWidth = this.EVENTS_BORDER_WIDTH;
        eventsAttr.divIdPre    = this.EVENTS_MAIN_DIV_ID;

        // All done, let's render the template
        this.render(["phpr.Calendar.template", "dayListSelf.html"], dojo.byId('gridBox'), {
            widthTable:           this._widthTable,
            widthHourColumn:      this._widthHourColumn,
            schedule:             this._schedule,
            events:               this.events,
            furtherEvents:        this._furtherEvents,
            furtherEventsMessage: phpr.nls.get('Further events'),
            eventsAttr:           eventsAttr
        });

        dojo.publish('Calendar.connectMouseScroll');

        this.setVarsAndDivs();
        this.classesSetup(true);
    },

    exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        // Description:
        //    Open a new window in CSV mode
        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvDayListSelf/date/" + this._date);

        return false;
    }
});
