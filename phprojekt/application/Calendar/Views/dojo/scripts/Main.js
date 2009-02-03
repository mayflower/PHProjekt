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
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Calendar.Main");

dojo.declare("phpr.Calendar.Main", phpr.Default.Main, {
    _date: new Date(),

    constructor:function() {
        this.module = "Calendar";
        this.loadFunctions(this.module);
        dojo.subscribe(this.module + ".loadGrid", this, "loadGrid");
        dojo.subscribe(this.module + ".loadDayList", this, "loadDayList");
        dojo.subscribe(this.module + ".showFormFromList", this, "showFormFromList");
        dojo.subscribe(this.module + ".dayViewClick", this, "dayViewClick");
        dojo.subscribe(this.module + ".setDay", this, "setDay");
        this.gridWidget    = phpr.Calendar.Grid;
        this.dayListWidget = phpr.Calendar.DayList;
        this.formWidget    = phpr.Calendar.Form;
        this.treeWidget    = phpr.Calendar.Tree;
        this._listMode     = 'grid';
    },

    reload:function(mode, date) {
        // summary:
        //    This function reloads the current module
        // description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session
        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        this.render(["phpr.Calendar.template", "mainContent.html"],dojo.byId('centerMainContent') ,{
            view: phpr.nls.get('View'),
            list: phpr.nls.get('List'),
            day: phpr.nls.get('Day'),
            changeDate: phpr.nls.get('Change date'),
            today: phpr.nls.get('Today')
        });
        dijit.byId("selectDate").attr('value', new Date(this._date.getFullYear(), this._date.getMonth(), this._date.getDate()));
        this.cleanPage();
        if (this._isGlobalModule(this.module)) {
            this.setSubGlobalModulesNavigation();
        } else {
            this.setSubmoduleNavigation();
        }
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);

        if (mode != null) {
            this._listMode = mode;
        }
        if (date != null) {
            this._date = date;
        }
        if (this._listMode == 'grid') {
            this.loadGrid();
        } else if (this._listMode == 'dayList') {
            this.loadDayList();
        }
    },

    loadGrid:function() {
        // summary:
        //   This function loads the Dojo Grid
        this._listMode = 'grid';
        this.dayList   = null;
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('dayViewButtonBar').style.display = 'none';
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },

    loadDayList:function(date) {
        // summary:
        //    This function loads the Day List instead of the Dojo Grid
        this._listMode = 'dayList';
        this.grid      = null;
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('dayViewButtonBar').style.display = 'inline';
        if (date != null) {
            this._date = date;
        }
        this.dayList = new this.dayListWidget(this, phpr.currentProjectId, this._date);
    },

    showFormFromList:function(rowID) {
        // summary:
        //    This function opens an specific item clicked from the Day List view
        this.publish("openForm", [rowID]);
    },

    updateCacheData:function() {
        // summary:
        //    This function reload the grid place with the result of a search or a tag
        // description:
        //    The server return the found records and the function display it
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
        if (this.dayList) {
            this.dayList.updateData();
        }
    },

    dayViewClick:function() {
        // summary:
        //    This function loads the Day List with the entered date, if any
        if (dijit.byId('selectDate').attr('value') != null) {
            this.loadDayList(dijit.byId('selectDate').attr('value'));
        }
    },

    setDay:function(day) {
        // summary
        //    This function is called by the buttons '<< Today >>' to load a specific day into the Day List
        var DAY_PREVIOUS = 0;
        var DAY_TODAY    = 1;
        var DAY_NEXT     = 2;

        switch (day) {
            case DAY_PREVIOUS:
                var dCurrentDate = dijit.byId('selectDate').attr('value');
                this._date       = dojo.date.add(dCurrentDate, 'day', -1);
                break;
            case DAY_TODAY:
            default:
                this._date = new Date();
                break;
            case DAY_NEXT:
                var dCurrentDate = dijit.byId('selectDate').attr('value');
                this._date       = dojo.date.add(dCurrentDate, 'day', 1);
                break;
        }
        dijit.byId("selectDate").attr('value', this._date);
        this.loadDayList(this._date);
    },

    openForm:function(/*int*/ id, /*String*/ module, /*String*/ startTime) {
        // Summary:
        //    This function opens a new Detail View
        if (!dojo.byId('detailsBox')) {
            this.reload();
        }
        if (id == undefined) {
            var params = new Array();

            if (this._listMode == 'grid') {
                params['startDate'] = '';
                params['startTime'] = '08:00';
                params['endTime']   = '10:00';

            } else if (this._listMode == 'dayList') {
                var tmpDate      = dijit.byId("selectDate").attr('value');
                var selectedDate = tmpDate.getFullYear()
                    + '-'
                    + dojo.number.format(tmpDate.getMonth() + 1, {pattern: '00'})
                    + '-'
                    + dojo.number.format(tmpDate.getDate(), {pattern: '00'});
                params['startDate'] = selectedDate;

                if (startTime == undefined) {
                    params['startTime'] = '08:00';
                    params['endTime']   = '10:00';

                } else {
                    params['startTime'] = startTime;
                    // Generate the End Time, 2 hours after the Start Time
                    var temp         = startTime.split(':');
                    var startHour    = parseInt(temp[0], 10);
                    var startMinutes = parseInt(temp[1], 10);
                    startHour += 2;
                    endTime = dojo.number.format(startHour, {pattern: '00'}) + ':' + dojo.number.format(startMinutes, {pattern: '00'});
                    params['endTime'] = endTime;
                }
            }
        }
        this.form = new this.formWidget(this, id, module, params);
    }
});
