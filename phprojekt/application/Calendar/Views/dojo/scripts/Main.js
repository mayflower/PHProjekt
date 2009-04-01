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
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Calendar.Main");

dojo.declare("phpr.Calendar.Main", phpr.Default.Main, {
    _date:               new Date(),
    _usersSelectionMode: false,
    _usersSelected:      Array(),

    constructor:function() {
        this.module = "Calendar";
        this.loadFunctions(this.module);
        dojo.subscribe(this.module + ".showFormFromList", this, "showFormFromList");
        dojo.subscribe(this.module + ".listViewClick", this, "listViewClick");
        dojo.subscribe(this.module + ".dayViewClick", this, "dayViewClick");
        dojo.subscribe(this.module + ".weekViewClick", this, "weekViewClick");
        dojo.subscribe(this.module + ".setDate", this, "setDate");
        dojo.subscribe(this.module + ".userSelfClick", this, "userSelfClick");
        dojo.subscribe(this.module + ".userSelectionClick", this, "userSelectionClick");
        dojo.subscribe(this.module + ".usersSelectionDoneClick", this, "usersSelectionDoneClick");
        dojo.subscribe(this.module + ".weekViewDayClick", this, "weekViewDayClick");
        dojo.subscribe(this.module + ".loadAppropriateList", this, "loadAppropriateList");

        this.gridWidget          = phpr.Calendar.Grid;
        this.dayListSelfWidget   = phpr.Calendar.ViewDayListSelf;
        this.dayListSelectWidget = phpr.Calendar.ViewDayListSelect;
        this.weekListWidget      = phpr.Calendar.ViewWeekList;
        this.formWidget          = phpr.Calendar.Form;
        this.treeWidget          = phpr.Calendar.Tree;
    },

    reload:function() {
        // summary:
        //    This function reloads the current module
        // description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session
        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        this.render(["phpr.Calendar.template", "mainContent.html"], dojo.byId('centerMainContent'), {
            view:       phpr.nls.get('View'),
            list:       phpr.nls.get('List'),
            day:        phpr.nls.get('Day'),
            week:       phpr.nls.get('Week'),
            changeDate: phpr.nls.get('Change date'),
            today:      phpr.nls.get('Today'),
            user:       phpr.nls.get('User'),
            self:       phpr.nls.get('Self'),
            selection:  phpr.nls.get('Selection')
        });

        dijit.byId("selectDate").attr('value', new Date(this._date.getFullYear(), this._date.getMonth(),
            this._date.getDate()));
        this.cleanPage();
        if (this._isGlobalModule(this.module)) {
            phpr.TreeContent.fadeOut();
            this.setSubGlobalModulesNavigation();
        } else {
            phpr.TreeContent.fadeIn();
            this.setSubmoduleNavigation();
        }
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
        this.loadAppropriateList();
    },

    loadAppropriateList:function() {
        // Summary:
        //    Loads the appropriate list of events
        if (this.dayListSelf) {
            this.loadDayListSelf();
        } else if (this.dayListSelect) {
            this.loadDayListSelect();
        } else if (this.weekList) {
            this.loadWeekList();
        } else {
            // Nothing else loaded? Then loads the default one
            this.loadGrid();
        }
        this.setSubmoduleNavigation();
    },

    loadGrid:function() {
        // summary:
        //   This function loads the Dojo Grid
        this.destroyOtherLists('grid');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('scheduleBar').style.display      = 'none';
        dojo.byId('scheduleGroupBar').style.display = 'none';

        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;
        this.grid = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },

    loadDayListSelf:function() {
        // summary:
        //    This function loads the Day List in Self mode
        this.destroyOtherLists('dayListSelf');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('scheduleBar').style.display      = 'inline';
        dojo.byId('scheduleGroupBar').style.display = 'inline';

        this._date       = dijit.byId('selectDate').attr('value');
        this.dayListSelf = new this.dayListSelfWidget(this, phpr.currentProjectId, this._date);
        this.highlightBarUserSelection();
    },

    loadDayListSelect:function() {
        // summary:
        //    This function loads the Day List in a Selection mode
        this.destroyOtherLists('dayListSelect');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('scheduleBar').style.display      = 'inline';
        dojo.byId('scheduleGroupBar').style.display = 'inline';

        this._date         = dijit.byId('selectDate').attr('value');
        this.dayListSelect = new this.dayListSelectWidget(this, phpr.currentProjectId, this._date, this._usersSelected);
        this.highlightBarUserSelection();
    },

    loadWeekList:function() {
        // summary:
        //    This function loads the Week List
        this.destroyOtherLists('weekList');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('scheduleBar').style.display      = 'inline';
        dojo.byId('scheduleGroupBar').style.display = 'none';

        this._date    = dijit.byId('selectDate').attr('value');
        this.weekList = new this.weekListWidget(this, phpr.currentProjectId, this._date);
    },

    showFormFromList:function(rowID) {
        // summary:
        //    This function opens an specific item clicked from the views
        this.publish("openForm", [rowID]);
    },

    listViewClick:function() {
        // Summary:
        //    List button clicked, loads the regular grid
        this.loadGrid();
        this.setSubmoduleNavigation();
    },

    dayViewClick:function() {
        // summary:
        //    This function loads the Day List with the entered date, if any.
        if (dijit.byId('selectDate').attr('value') != null) {
            if (!this._usersSelectionMode) {
                this.loadDayListSelf();
            } else {
                this.loadDayListSelect();
            }
            this.setSubmoduleNavigation();
        }
    },

    weekViewClick:function() {
        // summary:
        //    This function loads the Week List with the entered date, if any.
        if (dijit.byId('selectDate').attr('value') != null) {
            this.loadWeekList();
            this.setSubmoduleNavigation();
        }
    },

    setDate:function(day) {
        // summary
        //    This function is called by the buttons '<< Today >>' to load a specific date into the Day or Week List
        var PREVIOUS = 0;
        var TODAY    = 1;
        var NEXT     = 2;

        if (this.dayListSelf || this.dayListSelect) {
            var interval = 'day';
        } else if (this.weekList) {
            var interval = 'week';
        }

        switch (day) {
            case PREVIOUS:
                var dCurrentDate = dijit.byId('selectDate').attr('value');
                this._date       = dojo.date.add(dCurrentDate, interval, -1);
                break;
            case TODAY:
            default:
                this._date = new Date();
                break;
            case NEXT:
                var dCurrentDate = dijit.byId('selectDate').attr('value');
                this._date       = dojo.date.add(dCurrentDate, interval, 1);
                break;
        }
        dijit.byId("selectDate").attr('value', this._date);
        if (this.dayListSelf) {
            this.loadDayListSelf();
        } else if (this.dayListSelect) {
            this.loadDayListSelect();
        } else if (this.weekList) {
            this.loadWeekList();
        }
    },

    openForm:function(/*int*/ id, /*String*/ module, /*String*/ startDate, /*String*/ startTime) {
        // Summary:
        //    This function opens a new Detail View
        if (!dojo.byId('detailsBox')) {
            this.reload();
        }
        if (id == undefined) {
            var params = new Array();

            if (this.grid) {
                params['startDate'] = '';
                params['startTime'] = '08:00';
                params['endDate']   = '';
                params['endTime']   = '10:00';
            } else if (this.dayListSelf || this.dayListSelect || this.weekList) {
                if (startDate != undefined) {
                    params['startDate'] = startDate;
                    params['endDate']   = startDate;
                } else {
                    var tmpDate      = dijit.byId("selectDate").attr('value');
                    var selectedDate = tmpDate.getFullYear()
                        + '-'
                        + dojo.number.format(tmpDate.getMonth() + 1, {pattern: '00'})
                        + '-'
                        + dojo.number.format(tmpDate.getDate(), {pattern: '00'});
                    params['startDate'] = selectedDate;
                    params['endDate']   = selectedDate;
                }

                if (startTime == undefined) {
                    params['startTime'] = '08:00';
                    params['endTime']   = '10:00';
                } else {
                    params['startTime'] = startTime;
                    // Generate the End Time, 2 hours after the Start Time
                    var temp          = startTime.split(':');
                    var startHour     = parseInt(temp[0], 10);
                    var startMinutes  = parseInt(temp[1], 10);
                    startHour        += 2;
                    endTime           = dojo.number.format(startHour, {pattern: '00'}) + ':'
                        + dojo.number.format(startMinutes, {pattern: '00'});
                    params['endTime'] = endTime;
                }
            }
        }
        this.form = new this.formWidget(this, id, module, params);
    },

    highlightBarUserSelection:function() {
        // Summary:
        //    This function highlights setting to bold the button of user mode (self/selection) and unbolding the other
        if (!this._usersSelectionMode) {
            dojo.byId('selfBarButton').style.fontWeight      = 'bold';
            dojo.byId('selectionBarButton').style.fontWeight = '';
        } else {
            dojo.byId('selfBarButton').style.fontWeight      = '';
            dojo.byId('selectionBarButton').style.fontWeight = 'bold';
        }
    },

    userSelfClick:function() {
        // Summary:
        //    This function loads the corresponding view in 'self' mode
        if (this._usersSelectionMode) {
            this._usersSelectionMode = false;
            this.highlightBarUserSelection();
            if (dijit.byId('selectDate').attr('value') != null) {
               this.loadDayListSelf();
            }
        }
    },

    userSelectionClick:function() {
        // Summary:
        //    First function of the user selection window process, for the group view.
        // Description:
        //    Request the user list to the DB and then calls the next function of the process to show the selection
        // window.
        this.userStore = new phpr.Store.User();
        this.userStore.fetch(dojo.hitch(this, "selectorRender"));
    },

    selectorRender:function() {
        // Summary:
        //    Called after receiving the users list from the DB. Shows the user selection window for the group view.
        var userList = this.userStore.getList();

        phpr.destroyWidget('selectorContent');
        dojo.byId('selectorTitle').innerHTML = phpr.nls.get('User selection');
        dijit.byId('selectorDialog').attr('title', phpr.nls.get('Calendar'));

        this.render(["phpr.Calendar.template", "usersSelector.html"], dojo.byId('selectorContainer'), {
            label:           phpr.nls.get('Select users for the group view'),
            userList:        userList,
            done:            phpr.nls.get('Done'),
            noUsersSelected: phpr.nls.get('You have to select at least one user!')
        });
        if (this._usersSelected.length > 0) {
            dijit.byId('userList').setValue(this._usersSelected.join(','));
        }
        dijit.byId('selectorDialog').show();
    },

    usersSelectionDoneClick:function() {
        // Summary:
        //    Called once the users of the selection window have been selected.
        var userList = dijit.byId('userList').attr('value');
        if (userList.length == 0) {
            dojo.byId("usersSelectorError").style.visibility = 'visible';
            return;
        }
        this._usersSelectionMode = true;
        this.highlightBarUserSelection();
        dojo.byId("usersSelectorError").style.visibility = 'hidden';
        this._usersSelected = new Array();
        dijit.byId('selectorDialog').hide();

        // The userList array comes with lots and lots of string indexes apart from the number indexes (these last ones
        // are the correct ones). This seems to be a Dojo bug. So, here it will be picked up the only the ones that
        // matter.
        for (var i = 0; i < userList.length; i ++) {
            this._usersSelected[i] = userList[i];
        }
        this.loadDayListSelect();
    },

    weekViewDayClick:function(date) {
        // Summary:
        //    The header of every day in the week view has a link to this function to load the day list of that day.
        var temp  = date.split('-');
        var year  = temp[0];
        var month = temp[1];
        var day   = temp[2];

        this._date.setFullYear(year);
        this._date.setMonth(month - 1);
        this._date.setDate(day);

        dijit.byId("selectDate").attr('value', this._date);
        this._usersSelectionMode = false;
        this.loadDayListSelf();
    },

    destroyOtherLists:function(mode) {
        // Summary:
        //    Destroys the objects of the lists not being used
        if (mode != 'grid') {
            this.grid = null;
        }
        if (mode != 'dayListSelf') {
            this.dayListSelf = null;
        }
        if (mode != 'dayListSelect') {
            this.dayListSelect = null;
        }
        if (mode != 'weekList') {
            this.weekList = null;
        }
    },

    setSubmoduleNavigation:function() {
        // Description:
        //    This function is responsible for displaying the Navigation top bar of the Calendar
        //    Current submodules are: List, Day and Week.
        var moduleViews       = new Array();
        var dayListActive     = false;
        if (this.isListActive(this.dayListSelf) || this.isListActive(this.dayListSelect)) {
            dayListActive = true;
        }
        this.addModuleView(moduleViews, 'List', 'listViewClick', this.isListActive(this.grid));
        this.addModuleView(moduleViews, 'Day', 'dayViewClick', dayListActive);
        this.addModuleView(moduleViews, 'Week', 'weekViewClick', this.isListActive(this.weekList));

        var navigation ='<ul id="nav_main">';
        for (var i = 0; i < moduleViews.length; i++) {
            var liclass = '';
            if (moduleViews[i].activeTab) {
                liclass = 'class = active';
                }
            navigation += this.render(["phpr.Default.template", "navigation.html"], null, {
                moduleName :    'Calendar',
                moduleLabel:    moduleViews[i].label,
                liclass:        liclass,
                moduleFunction: moduleViews[i].functionName
            });
        }
        navigation += "</ul>";
        dojo.byId("subModuleNavigation").innerHTML = navigation;
        phpr.initWidgets(dojo.byId("subModuleNavigation"));
    },

    addModuleView:function(moduleViews, label, functionName, activeTab) {
        // Summary:
        //    Adds a specific view to the moduleViews array 
        var i                          = moduleViews.length;
        moduleViews[i]                 = new Array();
        moduleViews[i]['label']        = label;
        moduleViews[i]['functionName'] = functionName;
        moduleViews[i]['activeTab']    = activeTab;
    },

    isListActive:function(list) {
        // Summary
        //    Returns whether a specific list type is active or not
        if (list != undefined) {
            answer = true;
        } else {
            answer = false;
        }

        return answer;
    } 
});
